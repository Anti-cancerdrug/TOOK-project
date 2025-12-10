<?php
require_once '../../config/db.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

require_login();

$weid = $_GET['weid'] ?? 0;
$csrf_token = $_GET['csrf_token'] ?? '';

if (!$weid || !verify_csrf_token($csrf_token)) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/index.php');
}

$current_user = get_logged_in_user();

try {
    // 권한 및 정보 확인
    $stmt = $pdo->prepare("
        SELECT we.wid, we.image_path, wa.role
        FROM wiki_element we
        JOIN world_admin wa ON we.wid = wa.wid
        WHERE we.weid = ? AND wa.uid = ?
    ");
    $stmt->execute([$weid, $current_user['uid']]);
    $wiki = $stmt->fetch();
    
    if (!$wiki) {
        set_error_message('삭제 권한이 없습니다.');
        redirect(BASE_URL . '/index.php');
    }
    
    // 삭제
    $stmt = $pdo->prepare("DELETE FROM wiki_element WHERE weid = ?");
    $stmt->execute([$weid]);
    
    // 이미지 파일 삭제
    if ($wiki['image_path']) {
        @unlink(UPLOAD_PATH . 'wiki/' . $wiki['image_path']);
    }
    
    set_success_message('위키 요소가 삭제되었습니다.');
    redirect(BASE_URL . '/world/view.php?wid=' . $wiki['wid']);
    
} catch (PDOException $e) {
    error_log("Wiki Delete Error: " . $e->getMessage());
    set_error_message('삭제 중 오류가 발생했습니다.');
    redirect(BASE_URL . '/index.php');
}
?>