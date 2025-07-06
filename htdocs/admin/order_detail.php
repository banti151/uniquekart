<?php
require_once 'common/header.php';

if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
    header('Content-Type: application/json');
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed_statuses = ['Placed', 'Dispatched', 'Delivered', 'Cancelled'];
    
    if (in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        echo json_encode(['status' => 'success', 'message' => 'Order status updated!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status.']);
    }
    exit;
}

$order_id = (int)$_GET['id'];
$order = $pdo->prepare("SELECT o.*, u.name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$order->execute([$order_id]);
$order_details = $order->fetch();

$items = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items->execute([$order_id]);
$order_items = $items->fetchAll();
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Ordered Items -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-4">Order #<?= $order_id ?> Items</h3>
            <div class="space-y-4">
            <?php foreach($order_items as $item): ?>
                <div class="flex items-center border-b pb-4">
                    <img src="../<?= htmlspecialchars($item['image']) ?>" class="w-16 h-16 rounded object-cover mr-4">
                    <div class="flex-1">
                        <p class="font-semibold"><?= htmlspecialchars($item['product_name']) ?></p>
                        <p class="text-sm text-gray-500">Price: ₹<?= number_format($item['price']) ?> x <?= $item['quantity'] ?> Qty</p>
                    </div>
                    <p class="font-bold">₹<?= number_format($item['price'] * $item['quantity']) ?></p>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="text-right mt-4">
                <p class="text-gray-600">Total: <span class="font-bold text-xl text-indigo-600">₹<?= number_format($order_details['total_amount']) ?></span></p>
            </div>
        </div>
    </div>
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-4">Customer Details</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($order_details['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order_details['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order_details['phone']) ?></p>
            <p class="mt-2"><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order_details['shipping_address'])) ?></p>
        </div>
        <!-- Order Status -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold mb-4">Update Status</h3>
            <form id="status-form">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <select name="status" id="status-select" class="w-full p-2 border rounded-md">
                    <option value="Placed" <?= $order_details['status'] == 'Placed' ? 'selected' : '' ?>>Placed</option>
                    <option value="Dispatched" <?= $order_details['status'] == 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                    <option value="Delivered" <?= $order_details['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $order_details['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="w-full mt-4 bg-indigo-600 text-white py-2 rounded-lg font-semibold">Update Status</button>
            </form>
            <p id="status-alert" class="mt-2 text-sm hidden"></p>
        </div>
    </div>
</div>

<script>
document.getElementById('status-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const result = await ajaxRequest('order_detail.php?id=<?= $order_id ?>', new FormData(e.target));
    const alertBox = document.getElementById('status-alert');
    alertBox.textContent = result.message;
    alertBox.className = 'mt-2 text-sm';
    alertBox.classList.add(result.status === 'success' ? 'text-green-600' : 'text-red-600');
    alertBox.classList.remove('hidden');
    setTimeout(() => alertBox.classList.add('hidden'), 3000);
});
</script>

<?php require_once 'common/bottom.php'; ?>