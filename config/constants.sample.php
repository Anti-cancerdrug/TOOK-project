<?php
// 사이트 기본 설정
define('SITE_NAME', 'TOOK');
define('SITE_SLOGAN', '툭, 던지듯 나오는 세계관의 시작점.');

// 경로 설정
define('BASE_URL', 'http://localhost/took-project');  // 로컬 개발 기준. 배포 시 환경에 맞게 변경
define('UPLOAD_PATH', __DIR__ . '/../uploads/');      // 업로드 파일 저장 경로
define('ASSET_PATH', BASE_URL . '/assets');

// 업로드 설정
define('MAX_FILE_SIZE', 5 * 1024 * 1024);             // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// 세션 설정
define('SESSION_LIFETIME', 7200);                      // 2시간

// 페이지네이션
define('ITEMS_PER_PAGE', 12);

// 사용자 역할
define('ROLE_OWNER', 1);
define('ROLE_EDITOR', 2);
define('ROLE_VIEWER', 3);

// 기본 이미지
define('DEFAULT_PROFILE_IMAGE', 'default.jpg');
define('DEFAULT_WORLD_THUMBNAIL', 'default_world.jpg');
define('DEFAULT_CONTENT_THUMBNAIL', 'default_content.jpg');
?>