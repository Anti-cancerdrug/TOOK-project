<?php
$page_title = '세계관 편집';
include '../includes/header.php';

require_login();

$wid = $_GET['wid'] ?? 0;
$current_user = get_logged_in_user();

if (!$wid) {
    set_error_message('잘못된 접근입니다.');
    redirect(BASE_URL . '/index.php');
}

// 세계관 정보 및 권한 확인
try {
    $stmt = $pdo->prepare("
        SELECT w.*, wa.role
        FROM world w
        JOIN world_admin wa ON w.wid = wa.wid
        WHERE w.wid = ? AND wa.uid = ?
    ");
    $stmt->execute([$wid, $current_user['uid']]);
    $world = $stmt->fetch();
    
    if (!$world || $world['role'] != 1) {
        set_error_message('편집 권한이 없습니다.');
        redirect(BASE_URL . '/world/view.php?wid=' . $wid);
    }
    
    // 선택된 장르 가져오기
    $stmt = $pdo->prepare("SELECT gid FROM world_genre WHERE wid = ?");
    $stmt->execute([$wid]);
    $selected_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 전체 장르 목록
    $stmt = $pdo->query("SELECT * FROM genre ORDER BY name");
    $genres = $stmt->fetchAll();
    
} catch (PDOException $e) {
    set_error_message('세계관을 불러올 수 없습니다.');
    redirect(BASE_URL . '/index.php');
}
?>

<style>
.edit-container {
    max-width: 800px;
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

.edit-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color);
}

.thumbnail-preview {
    width: 100%;
    height: 300px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    background: var(--bg-light);
    overflow: hidden;
    position: relative;
}

.thumbnail-preview:hover {
    border-color: var(--secondary-color);
}

.thumbnail-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-change-btn {
    position: absolute;
    bottom: 16px;
    right: 16px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
}

.genre-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.genre-checkbox {
    display: none;
}

.genre-label {
    display: block;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.genre-checkbox:checked + .genre-label {
    border-color: var(--secondary-color);
    background: #e3f2fd;
    color: var(--secondary-color);
    font-weight: 600;
}

.public-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--bg-light);
    border-radius: 8px;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
}

.toggle-switch input {
    display: none;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: var(--secondary-color);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid var(--border-color);
}

.danger-zone {
    background: #fff5f5;
    border: 1px solid #f5c6cb;
    padding: 20px;
    border-radius: 8px;
    margin-top: 40px;
}

.danger-zone h3 {
    color: var(--accent-color);
    margin-bottom: 12px;
}

.btn-danger {
    background: var(--accent-color);
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}
</style>

<div class="edit-container">
    <div class="page-header">
        <h1 class="edit-title">세계관 편집</h1>
        <button class="btn btn-outline" onclick="location.href='view.php?wid=<?php echo $wid; ?>'">← 돌아가기</button>
    </div>

    <form action="edit_process.php" method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="wid" value="<?php echo $wid; ?>">
        <input type="hidden" name="current_thumbnail" value="<?php echo escape($world['thumbnail']); ?>">

        <!-- 썸네일 -->
        <div class="form-group">
            <label class="form-label">세계관 대표 이미지</label>
            <div class="thumbnail-preview" onclick="document.getElementById('thumbnail').click()">
                <img id="thumbnailPreview" 
                     src="<?php echo BASE_URL; ?>/uploads/thumbnails/<?php echo escape($world['thumbnail']); ?>"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display: none; text-align: center;">
                    <div style="font-size: 64px; margin-bottom: 16px;">🖼️</div>
                    <p>클릭하여 이미지 업로드</p>
                </div>
                <div class="thumbnail-change-btn">이미지 변경</div>
            </div>
            <input type="file" 
                   id="thumbnail" 
                   name="thumbnail" 
                   accept="image/*" 
                   style="display: none;"
                   onchange="previewThumbnail(this)">
        </div>

        <!-- 제목 -->
        <div class="form-group">
            <label class="form-label" for="title">세계관 제목 *</label>
            <input type="text" 
                   class="form-input" 
                   id="title" 
                   name="title" 
                   value="<?php echo escape($world['title']); ?>"
                   maxlength="100"
                   required>
        </div>

        <!-- 설명 -->
        <div class="form-group">
            <label class="form-label" for="description">세계관 설명</label>
            <textarea class="form-input" 
                      id="description" 
                      name="description" 
                      rows="6" 
                      maxlength="1000"><?php echo escape($world['description']); ?></textarea>
        </div>

        <!-- 장르 -->
        <div class="form-group">
            <label class="form-label">장르 (최대 3개)</label>
            <div class="genre-grid">
                <?php foreach ($genres as $genre): ?>
                    <div class="genre-item">
                        <input type="checkbox" 
                               class="genre-checkbox" 
                               id="genre_<?php echo $genre['gid']; ?>" 
                               name="genres[]" 
                               value="<?php echo $genre['gid']; ?>"
                               <?php echo in_array($genre['gid'], $selected_genres) ? 'checked' : ''; ?>
                               onchange="limitGenres(this)">
                        <label class="genre-label" for="genre_<?php echo $genre['gid']; ?>">
                            <?php echo escape($genre['name']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 공개 설정 -->
        <div class="form-group">
            <label class="form-label">공개 설정</label>
            <div class="public-toggle">
                <label class="toggle-switch">
                    <input type="checkbox" 
                           name="is_public" 
                           id="is_public" 
                           <?php echo $world['is_public'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
                <div>
                    <strong id="publicText"><?php echo $world['is_public'] ? '공개' : '비공개'; ?></strong>
                    <p style="font-size: 13px; color: var(--text-light); margin: 0;">
                        <span id="publicDesc"><?php echo $world['is_public'] ? '모든 사람이 볼 수 있습니다' : '나만 볼 수 있습니다'; ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 버튼 -->
        <div class="form-actions">
            <button type="button" class="btn btn-outline" onclick="history.back()">취소</button>
            <button type="submit" class="btn btn-secondary">💾 저장하기</button>
        </div>
    </form>

    <!-- 위험 구역 -->
    <div class="danger-zone">
        <h3>⚠️ 위험 구역</h3>
        <p style="color: var(--text-light); margin-bottom: 16px;">
            세계관을 삭제하면 관련된 모든 위키 요소와 콘텐츠가 함께 삭제됩니다. 이 작업은 되돌릴 수 없습니다.
        </p>
        <button type="button" class="btn btn-danger" onclick="deleteWorld()">🗑️ 세계관 삭제</button>
    </div>
</div>

<script>
function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnailPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function limitGenres(checkbox) {
    const checkboxes = document.querySelectorAll('.genre-checkbox:checked');
    if (checkboxes.length > 3) {
        checkbox.checked = false;
        alert('장르는 최대 3개까지 선택할 수 있습니다.');
    }
}

document.getElementById('is_public').addEventListener('change', function() {
    document.getElementById('publicText').textContent = this.checked ? '공개' : '비공개';
    document.getElementById('publicDesc').textContent = this.checked ? '모든 사람이 볼 수 있습니다' : '나만 볼 수 있습니다';
});

function deleteWorld() {
    if (confirm('정말로 이 세계관을 삭제하시겠습니까?\n\n모든 위키 요소와 콘텐츠가 함께 삭제되며, 이 작업은 되돌릴 수 없습니다.')) {
        if (confirm('정말 확실합니까? 다시 한 번 확인해주세요.')) {
            location.href = 'delete_process.php?wid=<?php echo $wid; ?>&csrf_token=<?php echo generate_csrf_token(); ?>';
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>