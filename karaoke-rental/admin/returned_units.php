<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();

$page_title = 'Returned Units';
$base_path = '../';
$is_admin = true;
$show_logout = true;
$user_name = $_SESSION['name'];
$nav_links = [
    ['url' => 'dashboard.php', 'text' => 'Dashboard', 'active' => false],
    ['url' => 'manage_units.php', 'text' => 'Manage Units', 'active' => false],
    ['url' => 'returned_units.php', 'text' => 'Returned Units', 'active' => true]
];
include '../includes/header.php';

// Fetch returned bookings
$sql = 'SELECT b.*, u.name as user_name FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.units_returned = 1 ORDER BY b.created_at DESC';
$result = $conn->query($sql);
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center">Returned Units</h2>
                <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User</th>
                                <th>Rental Date</th>
                                <th>Duration (days)</th>
                                <th>Units Returned</th>
                                <th>Status</th>
                                <th>Returned At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['rental_date']); ?></td>
                                <td><?php echo $row['duration_days']; ?></td>
                                <td><?php echo $row['units_requested']; ?></td>
                                <td><span class="badge bg-success">Returned</span></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="theme-text">No units have been returned yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 