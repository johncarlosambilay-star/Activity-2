<?php
require_once "config.php";
require_admin();

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);
$stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User account not found. <a href='users.php'>Go back</a>");
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $role = $_POST["role"] ?? "user";
    $newPassword = $_POST["password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ["admin", "user"], true)) {
        $error = "Please enter a valid name, email, and role.";
    } elseif ($id === (int)$_SESSION["user_id"] && $role !== "admin") {
        $error = "You cannot remove your own administrator role.";
    } else {
        try {
            if ($newPassword !== "" && strlen($newPassword) < 6) {
                $error = "New password must contain at least 6 characters.";
            } else {
                if ($newPassword !== "") {
                    $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, password=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, password_hash($newPassword, PASSWORD_DEFAULT), $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE id=?");
                    $stmt->execute([$name, $email, $role, $id]);
                }
                if ($id === (int)$_SESSION["user_id"]) {
                    $_SESSION["full_name"] = $name;
                    $_SESSION["email"] = $email;
                }
                header("Location: users.php?updated=1");
                exit;
            }
        } catch (PDOException $e) {
            $error = "That email address is already used by another account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Account | User Account System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar"><div class="brand">User Account System</div><nav><a href="dashboard.php">Dashboard</a><a href="users.php">Manage Users</a><a href="logout.php">Logout</a></nav></header>
<main class="container narrow">
    <section class="panel">
        <p class="eyebrow">ACCOUNT MANAGEMENT</p>
        <h1>Edit User Account</h1>
        <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= h($user["id"]) ?>">
            <label>Full Name</label><input type="text" name="full_name" value="<?= h($user["full_name"]) ?>" required>
            <label>Email Address</label><input type="email" name="email" value="<?= h($user["email"]) ?>" required>
            <label>Account Role</label>
            <select name="role"><option value="user" <?= $user["role"] === "user" ? "selected" : "" ?>>User</option><option value="admin" <?= $user["role"] === "admin" ? "selected" : "" ?>>Admin</option></select>
            <label>New Password <span class="muted">(leave blank to keep current password)</span></label>
            <input type="password" name="password" minlength="6" placeholder="Enter new password (optional)">
            <div class="form-actions"><button class="btn" type="submit">Save Changes</button><a href="users.php" class="btn secondary">Cancel</a></div>
        </form>
    </section>
</main>
</body>
</html>
