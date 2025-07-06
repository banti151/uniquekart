<?php
require_once 'common/header.php';

if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    header('Content-Type: application/json');
    $user_id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['status' => 'success', 'message' => 'User deleted.']);
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<h2 class="text-2xl font-bold mb-6">Registered Users</h2>
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full">
        <thead>
            <tr class="bg-gray-100 uppercase text-sm">
                <th class="py-3 px-4 text-left">Name</th>
                <th class="py-3 px-4 text-left">Email</th>
                <th class="py-3 px-4 text-left">Phone</th>
                <th class="py-3 px-4 text-left">Joined</th>
                <th class="py-3 px-4 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr id="user-row-<?= $user['id'] ?>" class="border-b hover:bg-gray-50">
                <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($user['name']) ?></td>
                <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                <td class="py-3 px-4"><?= htmlspecialchars($user['phone']) ?></td>
                <td class="py-3 px-4"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                <td class="py-3 px-4 text-right">
                    <button onclick="deleteUser(<?= $user['id'] ?>)" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
async function deleteUser(id) {
    if(!confirm('Are you sure? This will delete the user and all their orders.')) return;
    const fd = new FormData();
    fd.append('action', 'delete_user');
    fd.append('id', id);
    const res = await ajaxRequest('user.php', fd);
    if(res.status === 'success') {
        document.getElementById(`user-row-${id}`).remove();
    } else {
        alert(res.message);
    }
}
</script>
<?php require_once 'common/bottom.php'; ?>