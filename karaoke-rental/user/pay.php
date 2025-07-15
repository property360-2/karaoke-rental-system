<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

if (!isset($_GET['id'])) {
    header('Location: history.php');
    exit();
}
$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
if (!$booking) {
    header('Location: history.php');
    exit();
}

if ($booking['payment_status'] === 'paid' || $booking['status'] === 'cancelled') {
    header('Location: history.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare('UPDATE bookings SET payment_status = "paid" WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $booking_id, $user_id);
    $stmt->execute();
    echo '<script>alert("Payment successful! Redirecting to history...");window.location="history.php";</script>';
    exit();
}

$page_title = 'Pay for Booking';
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
        <div class="col-md-6">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center">Simulated GCash Payment</h2>
                <p class="theme-text">Booking ID: <?php echo $booking['id']; ?><br>
                Total: <b>₱<?php echo number_format($booking['total_price'],2); ?></b></p>
                <form method="post">
                    <button type="submit" class="btn theme-btn w-100">Pay Now</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="history.php" class="theme-text">Back to History</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?> 