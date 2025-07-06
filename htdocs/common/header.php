<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Custom Styles for Mobile App Feel */
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10+ */
            user-select: none; /* Standard syntax */
            -webkit-tap-highlight-color: transparent;
            font-family: 'Inter', sans-serif; /* A nice, clean font */
        }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        /* Dotted loader style */
        .loader-dots div {
            animation-name: loader-dots;
            animation-duration: 1s;
            animation-iteration-count: infinite;
            animation-timing-function: linear;
        }
        @keyframes loader-dots {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 overflow-x-hidden">

    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex-col items-center justify-center hidden">
        <div class="flex space-x-2">
            <div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0s;"></div>
            <div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0.2s;"></div>
            <div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0.4s;"></div>
        </div>
        <p class="text-white mt-4">Loading...</p>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="pb-24"> <!-- Padding bottom to avoid overlap with bottom nav -->