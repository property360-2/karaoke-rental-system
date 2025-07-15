<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Karaoke Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/theme.css">
    <!-- Theme toggle script removed -->
</head>
<body>
    <?php include __DIR__ . '/nav.php'; ?>
    <script>
    // Navbar color adaptation for theme
    function updateNavbarTheme() {
        const navbar = document.getElementById('mainNavbar');
        const brandLogo = document.getElementById('brandLogo');
        if (document.body.classList.contains('light-mode')) {
            navbar.classList.remove('navbar-dark');
            navbar.classList.add('navbar-light');
            brandLogo.style.color = '#222';
        } else {
            navbar.classList.remove('navbar-light');
            navbar.classList.add('navbar-dark');
            brandLogo.style.color = '';
        }
    }
    document.addEventListener('DOMContentLoaded', updateNavbarTheme);
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('change', updateNavbarTheme);
    }
    // Also update on theme change via system
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateNavbarTheme);
    </script> 