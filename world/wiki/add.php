<?php
$page_title = '위키 요소 추가';
include '../../includes/header.php';

require_login();

$wid = $_GET['wid'] ?? 0;
$current_user = get_logged_in_user();

if (!$wid) {
    set_error_message('잘못된 접근입니다.');
    redirect(BASE_URL . '/index.php');
}

// 권한 확인
try {
    $stmt = $pdo->prepare("
        SELECT w.title, wa.role
        FROM world w
        JOIN world_admin wa ON w.wid = wa.wid
        WHERE w.wid = ? AND wa.uid = ?
    ");
    $stmt->execute([$wid, $current_user['uid']]);
    $world = $stmt->fetch();
    
    if (!$world) {
        set_error_message('권한이 없습니다.');
        redirect(BASE_URL . '/world/view.php?wid=' . $wid);
    }
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

.world-badge {
    background: var(--bg-light);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    color: var(--text-light);
}

.type-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.type-option {
    position: relative;
}

.type-radio {
    display: none;
}

.type-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
}

.type-radio:checked + .type-label {
    border-color: var(--secondary-color);
    background: #e3f2fd;
}

.type-icon {
    font-size: 48px;
    margin-bottom: 12px;
}

.type-name {
    font-weight: 600;
    color: var(--text-color);
}

.type-radio:checked + .type-label .type-name {
    color: var(--secondary-color);
}

.image-preview {
    width: 100%;
    height: 250px;
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

.image-preview:hover {
    border-color: var(--secondary-color);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

.dynamic-fields {
    display: none;
}

.dynamic-fields.active {
    display: block;
}
</style>

<div class="wiki-container">
    <div class="page-header">
        <div>
            <h1 class="wiki-title">📝 위키 요소 추가</h1>
            <div class="world-badge">🌍 <?php echo escape($world['title']); ?></div>
        </div>
        <button class="btn btn-outline" onclick="location.href='../../world/view.php?wid=<?php echo $wid; ?>'">← 돌아가기</button>
    </div>

    <form action="add_process.php" method="POST" enctype="multipart/form-data" id="wikiForm">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="wid" value="<?php echo $wid; ?>">

        <!-- 타입 선택 -->
        <div class="form-group">
            <label class="form-label">요소 타입 선택 *</label>
            <div class="type-selector">
                <div class="type-option">
                    <input type="radio" name="element_type" value="character" id="type_character" class="type-radio" checked onchange="changeType('character')">
                    <label for="type_character" class="type-label">
                        <div class="type-icon">👤</div>
                        <div class="type-name">캐릭터</div>
                    </label>
                </div>
                <div class="type-option">
                    <input type="radio" name="element_type" value="place" id="type_place" class="type-radio" onchange="changeType('place')">
                    <label for="type_place" class="type-label">
                        <div class="type-icon">🏛️</div>
                        <div class="type-name">장소</div>
                    </label>
                </div>
                <div class="type-option">
                    <input type="radio" name="element_type" value="organization" id="type_organization" class="type-radio" onchange="changeType('organization')">
                    <label for="type_organization" class="type-label">
                        <div class="type-icon">🏢</div>
                        <div class="type-name">조직</div>
                    </label>
                </div>
                <div class="type-option">
                    <input type="radio" name="element_type" value="event" id="type_event" class="type-radio" onchange="changeType('event')">
                    <label for="type_event" class="type-label">
                        <div class="type-icon">⚡</div>
                        <div class="type-name">사건</div>
                    </label>
                </div>
                <div class="type-option">
                    <input type="radio" name="element_type" value="item" id="type_item" class="type-radio" onchange="changeType('item')">
                    <label for="type_item" class="type-label">
                        <div class="type-icon">💎</div>
                        <div class="type-name">아이템</div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 이미지 -->
        <div class="form-group">
            <label class="form-label">이미지</label>
            <div class="image-preview" onclick="document.getElementById('image').click()">
                <div id="imagePlaceholder">
                    <div style="font-size: 64px; margin-bottom: 16px;">🖼️</div>
                    <p>클릭하여 이미지 업로드</p>
                </div>
                <img id="imagePreview" style="display: none;">
            </div>
            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
        </div>

        <!-- 제목 -->
        <div class="form-group">
            <label class="form-label" for="title">이름/제목 *</label>
            <input type="text" class="form-input" id="title" name="title" placeholder="예: 아리아, 엘린 왕국, 붉은 달의 사건..." required maxlength="100">
        </div>

        <!-- 설명 -->
        <div class="form-group">
            <label class="form-label" for="description">설명</label>
            <textarea class="form-input" id="description" name="description" rows="6" placeholder="이 요소에 대한 상세한 설명을 작성하세요..." maxlength="2000"></textarea>
        </div>

        <!-- 캐릭터 전용 필드 -->
        <div id="fields_character" class="dynamic-fields active">
            <div class="metadata-section">
                <div class="metadata-title">📋 캐릭터 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label" for="char_age">나이</label>
                        <input type="text" class="form-input" id="char_age" name="meta[age]" placeholder="예: 25세">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="char_gender">성별</label>
                        <select class="form-input" id="char_gender" name="meta[gender]">
                            <option value="">선택</option>
                            <option value="male">남성</option>
                            <option value="female">여성</option>
                            <option value="other">기타</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="char_role">역할</label>
                        <input type="text" class="form-input" id="char_role" name="meta[role]" placeholder="예: 주인공, 조연, 악역">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="char_occupation">직업</label>
                        <input type="text" class="form-input" id="char_occupation" name="meta[occupation]" placeholder="예: 기사, 마법사">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="char_personality">성격</label>
                    <input type="text" class="form-input" id="char_personality" name="meta[personality]" placeholder="예: 용감한, 신중한, 냉정한">
                </div>
            </div>
        </div>

        <!-- 장소 전용 필드 -->
        <div id="fields_place" class="dynamic-fields">
            <div class="metadata-section">
                <div class="metadata-title">🗺️ 장소 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label" for="place_type">장소 유형</label>
                        <input type="text" class="form-input" id="place_type" name="meta[type]" placeholder="예: 왕국, 마을, 던전">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="place_region">지역</label>
                        <input type="text" class="form-input" id="place_region" name="meta[region]" placeholder="예: 북부, 동부 평원">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="place_features">특징</label>
                    <input type="text" class="form-input" id="place_features" name="meta[features]" placeholder="예: 높은 성벽, 마법 결계">
                </div>
            </div>
        </div>

        <!-- 조직 전용 필드 -->
        <div id="fields_organization" class="dynamic-fields">
            <div class="metadata-section">
                <div class="metadata-title">🏢 조직 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label" for="org_type">조직 유형</label>
                        <input type="text" class="form-input" id="org_type" name="meta[type]" placeholder="예: 길드, 왕국, 교단">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="org_leader">대표/리더</label>
                        <input type="text" class="form-input" id="org_leader" name="meta[leader]" placeholder="예: 길드 마스터 엘리스">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="org_purpose">목적/이념</label>
                    <input type="text" class="form-input" id="org_purpose" name="meta[purpose]" placeholder="예: 마법 연구, 평화 수호">
                </div>
            </div>
        </div>

        <!-- 사건 전용 필드 -->
        <div id="fields_event" class="dynamic-fields">
            <div class="metadata-section">
                <div class="metadata-title">⚡ 사건 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label" for="event_date">발생 시기</label>
                        <input type="text" class="form-input" id="event_date" name="meta[date]" placeholder="예: 왕국력 723년, 3월">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="event_location">발생 장소</label>
                        <input type="text" class="form-input" id="event_location" name="meta[location]" placeholder="예: 왕도, 엘린 성">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="event_result">결과/영향</label>
                    <input type="text" class="form-input" id="event_result" name="meta[result]" placeholder="예: 왕국 멸망, 새로운 시대 시작">
                </div>
            </div>
        </div>

        <!-- 아이템 전용 필드 -->
        <div id="fields_item" class="dynamic-fields">
            <div class="metadata-section">
                <div class="metadata-title">💎 아이템 정보</div>
                <div class="metadata-grid">
                    <div class="form-group">
                        <label class="form-label" for="item_type">아이템 유형</label>
                        <input type="text" class="form-input" id="item_type" name="meta[type]" placeholder="예: 무기, 방어구, 마법 아이템">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="item_rarity">등급/희귀도</label>
                        <input type="text" class="form-input" id="item_rarity" name="meta[rarity]" placeholder="예: 전설, 유일, 일반">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="item_ability">능력/효과</label>
                    <input type="text" class="form-input" id="item_ability" name="meta[ability]" placeholder="예: 힘 +10, 화염 저항">
                </div>
            </div>
        </div>

        <!-- 버튼 -->
        <div class="form-actions">
            <button type="button" class="btn btn-outline" onclick="history.back()">취소</button>
            <button type="submit" class="btn btn-secondary">✅ 추가하기</button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('imagePlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function changeType(type) {
    // 모든 dynamic-fields 숨기기
    document.querySelectorAll('.dynamic-fields').forEach(el => {
        el.classList.remove('active');
    });
    
    // 선택된 타입의 필드만 보이기
    const selectedFields = document.getElementById('fields_' + type);
    if (selectedFields) {
        selectedFields.classList.add('active');
    }
}
</script>

<?php include '../../includes/footer.php'; ?>