<?php
$page_title = '위키 요소 편집';
include '../../includes/header.php';

require_login();

$weid = $_GET['weid'] ?? 0;
$current_user = get_logged_in_user();

if (!$weid) {
    set_error_message('잘못된 접근입니다.');
    redirect(BASE_URL . '/index.php');
}

try {
    // 위키 요소 + 권한 확인
    $stmt = $pdo->prepare("
        SELECT we.*, w.title as world_title, wa.role
        FROM wiki_element we
        JOIN world w ON we.wid = w.wid
        JOIN world_admin wa ON w.wid = wa.wid
        WHERE we.weid = ? AND wa.uid = ?
    ");
    $stmt->execute([$weid, $current_user['uid']]);
    $element = $stmt->fetch();
    
    if (!$element) {
        set_error_message('편집 권한이 없습니다.');
        redirect(BASE_URL . '/index.php');
    }
    
    $metadata = $element['metadata'] ? json_decode($element['metadata'], true) : [];
    
} catch (PDOException $e) {
    set_error_message('오류가 발생했습니다.');
    redirect(BASE_URL . '/index.php');
}
?>

<style>
.wiki-container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--border-color);
}

.wiki-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color);
}

.type-badge {
    display: inline-block;
    background: var(--bg-light);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    color: var(--text-color);
    margin-left: 12px;
}

.image-preview {
    width: 100%;
    height: 250px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    overflow: hidden;
    position: relative;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-change-btn {
    position: absolute;
    bottom: 16px;
    right: 16px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
}

.metadata-section {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.metadata-title {
    font-weight: 600;
    margin-bottom: 16px;
    color: var(--primary-color);
}

.metadata-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}
</style>

<div class="wiki-container">
    <div class="page-header">
        <div>
            <h1 class="wiki-title">
                ✏️ 위키 요소 편집
                <span class="type-badge">
                    <?php 
                    $type_icons = ['character' => '👤', 'place' => '🏛️', 'organization' => '🏢', 'event' => '⚡', 'item' => '💎'];
                    $type_names = ['character' => '캐릭터', 'place' => '장소', 'organization' => '조직', 'event' => '사건', 'item' => '아이템'];
                    echo $type_icons[$element['element_type']] . ' ' . $type_names[$element['element_type']];
                    ?>
                </span>
            </h1>
        </div>
        <button class="btn btn-outline" onclick="location.href='view.php?weid=<?php echo $weid; ?>'">← 돌아가기</button>
    </div>

    <form action="edit_process.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="weid" value="<?php echo $weid; ?>">
        <input type="hidden" name="current_image" value="<?php echo escape($element['image_path']); ?>">

        <!-- 이미지 -->
        <div class="form-group">
            <label class="form-label">이미지</label>
            <div class="image-preview" onclick="document.getElementById('image').click()">
                <?php if ($element['image_path']): ?>
                    <img id="imagePreview" src="<?php echo BASE_URL; ?>/uploads/wiki/<?php echo escape($element['image_path']); ?>">
                <?php else: ?>
                    <img id="imagePreview" style="display: none;">
                    <div id="imagePlaceholder">
                        <div style="font-size: 64px;">🖼️</div>
                        <p>클릭하여 이미지 업로드</p>
                    </div>
                <?php endif; ?>
                <div class="image-change-btn">이미지 변경</div>
            </div>
            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
        </div>

        <!-- 제목 -->
        <div class="form-group">
            <label class="form-label" for="title">이름/제목 *</label>
            <input type="text" class="form-input" id="title" name="title" 
                   value="<?php echo escape($element['title']); ?>" required maxlength="100">
        </div>

        <!-- 설명 -->
        <div class="form-group">
            <label class="form-label" for="description">설명</label>
            <textarea class="form-input" id="description" name="description" rows="6" maxlength="2000"><?php echo escape($element['description']); ?></textarea>
        </div>

        <!-- 타입별 메타데이터 -->
        <?php if ($element['element_type'] == 'character'): ?>
            <div class="metadata-section">
                <div class="metadata-title">📋 캐릭터 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label">나이</label>
                        <input type="text" class="form-input" name="meta[age]" value="<?php echo escape($metadata['age'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">성별</label>
                        <select class="form-input" name="meta[gender]">
                            <option value="">선택</option>
                            <option value="male" <?php echo ($metadata['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>남성</option>
                            <option value="female" <?php echo ($metadata['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>여성</option>
                            <option value="other" <?php echo ($metadata['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>기타</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">역할</label>
                        <input type="text" class="form-input" name="meta[role]" value="<?php echo escape($metadata['role'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">직업</label>
                        <input type="text" class="form-input" name="meta[occupation]" value="<?php echo escape($metadata['occupation'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">성격</label>
                    <input type="text" class="form-input" name="meta[personality]" value="<?php echo escape($metadata['personality'] ?? ''); ?>">
                </div>
            </div>
        <?php elseif ($element['element_type'] == 'place'): ?>
            <div class="metadata-section">
                <div class="metadata-title">🗺️ 장소 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label">장소 유형</label>
                        <input type="text" class="form-input" name="meta[type]" value="<?php echo escape($metadata['type'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">지역</label>
                        <input type="text" class="form-input" name="meta[region]" value="<?php echo escape($metadata['region'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">특징</label>
                    <input type="text" class="form-input" name="meta[features]" value="<?php echo escape($metadata['features'] ?? ''); ?>">
                </div>
            </div>
        <?php elseif ($element['element_type'] == 'organization'): ?>
            <div class="metadata-section">
                <div class="metadata-title">🏢 조직 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label">조직 유형</label>
                        <input type="text" class="form-input" name="meta[type]" value="<?php echo escape($metadata['type'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">대표/리더</label>
                        <input type="text" class="form-input" name="meta[leader]" value="<?php echo escape($metadata['leader'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">목적/이념</label>
                    <input type="text" class="form-input" name="meta[purpose]" value="<?php echo escape($metadata['purpose'] ?? ''); ?>">
                </div>
            </div>
        <?php elseif ($element['element_type'] == 'event'): ?>
            <div class="metadata-section">
                <div class="metadata-title">⚡ 사건 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label">발생 시기</label>
                        <input type="text" class="form-input" name="meta[date]" value="<?php echo escape($metadata['date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">발생 장소</label>
                        <input type="text" class="form-input" name="meta[location]" value="<?php echo escape($metadata['location'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">결과/영향</label>
                    <input type="text" class="form-input" name="meta[result]" value="<?php echo escape($metadata['result'] ?? ''); ?>">
                </div>
            </div>
        <?php elseif ($element['element_type'] == 'item'): ?>
            <div class="metadata-section">
                <div class="metadata-title">💎 아이템 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label">아이템 유형</label>
                        <input type="text" class="form-input" name="meta[type]" value="<?php echo escape($metadata['type'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">등급/희귀도</label>
                        <input type="text" class="form-input" name="meta[rarity]" value="<?php echo escape($metadata['rarity'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">능력/효과</label>
                    <input type="text" class="form-input" name="meta[ability]" value="<?php echo escape($metadata['ability'] ?? ''); ?>">
                </div>
            </div>
        <?php endif; ?>

        <!-- 버튼 -->
        <div class="form-actions">
            <button type="button" class="btn btn-outline" onclick="history.back()">취소</button>
            <button type="submit" class="btn btn-secondary">💾 저장하기</button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('imagePlaceholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../../includes/footer.php'; ?>