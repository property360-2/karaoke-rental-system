<?php
require_once '../includes/db.php';
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    if (!$name || !$email || !$password || !$confirm) {
        $errors[] = 'All fields marked * are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $name, $email, $hash, $phone, $address);
            if ($stmt->execute()) {
                $success = 'Registration successful! <a href="/karaoke-rental\login.php">Login here</a>.';
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
}

$page_title = 'User Registration';
$base_path = '../';
$show_login = true;
include '../includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card theme-navbar p-4" style="background: var(--dark-card, #fff); box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h2 class="mb-4 theme-title text-center" style="font-size:2rem; font-weight:700; color:var(--accent,#222); letter-spacing:1px;">User Registration</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger" style="font-size:1.1rem; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb; border-radius:4px;">
                        <?php foreach ($errors as $e) echo '<span style="display:block; margin-bottom:2px;">'.htmlspecialchars($e).'</span>'; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="font-size:1.1rem; color:#155724; background:#d4edda; border:1px solid #c3e6cb; border-radius:4px;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Full Name *</label>
                        <input type="text" name="name" class="form-control" required style="font-size:1.08rem;" value="<?php echo isset($name) ? htmlspecialchars($name) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Email *</label>
                        <input type="email" name="email" class="form-control" required style="font-size:1.08rem;" value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Password *</label>
                        <input type="password" name="password" class="form-control" required style="font-size:1.08rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Confirm Password *</label>
                        <input type="password" name="confirm" class="form-control" required style="font-size:1.08rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Phone</label>
                        <input type="text" name="phone" class="form-control" style="font-size:1.08rem;" value="<?php echo isset($phone) ? htmlspecialchars($phone) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:1.1rem; font-weight:600; color:var(--accent,#222);">Address</label>
                        <textarea name="address" class="form-control" style="font-size:1.08rem; min-height:60px;"><?php echo isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn theme-btn w-100" style="font-size:1.15rem; font-weight:600; padding:10px 0;">Register</button>
                    <div class="mt-3 text-center">
                        <a href="login.php" class="theme-text" style="font-size:1.05rem; text-decoration:underline; color:var(--accent,#222);">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 