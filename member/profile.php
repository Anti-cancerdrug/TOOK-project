<?php
$page_title = '내 프로필';
include '../includes/header.php';

// 로그인 필수
require_login();

$current_user = get_logged_in_user();

// 내가 만든 세계관 개수
$stmt = $pdo->prepare("SELECT COUNT(*) FROM world_admin WHERE uid = ? AND role = 1");
$stmt->execute([$current_user['uid']]);
$world_count = $stmt->fetchColumn();

// 내가 만든 콘텐츠 개수
$stmt = $pdo->prepare("SELECT COUNT(*) FROM content WHERE uid = ?");
$stmt->execute([$current_user['uid']]);
$content_count = $stmt->fetchColumn();

// 받은 좋아요 개수
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM content_reaction cr
    JOIN content c ON cr.cid = c.cid
    WHERE c.uid = ? AND cr.reaction_type = 'like'
");
$stmt->execute([$current_user['uid']]);
$like_count = $stmt->fetchColumn();
?>

<style>
.profile-container {
    max-width: 900px;
    margin: 40px auto;
}

.profile-header {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    display: flex;
    gap: 30px;
    align-items: center;
    margin-bottom: 30px;
}

.profile-image-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--border-color);
}

.profile-image-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info {
    flex: 1;
}

.profile-nickname {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--primary-color);
}

.profile-email {
    color: var(--text-light);
    margin-bottom: 12px;
}

.profile-bio {
    color: var(--text-color);
    line-height: 1.6;
    margin-bottom: 20px;
}

.profile-stats {
    display: flex;
    gap: 30px;
    background: var(--bg-light);
    padding: 20px;
    border-radius: 8px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--secondary-color);
}

.stat-label {
    font-size: 14px;
    color: var(--text-light);
    margin-top: 4px;
}

.profile-actions {
    display: flex;
    gap: 12px;
}

.profile-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    margin-bottom: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
}

.section-title-small {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-color);
}

.menu-list {
    list-style: none;
}

.menu-item {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.menu-item:hover {
    background: var(--bg-light);
}

.menu-item a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    color: var(--text-color);
}

.menu-icon {
    font-size: 20px;
    margin-right: 12px;
}

.logout-btn {
    color: var(--accent-color);
    cursor: pointer;
}
</style>

<div class="profile-container">
    <!-- 프로필 헤더 -->
    <div class="profile-header">
        <div class="profile-image-large">
            <img src="<?php echo BASE_URL; ?>/uploads/profile/<?php echo escape($current_user['profile_image']); ?>" 
                 alt="프로필" 
                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default.jpg'">
        </div>
        <div class="profile-info">
            <h1 class="profile-nickname"><?php echo escape($current_user['nickname']); ?></h1>
            <p class="profile-email"><?php echo escape($current_user['email']); ?></p>
            <?php if ($current_user['bio']): ?>
                <p class="profile-bio"><?php echo nl2br(escape($current_user['bio'])); ?></p>
            <?php else: ?>
                <p class="profile-bio" style="color: var(--text-light); font-style: italic;">자기소개가 없습니다.</p>
            <?php endif; ?>
            <div class="profile-actions">
                <button class="btn btn-secondary" onclick="location.href='edit_profile.php'">프로필 수정</button>
                <button class="btn btn-outline" onclick="location.href='dashboard.php'">대시보드</button>
            </div>
        </div>
    </div>

    <!-- 통계 -->
    <div class="profile-section">
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($world_count); ?></div>
                <div class="stat-label">세계관</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($content_count); ?></div>
                <div class="stat-label">작품</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($like_count); ?></div>
                <div class="stat-label">받은 좋아요</div>
            </div>
        </div>
    </div>

    <!-- 메뉴 -->
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title-small">계정 관리</h2>
        </div>
        <ul class="menu-list">
            <li class="menu-item">
                <a href="edit_profile.php">
                    <span><span class="menu-icon">✏️</span>프로필 수정</span>
                    <span>→</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="change_password.php">
                    <span><span class="menu-icon">🔒</span>비밀번호 변경</span>
                    <span>→</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="dashboard.php">
                    <span><span class="menu-icon">📋</span>대시보드</span>
                    <span>→</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="../world/my_worlds.php">
                    <span><span class="menu-icon">🌍</span>내 세계관</span>
                    <span>→</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="my_content.php">
                    <span><span class="menu-icon">📚</span>내 작품</span>
                    <span>→</span>
                </a>
            </li>
            <li class="menu-item logout-btn" onclick="if(confirm('로그아웃 하시겠습니까?')) location.href='logout.php'">
                <span><span class="menu-icon">🚪</span>로그아웃</span>
                <span>→</span>
            </li>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>