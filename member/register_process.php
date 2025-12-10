<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/member/register.php');
}

// CSRF 토큰 검증
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/member/register.php');
}

// 입력값 받기
$email = trim($_POST['email'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$agree_terms = isset($_POST['agree_terms']);

// 유효성 검사
$errors = [];

// 이메일 검증
if (empty($email)) {
    $errors[] = '이메일을 입력해주세요.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '올바른 이메일 형식이 아닙니다.';
} else {
    // 이메일 중복 체크
    $stmt = $pdo->prepare("SELECT uid FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = '이미 사용 중인 이메일입니다.';
    }
}

// 닉네임 검증
if (empty($nickname)) {
    $errors[] = '닉네임을 입력해주세요.';
} elseif (mb_strlen($nickname) < 2 || mb_strlen($nickname) > 20) {
    $errors[] = '닉네임은 2-20자 사이여야 합니다.';
} elseif (!preg_match('/^[가-힣a-zA-Z0-9_]+$/', $nickname)) {
    $errors[] = '닉네임은 한글, 영문, 숫자, 언더스코어만 사용 가능합니다.';
} else {
    // 닉네임 중복 체크
    $stmt = $pdo->prepare("SELECT uid FROM user WHERE nickname = ?");
    $stmt->execute([$nickname]);
    if ($stmt->fetch()) {
        $errors[] = '이미 사용 중인 닉네임입니다.';
    }
}

// 비밀번호 검증
if (empty($password)) {
    $errors[] = '비밀번호를 입력해주세요.';
} elseif (strlen($password) < 8) {
    $errors[] = '비밀번호는 8자 이상이어야 합니다.';
} elseif ($password !== $password_confirm) {
    $errors[] = '비밀번호가 일치하지 않습니다.';
}

// 약관 동의 확인
if (!$agree_terms) {
    $errors[] = '이용약관에 동의해주세요.';
}

// 에러가 있으면 돌아가기
if (!empty($errors)) {
    set_error_message(implode('<br>', $errors));
    redirect(BASE_URL . '/member/register.php');
}

// 회원가입 처리
try {
    // 비밀번호 해시화
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 사용자 추가
    $stmt = $pdo->prepare("
        INSERT INTO user (email, password, nickname, profile_image) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $email,
        $hashed_password,
        $nickname,
        DEFAULT_PROFILE_IMAGE
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // 크리에이터 권한 자동 부여
    $stmt = $pdo->prepare("INSERT INTO creator (uid) VALUES (?)");
    $stmt->execute([$user_id]);
    
    // 성공 메시지
    set_success_message('회원가입이 완료되었습니다! 로그인해주세요.');
    redirect(BASE_URL . '/member/login.php');
    
} catch (PDOException $e) {
    error_log("Register Error: " . $e->getMessage());
    set_error_message('회원가입 중 오류가 발생했습니다. 다시 시도해주세요.');
    redirect(BASE_URL . '/member/register.php');
}
?>