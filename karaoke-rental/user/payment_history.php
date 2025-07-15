<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get paid bookings only
$paid_bookings = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id AND payment_status = 'paid' ORDER BY created_at DESC");

$page_title = 'Payment History';
$base_path = '../';
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

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="theme-title">Payment History</h2>
        <a href="dashboard.php" class="btn btn-sm theme-btn">Back to Dashboard</a>
    </div>

    <div class="card theme-navbar p-4">
        <h4 class="theme-title mb-3">Paid Bookings</h4>
        <?php if ($paid_bookings->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Units</th>
                            <th>Total Price</th>
                            <th>Payment Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $paid_bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['rental_date']); ?></td>
                            <td><?php echo $row['duration_days']; ?> day(s)</td>
                            <td><?php echo $row['units_requested']; ?></td>
                            <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td><span class="badge bg-success">Paid</span></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p class="theme-text">No payment history found.</p>
                <a href="dashboard.php" class="btn theme-btn">Pay a booking to see your first payment history</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 