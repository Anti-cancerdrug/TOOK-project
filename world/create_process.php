<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// 로그인 확인
require_login();

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/world/create.php');
}

// CSRF 토큰 검증
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/world/create.php');
}

$current_user = get_logged_in_user();

// 입력값 받기
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_public = isset($_POST['is_public']) ? 1 : 0;
$genres = $_POST['genres'] ?? [];

// 유효성 검사
$errors = [];

if (empty($title)) {
    $errors[] = '세계관 제목을 입력해주세요.';
} elseif (mb_strlen($title) > 100) {
    $errors[] = '제목은 100자 이내로 입력해주세요.';
}

if (!empty($description) && mb_strlen($description) > 1000) {
    $errors[] = '설명은 1000자 이내로 입력해주세요.';
}

if (count($genres) > 3) {
    $errors[] = '장르는 최대 3개까지 선택할 수 있습니다.';
}

// 에러가 있으면 돌아가기
if (!empty($errors)) {
    set_error_message(implode('<br>', $errors));
    redirect(BASE_URL . '/world/create.php');
}

// 썸네일 업로드 처리
$thumbnail = DEFAULT_WORLD_THUMBNAIL;

if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $upload_result = upload_file(
        $_FILES['thumbnail'],
        UPLOAD_PATH . 'thumbnails/',
        ALLOWED_IMAGE_TYPES
    );
    
    if ($upload_result['success']) {
        $thumbnail = $upload_result['filename'];
    } else {
        set_error_message($upload_result['message']);
        redirect(BASE_URL . '/world/create.php');
    }
}

// 세계관 생성
try {
    $pdo->beginTransaction();
    
    // 1. world 테이블에 삽입
    $stmt = $pdo->prepare("
        INSERT INTO world (title, description, thumbnail, is_public) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $thumbnail, $is_public]);
    
    $world_id = $pdo->lastInsertId();
    
    // 2. world_admin 테이블에 소유자로 등록
    $stmt = $pdo->prepare("
        INSERT INTO world_admin (wid, uid, role) 
        VALUES (?, ?, 1)
    ");
    $stmt->execute([$world_id, $current_user['uid']]);
    
    // 3. 장르 연결
    if (!empty($genres)) {
        $stmt = $pdo->prepare("INSERT INTO world_genre (wid, gid) VALUES (?, ?)");
        foreach ($genres as $genre_id) {
            $stmt->execute([$world_id, $genre_id]);
        }
    }
    
    $pdo->commit();
    
    set_success_message('세계관이 성공적으로 생성되었습니다! 🎉');
    redirect(BASE_URL . '/world/view.php?wid=' . $world_id);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("World Create Error: " . $e->getMessage());
    set_error_message('세계관 생성 중 오류가 발생했습니다. 다시 시도해주세요.');
    redirect(BASE_URL . '/world/create.php');
}
?>