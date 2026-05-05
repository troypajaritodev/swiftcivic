<?php
require_once 'auth.php';
require_once 'db.php';

if (isLoggedIn()) {
    if (isStaff()) {
        header('Location: admin/index.php');
    } else {
        header('Location: citizen_dashboard.php');
    }
    exit();
}

$pageTitle = 'Registration Successful';
require_once 'header.php';
?>

<div class="max-w-md mx-auto mt-8 md:mt-16 px-4">
    <div class="bg-white rounded-civic shadow-lg p-6 md:p-8 text-center">
        <div class="text-green-500 mb-4">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl md:text-3xl font-bold text-navy mb-4">Registration Successful!</h1>
        
        <p class="text-gray-600 mb-6">
            Your account has been created successfully. You can now login and start applying for civil documents.
        </p>
        
        <div class="space-y-3">
            <a href="login.php" 
               class="block w-full bg-skyblue hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-civic transition duration-200">
                Login Now
            </a>
            
            <a href="index.php" 
               class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-civic transition duration-200">
                Back to Home
            </a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
