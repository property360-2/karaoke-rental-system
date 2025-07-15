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
$nav_links = [
    ['url' => 'dashboard.php', 'text' => 'Dashboard'],
    ['url' => 'rent.php', 'text' => 'Book Rental'],
    ['url' => 'history.php', 'text' => 'Booking History'],
    ['url' => 'payment_history.php', 'text' => 'Payment History']
];
include '../includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center">My Booking History</h2>
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Units</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['rental_date']); ?></td>
                            <td><?php echo $row['duration_days']; ?> day(s)</td>
                            <td><?php echo $row['units_requested']; ?></td>
                            <td>₱<?php echo number_format($row['total_price'],2); ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                            <td><?php echo ucfirst($row['payment_status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 