<?php
require_once 'common/header.php';

if (isset($_POST['action']) && $_POST['action'] == 'update_settings') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An error occurred.'];
    try {
        $username = trim($_POST['username']);
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        
        if (empty($username) || empty($current_pass)) {
            throw new Exception('Username and Current Password are required.');
        }

        $stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($current_pass, $admin['password'])) {
            throw new Exception('Incorrect current password.');
        }

        $password_to_set = $admin['password'];
        if (!empty($new_pass)) {
            $password_to_set = password_hash($new_pass, PASSWORD_DEFAULT);
        }

        $stmt_update = $pdo->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
        $stmt_update->execute([$username, $password_to_set, $_SESSION['admin_id']]);
        
        $_SESSION['admin_username'] = $username;
        $response = ['status' => 'success', 'message' => 'Settings updated successfully!'];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit;
}

$admin = $pdo->query("SELECT username FROM admin WHERE id = {$_SESSION['admin_id']}")->fetch();
?>

<h2 class="text-2xl font-bold mb-6">Admin Settings</h2>
<div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
    <div id="alert-box" class="p-3 mb-4 rounded-lg text-sm hidden"></div>
    <form id="settings-form" class="space-y-4">
        <input type="hidden" name="action" value="update_settings">
        <div>
            <label class="font-medium">Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" class="w-full mt-1 p-2 border rounded-md" required>
        </div>
        <div>
            <label class="font-medium">Current Password (Required to save)</label>
            <input type="password" name="current_password" class="w-full mt-1 p-2 border rounded-md" required>
        </div>
        <div>
            <label class="font-medium">New Password (Leave blank to keep unchanged)</label>
            <input type="password" name="new_password" class="w-full mt-1 p-2 border rounded-md">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold">Save Settings</button>
    </form>
</div>

<script>
document.getElementById('settings-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const result = await ajaxRequest('setting.php', new FormData(this));
    const alertBox = document.getElementById('alert-box');
    alertBox.textContent = result.message;
    alertBox.className = 'p-3 mb-4 rounded-lg text-sm';
    alertBox.classList.add(result.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
    alertBox.classList.remove('hidden');

    if(result.status === 'success') {
        this.reset();
        // You might want to refresh the page or update UI elements if username changes
        setTimeout(() => location.reload(), 1500);
    }
});
</script>

<?php require_once 'common/bottom.php'; ?>