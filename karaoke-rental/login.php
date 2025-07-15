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
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center">Login</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e) echo $e.'<br>'; ?>
                    </div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn theme-btn w-100">Login</button>
                    <div class="mt-3 text-center">
                        <a href="user/register.php" class="theme-text">Don't have an account? Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 