<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();

// Get stats
$units = $conn->query('SELECT total_units FROM settings WHERE id=1')->fetch_assoc()['total_units'];
$total_bookings = $conn->query('SELECT COUNT(*) as cnt FROM bookings')->fetch_assoc()['cnt'];

// Filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$payment = isset($_GET['payment']) ? $_GET['payment'] : '';
$where = [];
$params = [];
$types = '';
if ($status) { $where[] = 'status=?'; $params[] = $status; $types .= 's'; }
if ($payment) { $where[] = 'payment_status=?'; $params[] = $payment; $types .= 's'; }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT b.*, u.name FROM bookings b JOIN users u ON b.user_id=u.id $where_sql ORDER BY b.created_at DESC";
$stmt = $conn->prepare($sql . ($params ? '' : ''));
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$page_title = 'Admin Dashboard';
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
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="theme-title">Admin Dashboard</h2>
        <div>
            <a href="manage_units.php" class="btn btn-sm theme-btn">Manage Units</a>
            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 mb-2 theme-navbar">
                <div class="theme-text">Total Karaoke Units</div>
                <h3><?php echo $units; ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 mb-2 theme-navbar">
                <div class="theme-text">Total Bookings</div>
                <h3><?php echo $total_bookings; ?></h3>
            </div>
        </div>
    </div>
    <form class="row g-3 mb-3" method="get">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?php if($status==='pending') echo 'selected'; ?>>Pending</option>
                <option value="confirmed" <?php if($status==='confirmed') echo 'selected'; ?>>Confirmed</option>
                <option value="cancelled" <?php if($status==='cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="payment" class="form-select">
                <option value="">All Payment</option>
                <option value="unpaid" <?php if($payment==='unpaid') echo 'selected'; ?>>Unpaid</option>
                <option value="paid" <?php if($payment==='paid') echo 'selected'; ?>>Paid</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn theme-btn w-100">Filter</button>
        </div>
    </form>
    <div class="card theme-navbar p-3">
        <h4 class="theme-title mb-3">All Bookings</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>User</th>
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $row['rental_date']; ?></td>
                        <td><?php echo $row['duration_days']; ?> day(s)</td>
                        <td><?php echo $row['units_requested']; ?></td>
                        <td>₱<?php echo number_format($row['total_price'],2); ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td><?php echo ucfirst($row['payment_status']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="dashboard.php?action=confirm&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Confirm</a>
                                <a href="dashboard.php?action=cancel&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                            <?php elseif ($row['status'] === 'confirmed' && !$row['units_returned']): ?>
                                <a href="dashboard.php?action=return&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Returned</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// Handle confirm/cancel actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'confirm') {
        // Get units_requested for this booking
        $booking = $conn->query("SELECT units_requested, status FROM bookings WHERE id=$id")->fetch_assoc();
        if ($booking && $booking['status'] !== 'confirmed') {
            $units_requested = (int)$booking['units_requested'];
            // Decrease total_units
            $conn->query("UPDATE settings SET total_units = total_units - $units_requested WHERE id=1");
            // Confirm the booking
            $conn->query("UPDATE bookings SET status='confirmed' WHERE id=$id");
        }
    } elseif ($_GET['action'] === 'cancel') {
        $conn->query("UPDATE bookings SET status='cancelled' WHERE id=$id");
    } elseif ($_GET['action'] === 'return') {
        // Mark as returned and increase total_units
        $booking = $conn->query("SELECT units_requested, units_returned, status FROM bookings WHERE id=$id")->fetch_assoc();
        if ($booking && $booking['status'] === 'confirmed' && !$booking['units_returned']) {
            $units_requested = (int)$booking['units_requested'];
            $conn->query("UPDATE settings SET total_units = total_units + $units_requested WHERE id=1");
            $conn->query("UPDATE bookings SET units_returned=1 WHERE id=$id");
        }
    }
    echo '<script>window.location="dashboard.php";</script>';
    exit();
}
?>
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