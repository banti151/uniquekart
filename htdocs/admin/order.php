<?php
require_once 'common/header.php';

$orders = $pdo->query("
    SELECT o.id, o.total_amount, o.status, o.created_at, u.name as user_name, u.email as user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<h2 class="text-2xl font-bold mb-6">All Orders</h2>

<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-100">
            <tr>
                <th class="py-3 px-4 text-left">Order ID</th>
                <th class="py-3 px-4 text-left">User</th>
                <th class="py-3 px-4 text-left">Amount</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-left">Date</th>
                <th class="py-3 px-4 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4 font-mono">#<?= $order['id'] ?></td>
                <td class="py-3 px-4">
                    <p class="font-semibold"><?= htmlspecialchars($order['user_name']) ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($order['user_email']) ?></p>
                </td>
                <td class="py-3 px-4 font-bold">₹<?= number_format($order['total_amount']) ?></td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        <?= $order['status'] == 'Delivered' ? 'bg-green-100 text-green-800' : '' ?>
                        <?= $order['status'] == 'Dispatched' ? 'bg-blue-100 text-blue-800' : '' ?>
                        <?= $order['status'] == 'Placed' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                        <?= $order['status'] == 'Cancelled' ? 'bg-red-100 text-red-800' : '' ?>
                    ">
                        <?= $order['status'] ?>
                    </span>
                </td>
                <td class="py-3 px-4 text-sm"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                <td class="py-3 px-4 text-right">
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="bg-indigo-500 text-white text-xs py-1 px-3 rounded-md hover:bg-indigo-600">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'common/bottom.php'; ?>