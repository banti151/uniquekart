<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gray-800 text-white shadow-xl z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="p-4 text-center border-b border-gray-700">
        <h2 class="text-2xl font-bold text-white"><?= SITE_NAME ?></h2>
        <p class="text-xs text-gray-400">ADMIN</p>
    </div>
    
    <nav class="mt-4 flex flex-col space-y-1 px-2">
        <a href="index.php" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg">
            <i class="fas fa-tachometer-alt w-6 text-center"></i><span class="ml-3">Dashboard</span>
        </a>
        <a href="category.php" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg">
            <i class="fas fa-tags w-6 text-center"></i><span class="ml-3">Categories</span>
        </a>
        <a href="product.php" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg">
            <i class="fas fa-box-open w-6 text-center"></i><span class="ml-3">Products</span>
        </a>
        <a href="order.php" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg">
            <i class="fas fa-shopping-cart w-6 text-center"></i><span class="ml-3">Orders</span>
        </a>
        <a href="user.php" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg">
            <i class="fas fa-users w-6 text-center"></i><span class="ml-3">Users</span>
        </a>
        <a href="setting.php" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg">
            <i class="fas fa-cog w-6 text-center"></i><span class="ml-3">Settings</span>
        </a>
    </nav>

    <div class="absolute bottom-0 w-full p-2 border-t border-gray-700">
         <a href="login.php?action=logout" class="flex items-center p-3 text-red-400 hover:bg-red-500 hover:text-white rounded-lg">
            <i class="fas fa-sign-out-alt w-6 text-center"></i><span class="ml-3">Logout</span>
        </a>
    </div>
</aside>