<?php
$page_title = '로그인';
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

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.form-options a {
    font-size: 14px;
    color: var(--text-light);
}

.form-options a:hover {
    color: var(--secondary-color);
}
</style>

<div class="auth-container">
    <h1 class="auth-title">로그인</h1>
    <p class="auth-subtitle">다시 오신 것을 환영합니다!</p>

    <form action="login_process.php" method="POST" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="form-group">
            <label class="form-label" for="email">이메일</label>
            <input type="email" 
                   class="form-input" 
                   id="email" 
                   name="email" 
                   placeholder="example@email.com" 
                   required
                   autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">비밀번호</label>
            <input type="password" 
                   class="form-input" 
                   id="password" 
                   name="password" 
                   placeholder="비밀번호" 
                   required>
        </div>

        <div class="form-options">
            <label class="checkbox-label">
                <input type="checkbox" name="remember_me" id="remember_me">
                <span>로그인 상태 유지</span>
            </label>
            <a href="find_password.php">비밀번호 찾기</a>
        </div>

        <button type="submit" class="btn btn-secondary btn-full">로그인</button>
    </form>

    <div class="auth-footer">
        아직 계정이 없으신가요? <a href="register.php">회원가입</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>