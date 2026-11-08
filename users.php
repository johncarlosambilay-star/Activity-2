<?php
require_once "config.php";
require_admin();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $action = $_POST["action"] ?? "";

    if ($action === "create") {
        $name = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $role = $_POST["role"] ?? "user";

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || !in_array($role, ["admin", "user"], true)) {
            $error = "Enter a valid name and email, a password with at least 6 characters, and a valid role.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
                $message = "User account created successfully.";
            } catch (PDOException $e) {
                $error = "That email address is already registered.";
            }
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);
        if ($id === (int)$_SESSION["user_id"]) {
            $error = "You cannot delete your own administrator account while signed in.";
        } else {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $target = $stmt->fetch();
            if (!$target) {
                $error = "User account not found.";
            } elseif ($target["role"] === "admin" && (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() <= 1) {
                $error = "You cannot delete the last administrator account.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $message = "User account deleted.";
            }
        }
    }
}

$users = $pdo->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | User Account System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">User Account System</div>
    <nav><a href="dashboard.php">Dashboard</a><a href="users.php">Manage Users</a><a href="logout.php">Logout</a></nav>
</header>
<main class="container">
    <div class="page-heading"><div><p class="eyebrow">ADMINISTRATION</p><h1>Manage User Accounts</h1><p class="muted">Add, view, edit, and remove accounts.</p></div><a href="dashboard.php" class="btn secondary">Back to Dashboard</a></div>

    <?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

    <section class="panel">
        <h2>Create New Account</h2>
        <form method="POST" class="create-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">
            <div><label>Full Name</label><input type="text" name="full_name" placeholder="Full name" required></div>
            <div><label>Email Address</label><input type="email" name="email" placeholder="name@example.com" required></div>
            <div><label>Password</label><input type="password" name="password" placeholder="At least 6 characters" minlength="6" required></div>
            <div><label>Account Role</label><select name="role"><option value="user">User</option><option value="admin">Admin</option></select></div>
            <button type="submit" class="btn">Add Account</button>
        </form>
    </section>

    <section class="panel">
        <h2>Registered Accounts <span class="count"><?= count($users) ?></span></h2>
        <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Date Created</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= h($user["id"]) ?></td>
                    <td><?= h($user["full_name"]) ?></td>
                    <td><?= h($user["email"]) ?></td>
                    <td><span class="role"><?= h($user["role"]) ?></span></td>
                    <td><?= h($user["created_at"]) ?></td>
                    <td class="actions">
                        <a class="small-btn" href="edit_user.php?id=<?= h($user["id"]) ?>">Edit</a>
                        <?php if ((int)$user["id"] !== (int)$_SESSION["user_id"]): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= h($user["id"]) ?>">
                            <button class="small-btn danger-btn" type="submit">Delete</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users): ?><tr><td colspan="6">No accounts found.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>
</main>
</body>
</html>
