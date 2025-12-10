<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

require_login();

$wid = $_GET['wid'] ?? 0;
$csrf_token = $_GET['csrf_token'] ?? '';

if (!$wid || !verify_csrf_token($csrf_token)) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/index.php');
}

$current_user = get_logged_in_user();

try {
    // 권한 확인
    $stmt = $pdo->prepare("SELECT role FROM world_admin WHERE wid = ? AND uid = ?");
    $stmt->execute([$wid, $current_user['uid']]);
    $role = $stmt->fetchColumn();
    
    if ($role != 1) {
        set_error_message('삭제 권한이 없습니다.');
        redirect(BASE_URL . '/index.php');
    }
    
    // 썸네일 파일 경로 가져오기
    $stmt = $pdo->prepare("SELECT thumbnail FROM world WHERE wid = ?");
    $stmt->execute([$wid]);
    $thumbnail = $stmt->fetchColumn();
    
    // 세계관 삭제 (CASCADE로 연결된 데이터 자동 삭제)
    $stmt = $pdo->prepare("DELETE FROM world WHERE wid = ?");
    $stmt->execute([$wid]);
    
    // 썸네일 파일 삭제
    if ($thumbnail && $thumbnail != DEFAULT_WORLD_THUMBNAIL) {
        @unlink(UPLOAD_PATH . 'thumbnails/' . $thumbnail);
    }
    
    set_success_message('세계관이 삭제되었습니다.');
    redirect(BASE_URL . '/member/dashboard.php');
    
} catch (PDOException $e) {
    error_log("World Delete Error: " . $e->getMessage());
    set_error_message('삭제 중 오류가 발생했습니다.');
    redirect(BASE_URL . '/world/view.php?wid=' . $wid);
}
?>