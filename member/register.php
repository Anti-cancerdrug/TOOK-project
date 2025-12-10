<?php
$page_title = '회원가입';
include '../includes/header.php';

// 이미 로그인되어 있으면 메인으로
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}
?>

<style>
.auth-container {
    max-width: 480px;
    margin: 60px auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.auth-title {
    font-size: 28px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.auth-subtitle {
    text-align: center;
    color: var(--text-light);
    margin-bottom: 30px;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    border: 1px solid #f5c6cb;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    border: 1px solid #c3e6cb;
}

.form-help {
    font-size: 12px;
    color: var(--text-light);
    margin-top: 4px;
}

.password-strength {
    height: 4px;
    background: #e1e8ed;
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    transition: all 0.3s;
    border-radius: 2px;
}

.auth-divider {
    text-align: center;
    margin: 30px 0;
    color: var(--text-light);
    position: relative;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: var(--border-color);
}

.auth-divider::before {
    left: 0;
}

.auth-divider::after {
    right: 0;
}

.auth-footer {
    text-align: center;
    margin-top: 20px;
    color: var(--text-light);
    font-size: 14px;
}

.auth-footer a {
    color: var(--secondary-color);
    font-weight: 500;
}

.btn-full {
    width: 100%;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>

<div class="auth-container">
    <h1 class="auth-title">회원가입</h1>
    <p class="auth-subtitle">TOOK에 오신 것을 환영합니다!</p>

    <form action="register_process.php" method="POST" id="registerForm">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="form-group">
            <label class="form-label" for="email">이메일 *</label>
            <input type="email" 
                   class="form-input" 
                   id="email" 
                   name="email" 
                   placeholder="example@email.com" 
                   required>
            <p class="form-help">로그인 시 사용할 이메일 주소를 입력하세요</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="nickname">닉네임 *</label>
            <input type="text" 
                   class="form-input" 
                   id="nickname" 
                   name="nickname" 
                   placeholder="닉네임 (2-20자)" 
                   minlength="2"
                   maxlength="20"
                   required>
            <p class="form-help">다른 사용자에게 표시될 이름입니다</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">비밀번호 *</label>
            <input type="password" 
                   class="form-input" 
                   id="password" 
                   name="password" 
                   placeholder="8자 이상" 
                   minlength="8"
                   required>
            <div class="password-strength">
                <div class="password-strength-bar" id="strengthBar"></div>
            </div>
            <p class="form-help" id="strengthText">영문, 숫자, 특수문자 포함 8자 이상</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirm">비밀번호 확인 *</label>
            <input type="password" 
                   class="form-input" 
                   id="password_confirm" 
                   name="password_confirm" 
                   placeholder="비밀번호 재입력" 
                   required>
            <p class="form-help" id="confirmText"></p>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="agree_terms" id="agree_terms" required>
                <span><a href="../terms.php" target="_blank">이용약관</a> 및 <a href="../privacy.php" target="_blank">개인정보처리방침</a>에 동의합니다 *</span>
            </label>
        </div>

        <button type="submit" class="btn btn-secondary btn-full">회원가입</button>
    </form>

    <div class="auth-footer">
        이미 계정이 있으신가요? <a href="login.php">로그인</a>
    </div>
</div>

<script>
// 비밀번호 강도 체크
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    const colors = ['#e74c3c', '#e67e22', '#f39c12', '#3498db', '#2ecc71'];
    const texts = ['매우 약함', '약함', '보통', '강함', '매우 강함'];
    const widths = ['20%', '40%', '60%', '80%', '100%'];
    
    if (password.length > 0) {
        strengthBar.style.width = widths[strength - 1];
        strengthBar.style.backgroundColor = colors[strength - 1];
        strengthText.textContent = '비밀번호 강도: ' + texts[strength - 1];
        strengthText.style.color = colors[strength - 1];
    } else {
        strengthBar.style.width = '0';
        strengthText.textContent = '영문, 숫자, 특수문자 포함 8자 이상';
        strengthText.style.color = '#666';
    }
});

// 비밀번호 확인
const passwordConfirm = document.getElementById('password_confirm');
const confirmText = document.getElementById('confirmText');

passwordConfirm.addEventListener('input', function() {
    if (this.value.length > 0) {
        if (this.value === passwordInput.value) {
            confirmText.textContent = '✓ 비밀번호가 일치합니다';
            confirmText.style.color = '#2ecc71';
        } else {
            confirmText.textContent = '✗ 비밀번호가 일치하지 않습니다';
            confirmText.style.color = '#e74c3c';
        }
    } else {
        confirmText.textContent = '';
    }
});

// 폼 제출 검증
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const agreeTerms = document.getElementById('agree_terms').checked;
    
    if (password !== passwordConfirm) {
        e.preventDefault();
        alert('비밀번호가 일치하지 않습니다.');
        return false;
    }
    
    if (!agreeTerms) {
        e.preventDefault();
        alert('이용약관에 동의해주세요.');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>