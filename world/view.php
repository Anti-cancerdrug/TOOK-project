<?php
include '../includes/header.php';

// wid 파라미터 확인
$wid = $_GET['wid'] ?? 0;

if (!$wid) {
    set_error_message('잘못된 접근입니다.');
    redirect(BASE_URL . '/index.php');
}

// 세계관 정보 가져오기
try {
    $stmt = $pdo->prepare("
        SELECT w.*, u.nickname as creator_name, u.uid as creator_uid
        FROM world w
        JOIN world_admin wa ON w.wid = wa.wid AND wa.role = 1
        JOIN user u ON wa.uid = u.uid
        WHERE w.wid = ?
    ");
    $stmt->execute([$wid]);
    $world = $stmt->fetch();
    
    if (!$world) {
        set_error_message('존재하지 않는 세계관입니다.');
        redirect(BASE_URL . '/index.php');
    }
    
    // 비공개이고 소유자가 아니면 접근 불가
    $current_user = get_logged_in_user();
    $is_owner = $current_user && $current_user['uid'] == $world['creator_uid'];
    
    if (!$world['is_public'] && !$is_owner) {
        set_error_message('비공개 세계관입니다.');
        redirect(BASE_URL . '/index.php');
    }
    
    // 조회수 증가
    $stmt = $pdo->prepare("UPDATE world SET view_count = view_count + 1 WHERE wid = ?");
    $stmt->execute([$wid]);
    
    // 장르 가져오기
    $stmt = $pdo->prepare("
        SELECT g.name 
        FROM genre g
        JOIN world_genre wg ON g.gid = wg.gid
        WHERE wg.wid = ?
    ");
    $stmt->execute([$wid]);
    $genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 위키 요소 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_element WHERE wid = ?");
    $stmt->execute([$wid]);
    $wiki_count = $stmt->fetchColumn();
    
    // 콘텐츠 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM content WHERE wid = ?");
    $stmt->execute([$wid]);
    $content_count = $stmt->fetchColumn();
    
    // 최근 위키 요소
    $stmt = $pdo->prepare("
        SELECT * FROM wiki_element 
        WHERE wid = ? 
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $stmt->execute([$wid]);
    $wiki_elements = $stmt->fetchAll();
    
    // 최근 콘텐츠
    $stmt = $pdo->prepare("
        SELECT c.*, ct.type_name
        FROM content c
        JOIN content_type ct ON c.ctid = ct.ctid
        WHERE c.wid = ? AND c.is_public = 1
        ORDER BY c.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$wid]);
    $contents = $stmt->fetchAll();
    
    $page_title = escape($world['title']);
    
} catch (PDOException $e) {
    error_log("World View Error: " . $e->getMessage());
    set_error_message('세계관을 불러오는 중 오류가 발생했습니다.');
    redirect(BASE_URL . '/index.php');
}
?>

<style>
.world-header {
    position: relative;
    height: 400px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    overflow: hidden;
    margin-bottom: 40px;
}

.world-header-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.3;
}

.world-header-content {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 40px;
    color: white;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.world-title {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 16px;
}

.world-meta {
    display: flex;
    gap: 24px;
    align-items: center;
    font-size: 16px;
    opacity: 0.95;
    margin-bottom: 16px;
}

.world-genres {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.genre-badge {
    background: rgba(255,255,255,0.2);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    backdrop-filter: blur(10px);
}

.world-actions {
    position: absolute;
    top: 20px;
    right: 40px;
    display: flex;
    gap: 12px;
}

.world-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px 60px;
}

.world-description {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    margin-bottom: 40px;
    line-height: 1.8;
    white-space: pre-wrap;
}

.world-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 8px;
}

.stat-label {
    color: var(--text-light);
    font-size: 14px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color);
}

.element-type-badge {
    background: var(--bg-light);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    color: var(--text-light);
}
</style>

<!-- 세계관 헤더 -->
<div class="world-header">
    <?php if ($world['thumbnail'] != DEFAULT_WORLD_THUMBNAIL): ?>
        <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($world['thumbnail']); ?>" 
             alt="<?php echo escape($world['title']); ?>"
             class="world-header-bg">
    <?php endif; ?>
    
    <div class="world-header-content">
        <div class="world-genres">
            <?php foreach ($genres as $genre): ?>
                <span class="genre-badge"><?php echo escape($genre); ?></span>
            <?php endforeach; ?>
        </div>
        <h1 class="world-title"><?php echo escape($world['title']); ?></h1>
        <div class="world-meta">
            <span>👤 <?php echo escape($world['creator_name']); ?></span>
            <span>👁️ <?php echo number_format($world['view_count']); ?></span>
            <span>📅 <?php echo date('Y-m-d', strtotime($world['created_at'])); ?></span>
            <span><?php echo $world['is_public'] ? '🌐 공개' : '🔒 비공개'; ?></span>
        </div>
    </div>
    
    <?php if ($is_owner): ?>
        <div class="world-actions">
            <button class="btn btn-secondary" onclick="location.href='edit.php?wid=<?php echo $wid; ?>'">⚙️ 편집</button>
            <button class="btn btn-outline" style="background: white;">📋 관리</button>
        </div>
    <?php endif; ?>
</div>

<div class="world-container">
    <!-- 설명 -->
    <?php if ($world['description']): ?>
        <div class="world-description">
            <?php echo nl2br(escape($world['description'])); ?>
        </div>
    <?php endif; ?>

    <!-- 통계 -->
    <div class="world-stats">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($wiki_count); ?></div>
            <div class="stat-label">위키 요소</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($content_count); ?></div>
            <div class="stat-label">콘텐츠</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($world['view_count']); ?></div>
            <div class="stat-label">조회수</div>
        </div>
    </div>

    <!-- 위키 요소 -->
    <section style="margin-bottom: 60px;">
        <div class="section-header">
            <h2 class="section-title">📝 위키 요소</h2>
            <?php if ($is_owner): ?>
                <button class="btn btn-secondary" onclick="location.href='wiki/add.php?wid=<?php echo $wid; ?>'">+ 추가하기</button>
            <?php endif; ?>
        </div>
        
        <?php if (count($wiki_elements) > 0): ?>
            <div class="card-grid">
                <?php foreach ($wiki_elements as $element): ?>
                    <div class="card" onclick="location.href='wiki/view.php?weid=<?php echo $element['weid']; ?>'">
                        <div class="card-thumbnail">
                            <?php if ($element['image_path']): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/wiki/<?php echo escape($element['image_path']); ?>" 
                                     alt="<?php echo escape($element['title']); ?>">
                            <?php else: ?>
                                <span style="font-size: 48px;">
                                    <?php 
                                    $icons = [
                                        'character' => '👤',
                                        'place' => '🏛️',
                                        'organization' => '🏢',
                                        'event' => '⚡',
                                        'item' => '💎'
                                    ];
                                    echo $icons[$element['element_type']] ?? '📄';
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <span class="element-type-badge">
                                <?php 
                                $type_names = [
                                    'character' => '캐릭터',
                                    'place' => '장소',
                                    'organization' => '조직',
                                    'event' => '사건',
                                    'item' => '아이템'
                                ];
                                echo $type_names[$element['element_type']] ?? $element['element_type'];
                                ?>
                            </span>
                            <h3 class="card-title"><?php echo escape($element['title']); ?></h3>
                            <p class="card-description"><?php echo escape($element['description'] ?? '설명이 없습니다.'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                <p style="font-size: 48px; margin-bottom: 20px;">📝</p>
                <h3 style="margin-bottom: 10px; color: #666;">아직 위키 요소가 없습니다</h3>
                <?php if ($is_owner): ?>
                    <button class="btn btn-secondary" style="margin-top: 16px;" onclick="location.href='wiki/add.php?wid=<?php echo $wid; ?>'">첫 위키 요소 만들기</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- 콘텐츠 -->
    <section>
        <div class="section-header">
            <h2 class="section-title">📚 콘텐츠</h2>
        </div>
        
        <?php if (count($contents) > 0): ?>
            <div class="card-grid">
                <?php foreach ($contents as $content): ?>
                    <a href="<?php echo BASE_URL; ?>/<?php echo $content['ctid'] == 1 ? 'toon' : 'novel'; ?>/view.php?cid=<?php echo $content['cid']; ?>" class="card">
                        <div class="card-thumbnail">
                            <?php if ($content['thumbnail_path']): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($content['thumbnail_path']); ?>" 
                                     alt="<?php echo escape($content['title']); ?>">
                            <?php else: ?>
                                <span style="font-size: 48px;"><?php echo $content['ctid'] == 1 ? '🎨' : '📖'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <div style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 4px 12px; border-radius: 12px; font-size: 12px; margin-bottom: 8px;">
                                <?php echo escape($content['type_name']); ?>
                            </div>
                            <h3 class="card-title"><?php echo escape($content['title']); ?></h3>
                            <p class="card-description"><?php echo escape($content['description'] ?? '설명이 없습니다.'); ?></p>
                            <div class="card-meta">
                                <span>👁️ <?php echo number_format($content['view_count']); ?></span>
                                <span>❤️ <?php echo number_format($content['like_count']); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                <p style="font-size: 48px; margin-bottom: 20px;">📚</p>
                <h3 style="margin-bottom: 10px; color: #666;">아직 콘텐츠가 없습니다</h3>
                <?php if ($is_owner): ?>
                    <p style="color: #999; margin-bottom: 16px;">이 세계관을 기반으로 작품을 만들어보세요</p>
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button class="btn btn-secondary" onclick="location.href='<?php echo BASE_URL; ?>/toon/upload.php?wid=<?php echo $wid; ?>'">툭툰 올리기</button>
                        <button class="btn btn-outline" onclick="location.href='<?php echo BASE_URL; ?>/novel/write.php?wid=<?php echo $wid; ?>'">툭소설 쓰기</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../includes/footer.php'; ?>