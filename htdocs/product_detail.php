<?php
// Step 1: Sabse pehle config file ko include karna, taaki session aur DB connection mil jaaye.
require_once 'common/config.php';

// Step 2: AJAX request ko alag se handle karna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    // Sirf JSON response bhejna hai, koi HTML nahi
    header('Content-Type: application/json');
    
    // Pehle check karo ki user logged in hai ya nahi
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please login to add items to cart.']);
        exit();
    }

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($product_id > 0 && $quantity > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Product details fetch karna (yeh zaroori hai)
        $stmt = $pdo->prepare("SELECT name, price, image, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            // Check if there's enough stock
            $current_cart_qty = isset($_SESSION['cart'][$product_id]['quantity']) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
            if (($current_cart_qty + $quantity) > $product['stock']) {
                 echo json_encode(['status' => 'error', 'message' => 'Not enough stock available!']);
                 exit();
            }

            // Cart mein add ya update karna
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity,
                ];
            }
            echo json_encode(['status' => 'success', 'message' => 'Product added to cart!', 'cart_count' => count($_SESSION['cart'])]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity.']);
    }
    // AJAX request handle hone ke baad script ko yahin rok do
    exit();
}

// --- Agar yeh AJAX request nahi hai, to normal page load hoga ---

// Step 3: Ab HTML header ko include karna
require_once 'common/header.php';
check_login(); // User ka login check karna

// Step 4: Product details fetch karna page par dikhane ke liye
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    echo "<p class='text-center text-red-500 p-4'>Invalid Product ID.</p>";
    require_once 'common/bottom.php';
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<p class='text-center text-red-500 p-4'>Product not found.</p>";
    require_once 'common/bottom.php';
    exit();
}

// Fetch related products
$stmt_related = $pdo->prepare("SELECT id, name, price, image FROM products WHERE cat_id = ? AND id != ? LIMIT 4");
$stmt_related->execute([$product['cat_id'], $product_id]);
$related_products = $stmt_related->fetchAll();

?>

<!-- Top Header -->
<header class="bg-white p-4 shadow-sm sticky top-0 z-20">
    <div class="container mx-auto flex items-center">
        <a href="javascript:history.back()" class="text-gray-600 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-xl font-semibold text-gray-800 truncate"><?= htmlspecialchars($product['name']) ?></h1>
    </div>
</header>

<div class="container mx-auto">
    <!-- Product Image -->
    <div class="relative bg-gray-200">
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-80 object-cover">
    </div>
    
    <!-- Product Info -->
    <div class="p-4 bg-white">
        <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($product['name']) ?></h2>
        <p class="text-3xl font-bold text-indigo-600 my-2">₹<?= number_format($product['price']) ?></p>
        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?= $product['stock'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' left)' : 'Out of Stock' ?>
        </span>
        
        <div class="mt-6">
            <h3 class="font-semibold text-gray-700 mb-2">Description</h3>
            <p class="text-gray-600 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="p-4 mt-4 bg-gray-50">
        <h3 class="text-xl font-bold text-gray-800 mb-4">You might also like</h3>
        <div class="grid grid-cols-2 gap-4">
            <?php foreach ($related_products as $related): ?>
            <a href="product_detail.php?id=<?= $related['id'] ?>" class="bg-white rounded-lg shadow-sm overflow-hidden">
                <img src="<?= htmlspecialchars($related['image']) ?>" class="w-full h-28 object-cover">
                <div class="p-2">
                    <h4 class="text-sm font-semibold truncate"><?= htmlspecialchars($related['name']) ?></h4>
                    <p class="text-indigo-600 font-bold text-sm mt-1">₹<?= number_format($related['price']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add to Cart Floating Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white shadow-[0_-2px_10px_-3px_rgba(0,0,0,0.1)] p-3 flex items-center justify-between z-30">
    <div class="flex items-center">
        <button id="decrease-qty" class="w-8 h-8 flex items-center justify-center bg-gray-200 text-gray-700 rounded-md">-</button>
        <input id="quantity" type="text" value="1" readonly class="w-12 h-8 text-center font-bold border-y border-gray-200">
        <button id="increase-qty" class="w-8 h-8 flex items-center justify-center bg-gray-200 text-gray-700 rounded-md">+</button>
    </div>
    <button id="add-to-cart-btn" class="flex-1 ml-4 bg-indigo-600 text-white font-semibold py-3 rounded-lg hover:bg-indigo-700 transition-colors <?= $product['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
        <?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
    </button>
</div>

<!-- Success Toast -->
<div id="toast-success" class="fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <!-- Message will be set by JS -->
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-qty');
    const increaseBtn = document.getElementById('increase-qty');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const toast = document.getElementById('toast-success');
    const maxStock = <?= (int)$product['stock'] ?>;

    decreaseBtn.addEventListener('click', () => {
        let currentQty = parseInt(quantityInput.value);
        if (currentQty > 1) {
            quantityInput.value = currentQty - 1;
        }
    });

    increaseBtn.addEventListener('click', () => {
        let currentQty = parseInt(quantityInput.value);
        if (currentQty < maxStock) {
            quantityInput.value = currentQty + 1;
        }
    });

    addToCartBtn.addEventListener('click', async () => {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', '<?= $product_id ?>');
        formData.append('quantity', quantityInput.value);

        const result = await ajaxRequest('product_detail.php', formData);
        
        if (result.status === 'success') {
            toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${result.message}`;
            toast.classList.remove('translate-x-full', 'bg-red-500');
            toast.classList.add('bg-green-500');
            setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 2000);
            // Optionally update cart icon in bottom nav dynamically
        } else {
            // Agar server se error message aaya hai to woh dikhao
            toast.innerHTML = `<i class="fas fa-times-circle mr-2"></i> ${result.message}`;
            toast.classList.remove('translate-x-full', 'bg-green-500');
            toast.classList.add('bg-red-500');
             setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 3000);
        }
    });
});
</script>

<?php
// We don't include bottom.php, so manually add its dependencies
?>
</div> <!-- End Main Content Wrapper from header.php -->
<script>
    // --- GLOBAL JS (from bottom.php) ---
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('touchstart', function(event) { if (event.touches.length > 1) event.preventDefault(); }, { passive: false });
    
    const loadingModal = document.getElementById('loading-modal');
    function showLoader() { loadingModal.classList.remove('hidden'); loadingModal.classList.add('flex'); }
    function hideLoader() { loadingModal.classList.add('hidden'); loadingModal.classList.remove('flex'); }
    
    async function ajaxRequest(url, formData) {
        showLoader();
        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            // Iske neeche wali line error ko pakad legi
            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            // Ab hum server ke error ki jagah ek specific client-side error dikhaenge
            return { status: 'error', message: 'Request failed. Check connection.' };
        } finally {
            hideLoader();
        }
    }
</script>
</body>
</html>