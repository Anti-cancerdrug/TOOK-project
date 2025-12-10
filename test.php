<?php
require_once 'config/db.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOOK - 연결 테스트</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="test-box">
        <h1>🚀 TOOK 프로젝트 시스템 테스트</h1>
        
        <h2>📋 기본 정보</h2>
        <div class="info">
            <p><strong>사이트 이름:</strong> <?php echo SITE_NAME; ?></p>
            <p><strong>슬로건:</strong> <?php echo SITE_SLOGAN; ?></p>
            <p><strong>베이스 URL:</strong> <?php echo BASE_URL; ?></p>
        </div>

        <h2>🗄️ 데이터베이스 연결 테스트</h2>
        <?php
        try {
            // 데이터베이스 연결 확인
            $stmt = $pdo->query("SELECT VERSION()");
            $version = $stmt->fetchColumn();
            echo "<p class='success'>✅ 데이터베이스 연결 성공!</p>";
            echo "<p>데이터베이스 버전: <strong>{$version}</strong></p>";
            
            // 테이블 목록 확인
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<h3>📊 생성된 테이블 목록 (" . count($tables) . "개)</h3>";
            
            if (count($tables) > 0) {
                echo "<table>";
                echo "<tr><th>번호</th><th>테이블 이름</th><th>레코드 수</th></tr>";
                
                foreach ($tables as $index => $table) {
                    $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                    $count = $countStmt->fetchColumn();
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>";
                    echo "<td><strong>{$table}</strong></td>";
                    echo "<td>{$count}개</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // 장르 데이터 확인
                $stmt = $pdo->query("SELECT * FROM genre");
                $genres = $stmt->fetchAll();
                
                if (count($genres) > 0) {
                    echo "<h3>🎭 장르 데이터 확인</h3>";
                    echo "<p class='success'>✅ 기본 장르 데이터 " . count($genres) . "개 확인</p>";
                    echo "<p>" . implode(', ', array_column($genres, 'name')) . "</p>";
                }
                
            } else {
                echo "<p class='error'>❌ 테이블이 생성되지 않았습니다. SQL 파일을 다시 실행해주세요.</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p class='error'>❌ 데이터베이스 오류: " . $e->getMessage() . "</p>";
        }
        ?>

        <h2>🔧 함수 테스트</h2>
        <?php
        // CSRF 토큰 생성 테스트
        $token = generate_csrf_token();
        echo "<p class='success'>✅ CSRF 토큰 생성: " . substr($token, 0, 20) . "...</p>";
        
        // 로그인 상태 확인
        if (is_logged_in()) {
            echo "<p class='success'>✅ 현재 로그인 상태입니다.</p>";
        } else {
            echo "<p>ℹ️ 현재 로그아웃 상태입니다.</p>";
        }
        
        // 시간 함수 테스트
        $test_time = date('Y-m-d H:i:s', strtotime('-2 hours'));
        echo "<p class='success'>✅ 시간 함수: " . time_ago($test_time) . "</p>";
        
        // XSS 방지 함수 테스트
        $dangerous = "<script>alert('xss')</script>";
        $safe = escape($dangerous);
        echo "<p class='success'>✅ XSS 방지: " . $safe . "</p>";
        ?>

        <h2>📁 업로드 폴더 확인</h2>
        <?php
        $upload_dirs = [
            'uploads/profile',
            'uploads/thumbnails',
            'uploads/toon',
            'uploads/novel'
        ];
        
        foreach ($upload_dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (is_dir($path) && is_writable($path)) {
                echo "<p class='success'>✅ {$dir} - 존재하고 쓰기 가능</p>";
            } elseif (is_dir($path)) {
                echo "<p class='error'>⚠️ {$dir} - 존재하지만 쓰기 권한 없음</p>";
            } else {
                echo "<p class='error'>❌ {$dir} - 폴더가 없습니다.</p>";
            }
        }
        ?>

        <h2>✅ 최종 결과</h2>
        <div class="info">
            <?php
            if (count($tables) >= 12) {
                echo "<p class='success'><strong>🎉 모든 설정이 완료되었습니다!</strong></p>";
                echo "<p>이제 회원가입/로그인 기능을 만들 준비가 되었습니다.</p>";
                echo "<p><a href='member/register.php'>회원가입 페이지로 이동 →</a></p>";
            } else {
                echo "<p class='error'><strong>⚠️ 설정이 완료되지 않았습니다.</strong></p>";
                echo "<p>SQL 파일을 다시 실행해주세요.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>