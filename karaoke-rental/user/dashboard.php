<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get user stats
$total_bookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE user_id = $user_id")->fetch_assoc()['cnt'];
$pending_bookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE user_id = $user_id AND status = 'pending'")->fetch_assoc()['cnt'];
$confirmed_bookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE user_id = $user_id AND status = 'confirmed'")->fetch_assoc()['cnt'];
$total_spent = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE user_id = $user_id AND payment_status = 'paid'")->fetch_assoc()['total'] ?: 0;

// Get recent bookings
$recent_bookings = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

$page_title = 'User Dashboard';
$base_path = '../';
$user_name = $_SESSION['name'];
$show_logout = true;
$nav_links = [
    ['url' => 'dashboard.php', 'text' => 'Dashboard'],
    ['url' => 'rent.php', 'text' => 'Book Rental'],
    ['url' => 'history.php', 'text' => 'Booking History'],
    ['url' => 'payment_history.php', 'text' => 'Payment History']
];
include '../includes/header.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="theme-title">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <div>
            <a href="rent.php" class="btn btn-sm theme-btn">Book New Rental</a>
            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 mb-2 theme-navbar">
                <div class="theme-text">Total Bookings</div>
                <h3><?php echo $total_bookings; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 mb-2 theme-navbar">
                <div class="theme-text">Pending</div>
                <h3><?php echo $pending_bookings; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 mb-2 theme-navbar">
                <div class="theme-text">Confirmed</div>
                <h3><?php echo $confirmed_bookings; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 mb-2 theme-navbar">
                <div class="theme-text">Total Spent</div>
                <h3>₱<?php echo number_format($total_spent, 2); ?></h3>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card theme-navbar p-4">
                <h4 class="theme-title mb-3">Quick Actions</h4>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="rent.php" class="btn theme-btn w-100">Book New Rental</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="history.php" class="btn theme-btn w-100">View All Bookings</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="payment_history.php" class="btn theme-btn w-100">Payment History</a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="#" class="btn theme-btn w-100">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card theme-navbar p-4">
                <h4 class="theme-title mb-3">Recent Bookings</h4>
                <?php if ($recent_bookings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Units</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['rental_date']); ?></td>
                                    <td><?php echo $row['duration_days']; ?> day(s)</td>
                                    <td><?php echo $row['units_requested']; ?></td>
                                    <td>₱<?php echo number_format($row['total_price'],2); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td><?php echo ucfirst($row['payment_status']); ?></td>
                                    <td>
                                        <?php if ($row['payment_status'] === 'unpaid' && $row['status'] !== 'cancelled'): ?>
                                            <a href="pay.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Pay Now</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="theme-text">No bookings yet.</p>
                        <a href="rent.php" class="btn theme-btn">Make Your First Booking</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content theme-navbar">
            <div class="modal-header">
                <h5 class="modal-title theme-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="theme-text">Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div> 