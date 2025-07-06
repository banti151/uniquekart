<?php require_once 'common/header.php'; 

// Fetch stats
$total_users = $pdo->query("SELECT COUNT(id) FROM users")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(id) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Delivered'")->fetchColumn() ?? 0;
$active_products = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn();
$cancellations = $pdo->query("SELECT COUNT(id) FROM orders WHERE status = 'Cancelled'")->fetchColumn();
$shipments = $pdo->query("SELECT COUNT(id) FROM orders WHERE status = 'Dispatched'")->fetchColumn();
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Stat Card: Total Users -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
        <div class="bg-indigo-100 p-4 rounded-full"><i class="fas fa-users text-3xl text-indigo-500"></i></div>
        <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold"><?= $total_users ?></p>
        </div>
    </div>
    <!-- Stat Card: Total Orders -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
        <div class="bg-green-100 p-4 rounded-full"><i class="fas fa-box text-3xl text-green-500"></i></div>
        <div>
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold"><?= $total_orders ?></p>
        </div>
    </div>
    <!-- Stat Card: Total Revenue -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
        <div class="bg-yellow-100 p-4 rounded-full"><i class="fas fa-dollar-sign text-3xl text-yellow-500"></i></div>
        <div>
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold">₹<?= number_format($total_revenue) ?></p>
        </div>
    </div>
    <!-- Stat Card: Active Products -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
        <div class="bg-blue-100 p-4 rounded-full"><i class="fas fa-tag text-3xl text-blue-500"></i></div>
        <div>
            <p class="text-sm text-gray-500">Active Products</p>
            <p class="text-2xl font-bold"><?= $active_products ?></p>
        </div>
    </div>
    <!-- Stat Card: Shipments -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
        <div class="bg-purple-100 p-4 rounded-full"><i class="fas fa-truck text-3xl text-purple-500"></i></div>
        <div>
            <p class="text-sm text-gray-500">Active Shipments</p>
            <p class="text-2xl font-bold"><?= $shipments ?></p>
        </div>
    </div>
    <!-- Stat Card: Cancellations -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
        <div class="bg-red-100 p-4 rounded-full"><i class="fas fa-times-circle text-3xl text-red-500"></i></div>
        <div>
            <p class="text-sm text-gray-500">Cancellations</p>
            <p class="text-2xl font-bold"><?= $cancellations ?></p>
        </div>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="font-bold text-lg mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="product.php" class="bg-indigo-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-600"><i class="fas fa-plus mr-2"></i>Add Product</a>
            <a href="order.php" class="bg-green-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-600"><i class="fas fa-eye mr-2"></i>View Orders</a>
            <a href="user.php" class="bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600"><i class="fas fa-users-cog mr-2"></i>Manage Users</a>
        </div>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>