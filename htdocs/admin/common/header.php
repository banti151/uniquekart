<?php require_once __DIR__ . '/../../common/config.php'; ?>
<?php check_admin_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Admin - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { -webkit-user-select: none; user-select: none; -webkit-tap-highlight-color: transparent; font-family: 'Inter', sans-serif; }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        .loader-dots div { animation: loader-dots 1s infinite linear; }
        @keyframes loader-dots { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        /* Keep sidebar visible on larger screens */
        @media (min-width: 768px) {
            #main-content { margin-left: 16rem; } /* 64 * 0.25rem = 16rem */
            #sidebar { transform: translateX(0); }
            #open-sidebar-button { display: none; }
            #sidebar-overlay { display: none; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex-col items-center justify-center hidden">
        <div class="flex space-x-2"><div class="w-4 h-4 bg-white rounded-full loader-dots"></div><div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0.2s;"></div><div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0.4s;"></div></div>
    </div>
    
    <?php require_once 'sidebar.php'; ?>

    <div id="main-content" class="md:ml-64 transition-all duration-300">
        <!-- Top header -->
        <header class="bg-white p-4 shadow-md sticky top-0 z-20 flex justify-between items-center">
             <button id="open-sidebar-button" class="md:hidden text-gray-600">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h1 class="text-xl font-semibold text-gray-700">Admin Panel</h1>
            <div class="text-sm">Welcome, <span class="font-bold"><?= $_SESSION['admin_username'] ?></span></div>
        </header>
        
        <!-- Page content starts here -->
        <main class="p-4 md:p-6">