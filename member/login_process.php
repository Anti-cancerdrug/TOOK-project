<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/member/login.php');
}

// CSRF 토큰 검증
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/member/login.php');
}

// 입력값 받기
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// 유효성 검사
if (empty($email) || empty($password)) {
    set_error_message('이메일과 비밀번호를 모두 입력해주세요.');
    redirect(BASE_URL . '/member/login.php');
}

try {
    // 사용자 조회
    $stmt = $pdo->prepare("SELECT uid, email, password, nickname FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // 사용자가 없거나 비밀번호가 틀린 경우
    if (!$user || !password_verify($password, $user['password'])) {
        set_error_message('이메일 또는 비밀번호가 올바르지 않습니다.');
        redirect(BASE_URL . '/member/login.php');
    }
    
    // 로그인 성공
    session_regenerate_id(true); // 세션 ID 재생성 (보안)
    
    $_SESSION['user_id'] = $user['uid'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_nickname'] = $user['nickname'];
    $_SESSION['login_time'] = time();
    
    // 로그인 상태 유지 (30일)
    if ($remember_me) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        // 실제 운영에서는 토큰을 DB에 저장해야 합니다
    }
    
    // 리다이렉트 URL 확인
    $redirect_url = $_SESSION['redirect_url'] ?? BASE_URL . '/index.php';
    unset($_SESSION['redirect_url']);
    
    set_success_message($user['nickname'] . '님, 환영합니다!');
    redirect($redirect_url);
    
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    set_error_message('로그인 중 오류가 발생했습니다. 다시 시도해주세요.');
    redirect(BASE_URL . '/member/login.php');
}
?>  