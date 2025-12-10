<?php
$page_title = '내 세계관';
include '../includes/header.php';

require_login();

$current_user = get_logged_in_user();

// 내 세계관 목록
try {
    $stmt = $pdo->prepare("
        SELECT w.*, 
               (SELECT COUNT(*) FROM wiki_element WHERE wid = w.wid) as wiki_count,
               (SELECT COUNT(*) FROM content WHERE wid = w.wid) as content_count
        FROM world w
        JOIN world_admin wa ON w.wid = wa.wid
        WHERE wa.uid = ? AND wa.role = 1
        ORDER BY w.updated_at DESC
    ");
    $stmt->execute([$current_user['uid']]);
    $worlds = $stmt->fetchAll();
} catch (PDOException $e) {
    $worlds = [];
}
?>

<style>
.worlds-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.worlds-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.worlds-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-color);
}

.world-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: all 0.3s;
    margin-bottom: 20px;
}

.world-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.world-card-inner {
    display: flex;
    gap: 0;
}

.world-thumbnail {
    width: 280px;
    height: 200px;
    flex-shrink: 0;
    overflow: hidden;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
}

.world-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.world-content {
    flex: 1;
    padding: 24px;
    display: flex;
    flex-direction: column;
}

.world-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.world-title-link {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-color);
    cursor: pointer;
    transition: color 0.3s;
}

.world-title-link:hover {
    color: var(--secondary-color);
}

.world-status {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-public {
    background: #e3f2fd;
    color: #1976d2;
}

.status-private {
    background: #fff3e0;
    color: #f57c00;
}

.world-description {
    color: var(--text-light);
    margin-bottom: 16px;
    line-height: 1.6;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.world-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.stat {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-light);
    font-size: 14px;
}

.world-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.empty-state {
    text-align: center;
    padding: 100px 20px;
    background: white;
    border-radius: 12px;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 24px;
}
</style>

<div class="worlds-container">
    <div class="worlds-header">
        <h1 class="worlds-title">🌍 내 세계관</h1>
        <button class="btn btn-secondary" onclick="location.href='create.php'">+ 새 세계관 만들기</button>
    </div>

    <?php if (count($worlds) > 0): ?>
        <?php foreach ($worlds as $world): ?>
            <div class="world-card">
                <div class="world-card-inner">
                    <div class="world-thumbnail" onclick="location.href='view.php?wid=<?php echo $world['wid']; ?>'">
                        <?php if ($world['thumbnail'] && $world['thumbnail'] != DEFAULT_WORLD_THUMBNAIL): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($world['thumbnail']); ?>" 
                                 alt="<?php echo escape($world['title']); ?>">
                        <?php else: ?>
                            <span style="font-size: 64px;">🌍</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="world-content">
                        <div class="world-header-row">
                            <h2 class="world-title-link" onclick="location.href='view.php?wid=<?php echo $world['wid']; ?>'">
                                <?php echo escape($world['title']); ?>
                            </h2>
                            <span class="world-status <?php echo $world['is_public'] ? 'status-public' : 'status-private'; ?>">
                                <?php echo $world['is_public'] ? '🌐 공개' : '🔒 비공개'; ?>
                            </span>
                        </div>
                        
                        <?php if ($world['description']): ?>
                            <p class="world-description"><?php echo escape($world['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="world-stats">
                            <div class="stat">
                                <span>📝</span>
                                <span><?php echo number_format($world['wiki_count']); ?>개 위키</span>
                            </div>
                            <div class="stat">
                                <span>📚</span>
                                <span><?php echo number_format($world['content_count']); ?>개 콘텐츠</span>
                            </div>
                            <div class="stat">
                                <span>👁️</span>
                                <span><?php echo number_format($world['view_count']); ?>회 조회</span>
                            </div>
                            <div class="stat">
                                <span>📅</span>
                                <span><?php echo time_ago($world['updated_at']); ?></span>
                            </div>
                        </div>
                        
                        <div class="world-actions">
                            <button class="btn btn-secondary" onclick="location.href='view.php?wid=<?php echo $world['wid']; ?>'">
                                👁️ 보기
                            </button>
                            <button class="btn btn-outline" onclick="location.href='edit.php?wid=<?php echo $world['wid']; ?>'">
                                ✏️ 편집
                            </button>
                            <button class="btn btn-outline" onclick="location.href='wiki/add.php?wid=<?php echo $world['wid']; ?>'">
                                + 위키 추가
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🌟</div>
            <h2 style="margin-bottom: 16px; color: var(--primary-color);">아직 만든 세계관이 없습니다</h2>
            <p style="color: var(--text-light); margin-bottom: 24px;">
                첫 번째 세계관을 만들어 당신만의 이야기를 시작하세요!
            </p>
            <button class="btn btn-secondary" onclick="location.href='create.php'">
                ✨ 첫 세계관 만들기
            </button>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>