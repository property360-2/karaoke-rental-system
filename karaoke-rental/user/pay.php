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
                <h2 class="mb-4 theme-title text-center"><i class="bi bi-wallet2"></i> Simulated GCash Payment</h2>
                <p class="theme-text">Booking ID: <?php echo $booking['id']; ?><br>
                Total: <b>₱<?php echo number_format($booking['total_price'],2); ?></b></p>
                <form method="post" id="payForm">
                    <button type="button" class="btn theme-btn w-100 d-flex align-items-center justify-content-center gap-2" id="payNowBtn" aria-label="Pay Now"><i class="bi bi-cash-coin"></i> Pay Now</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="history.php" class="theme-text d-flex align-items-center justify-content-center gap-1"><i class="bi bi-arrow-left"></i> Back to History</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Payment Confirmation Modal -->
<div class="modal fade" id="payConfirmModal" tabindex="-1" aria-labelledby="payConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content theme-navbar">
            <div class="modal-header">
                <h5 class="modal-title theme-title" id="payConfirmModalLabel"><i class="bi bi-shield-check"></i> Confirm Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="theme-text">Are you sure you want to pay for this booking?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="payForm" class="btn btn-success d-flex align-items-center gap-2" id="confirmPayBtn"><span id="paySpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Yes, Pay Now</button>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
<script>
const payNowBtn = document.getElementById('payNowBtn');
const payConfirmModal = new bootstrap.Modal(document.getElementById('payConfirmModal'));
const payForm = document.getElementById('payForm');
const paySpinner = document.getElementById('paySpinner');
const confirmPayBtn = document.getElementById('confirmPayBtn');

payNowBtn.addEventListener('click', function(e) {
    e.preventDefault();
    payConfirmModal.show();
});
confirmPayBtn.addEventListener('click', function() {
    paySpinner.classList.remove('d-none');
    payForm.submit();
});
</script> 