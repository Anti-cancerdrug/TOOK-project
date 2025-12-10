<?php
$page_title = '대시보드';
include '../includes/header.php';

// 로그인 필수
require_login();

$current_user = get_logged_in_user();

// 내 세계관 목록
$stmt = $pdo->prepare("
    SELECT w.* 
    FROM world w
    JOIN world_admin wa ON w.wid = wa.wid
    WHERE wa.uid = ? AND wa.role = 1
    ORDER BY w.updated_at DESC
    LIMIT 5
");
$stmt->execute([$current_user['uid']]);
$my_worlds = $stmt->fetchAll();

// 내 최근 콘텐츠
$stmt = $pdo->prepare("
    SELECT c.*, ct.type_name, w.title as world_title
    FROM content c
    JOIN content_type ct ON c.ctid = ct.ctid
    JOIN world w ON c.wid = w.wid
    WHERE c.uid = ?
    ORDER BY c.updated_at DESC
    LIMIT 5
");
$stmt->execute([$current_user['uid']]);
$my_contents = $stmt->fetchAll();
?>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 40px auto;
}

.dashboard-header {
    margin-bottom: 40px;
}

.dashboard-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 8px;
}

.dashboard-subtitle {
    color: var(--text-light);
    font-size: 16px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.quick-action-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.quick-action-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.quick-action-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.quick-action-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--primary-color);
}

.quick-action-desc {
    font-size: 14px;
    color: var(--text-light);
}

.section-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    margin-bottom: 24px;
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

.item-list {
    list-style: none;
}

.item-row {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.3s;
    cursor: pointer;
}

.item-row:hover {
    background: var(--bg-light);
}

.item-info {
    flex: 1;
}

.item-title {
    font-weight: 600;
    margin-bottom: 4px;
    color: var(--text-color);
}

.item-meta {
    font-size: 13px;
    color: var(--text-light);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-light);
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
}
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">환영합니다, <?php echo escape($current_user['nickname']); ?>님! 👋</h1>
        <p class="dashboard-subtitle">오늘도 멋진 창작 하세요!</p>
    </div>

    <!-- 빠른 액션 -->
    <div class="dashboard-grid">
        <div class="quick-action-card" onclick="location.href='../world/create.php'">
            <div class="quick-action-icon">🌍</div>
            <h3 class="quick-action-title">새 세계관 만들기</h3>
            <p class="quick-action-desc">새로운 세계관을 구축하세요</p>
        </div>
        <div class="quick-action-card" onclick="location.href='../toon/upload.php'">
            <div class="quick-action-icon">🎨</div>
            <h3 class="quick-action-title">툭툰 올리기</h3>
            <p class="quick-action-desc">웹툰을 업로드하세요</p>
        </div>
        <div class="quick-action-card" onclick="location.href='../novel/write.php'">
            <div class="quick-action-icon">📖</div>
            <h3 class="quick-action-title">툭소설 쓰기</h3>
            <p class="quick-action-desc">새로운 이야기를 작성하세요</p>
        </div>
    </div>

    <!-- 내 세계관 -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title-small">내 세계관</h2>
            <a href="../world/my_worlds.php" class="btn btn-outline">전체 보기</a>
        </div>
        <?php if (count($my_worlds) > 0): ?>
            <ul class="item-list">
                <?php foreach ($my_worlds as $world): ?>
                    <li class="item-row" onclick="location.href='../world/view.php?wid=<?php echo $world['wid']; ?>'">
                        <div class="item-info">
                            <div class="item-title"><?php echo escape($world['title']); ?></div>
                            <div class="item-meta">
                                <?php echo $world['is_public'] ? '🌐 공개' : '🔒 비공개'; ?> · 
                                👁️ <?php echo number_format($world['view_count']); ?> · 
                                📅 <?php echo time_ago($world['updated_at']); ?>
                            </div>
                        </div>
                        <button class="btn btn-outline" onclick="event.stopPropagation(); location.href='../world/edit.php?wid=<?php echo $world['wid']; ?>'">편집</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🌟</div>
                <p>아직 만든 세계관이 없습니다</p>
                <button class="btn btn-secondary" style="margin-top: 16px;" onclick="location.href='../world/create.php'">첫 세계관 만들기</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- 내 최근 콘텐츠 -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title-small">최근 작품</h2>
            <a href="my_content.php" class="btn btn-outline">전체 보기</a>
        </div>
        <?php if (count($my_contents) > 0): ?>
            <ul class="item-list">
                <?php foreach ($my_contents as $content): ?>
                    <li class="item-row" onclick="location.href='../<?php echo $content['ctid'] == 1 ? 'toon' : 'novel'; ?>/view.php?cid=<?php echo $content['cid']; ?>'">
                        <div class="item-info">
                            <div class="item-title">
                                <span style="background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-right: 8px;">
                                    <?php echo escape($content['type_name']); ?>
                                </span>
                                <?php echo escape($content['title']); ?>
                            </div>
                            <div class="item-meta">
                                🌍 <?php echo escape($content['world_title']); ?> · 
                                ❤️ <?php echo number_format($content['like_count']); ?> · 
                                📅 <?php echo time_ago($content['updated_at']); ?>
                            </div>
                        </div>
                        <button class="btn btn-outline" onclick="event.stopPropagation(); location.href='../<?php echo $content['ctid'] == 1 ? 'toon' : 'novel'; ?>/edit.php?cid=<?php echo $content['cid']; ?>'">편집</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">✨</div>
                <p>아직 작품이 없습니다</p>
                <div style="display: flex; gap: 12px; justify-content: center; margin-top: 16px;">
                    <button class="btn btn-secondary" onclick="location.href='../toon/upload.php'">툭툰 올리기</button>
                    <button class="btn btn-outline" onclick="location.href='../novel/write.php'">툭소설 쓰기</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>