<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();
$user_id = $_SESSION['user_id'];
$res = $conn->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC');
$res->bind_param('i', $user_id);
$res->execute();
$result = $res->get_result();

$page_title = 'Booking History';
$base_path = '../';
$user_name = $_SESSION['name'];
$show_logout = true;
$current_page = basename(__FILE__);
$nav_links = [
    ['url' => 'dashboard.php', 'text' => 'Dashboard', 'active' => $current_page === 'dashboard.php'],
    ['url' => 'rent.php', 'text' => 'Book Rental', 'active' => $current_page === 'rent.php'],
    ['url' => 'history.php', 'text' => 'Booking History', 'active' => $current_page === 'history.php'],
    ['url' => 'payment_history.php', 'text' => 'Payment History', 'active' => $current_page === 'payment_history.php'],
];
include '../includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center"><i class="bi bi-clock-history"></i> My Booking History</h2>
                <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th><i class="bi bi-calendar-event"></i> Date</th>
                            <th><i class="bi bi-hourglass"></i> Duration</th>
                            <th><i class="bi bi-speaker"></i> Units</th>
                            <th><i class="bi bi-cash-coin"></i> Total Price</th>
                            <th><i class="bi bi-info-circle"></i> Status</th>
                            <th><i class="bi bi-credit-card"></i> Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="table-row-hover">
                            <td><i class="bi bi-calendar-event"></i> <?php echo date('M d, Y', strtotime($row['rental_date'])); ?></td>
                            <td><?php echo $row['duration_days']; ?> day(s)</td>
                            <td><?php echo $row['units_requested']; ?></td>
                            <td>₱<?php echo number_format($row['total_price'],2); ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>
                                <?php elseif ($row['status'] === 'confirmed'): ?>
                                    <span class="badge bg-success"><i class="bi bi-patch-check"></i> Confirmed</span>
                                <?php elseif ($row['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Cancelled</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo ucfirst($row['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['payment_status'] === 'paid'): ?>
                                    <span class="badge bg-success"><i class="bi bi-credit-card"></i> Paid</span>
                                <?php elseif ($row['payment_status'] === 'unpaid'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-cash"></i> Unpaid</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo ucfirst($row['payment_status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-emoji-frown display-4 text-muted"></i>
                    <p class="theme-text">No booking history found.</p>
                    <a href="rent.php" class="btn theme-btn"><i class="bi bi-plus-circle"></i> Book your first karaoke rental</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<style>
.table-row-hover:hover {
    background-color: rgba(148,137,121,0.1) !important;
    cursor: pointer;
}
</style>
<?php include '../includes/footer.php'; ?> 