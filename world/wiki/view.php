<?php
include '../../includes/header.php';

$weid = $_GET['weid'] ?? 0;

if (!$weid) {
    set_error_message('잘못된 접근입니다.');
    redirect(BASE_URL . '/index.php');
}

try {
    // 위키 요소 정보
    $stmt = $pdo->prepare("
        SELECT we.*, w.title as world_title, w.wid
        FROM wiki_element we
        JOIN world w ON we.wid = w.wid
        WHERE we.weid = ?
    ");
    $stmt->execute([$weid]);
    $element = $stmt->fetch();
    
    if (!$element) {
        set_error_message('존재하지 않는 위키 요소입니다.');
        redirect(BASE_URL . '/index.php');
    }
    
    // 편집 권한 확인
    $current_user = get_logged_in_user();
    $can_edit = false;
    if ($current_user) {
        $stmt = $pdo->prepare("SELECT role FROM world_admin WHERE wid = ? AND uid = ?");
        $stmt->execute([$element['wid'], $current_user['uid']]);
        $can_edit = (bool)$stmt->fetchColumn();
    }
    
    // metadata 파싱
    $metadata = $element['metadata'] ? json_decode($element['metadata'], true) : [];
    
    $page_title = escape($element['title']);
    
    // 타입별 아이콘
    $type_icons = [
        'character' => '👤',
        'place' => '🏛️',
        'organization' => '🏢',
        'event' => '⚡',
        'item' => '💎'
    ];
    
    $type_names = [
        'character' => '캐릭터',
        'place' => '장소',
        'organization' => '조직',
        'event' => '사건',
        'item' => '아이템'
    ];
    
} catch (PDOException $e) {
    error_log("Wiki View Error: " . $e->getMessage());
    set_error_message('위키 요소를 불러올 수 없습니다.');
    redirect(BASE_URL . '/index.php');
}
?>

<style>
.wiki-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 60px 40px;
    margin-bottom: 40px;
}

.wiki-header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

.wiki-image {
    width: 300px;
    height: 300px;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.wiki-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wiki-info {
    flex: 1;
}

.wiki-type {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    margin-bottom: 16px;
}

.wiki-title {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 12px;
}

.wiki-world {
    opacity: 0.9;
    margin-bottom: 20px;
}

.wiki-actions {
    display: flex;
    gap: 12px;
}

.wiki-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px 60px;
}

.wiki-description {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    line-height: 1.8;
    white-space: pre-wrap;
}

.metadata-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.metadata-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
}

.metadata-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.metadata-item {
    padding: 16px;
    background: var(--bg-light);
    border-radius: 8px;
}

.metadata-label {
    font-size: 13px;
    color: var(--text-light);
    margin-bottom: 6px;
}

.metadata-value {
    font-weight: 600;
    color: var(--text-color);
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--text-light);
}
</style>

<!-- 헤더 -->
<div class="wiki-header">
    <div class="wiki-header-content">
        <div class="wiki-image">
            <?php if ($element['image_path']): ?>
                <img src="<?php echo BASE_URL; ?>/uploads/wiki/<?php echo escape($element['image_path']); ?>" 
                     alt="<?php echo escape($element['title']); ?>">
            <?php else: ?>
                <span style="font-size: 120px;"><?php echo $type_icons[$element['element_type']]; ?></span>
            <?php endif; ?>
        </div>
        
        <div class="wiki-info">
            <div class="wiki-type">
                <?php echo $type_icons[$element['element_type']]; ?>
                <?php echo $type_names[$element['element_type']]; ?>
            </div>
            
            <h1 class="wiki-title"><?php echo escape($element['title']); ?></h1>
            
            <div class="wiki-world">
                <a href="<?php echo BASE_URL; ?>/world/view.php?wid=<?php echo $element['wid']; ?>" 
                   style="color: white; opacity: 0.9;">
                    🌍 <?php echo escape($element['world_title']); ?>
                </a>
            </div>
            
            <div class="wiki-actions">
                <?php if ($can_edit): ?>
                    <button class="btn-primary" onclick="location.href='edit.php?weid=<?php echo $weid; ?>'">✏️ 편집</button>
                    <button class="btn-primary" style="background: rgba(255,255,255,0.2);" 
                            onclick="if(confirm('정말 삭제하시겠습니까?')) location.href='delete_process.php?weid=<?php echo $weid; ?>&csrf_token=<?php echo generate_csrf_token(); ?>'">
                        🗑️ 삭제
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="wiki-container">
    <!-- 설명 -->
    <?php if ($element['description']): ?>
        <div class="wiki-description">
            <?php echo nl2br(escape($element['description'])); ?>
        </div>
    <?php endif; ?>

    <!-- 메타데이터 -->
    <?php if (!empty($metadata)): ?>
        <div class="metadata-card">
            <h2 class="metadata-title">📋 상세 정보</h2>
            <div class="metadata-grid">
                <?php
                // 타입별 메타데이터 라벨
                $labels = [
                    'character' => [
                        'age' => '나이',
                        'gender' => '성별',
                        'role' => '역할',
                        'occupation' => '직업',
                        'personality' => '성격'
                    ],
                    'place' => [
                        'type' => '장소 유형',
                        'region' => '지역',
                        'features' => '특징'
                    ],
                    'organization' => [
                        'type' => '조직 유형',
                        'leader' => '대표/리더',
                        'purpose' => '목적/이념'
                    ],
                    'event' => [
                        'date' => '발생 시기',
                        'location' => '발생 장소',
                        'result' => '결과/영향'
                    ],
                    'item' => [
                        'type' => '아이템 유형',
                        'rarity' => '등급/희귀도',
                        'ability' => '능력/효과'
                    ]
                ];
                
                $type_labels = $labels[$element['element_type']] ?? [];
                
                foreach ($metadata as $key => $value):
                    $label = $type_labels[$key] ?? ucfirst($key);
                ?>
                    <div class="metadata-item">
                        <div class="metadata-label"><?php echo $label; ?></div>
                        <div class="metadata-value"><?php echo escape($value); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="metadata-card">
            <div class="empty-state">
                <p>추가 정보가 없습니다.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>