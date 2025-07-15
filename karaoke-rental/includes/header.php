<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Karaoke Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/theme.css">
</head>
<body class="theme-bg">
    <nav class="navbar navbar-expand-lg navbar-dark theme-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">Karaoke Rental</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($nav_links)): ?>
                <ul class="navbar-nav me-auto">
                    <?php foreach ($nav_links as $link): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $link['url']; ?>"><?php echo $link['text']; ?></a>
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
                    <li class="nav-item">
                        <div class="form-check form-switch ms-3 mt-2">
                            <input class="form-check-input" type="checkbox" id="themeToggle">
                            <label class="form-check-label" for="themeToggle">Dark Mode</label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav> 