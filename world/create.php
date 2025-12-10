<?php
$page_title = '새 세계관 만들기';
include '../includes/header.php';

// 로그인 필수
require_login();

// 장르 목록 가져오기
$stmt = $pdo->query("SELECT * FROM genre ORDER BY name");
$genres = $stmt->fetchAll();
?>

<style>
.create-container {
    max-width: 800px;
    margin: 40px auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.create-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.create-subtitle {
    color: var(--text-light);
    margin-bottom: 30px;
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
    background: #f0f8ff;
}

.thumbnail-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-placeholder {
    text-align: center;
    color: var(--text-light);
}

.thumbnail-placeholder-icon {
    font-size: 64px;
    margin-bottom: 16px;
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
    opacity: 0;
    transition: opacity 0.3s;
}

.thumbnail-preview:hover .thumbnail-change-btn {
    opacity: 1;
}

.genre-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.genre-item {
    position: relative;
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
    background: white;
}

.genre-checkbox:checked + .genre-label {
    border-color: var(--secondary-color);
    background: #e3f2fd;
    color: var(--secondary-color);
    font-weight: 600;
}

.genre-label:hover {
    border-color: var(--secondary-color);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid var(--border-color);
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

.char-counter {
    font-size: 12px;
    color: var(--text-light);
    text-align: right;
    margin-top: 4px;
}
</style>

<div class="create-container">
    <h1 class="create-title">✨ 새 세계관 만들기</h1>
    <p class="create-subtitle">당신만의 독특한 세계를 구축해보세요</p>

    <form action="create_process.php" method="POST" enctype="multipart/form-data" id="createForm">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <!-- 썸네일 -->
        <div class="form-group">
            <label class="form-label">세계관 대표 이미지</label>
            <div class="thumbnail-preview" onclick="document.getElementById('thumbnail').click()">
                <div class="thumbnail-placeholder" id="thumbnailPlaceholder">
                    <div class="thumbnail-placeholder-icon">🖼️</div>
                    <p>클릭하여 이미지 업로드</p>
                    <small>권장 크기: 1200x630 (JPG, PNG, 최대 5MB)</small>
                </div>
                <img id="thumbnailPreview" style="display: none;">
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
                   placeholder="예: 아르카디아 세계관" 
                   maxlength="100"
                   required
                   oninput="updateCharCount('title', 'titleCount', 100)">
            <div class="char-counter">
                <span id="titleCount">0</span> / 100
            </div>
        </div>

        <!-- 설명 -->
        <div class="form-group">
            <label class="form-label" for="description">세계관 설명</label>
            <textarea class="form-input" 
                      id="description" 
                      name="description" 
                      rows="6" 
                      placeholder="이 세계관에 대해 설명해주세요. 배경, 시대, 주요 특징 등을 자유롭게 작성하세요."
                      maxlength="1000"
                      oninput="updateCharCount('description', 'descCount', 1000)"></textarea>
            <div class="char-counter">
                <span id="descCount">0</span> / 1000
            </div>
        </div>

        <!-- 장르 선택 -->
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
                               onchange="limitGenres(this)">
                        <label class="genre-label" for="genre_<?php echo $genre['gid']; ?>">
                            <?php echo escape($genre['name']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="form-help">세계관의 주요 장르를 선택해주세요</p>
        </div>

        <!-- 공개 설정 -->
        <div class="form-group">
            <label class="form-label">공개 설정</label>
            <div class="public-toggle">
                <label class="toggle-switch">
                    <input type="checkbox" name="is_public" id="is_public">
                    <span class="toggle-slider"></span>
                </label>
                <div>
                    <strong id="publicText">비공개</strong>
                    <p style="font-size: 13px; color: var(--text-light); margin: 0;">
                        <span id="publicDesc">나만 볼 수 있습니다</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 버튼 -->
        <div class="form-actions">
            <button type="button" class="btn btn-outline" onclick="history.back()">취소</button>
            <button type="submit" class="btn btn-secondary">🌍 세계관 만들기</button>
        </div>
    </form>
</div>

<script>
// 썸네일 미리보기
function previewThumbnail(input) {
    const preview = document.getElementById('thumbnailPreview');
    const placeholder = document.getElementById('thumbnailPlaceholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// 글자 수 카운터
function updateCharCount(inputId, counterId, maxLength) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    const length = input.value.length;
    
    counter.textContent = length;
    
    if (length > maxLength * 0.9) {
        counter.style.color = '#e74c3c';
    } else {
        counter.style.color = 'var(--text-light)';
    }
}

// 장르 선택 제한 (최대 3개)
function limitGenres(checkbox) {
    const checkboxes = document.querySelectorAll('.genre-checkbox:checked');
    
    if (checkboxes.length > 3) {
        checkbox.checked = false;
        alert('장르는 최대 3개까지 선택할 수 있습니다.');
    }
}

// 공개 설정 토글
document.getElementById('is_public').addEventListener('change', function() {
    const publicText = document.getElementById('publicText');
    const publicDesc = document.getElementById('publicDesc');
    
    if (this.checked) {
        publicText.textContent = '공개';
        publicDesc.textContent = '모든 사람이 볼 수 있습니다';
    } else {
        publicText.textContent = '비공개';
        publicDesc.textContent = '나만 볼 수 있습니다';
    }
});

// 폼 제출 전 확인
document.getElementById('createForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    
    if (!title) {
        e.preventDefault();
        alert('세계관 제목을 입력해주세요.');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>