<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';
$units_available = 0;

// Get total units from settings
$res = $conn->query('SELECT total_units FROM settings WHERE id=1');
if ($row = $res->fetch_assoc()) {
    $units_available = $row['total_units'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rental_date = $_POST['rental_date'];
    $duration = (int)$_POST['duration'];
    $units = (int)$_POST['units'];
    $total_price = $units * $duration * 500;
    if (!$rental_date || !$duration || !$units) {
        $errors[] = 'All fields are required.';
    } elseif ($units < 1 || $duration < 1) {
        $errors[] = 'Invalid duration or units.';
    } elseif ($units > $units_available) {
        $errors[] = 'Requested units exceed available.';
    } else {
        // Check for overlapping bookings
        $date_end = date('Y-m-d', strtotime($rental_date . ' + ' . ($duration-1) . ' days'));
        $stmt = $conn->prepare('SELECT SUM(units_requested) FROM bookings WHERE status IN ("pending","confirmed") AND ((rental_date <= ? AND DATE_ADD(rental_date, INTERVAL duration_days-1 DAY) >= ?) OR (rental_date <= ? AND DATE_ADD(rental_date, INTERVAL duration_days-1 DAY) >= ?))');
        $stmt->bind_param('ssss', $rental_date, $rental_date, $date_end, $date_end);
        $stmt->execute();
        $stmt->bind_result($units_booked);
        $stmt->fetch();
        $stmt->close();
        $units_booked = $units_booked ?: 0;
        if ($units_booked + $units > $units_available) {
            $errors[] = 'Not enough units available for the selected period.';
        } else {
            $stmt = $conn->prepare('INSERT INTO bookings (user_id, rental_date, duration_days, units_requested, total_price) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('isiid', $user_id, $rental_date, $duration, $units, $total_price);
            if ($stmt->execute()) {
                $success = 'Booking submitted! <a href="history.php">View your bookings</a>.';
            } else {
                $errors[] = 'Booking failed. Try again.';
            }
            $stmt->close();
        }
    }
}

$page_title = 'Book Karaoke Rental';
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
        <div class="col-md-7">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center"><i class="bi bi-music-note-beamed"></i> Book Karaoke Rental</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php foreach ($errors as $e) echo $e.'<br>'; ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div><?php echo $success; ?></div>
                    </div>
                <?php endif; ?>
                <form method="post" id="bookingForm" novalidate aria-label="Karaoke Booking Form">
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="rentalDateIcon"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="rental_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required aria-label="Rental Date" aria-describedby="rentalDateIcon">
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="durationIcon"><i class="bi bi-hourglass"></i></span>
                        <input type="number" name="duration" class="form-control" min="1" value="1" required aria-label="Duration (days)" aria-describedby="durationIcon">
                        <span class="input-group-text">days</span>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="unitsIcon"><i class="bi bi-speaker"></i></span>
                        <input type="number" name="units" class="form-control" min="1" max="<?php echo $units_available; ?>" value="1" required aria-label="Number of Units" aria-describedby="unitsIcon">
                        <span class="input-group-text">units</span>
                    </div>
                    <div class="form-text mb-2 ms-1"><i class="bi bi-info-circle"></i> Available: <?php echo $units_available; ?></div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="priceIcon"><i class="bi bi-cash-coin"></i></span>
                        <input type="text" id="totalPrice" class="form-control" value="500" readonly aria-label="Total Price (₱)" aria-describedby="priceIcon">
                    </div>
                    <!-- Booking Summary -->
                    <div class="mb-3 card p-2 bg-light border-0" id="bookingSummary" style="display:none;">
                        <div class="theme-text mb-1"><i class="bi bi-receipt"></i> <b>Booking Summary</b></div>
                        <div><i class="bi bi-calendar-event"></i> <span id="summaryDate"></span></div>
                        <div><i class="bi bi-hourglass"></i> <span id="summaryDuration"></span> day(s)</div>
                        <div><i class="bi bi-speaker"></i> <span id="summaryUnits"></span> unit(s)</div>
                        <div><i class="bi bi-cash-coin"></i> ₱<span id="summaryPrice"></span></div>
                    </div>
                    <button type="submit" class="btn theme-btn w-100 d-flex align-items-center justify-content-center gap-2" aria-label="Book Now"><i class="bi bi-check2-circle"></i> Book Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
// Auto-calculate total price and update summary
const form = document.getElementById('bookingForm');
const priceField = document.getElementById('totalPrice');
const summary = document.getElementById('bookingSummary');
const summaryDate = document.getElementById('summaryDate');
const summaryDuration = document.getElementById('summaryDuration');
const summaryUnits = document.getElementById('summaryUnits');
const summaryPrice = document.getElementById('summaryPrice');
function updateSummary() {
    const units = parseInt(form.units.value) || 0;
    const days = parseInt(form.duration.value) || 0;
    const date = form.rental_date.value;
    priceField.value = units * days * 500;
    if (date && units && days) {
        summary.style.display = '';
        summaryDate.textContent = date;
        summaryDuration.textContent = days;
        summaryUnits.textContent = units;
        summaryPrice.textContent = units * days * 500;
    } else {
        summary.style.display = 'none';
    }
}
form.addEventListener('input', updateSummary);
window.addEventListener('DOMContentLoaded', updateSummary);
</script>
<?php include '../includes/footer.php'; ?> 