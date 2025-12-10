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

$wid = $_POST['wid'] ?? 0;
$current_user = get_logged_in_user();

// 권한 확인
try {
    $stmt = $pdo->prepare("SELECT role FROM world_admin WHERE wid = ? AND uid = ?");
    $stmt->execute([$wid, $current_user['uid']]);
    $role = $stmt->fetchColumn();
    
    if (!$role) {
        set_error_message('권한이 없습니다.');
        redirect(BASE_URL . '/world/view.php?wid=' . $wid);
    }
} catch (PDOException $e) {
    set_error_message('오류가 발생했습니다.');
    redirect(BASE_URL . '/index.php');
}

$element_type = $_POST['element_type'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$meta = $_POST['meta'] ?? [];

// 유효성 검사
$valid_types = ['character', 'place', 'organization', 'event', 'item'];
if (!in_array($element_type, $valid_types)) {
    set_error_message('올바른 타입을 선택해주세요.');
    redirect(BASE_URL . '/world/wiki/add.php?wid=' . $wid);
}

if (empty($title)) {
    set_error_message('제목을 입력해주세요.');
    redirect(BASE_URL . '/world/wiki/add.php?wid=' . $wid);
}

// 이미지 업로드
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_result = upload_file($_FILES['image'], UPLOAD_PATH . 'wiki/', ALLOWED_IMAGE_TYPES);
    if ($upload_result['success']) {
        $image_path = $upload_result['filename'];
    }
}

// metadata JSON 변환 (빈 값 제거)
$metadata = [];
foreach ($meta as $key => $value) {
    if (!empty(trim($value))) {
        $metadata[$key] = trim($value);
    }
}
$metadata_json = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;

try {
    $stmt = $pdo->prepare("
        INSERT INTO wiki_element (wid, element_type, title, description, image_path, metadata)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$wid, $element_type, $title, $description, $image_path, $metadata_json]);
    
    set_success_message('위키 요소가 추가되었습니다! 🎉');
    redirect(BASE_URL . '/world/view.php?wid=' . $wid);
    
} catch (PDOException $e) {
    error_log("Wiki Add Error: " . $e->getMessage());
    set_error_message('추가 중 오류가 발생했습니다.');
    redirect(BASE_URL . '/world/wiki/add.php?wid=' . $wid);
}
?>