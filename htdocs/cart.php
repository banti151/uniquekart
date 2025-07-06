<?php
require_once 'common/header.php';
check_login();

// Handle AJAX cart updates (Remove/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    // Check karo ki cart session aur product ID valid hai ya nahi
    if ($product_id <= 0 || !isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found in cart.']);
        exit();
    }

    $action = $_POST['action'];
    $response = ['status' => 'error', 'message' => 'Invalid action.'];

    if ($action == 'update_quantity') {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            $response = ['status' => 'success'];
        }
    } elseif ($action == 'remove_item') {
        unset($_SESSION['cart'][$product_id]);
        $response = ['status' => 'success'];
    }

    // Har action ke baad total dobara calculate karke bhejo
    $subtotal = 0;
    if(isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
    }
    $response['cart_count'] = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    $response['subtotal_formatted'] = '₹' . number_format($subtotal);
    
    echo json_encode($response);
    exit();
}

// Page load par cart items ko session se get karna
$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
?>

<!-- Top Header -->
<header class="bg-white p-4 shadow-sm sticky top-0 z-20">
    <div class="container mx-auto flex items-center">
        <a href="index.php" class="text-gray-600 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-xl font-semibold text-gray-800">My Cart (<?= count($cart_items) ?> items)</h1>
    </div>
</header>

<div class="container mx-auto p-4">
    <?php if (empty($cart_items)): ?>
        <div class="text-center py-20">
            <i class="fas fa-shopping-cart text-6xl text-gray-300"></i>
            <p class="mt-4 text-gray-500 font-semibold">Your cart is empty.</p>
            <p class="text-sm text-gray-400">Looks like you haven't added anything to your cart yet.</p>
            <a href="index.php" class="mt-6 inline-block bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md hover:bg-indigo-700">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div id="cart-items-container" class="space-y-4">
            <?php foreach ($cart_items as $id => $item): 
                $item_total = $item['price'] * $item['quantity'];
                $subtotal += $item_total;
            ?>
            <div id="cart-item-<?= $id ?>" class="bg-white p-4 rounded-lg shadow-md flex items-start space-x-4">
                <img src="<?= htmlspecialchars($item['image']) ?>" class="w-20 h-20 object-cover rounded-md">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-indigo-600 font-bold">₹<?= number_format($item['price']) ?></p>
                    <div class="flex items-center mt-2">
                        <label class="text-sm mr-2">Qty:</label>
                        <input type="number" value="<?= $item['quantity'] ?>" min="1" max="10" 
                               class="w-16 p-1 border border-gray-300 rounded-md text-center" 
                               onchange="updateQuantity(<?= $id ?>, this.value)">
                        <button onclick="removeItem(<?= $id ?>)" class="ml-auto text-red-500 hover:text-red-700 text-lg">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Order Summary -->
        <div class="mt-8 bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold mb-4">Price Details</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="subtotal-display">₹<?= number_format($subtotal) ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Delivery Fee</span>
                    <span class="text-green-600 font-medium">FREE</span>
                </div>
                <hr class="my-2">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total Amount</span>
                    <span id="total-display">₹<?= number_format($subtotal) ?></span>
                </div>
            </div>
            <a href="checkout.php" class="block w-full text-center mt-6 bg-indigo-600 text-white font-semibold py-3 rounded-lg hover:bg-indigo-700 transition-all duration-200">
                Proceed to Checkout
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
async function updateCart(action, productId, quantity = null) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('product_id', productId);
    if (quantity !== null) {
        formData.append('quantity', quantity);
    }

    // Hum cart.php ko hi dobara call kar rahe hain
    const result = await ajaxRequest('cart.php', formData);

    if (result.status === 'success') {
        document.getElementById('subtotal-display').textContent = result.subtotal_formatted;
        document.getElementById('total-display').textContent = result.subtotal_formatted;
    } else {
        alert(result.message || 'Failed to update cart.');
    }
    return result;
}

async function updateQuantity(productId, quantity) {
    await updateCart('update_quantity', productId, quantity);
}

async function removeItem(productId) {
    if (!confirm('Are you sure you want to remove this item?')) return;
    
    const result = await updateCart('remove_item', productId);
    if (result.status === 'success') {
        const itemElement = document.getElementById(`cart-item-${productId}`);
        itemElement.style.transition = 'opacity 0.5s, transform 0.5s';
        itemElement.style.opacity = '0';
        itemElement.style.transform = 'translateX(-100%)';
        setTimeout(() => {
            itemElement.remove();
            if (result.cart_count === 0) {
                // Agar cart khaali ho gaya, to page reload kar do taaki "empty cart" message dikhe
                location.reload(); 
            }
        }, 500);
    }
}
</script>

<?php require_once 'common/bottom.php'; ?>