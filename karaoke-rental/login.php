<?php
require_once 'includes/db.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hash, $role);
        $stmt->fetch();
        // Check both password_hash() and MD5 for compatibility
        if (password_verify($password, $hash) || md5($password) === $hash) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            if ($role === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: user/dashboard.php');
            }
            exit();
        } else {
            $errors[] = 'Invalid email or password.';
        }
    } else {
        $errors[] = 'Invalid email or password.';
    }
    $stmt->close();
}

$page_title = 'Login';
$base_path = '';
$show_register = true;
include 'includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card theme-navbar p-4" style="background: var(--dark-card, #fff); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h2 class="mb-4 theme-title text-center" style="font-size:2rem; font-weight:700; color:var(--accent,#222); letter-spacing:1px;">Login</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger" style="font-size:1.1rem; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb; border-radius:4px;">
                        <?php foreach ($errors as $e) echo '<span style="display:block; margin-bottom:2px;">'.htmlspecialchars($e).'</span>'; ?>
                    </div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Email</label>
                        <input type="email" name="email" class="form-control" required style="font-size:1.08rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Password</label>
                        <input type="password" name="password" class="form-control" required style="font-size:1.08rem;">
                    </div>
                    <button type="submit" class="btn theme-btn w-100" style="font-size:1.15rem; font-weight:600; padding:10px 0;">Login</button>
                    <div class="mt-3 text-center">
                        <a href="user/register.php" class="theme-text" style="font-size:1.05rem; text-decoration:underline; color:var(--accent,#222);">Don't have an account? Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 