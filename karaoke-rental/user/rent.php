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
        <div class="col-md-7">
            <div class="card theme-navbar p-4">
                <h2 class="mb-4 theme-title text-center">Book Karaoke Rental</h2>
                <?php if ($errors): ?>
                    <div class="alert alert-danger"><?php foreach ($errors as $e) echo $e.'<br>'; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="post" id="bookingForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Rental Date</label>
                        <input type="date" name="rental_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (days)</label>
                        <input type="number" name="duration" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Number of Units</label>
                        <input type="number" name="units" class="form-control" min="1" max="<?php echo $units_available; ?>" value="1" required>
                        <div class="form-text">Available: <?php echo $units_available; ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Price (₱)</label>
                        <input type="text" id="totalPrice" class="form-control" value="500" readonly>
                    </div>
                    <button type="submit" class="btn theme-btn w-100">Book Now</button>
                    <div class="mt-3 text-center">
                        <a href="history.php" class="theme-text">View Booking History</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
// Auto-calculate total price
const form = document.getElementById('bookingForm');
const priceField = document.getElementById('totalPrice');
form.addEventListener('input', function() {
    const units = parseInt(form.units.value) || 0;
    const days = parseInt(form.duration.value) || 0;
    priceField.value = units * days * 500;
});
</script>
<?php include '../includes/footer.php'; ?> 