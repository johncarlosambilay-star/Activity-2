<?php
// Database connection and session setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$dbname = "user_accounts_system";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Database connection failed. Check config.php and import database.sql.");
}

function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if ($_SESSION["role"] !== "admin") {
        header("Location: dashboard.php");
        exit;
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf() {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $token = $_POST["csrf_token"] ?? "";
        if (!hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
            die("Invalid request. Please go back and try again.");
        }
    }
}
