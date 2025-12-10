<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// 세션 데이터 삭제
$_SESSION = [];

// 세션 쿠키 삭제
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// remember_me 쿠키 삭제
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// 세션 파괴
session_destroy();

// 새 세션 시작 (플래시 메시지용)
session_start();
set_success_message('로그아웃되었습니다.');

redirect(BASE_URL . '/index.php');
?>