<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $units = (int)$_POST['units'];
    if ($units > 0) {
        $conn->query("UPDATE settings SET total_units=$units WHERE id=1");
        $success = 'Total units updated!';
    }
}
$units = $conn->query('SELECT total_units FROM settings WHERE id=1')->fetch_assoc()['total_units'];

$page_title = 'Manage Karaoke Units';
$base_path = '../';
$is_admin = true;
$show_logout = true;
$user_name = $_SESSION['name'];
$nav_links = [
    ['url' => 'dashboard.php', 'text' => 'Dashboard', 'active' => basename(__FILE__) === 'dashboard.php'],
    ['url' => 'manage_units.php', 'text' => 'Manage Units', 'active' => basename(__FILE__) === 'manage_units.php']
];
include '../includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center">Manage Karaoke Units</h2>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Total Units</label>
                        <input type="number" name="units" class="form-control" min="1" value="<?php echo $units; ?>" required>
                    </div>
                    <button type="submit" class="btn theme-btn w-100">Update</button>
                    <div class="mt-3 text-center">
                        <a href="dashboard.php" class="theme-text">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 