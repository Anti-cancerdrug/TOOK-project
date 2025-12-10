<?php
$host = '127.0.0.1';
$dbname = 'took_db';
$username = 'root';  // ← 이건 $username
$password = '';      // ← 이건 $password
$charset = 'utf8mb4';

try {
    // $user, $pass (X) → $username, $password (O)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    echo "✅ 연결 성공!";
} catch(PDOException $e) {
    echo "❌ 연결 실패: " . $e->getMessage();
}
?>
```

**변경사항**: 11번째 줄
- `$user, $pass` → `$username, $password`

---

## 다시 테스트!
```
http://localhost/took-project/db_test.php