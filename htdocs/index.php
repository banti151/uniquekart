<?php
// header.php ko include karte hain, jo config.php ko bhi include kar leta hai
require_once 'common/header.php';
// User ka login check karte hain
check_login();

// Top 8 categories ko database se fetch karte hain
$stmt_cat = $pdo->query("SELECT id, name, image FROM categories ORDER BY name ASC LIMIT 8");
$categories = $stmt_cat->fetchAll();

// Latest 6 products ko "Featured" ke liye fetch karte hain
$stmt_prod = $pdo->query("SELECT id, name, price, image FROM products ORDER BY created_at DESC LIMIT 6");
$products = $stmt_prod->fetchAll();

// Session se cart ka count get karna
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!-- Top Header with Search Bar -->
<header class="bg-white shadow-sm sticky top-0 z-20 p-4">
    <div class="container mx-auto">
        <!-- Top row: Menu, Logo, Cart -->
        <div class="flex justify-between items-center">
            <!-- Sidebar kholne ka button -->
            <button id="open-sidebar" class="text-gray-600 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- App Name -->
            <h1 class="text-xl font-bold text-indigo-600">uniquekart</h1>
            
            <!-- Cart Icon with Count -->
            <a href="cart.php" class="relative text-gray-600">
                <i class="fas fa-shopping-cart text-xl"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white">
                        <?= $cart_count ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Bottom row: Search Bar -->
        <div class="mt-4">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fas fa-search text-gray-400"></i>
                </span>
                <!-- YAHAN CHANGE KIYA GAYA HAI: onsubmit="return false;" add kiya hai -->
                <form onsubmit="return false;" class="w-full">
                    <input type="search" name="search" placeholder="Search for products..." class="w-full bg-gray-100 border border-gray-200 rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-shadow">
                </form>
            </div>
        </div>
    </div>
</header>


<div class="container mx-auto p-4">

    <!-- Categories Section -->
    <section class="mb-8">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Categories</h2>
        <div class="flex overflow-x-auto space-x-4 pb-4 -mx-4 px-4" style="scrollbar-width: none; -ms-overflow-style: none;">
            <!-- PHP loop se saari categories dikhate hain -->
            <?php foreach ($categories as $category): ?>
            <a href="product.php?cat_id=<?= htmlspecialchars($category['id']) ?>" class="flex-shrink-0 text-center">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center overflow-hidden shadow-md">
                    <img src="https://placehold.co/80x80/E0E7FF/4F46E5/png?text=<?= substr(htmlspecialchars($category['name']), 0, 1) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-16 h-16 object-cover">
                </div>
                <p class="mt-2 text-sm font-medium text-gray-700 w-20 truncate"><?= htmlspecialchars($category['name']) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section>
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Featured Products</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <!-- PHP loop se saare products dikhate hain -->
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform duration-300">
                <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-40 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-indigo-600 font-bold mt-1">₹<?= number_format($product['price']) ?></p>
                        <button class="mt-3 w-full bg-indigo-500 text-white text-sm py-2 rounded-lg hover:bg-indigo-600 transition-colors">
                            View Details
                        </button>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
             <?php if (empty($products)): ?>
                <p class="col-span-full text-center text-gray-500">No featured products available.</p>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php
// Sidebar aur bottom navigation ko include karte hain
require_once 'common/sidebar.php';
require_once 'common/bottom.php';
?>