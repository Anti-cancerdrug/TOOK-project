<?php
/**
 * TOOK Project - 공통 함수 모음
 */

// 세션 시작 (아직 시작 안했으면)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 로그인 여부 확인
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * 로그인 필수 페이지 체크
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/member/login.php');
        exit;
    }
}

/**
 * 현재 로그인한 사용자 정보 가져오기
 */
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT uid, email, nickname, profile_image, bio FROM user WHERE uid = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * XSS 방지용 HTML 이스케이프
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF 토큰 생성
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF 토큰 검증
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 성공 메시지 설정
 */
function set_success_message($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * 에러 메시지 설정
 */
function set_error_message($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * 플래시 메시지 가져오기 (한 번만 표시)
 */
function get_flash_message() {
    $message = null;
    $type = null;
    
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        $type = 'success';
        unset($_SESSION['success_message']);
    } elseif (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        $type = 'error';
        unset($_SESSION['error_message']);
    }
    
    if ($message) {
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * 파일 업로드 처리
 */
function upload_file($file, $target_dir, $allowed_types = ALLOWED_IMAGE_TYPES) {
    // 파일이 업로드되었는지 확인
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 실패'];
    }
    
    // 파일 크기 확인
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => '파일 크기가 너무 큽니다. (최대 5MB)'];
    }
    
    // 파일 타입 확인
    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
    }
    
    // 안전한 파일명 생성
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_path = $target_dir . $filename;
    
    // 디렉토리 생성 (없으면)
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // 파일 이동
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => '파일 저장 실패'];
}

/**
 * 상대 시간 표시 (예: 3분 전, 2시간 전)
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return '방금 전';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '분 전';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '시간 전';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . '일 전';
    } else {
        return date('Y-m-d', $timestamp);
    }
}

/**
 * 페이지네이션 생성
 */
function pagination($current_page, $total_items, $items_per_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // 이전 페이지
    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '">이전</a>';
    }
    
    // 페이지 번호
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<a href="' . $base_url . '?page=' . $i . '" class="' . $active . '">' . $i . '</a>';
    }
    
    // 다음 페이지
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '">다음</a>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * URL 리다이렉트
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
?>