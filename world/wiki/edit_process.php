<?php
require_once '../../config/db.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_error_message('잘못된 요청입니다.');
    redirect(BASE_URL . '/index.php');
}

$weid = $_POST['weid'] ?? 0;
$current_user = get_logged_in_user();

// 권한 확인
try {
    $stmt = $pdo->prepare("
        SELECT we.wid, wa.role
        FROM wiki_element we
        JOIN world_admin wa ON we.wid = wa.wid
        WHERE we.weid = ? AND wa.uid = ?
    ");
    $stmt->execute([$weid, $current_user['uid']]);
    $wiki = $stmt->fetch();
    
    if (!$wiki) {
        set_error_message('편집 권한이 없습니다.');
        redirect(BASE_URL . '/index.php');
    }
} catch (PDOException $e) {
    set_error_message('오류가 발생했습니다.');
    redirect(BASE_URL . '/index.php');
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$meta = $_POST['meta'] ?? [];
$current_image = $_POST['current_image'] ?? null;

if (empty($title)) {
    set_error_message('제목을 입력해주세요.');
    redirect(BASE_URL . '/world/wiki/edit.php?weid=' . $weid);
}

// 이미지 업로드
$image_path = $current_image;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_result = upload_file($_FILES['image'], UPLOAD_PATH . 'wiki/', ALLOWED_IMAGE_TYPES);
    if ($upload_result['success']) {
        $image_path = $upload_result['filename'];
        if ($current_image) {
            @unlink(UPLOAD_PATH . 'wiki/' . $current_image);
        }
    }
}

// metadata 정리
$metadata = [];
foreach ($meta as $key => $value) {
    if (!empty(trim($value))) {
        $metadata[$key] = trim($value);
    }
}
$metadata_json = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;

try {
    $stmt = $pdo->prepare("
        UPDATE wiki_element 
        SET title = ?, description = ?, image_path = ?, metadata = ?
        WHERE weid = ?
    ");
    $stmt->execute([$title, $description, $image_path, $metadata_json, $weid]);
    
    set_success_message('위키 요소가 수정되었습니다.');
    redirect(BASE_URL . '/world/wiki/view.php?weid=' . $weid);
    
} catch (PDOException $e) {
    error_log("Wiki Edit Error: " . $e->getMessage());
    set_error_message('수정 중 오류가 발생했습니다.');
    redirect(BASE_URL . '/world/wiki/edit.php?weid=' . $weid);
}
?>