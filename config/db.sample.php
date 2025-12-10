<?php
$host = 'localhost';
$dbname = 'took_db';
$username = 'root';
$password = '';  // XAMPP는 기본적으로 비밀번호 없음!
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("데이터베이스 연결에 실패했습니다. 관리자에게 문의하세요.");
}
?>