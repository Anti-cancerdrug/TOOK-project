<?php
$page_title = '세계관 탐색';
include '../includes/header.php';

// 검색 및 필터
$search = $_GET['search'] ?? '';
$genre = $_GET['genre'] ?? '';
$sort = $_GET['sort'] ?? 'recent';

// 공개 세계관 목록
try {
    $sql = "
        SELECT w.*, u.nickname as creator_name,
               (SELECT COUNT(*) FROM wiki_element WHERE wid = w.wid) as wiki_count,
               (SELECT COUNT(*) FROM content WHERE wid = w.wid) as content_count
        FROM world w
        JOIN world_admin wa ON w.wid = wa.wid AND wa.role = 1
        JOIN user u ON wa.uid = u.uid
        WHERE w.is_public = 1
    ";
    
    $params = [];
    
    // 검색어
    if (!empty($search)) {
        $sql .= " AND (w.title LIKE ? OR w.description LIKE ? OR u.nickname LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // 장르 필터
    if (!empty($genre)) {
        $sql .= " AND EXISTS (SELECT 1 FROM world_genre wg WHERE wg.wid = w.wid AND wg.gid = ?)";
        $params[] = $genre;
    }
    
    // 정렬
    switch ($sort) {
        case 'popular':
            $sql .= " ORDER BY w.view_count DESC";
            break;
        case 'wiki':
            $sql .= " ORDER BY wiki_count DESC";
            break;
        default:
            $sql .= " ORDER BY w.created_at DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $worlds = $stmt->fetchAll();
    
    // 장르 목록
    $stmt = $pdo->query("SELECT * FROM genre ORDER BY name");
    $genres = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $worlds = [];
    $genres = [];
}
?>

<style>
.explore-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.explore-header {
    margin-bottom: 40px;
}

.explore-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 24px;
}

.filter-bar {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-label {
    font-weight: 600;
    color: var(--text-color);
    font-size: 14px;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 14px;
    min-width: 150px;
}

.search-bar {
    display: flex;
    gap: 8px;
    flex: 1;
    min-width: 250px;
}

.search-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 14px;
}

.results-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding: 16px;
    background: var(--bg-light);
    border-radius: 8px;
}
</style>

<div class="explore-container">
    <div class="explore-header">
        <h1 class="explore-title">🔍 세계관 탐색</h1>
        
        <form method="GET" class="filter-bar">
            <div class="search-bar">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="제목, 설명, 작가로 검색..." 
                       value="<?php echo escape($search); ?>">
                <button type="submit" class="btn btn-secondary">검색</button>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">장르</label>
                <select name="genre" class="filter-select" onchange="this.form.submit()">
                    <option value="">전체</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g['gid']; ?>" <?php echo $genre == $g['gid'] ? 'selected' : ''; ?>>
                            <?php echo escape($g['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">정렬</label>
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="recent" <?php echo $sort == 'recent' ? 'selected' : ''; ?>>최신순</option>
                    <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>인기순</option>
                    <option value="wiki" <?php echo $sort == 'wiki' ? 'selected' : ''; ?>>위키 많은 순</option>
                </select>
            </div>
        </form>
    </div>

    <?php if (!empty($search) || !empty($genre)): ?>
        <div class="results-info">
            <div>
                <?php if (!empty($search)): ?>
                    <strong>"<?php echo escape($search); ?>"</strong> 검색 결과
                <?php endif; ?>
                <span style="color: var(--text-light);">
                    <?php echo count($worlds); ?>개의 세계관
                </span>
            </div>
            <a href="explore.php" class="btn btn-outline">필터 초기화</a>
        </div>
    <?php endif; ?>

    <?php if (count($worlds) > 0): ?>
        <div class="card-grid">
            <?php foreach ($worlds as $world): ?>
                <a href="view.php?wid=<?php echo $world['wid']; ?>" class="card">
                    <div class="card-thumbnail">
                        <?php if ($world['thumbnail'] && $world['thumbnail'] != DEFAULT_WORLD_THUMBNAIL): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($world['thumbnail']); ?>" 
                                 alt="<?php echo escape($world['title']); ?>">
                        <?php else: ?>
                            <span style="font-size: 48px;">🌍</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo escape($world['title']); ?></h3>
                        <p class="card-description"><?php echo escape($world['description'] ?? '설명이 없습니다.'); ?></p>
                        <div class="card-meta">
                            <span>👤 <?php echo escape($world['creator_name']); ?></span>
                            <span>👁️ <?php echo number_format($world['view_count']); ?></span>
                        </div>
                        <div class="card-meta" style="margin-top: 8px;">
                            <span>📝 <?php echo number_format($world['wiki_count']); ?>개 위키</span>
                            <span>📚 <?php echo number_format($world['content_count']); ?>개 콘텐츠</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 100px 20px; background: white; border-radius: 12px;">
            <p style="font-size: 64px; margin-bottom: 20px;">🔍</p>
            <h3 style="margin-bottom: 12px; color: var(--primary-color);">검색 결과가 없습니다</h3>
            <p style="color: var(--text-light);">다른 검색어나 필터로 시도해보세요</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>