<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/index.php');
}

$wid = $_POST['wid'] ?? 0;
$current_user = get_logged_in_user();

// 권한 확인
try {
    $stmt = $pdo->prepare("SELECT role FROM world_admin WHERE wid = ? AND uid = ?");
    $stmt->execute([$wid, $current_user['uid']]);
    $role = $stmt->fetchColumn();
    
    if ($role != 1) {
        set_error_message('편집 권한이 없습니다.');
        redirect(BASE_URL . '/world/view.php?wid=' . $wid);
    }
} catch (PDOException $e) {
    set_error_message('오류가 발생했습니다.');
    redirect(BASE_URL . '/index.php');
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_public = isset($_POST['is_public']) ? 1 : 0;
$genres = $_POST['genres'] ?? [];
$current_thumbnail = $_POST['current_thumbnail'] ?? DEFAULT_WORLD_THUMBNAIL;

// 유효성 검사
if (empty($title)) {
    set_error_message('제목을 입력해주세요.');
    redirect(BASE_URL . '/world/edit.php?wid=' . $wid);
}

// 썸네일 처리
$thumbnail = $current_thumbnail;
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $upload_result = upload_file($_FILES['thumbnail'], UPLOAD_PATH . 'thumbnails/', ALLOWED_IMAGE_TYPES);
    if ($upload_result['success']) {
        $thumbnail = $upload_result['filename'];
        // 기존 이미지 삭제 (기본 이미지가 아니면)
        if ($current_thumbnail != DEFAULT_WORLD_THUMBNAIL) {
            @unlink(UPLOAD_PATH . 'thumbnails/' . $current_thumbnail);
        }
    }
}

try {
    $pdo->beginTransaction();
    
    // 세계관 업데이트
    $stmt = $pdo->prepare("
        UPDATE world 
        SET title = ?, description = ?, thumbnail = ?, is_public = ?
        WHERE wid = ?
    ");
    $stmt->execute([$title, $description, $thumbnail, $is_public, $wid]);
    
    // 기존 장르 삭제
    $stmt = $pdo->prepare("DELETE FROM world_genre WHERE wid = ?");
    $stmt->execute([$wid]);
    
    // 새 장르 추가
    if (!empty($genres)) {
        $stmt = $pdo->prepare("INSERT INTO world_genre (wid, gid) VALUES (?, ?)");
        foreach ($genres as $gid) {
            $stmt->execute([$wid, $gid]);
        }
    }
    
    $pdo->commit();
    
    set_success_message('세계관이 수정되었습니다.');
    redirect(BASE_URL . '/world/view.php?wid=' . $wid);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("World Edit Error: " . $e->getMessage());
    set_error_message('수정 중 오류가 발생했습니다.');
    redirect(BASE_URL . '/world/edit.php?wid=' . $wid);
}
?>