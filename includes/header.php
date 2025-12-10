<?php
// 세션이 시작되지 않았으면 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 설정 파일 로드
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// 현재 사용자 정보
$current_user = get_logged_in_user();

// 플래시 메시지 가져오기
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="<?php echo BASE_URL; ?>/index.php" class="logo"><?php echo SITE_NAME; ?></a>
                <nav class="main-nav">
                    <a href="<?php echo BASE_URL; ?>/index.php">홈</a>
                    <a href="<?php echo BASE_URL; ?>/world/explore.php">세계관 탐색</a>
                    <a href="<?php echo BASE_URL; ?>/toon/list.php">툭툰</a>
                    <a href="<?php echo BASE_URL; ?>/novel/list.php">툭소설</a>
                </nav>
            </div>
            
            <div class="header-right">
                <div class="search-box">
                    <input type="text" id="search" placeholder="제목, 작가 검색...">
                    <button type="button">🔍</button>
                </div>
                
                <?php if ($current_user): ?>
                    <!-- 로그인 상태 -->
                    <button class="icon-btn" title="대시보드" onclick="location.href='<?php echo BASE_URL; ?>/member/dashboard.php'">📋</button>
                    <button class="icon-btn" title="알림">🔔</button>
                    <div class="user-menu">
                        <div class="user-profile" onclick="toggleUserMenu()">
                            <img src="<?php echo BASE_URL; ?>/uploads/profile/<?php echo escape($current_user['profile_image']); ?>" 
                                 alt="프로필" 
                                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default.jpg'">
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">
                                <strong><?php echo escape($current_user['nickname']); ?></strong>
                                <small><?php echo escape($current_user['email']); ?></small>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo BASE_URL; ?>/member/profile.php" class="dropdown-item">
                                <span>👤</span> 내 프로필
                            </a>
                            <a href="<?php echo BASE_URL; ?>/member/dashboard.php" class="dropdown-item">
                                <span>📋</span> 대시보드
                            </a>
                            <a href="<?php echo BASE_URL; ?>/world/my_worlds.php" class="dropdown-item">
                                <span>🌍</span> 내 세계관
                            </a>
                            <a href="<?php echo BASE_URL; ?>/member/edit_profile.php" class="dropdown-item">
                                <span>⚙️</span> 설정
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo BASE_URL; ?>/member/logout.php" class="dropdown-item logout-item" onclick="return confirm('로그아웃 하시겠습니까?')">
                                <span>🚪</span> 로그아웃
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 로그아웃 상태 -->
                    <button class="btn btn-outline" onclick="location.href='<?php echo BASE_URL; ?>/member/login.php'">로그인</button>
                    <button class="btn btn-secondary" onclick="location.href='<?php echo BASE_URL; ?>/member/register.php'">회원가입</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($flash): ?>
        <!-- 플래시 메시지 -->
        <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage">
            <?php echo escape($flash['message']); ?>
        </div>
        <script>
            setTimeout(function() {
                const flash = document.getElementById('flashMessage');
                if (flash) {
                    flash.style.opacity = '0';
                    setTimeout(() => flash.remove(), 300);
                }
            }, 3000);
        </script>
    <?php endif; ?>

    <main>