<?php
require_once 'common/header.php';
check_login();

// Agar cart khaali hai, to cart page par wapas bhej do
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Order place karne ka AJAX logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'place_order') {
    header('Content-Type: application/json');
    $address = trim($_POST['address'] ?? '');

    if (empty($address)) {
        echo json_encode(['status' => 'error', 'message' => 'Shipping address is required.']);
        exit();
    }

    try {
        $pdo->beginTransaction();
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        $stmt_order = $pdo->prepare("INSERT INTO orders (user_id, shipping_address, total_amount, status) VALUES (?, ?, ?, 'Placed')");
        $stmt_order->execute([$_SESSION['user_id'], $address, $total_amount]);
        $order_id = $pdo->lastInsertId();

        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
            $stmt_stock->execute([$item['quantity'], $product_id]);
        }
        
        $pdo->commit();
        unset($_SESSION['cart']);

        $stmt_update_addr = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmt_update_addr->execute([$address, $_SESSION['user_id']]);

        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!', 'redirect' => 'order.php']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order placement failed: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Could not place order. Please try again later.']);
    }
    exit();
}

// User ki details fetch karo
$stmt_user = $pdo->prepare("SELECT name, phone, email, address FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();

$cart_items = $_SESSION['cart'] ?? [];
$total_amount = 0;
?>

<!-- Top Header -->
<header class="bg-white p-4 shadow-sm sticky top-0 z-20">
    <div class="container mx-auto flex items-center">
        <a href="cart.php" class="text-gray-600 mr-4"><i class="fas fa-arrow-left text-xl"></i></a>
        <h1 class="text-xl font-semibold text-gray-800">Checkout</h1>
    </div>
</header>

<div class="container mx-auto p-4 pb-28"> <!-- Neeche se extra padding -->
    <!-- Shipping Address -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h2 class="font-semibold text-lg mb-2 flex items-center"><i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i> Shipping Address</h2>
        <p class="font-medium text-gray-800"><?= htmlspecialchars($user['name']) ?></p>
        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($user['phone']) ?></p>
        <form id="checkout-form">
            <textarea name="address" rows="3" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-300" placeholder="Enter your full address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </form>
    </div>

    <!-- Order Summary -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h2 class="font-semibold text-lg mb-4 flex items-center"><i class="fas fa-receipt text-indigo-500 mr-2"></i> Order Summary</h2>
        <div class="space-y-3">
            <?php foreach($cart_items as $item): 
                $total_amount += $item['price'] * $item['quantity'];
            ?>
            <div class="flex items-center text-sm border-b border-gray-100 pb-2">
                <img src="<?= htmlspecialchars($item['image']) ?>" class="w-12 h-12 rounded-md mr-3">
                <div class="flex-1">
                    <p class="font-medium truncate"><?= htmlspecialchars($item['name']) ?></p>
                    <p class="text-gray-500">Qty: <?= $item['quantity'] ?></p>
                </div>
                <p class="font-semibold">₹<?= number_format($item['price'] * $item['quantity']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Payment Method -->
    <div class="bg-white p-4 rounded-lg shadow-md">
        <h2 class="font-semibold text-lg mb-2 flex items-center"><i class="fas fa-wallet text-indigo-500 mr-2"></i> Payment Method</h2>
        <div class="flex items-center p-3 border-2 border-indigo-500 rounded-lg bg-indigo-50">
            <i class="fas fa-money-bill-wave text-indigo-600 text-2xl mr-4"></i>
            <div>
                <p class="font-semibold">Cash on Delivery (COD)</p>
                <p class="text-sm text-gray-600">Pay with cash when your order is delivered.</p>
            </div>
        </div>
    </div>
</div>

<!-- Place Order Floating Bar - YEH AAPKA CONFIRM BUTTON HAI -->
<div class="fixed bottom-0 left-0 right-0 bg-white shadow-[0_-2px_10px_-3px_rgba(0,0,0,0.1)] p-3 flex items-center justify-between z-30">
    <div class="text-left">
        <p class="text-sm text-gray-500">Total Amount</p>
        <p class="font-bold text-2xl text-indigo-600">₹<?= number_format($total_amount) ?></p>
    </div>
    <button id="place-order-btn" class="flex-1 ml-4 bg-green-500 text-white font-bold py-3 rounded-lg hover:bg-green-600 transition-all duration-200 text-lg shadow-md">
        <i class="fas fa-check-circle mr-2"></i> Place Order
    </button>
</div>

<script>
document.getElementById('place-order-btn').addEventListener('click', async () => {
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    formData.append('action', 'place_order');

    if (form.address.value.trim() === '') {
        alert('Please enter your shipping address.');
        form.address.focus();
        return;
    }
    
    const result = await ajaxRequest('checkout.php', formData);
    
    if (result.status === 'success') {
        alert(result.message);
        window.location.href = result.redirect;
    } else {
        alert(result.message || 'An error occurred while placing the order.');
    }
});
</script>

<?php 
// Is page par bottom.php include NAHI karna hai
// Lekin global JS functions (jaise ajaxRequest) header.php mein hain, to alag se include karne ki zaroorat nahi hai.
// Bas body aur html tag ko close karna hai.
?>
</div> <!-- Main Content Wrapper ko band kiya -->
</body>
</html>