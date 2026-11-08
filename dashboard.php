<?php
require_once "config.php";
require_login();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalRegular = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | User Account System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">User Account System</div>
    <nav><a href="dashboard.php">Dashboard</a><?php if ($_SESSION["role"] === "admin"): ?><a href="users.php">Manage Users</a><?php endif; ?><a href="logout.php">Logout</a></nav>
</header>
<main class="container">
    <div class="welcome">
        <div><p class="eyebrow">DASHBOARD</p><h1>Welcome, <?= h($_SESSION["full_name"]) ?>!</h1><p class="muted">You are signed in as <span class="role"><?= h($_SESSION["role"]) ?></span>.</p></div>
    </div>

    <?php if ($_SESSION["role"] === "admin"): ?>
    <section class="stats">
        <div class="stat-card"><p>Total Accounts</p><h2><?= h($totalUsers) ?></h2></div>
        <div class="stat-card"><p>Administrator Accounts</p><h2><?= h($totalAdmins) ?></h2></div>
        <div class="stat-card"><p>Regular User Accounts</p><h2><?= h($totalRegular) ?></h2></div>
    </section>
    <section class="panel">
        <h2>Account Management</h2>
        <p class="muted">Create, view, update, and delete user accounts from one page.</p>
        <a class="btn" href="users.php">Manage User Accounts</a>
    </section>
    <?php else: ?>
    <section class="panel">
        <h2>My Account</h2>
        <p><strong>Name:</strong> <?= h($_SESSION["full_name"]) ?></p>
        <p><strong>Email:</strong> <?= h($_SESSION["email"]) ?></p>
        <p><strong>Role:</strong> <?= h($_SESSION["role"]) ?></p>
        <p class="muted">Your account is managed by the system administrator.</p>
    </section>
    <?php endif; ?>
</main>
</body>
</html>
