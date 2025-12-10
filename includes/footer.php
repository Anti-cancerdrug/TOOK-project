</main>

    <footer>
        <div class="footer-container">
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>/about.php">서비스 소개</a>
                <a href="<?php echo BASE_URL; ?>/terms.php">이용약관</a>
                <a href="<?php echo BASE_URL; ?>/privacy.php">개인정보처리방침</a>
                <a href="<?php echo BASE_URL; ?>/contact.php">문의하기</a>
            </div>
            <div class="footer-copyright">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                <br>
                <small><?php echo SITE_SLOGAN; ?></small>
            </div>
        </div>
    </footer>

    <style>
        /* 플래시 메시지 스타일 */
        .flash-message {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideDown 0.3s ease-out;
            transition: opacity 0.3s;
        }

        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideDown {
            from {
                top: 40px;
                opacity: 0;
            }
            to {
                top: 80px;
                opacity: 1;
            }
        }

        /* 사용자 드롭다운 메뉴 */
        .user-menu {
            position: relative;
        }

        .user-profile {
            cursor: pointer;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1001;
        }

        .user-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .dropdown-header strong {
            display: block;
            margin-bottom: 4px;
            color: var(--primary-color);
        }

        .dropdown-header small {
            display: block;
            color: var(--text-light);
            font-size: 13px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-color);
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: var(--bg-light);
        }

        .dropdown-item span {
            font-size: 18px;
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 8px 0;
        }

        .logout-item {
            color: var(--accent-color);
        }

        .logout-item:hover {
            background: #fff5f5;
        }
    </style>

    <script>
        // 사용자 메뉴 토글
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // 외부 클릭 시 메뉴 닫기
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (userMenu && dropdown && !userMenu.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>