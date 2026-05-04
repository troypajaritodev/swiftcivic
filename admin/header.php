<?php
if (!isset($pageTitle)) $pageTitle = 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - SwiftCivic Admin</title>
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
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-light-gray min-h-full">
    <nav class="bg-navy text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-between items-center py-3 md:py-0 md:h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center">
                        <img src="../assets/images/logo.svg" alt="SwiftCivic" class="h-10 md:h-12 w-auto">
                    </a>
                    <a href="index.php" class="ml-4 text-gray-300 hover:text-white text-sm md:text-base">Admin Panel</a>
                </div>
                <div class="flex items-center space-x-2 md:space-x-4 mt-2 md:mt-0">
                    <span class="text-xs md:text-sm hidden sm:block">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    <a href="index.php" class="px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm hover:bg-blue-900">Dashboard</a>
                    <a href="verify.php" class="px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm hover:bg-blue-900">Verify</a>
                    <a href="logs.php" class="px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm hover:bg-blue-900">Logs</a>
                    <a href="export.php" class="px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm hover:bg-blue-900">Export</a>
                    <div class="relative group">
                        <button class="px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm hover:bg-blue-900">My Account ▼</button>
                        <div class="absolute right-0 mt-1 w-40 md:w-48 bg-white rounded-civic shadow-lg py-2 hidden group-hover:block z-10">
                            <a href="../profile.php" class="block px-3 md:px-4 py-2 text-navy hover:bg-light-gray text-xs md:text-sm">Profile</a>
                            <a href="../citizen_dashboard.php" class="block px-3 md:px-4 py-2 text-navy hover:bg-light-gray text-xs md:text-sm">Dashboard</a>
                        </div>
                    </div>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 px-2 py-1 md:px-3 md:py-2 rounded-civic text-xs md:text-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
