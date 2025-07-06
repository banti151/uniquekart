<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white shadow-xl z-50 transform -translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-4">
        <!-- User Profile Section -->
        <div class="flex items-center mb-6">
            <!-- User Icon -->
            <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xl font-bold mr-3">
                <?= isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'G' ?>
            </div>
            <div>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?></p>
                <p class="text-sm text-gray-500 truncate w-40"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            </div>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex flex-col space-y-2">
            <a href="index.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-home w-6 text-center text-gray-500"></i>
                <span class="ml-3 font-medium">Home</span>
            </a>
            
            <!-- My Orders Button - YAHAN LINK THEEK KIYA GAYA HAI -->
            <a href="order.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-box w-6 text-center text-gray-500"></i>
                <span class="ml-3 font-medium">My Orders</span>
            </a>
            
            <a href="profile.php" class="flex items-center p-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-user-edit w-6 text-center text-gray-500"></i>
                <span class="ml-3 font-medium">My Profile</span>
            </a>
            
            <hr class="my-2 border-gray-200">
            
            <a href="login.php?action=logout" class="flex items-center p-3 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt w-6 text-center"></i>
                <span class="ml-3 font-medium">Logout</span>
            </a>
        </nav>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const openSidebarButton = document.getElementById('open-sidebar');
    
    // Yeh script sidebar ko kholne aur band karne ka kaam karti hai
    if (openSidebarButton) {
        openSidebarButton.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }
});
</script>