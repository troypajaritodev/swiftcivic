<?php
$pageTitle = 'Home';
require_once 'header.php';
?>

<div class="min-h-screen bg-white">

    <!-- Hero Section -->
    <div class="bg-navy text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="max-w-3xl mx-auto text-center">
                <a href="index.php" class="inline-block mb-6">
                    <img src="assets/images/logo.svg" alt="SwiftCivic" class="h-16 md:h-20 w-auto mx-auto">
                </a>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 leading-tight">SwiftCivic Civil Registry System</h1>
                <p class="text-base md:text-lg text-gray-200 mb-6 md:mb-8 leading-relaxed">
                    Apply for civil documents online, track your requests in real time, 
                    and receive secure delivery. Simplified processes for citizens and efficient tools for staff.
                </p>
                <div class="flex flex-col sm:flex-row flex-wrap justify-center gap-3 md:gap-4">
                    <a href="track.php" 
                       class="bg-white text-navy font-semibold px-4 py-2 md:px-6 md:py-3 rounded-civic hover:bg-gray-100 transition text-sm md:text-base">
                        Track a Request
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a href="apply.php" 
                           class="bg-skyblue text-white font-semibold px-4 py-2 md:px-6 md:py-3 rounded-civic hover:bg-blue-600 transition text-sm md:text-base">
                             New Application
                        </a>
                    <?php else: ?>
                        <a href="register.php" 
                           class="bg-skyblue text-white font-semibold px-4 py-2 md:px-6 md:py-3 rounded-civic hover:bg-blue-600 transition text-sm md:text-base">
                             Create Account
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (!isLoggedIn()): ?>
                    <p class="mt-4 text-sm text-gray-300">
                        Already have an account? 
                        <a href="login.php" class="text-skyblue hover:underline font-medium">Login here</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="mb-8 text-center">
            <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-navy mb-2">Our Services</h2>
            <div class="w-16 h-1 bg-skyblue mx-auto"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            <div class="bg-white border border-gray-200 rounded-civic p-4 md:p-6 hover:border-skyblue hover:shadow-lg transition group">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-navy text-white rounded-civic flex items-center justify-center mb-4 group-hover:bg-skyblue transition">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base md:text-lg font-semibold text-navy mb-2">Online Applications</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Submit requests for birth certificates, marriage certificates, and other civil documents through our streamlined 3-step process.
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-civic p-4 md:p-6 hover:border-skyblue hover:shadow-lg transition group">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-navy text-white rounded-civic flex items-center justify-center mb-4 group-hover:bg-skyblue transition">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base md:text-lg font-semibold text-navy mb-2">Payment Processing</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Secure payment options including GCash, bank transfers, and over-the-counter transactions with digital receipt uploads.
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-civic p-4 md:p-6 hover:border-skyblue hover:shadow-lg transition group">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-navy text-white rounded-civic flex items-center justify-center mb-4 group-hover:bg-skyblue transition">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-base md:text-lg font-semibold text-navy mb-2">Request Tracking</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Monitor your application through 5 distinct stages with visual progress indicators and status notifications.
                </p>
            </div>
        </div>
    </div>

    <!-- Process Section -->
    <div class="bg-light-gray">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="mb-8 text-center">
                <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-navy mb-2">Application Process</h2>
                <div class="w-16 h-1 bg-skyblue mx-auto"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 md:gap-8 max-w-4xl mx-auto">
                <div class="text-center bg-white rounded-civic p-6 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-navy text-white rounded-full flex items-center justify-center text-lg md:text-xl font-bold mx-auto mb-4">1</div>
                    <h3 class="text-base md:text-lg font-semibold text-navy mb-2">Submit Application</h3>
                    <p class="text-sm text-gray-600">Complete the online form and upload valid identification.</p>
                </div>

                <div class="text-center bg-white rounded-civic p-6 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-navy text-white rounded-full flex items-center justify-center text-lg md:text-xl font-bold mx-auto mb-4">2</div>
                    <h3 class="text-base md:text-lg font-semibold text-navy mb-2">Make Payment</h3>
                    <p class="text-sm text-gray-600">Upload your payment receipt through our secure portal.</p>
                </div>

                <div class="text-center bg-white rounded-civic p-6 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-navy text-white rounded-full flex items-center justify-center text-lg md:text-xl font-bold mx-auto mb-4">3</div>
                    <h3 class="text-base md:text-lg font-semibold text-navy mb-2">Receive Document</h3>
                    <p class="text-sm text-gray-600">Download digitally or collect in person when ready.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-navy text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-8 text-center">
                <div>
                    <p class="text-3xl md:text-4xl lg:text-5xl font-bold text-skyblue">5</p>
                    <p class="text-sm text-gray-300 mt-1">Processing Stages</p>
                </div>
                <div>
                    <p class="text-3xl md:text-4xl lg:text-5xl font-bold text-skyblue">3</p>
                    <p class="text-sm text-gray-300 mt-1">Easy Steps</p>
                </div>
                <div>
                    <p class="text-3xl md:text-4xl lg:text-5xl font-bold text-skyblue">24/7</p>
                    <p class="text-sm text-gray-300 mt-1">Online Access</p>
                </div>
                <div>
                    <p class="text-3xl md:text-4xl lg:text-5xl font-bold text-skyblue">100%</p>
                    <p class="text-sm text-gray-300 mt-1">Secure System</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 text-center">
        <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-navy mb-4">Access Your Civil Registry Account</h2>
        <p class="text-gray-600 mb-6 md:mb-8 max-w-2xl mx-auto">
            Manage your applications, track request status, and receive notifications when documents are ready.
        </p>
        <div class="flex flex-col sm:flex-row flex-wrap justify-center gap-3 md:gap-4">
            <?php if (isLoggedIn()): ?>
                <a href="citizen_dashboard.php" 
                   class="bg-navy text-white font-semibold px-6 py-3 md:px-8 md:py-4 rounded-civic hover:bg-blue-900 transition text-sm md:text-base">
                    Go to Dashboard
                </a>
            <?php else: ?>
                <a href="login.php" 
                   class="bg-navy text-white font-semibold px-6 py-3 md:px-8 md:py-4 rounded-civic hover:bg-blue-900 transition text-sm md:text-base">
                    Login
                </a>
                <a href="register.php" 
                   class="bg-white text-navy border-2 border-navy font-semibold px-6 py-3 md:px-8 md:py-4 rounded-civic hover:bg-gray-50 transition text-sm md:text-base">
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
