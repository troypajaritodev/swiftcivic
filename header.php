<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Frame-busting: Ensure page is not loaded in an iframe
        if (window.top !== window.self) {
            window.top.location = window.self.location;
        }
    </script>
    <title><?= isset($pageTitle) ? $pageTitle . ' - SwiftCivic' : 'SwiftCivic - Civil Registry System' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0B1F3A',
                        skyblue: '#0EA5E9',
                        'light-gray': '#F0F6FF'
                    },
                    borderRadius: {
                        'civic': '14px'
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Segoe UI', 'system-ui', '-apple-system', sans-serif; }
        .progress-bar-stage { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-light-gray min-h-full">
    <nav class="bg-navy text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3 md:py-0 md:h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center">
                        <img src="assets/images/logo.svg" alt="SwiftCivic" class="h-10 md:h-12 w-auto">
                    </a>
                </div>
                <div class="flex items-center space-x-2 md:space-x-4 mt-2 md:mt-0">
                    <?php if (isLoggedIn()): ?>
                        <?php $user = getCurrentUser(); ?>
                        <span class="text-xs md:text-sm hidden sm:block">Welcome, <?= htmlspecialchars($user['full_name']) ?></span>
                        <?php if (isStaff()): ?>
                            <a href="admin/index.php" class="bg-skyblue hover:bg-blue-600 px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm">Admin Panel</a>
                        <?php endif; ?>
                        <a href="citizen_dashboard.php" class="hover:text-skyblue px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">Dashboard</a>
                        <a href="profile.php" class="hover:text-skyblue px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">Profile</a>
                        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-skyblue px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">Login</a>
                        <a href="register.php" class="bg-skyblue hover:bg-blue-600 px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
