<?php
require_once 'common/header.php';
check_login();

// Get category ID from URL, default to 1 or show all if not set
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'new';

$sql = "SELECT p.id, p.name, p.price, p.image, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id";
$params = [];

if ($cat_id > 0) {
    $sql .= " WHERE p.cat_id = ?";
    $params[] = $cat_id;
}

// Sorting logic
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    default: // 'new'
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch category name if filtered
$category_name = 'All Products';
if ($cat_id > 0 && !empty($products)) {
    $category_name = htmlspecialchars($products[0]['category_name']);
} elseif ($cat_id > 0) {
    // If no products, get cat name separately
    $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->execute([$cat_id]);
    $category = $cat_stmt->fetch();
    if ($category) {
        $category_name = htmlspecialchars($category['name']);
    }
}
?>

<!-- Top Header -->
<header class="bg-white p-4 shadow-sm sticky top-0 z-20">
    <div class="container mx-auto flex items-center">
        <a href="index.php" class="text-gray-600 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-xl font-semibold text-gray-800 truncate"><?= $category_name ?></h1>
    </div>
</header>

<div class="container mx-auto p-4">
    <!-- Filter bar -->
    <div class="flex justify-end items-center mb-4">
        <label for="sort-filter" class="text-sm text-gray-600 mr-2">Sort by:</label>
        <select id="sort-filter" class="border border-gray-300 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="new" <?= $sort_by == 'new' ? 'selected' : '' ?>>Newest</option>
            <option value="price_asc" <?= $sort_by == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort_by == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
        </select>
    </div>

    <!-- Product Grid -->
    <?php if (!empty($products)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <?php foreach ($products as $product): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-40 object-cover">
                <div class="p-3">
                    <h3 class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-indigo-600 font-bold mt-1">₹<?= number_format($product['price']) ?></p>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="text-center py-10">
            <i class="fas fa-box-open text-5xl text-gray-300"></i>
            <p class="mt-4 text-gray-500">No products found in this category.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('sort-filter').addEventListener('change', function() {
    const sortBy = this.value;
    const url = new URL(window.location);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
});
</script>

<?php
require_once 'common/bottom.php';
?>