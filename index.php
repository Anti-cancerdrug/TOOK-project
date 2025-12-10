<?php
$page_title = '홈';
include 'includes/header.php';

// 최신 세계관 가져오기
try {
    $stmt = $pdo->query("
        SELECT w.*, u.nickname as creator_name
        FROM world w
        JOIN world_admin wa ON w.wid = wa.wid AND wa.role = 1
        JOIN user u ON wa.uid = u.uid
        WHERE w.is_public = 1
        ORDER BY w.created_at DESC
        LIMIT 6
    ");
    $worlds = $stmt->fetchAll();
} catch (PDOException $e) {
    $worlds = [];
}

// 최신 콘텐츠 가져오기
try {
    $stmt = $pdo->query("
        SELECT c.*, u.nickname as creator_name, ct.type_name, w.title as world_title
        FROM content c
        JOIN user u ON c.uid = u.uid
        JOIN content_type ct ON c.ctid = ct.ctid
        JOIN world w ON c.wid = w.wid
        WHERE c.is_public = 1
        ORDER BY c.created_at DESC
        LIMIT 6
    ");
    $contents = $stmt->fetchAll();
} catch (PDOException $e) {
    $contents = [];
}
?>

<!-- 히어로 섹션 -->
<section class="hero-section">
    <h1><?php echo SITE_NAME; ?></h1>
    <p><?php echo SITE_SLOGAN; ?></p>
    <p style="font-size: 18px; margin-bottom: 40px;">
        당신만의 세계관을 만들고, 이야기를 펼쳐보세요
    </p>
    <?php if ($current_user): ?>
        <button class="btn-primary" onclick="location.href='<?php echo BASE_URL; ?>/world/create.php'">
            ✨ 새 세계관 만들기
        </button>
    <?php else: ?>
        <button class="btn-primary" onclick="location.href='<?php echo BASE_URL; ?>/member/login.php'">
            🚀 시작하기
        </button>
    <?php endif; ?>
</section>

<!-- 최신 세계관 -->
<section>
    <h2 class="section-title">🌍 최신 세계관</h2>
    <?php if (count($worlds) > 0): ?>
        <div class="card-grid">
            <?php foreach ($worlds as $world): ?>
                <a href="<?php echo BASE_URL; ?>/world/view.php?wid=<?php echo $world['wid']; ?>" class="card">
                    <div class="card-thumbnail">
                        <?php if ($world['thumbnail']): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($world['thumbnail']); ?>" 
                                 alt="<?php echo escape($world['title']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default_world.jpg'">
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
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
            <p style="font-size: 48px; margin-bottom: 20px;">🌟</p>
            <h3 style="margin-bottom: 10px; color: #666;">아직 공개된 세계관이 없습니다</h3>
            <p style="color: #999;">첫 번째 세계관을 만들어보세요!</p>
        </div>
    <?php endif; ?>
</section>

<!-- 최신 콘텐츠 -->
<section style="margin-top: 60px;">
    <h2 class="section-title">📚 최신 콘텐츠</h2>
    <?php if (count($contents) > 0): ?>
        <div class="card-grid">
            <?php foreach ($contents as $content): ?>
                <a href="<?php echo BASE_URL; ?>/<?php echo $content['ctid'] == 1 ? 'toon' : 'novel'; ?>/view.php?cid=<?php echo $content['cid']; ?>" class="card">
                    <div class="card-thumbnail">
                        <?php if ($content['thumbnail_path']): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($content['thumbnail_path']); ?>" 
                                 alt="<?php echo escape($content['title']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default_content.jpg'">
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
                            <span>👤 <?php echo escape($content['creator_name']); ?></span>
                            <span>❤️ <?php echo number_format($content['like_count']); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
            <p style="font-size: 48px; margin-bottom: 20px;">✨</p>
            <h3 style="margin-bottom: 10px; color: #666;">아직 공개된 콘텐츠가 없습니다</h3>
            <p style="color: #999;">첫 번째 작품을 만들어보세요!</p>
        </div>
    <?php endif; ?>
</section>

<!-- 서비스 소개 -->
<section style="margin-top: 80px; background: white; padding: 60px 40px; border-radius: 12px;">
    <h2 class="section-title text-center">왜 TOOK인가요?</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-top: 40px;">
        <div style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
            <h3 style="margin-bottom: 12px;">체계적인 세계관 관리</h3>
            <p style="color: #666; line-height: 1.6;">캐릭터, 장소, 조직을 위키 형식으로 정리하고 관리하세요</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🎨</div>
            <h3 style="margin-bottom: 12px;">콘텐츠 제작</h3>
            <p style="color: #666; line-height: 1.6;">웹툰과 소설을 브라우저에서 바로 만들 수 있어요</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🔗</div>
            <h3 style="margin-bottom: 12px;">유기적 연결</h3>
            <p style="color: #666; line-height: 1.6;">세계관 설정과 콘텐츠를 자동으로 연결해 드려요</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🤝</div>
            <h3 style="margin-bottom: 12px;">공유와 협업</h3>
            <p style="color: #666; line-height: 1.6;">다른 창작자들과 세계관을 공유하고 협업하세요</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>