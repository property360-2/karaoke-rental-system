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
$pending_payments = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id AND payment_status = 'unpaid' AND status != 'cancelled' ORDER BY created_at DESC LIMIT 5");

// Get notifications (confirmed bookings)
$all_notifications = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id AND status = 'confirmed' ORDER BY created_at DESC");
$notifications = [];
while ($notif = $all_notifications->fetch_assoc()) {
    $notifications[] = $notif;
}
// Notification IDs for JS
$notification_ids = array_map(function($n) { return $n['id']; }, $notifications);

$page_title = 'User Dashboard';
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
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name); ?>&background=148137&color=fff&size=48" alt="User Avatar" class="rounded-circle border" width="48" height="48" aria-label="User Avatar">
            <h2 class="theme-title mb-0">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card p-3 h-100 theme-navbar position-relative" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" title="Total number of bookings you've made.">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-calendar2-check fs-3 text-info"></i>
                    <span class="theme-text">Total Bookings</span>
                </div>
                <h3 class="mb-0"><?php echo $total_bookings; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100 theme-navbar position-relative" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" title="Bookings awaiting confirmation.">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-hourglass-split fs-3 text-warning"></i>
                    <span class="theme-text">Pending</span>
                </div>
                <h3 class="mb-0"><?php echo $pending_bookings; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100 theme-navbar position-relative" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" title="Confirmed bookings.">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-patch-check fs-3 text-success"></i>
                    <span class="theme-text">Confirmed</span>
                </div>
                <h3 class="mb-0"><?php echo $confirmed_bookings; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100 theme-navbar position-relative" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" title="Total amount spent on paid bookings.">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-cash-stack fs-3 text-primary"></i>
                    <span class="theme-text">Total Spent</span>
                </div>
                <h3 class="mb-0">₱<?php echo number_format($total_spent, 2); ?></h3>
            </div>
        </div>
    </div>
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card theme-navbar p-4">
                <h4 class="theme-title mb-3">Quick Actions</h4>
                <div class="row g-2">
                    <div class="col-md-4 mb-2">
                        <a href="rent.php" class="btn theme-btn w-100 d-flex align-items-center justify-content-center gap-2" aria-label="Book New Rental"><i class="bi bi-plus-circle"></i> Book New Rental</a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="history.php" class="btn theme-btn w-100 d-flex align-items-center justify-content-center gap-2" aria-label="View All Bookings"><i class="bi bi-list-check"></i> View All Bookings</a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="#" class="btn theme-btn w-100 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#notificationModal" aria-label="Notifications"><i class="bi bi-bell"></i> Notifications <span class="notification-count" style="display:none;"></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card theme-navbar p-4">
                <h4 class="theme-title mb-3">Pending Payments</h4>
                <?php if ($pending_payments->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" aria-label="Pending Payments">
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
                            <?php while ($row = $pending_payments->fetch_assoc()): ?>
                                <tr tabindex="0" class="table-row-hover">
                                    <td><?php echo htmlspecialchars($row['rental_date']); ?></td>
                                    <td><?php echo $row['duration_days']; ?> day(s)</td>
                                    <td><?php echo $row['units_requested']; ?></td>
                                    <td>₱<?php echo number_format($row['total_price'],2); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($row['status'] === 'confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                        <?php elseif ($row['status'] === 'cancelled'): ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo ucfirst($row['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">Unpaid</span>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-success pay-now-btn" data-id="<?php echo $row['id']; ?>" aria-label="Pay Now for Booking <?php echo $row['id']; ?>"><i class="bi bi-credit-card"></i> Pay Now</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="theme-text">No pending payments. All your bookings are paid!</p>
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
<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content theme-navbar">
            <div class="modal-header">
                <h5 class="modal-title theme-title" id="notificationModalLabel">Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (count($notifications) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifications as $notif): ?>
                            <li class="list-group-item bg-transparent theme-text">
                                <i class="bi bi-patch-check text-success"></i> Admin has already approved the booking of the videoke (Booking #<?php echo $notif['id']; ?>)
                                <br><small class="text-muted">on <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="theme-text">No notifications yet.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Pay Now Confirmation Modal -->
<div class="modal fade" id="payNowModal" tabindex="-1" aria-labelledby="payNowModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content theme-navbar">
            <div class="modal-header">
                <h5 class="modal-title theme-title" id="payNowModalLabel">Confirm Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="theme-text">Are you sure you want to proceed to payment for this booking?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmPayNowBtn" class="btn btn-success">Proceed to Pay</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
<script>
// Enable Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});
// Pay Now confirmation modal logic
const payNowBtns = document.querySelectorAll('.pay-now-btn');
const payNowModal = document.getElementById('payNowModal');
const confirmPayNowBtn = document.getElementById('confirmPayNowBtn');
let payUrl = '';
payNowBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        payUrl = 'pay.php?id=' + this.getAttribute('data-id');
        var payModal = new bootstrap.Modal(payNowModal);
        payModal.show();
    });
});
if (confirmPayNowBtn) {
    confirmPayNowBtn.addEventListener('click', function() {
        window.location.href = payUrl;
    });
}

// Notification logic: track seen notifications in localStorage
const notificationIds = <?php echo json_encode($notification_ids); ?>;
function getSeenNotifications() {
    return JSON.parse(localStorage.getItem('seen_notifications') || '[]');
}
function setSeenNotifications(ids) {
    localStorage.setItem('seen_notifications', JSON.stringify(ids));
}
function getUnseenNotifications() {
    const seen = getSeenNotifications();
    return notificationIds.filter(id => !seen.includes(id));
}
function updateNotificationCount() {
    const count = getUnseenNotifications().length;
    document.querySelectorAll('.notification-count').forEach(el => {
        el.textContent = count > 0 ? count : '';
        el.style.display = count > 0 ? '' : 'none';
    });
}
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
    const notifModal = document.getElementById('notificationModal');
    if (notifModal) {
        notifModal.addEventListener('show.bs.modal', function() {
            // Mark all as seen
            setSeenNotifications(notificationIds);
            updateNotificationCount();
        });
    }
});
</script>
<style>
.table-row-hover:hover {
    background-color: rgba(148,137,121,0.1) !important;
    cursor: pointer;
}
.card[tabindex="0"]:focus {
    outline: 2px solid #948979;
    box-shadow: 0 0 0 2px #94897933;
}
</style> 