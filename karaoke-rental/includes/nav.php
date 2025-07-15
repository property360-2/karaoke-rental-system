<?php
// Shared navigation bar for user and admin
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<nav id="mainNavbar" class="navbar navbar-expand-lg theme-navbar">
    <div class="container-fluid">
        <span class="navbar-brand" id="brandLogo">Karaoke Rental</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($nav_links)): ?>
            <ul class="navbar-nav me-auto">
                <?php foreach ($nav_links as $link):
                    $is_active = (isset($link['active']) && $link['active']) || ($current_page === $link['url']); ?>
                <li class="nav-item">
                    <a class="nav-link<?php echo $is_active ? ' active' : ''; ?>" href="<?php echo $link['url']; ?>"><?php echo $link['text']; ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($user_name)): ?>
                <li class="nav-item"><span class="nav-link">Welcome, <?php echo htmlspecialchars($user_name); ?></span></li>
                <?php endif; ?>
                <?php if (isset($is_admin) && $is_admin): ?>
                <li class="nav-item"><span class="nav-link">Admin Panel</span></li>
                <?php endif; ?>
                <?php if (isset($show_login)): ?>
                <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>login.php">Login</a></li>
                <?php endif; ?>
                <?php if (isset($show_register)): ?>
                <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>user/register.php">Register</a></li>
                <?php endif; ?>
                <?php if (isset($show_logout) && $show_logout): ?>
                <li class="nav-item"><a class="nav-link text-danger" href="<?php echo $base_path; ?>logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<!-- Theme toggle JS removed --> 