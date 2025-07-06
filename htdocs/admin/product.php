<?php
require_once 'common/header.php';

// AJAX Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An unknown error occurred.'];
    
    try {
        switch ($_POST['action']) {
            case 'add_product':
            case 'edit_product':
                $id = (int)($_POST['product_id'] ?? 0);
                $cat_id = (int)$_POST['cat_id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = (float)$_POST['price'];
                $stock = (int)$_POST['stock'];

                if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || $cat_id <= 0) {
                    throw new Exception('All fields are required and must be valid.');
                }
                
                $image_path = $_POST['existing_image'] ?? '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = __DIR__ . "/../uploads/products/";
                    $image_name = uniqid() . basename($_FILES["image"]["name"]);
                    $target_file = $target_dir . $image_name;
                    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
                    $image_path = 'uploads/products/' . $image_name;
                }
                
                if ($_POST['action'] == 'add_product') {
                    $stmt = $pdo->prepare("INSERT INTO products (cat_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$cat_id, $name, $description, $price, $stock, $image_path]);
                    $response = ['status' => 'success', 'message' => 'Product added successfully.'];
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET cat_id=?, name=?, description=?, price=?, stock=?, image=? WHERE id=?");
                    $stmt->execute([$cat_id, $name, $description, $price, $stock, $image_path, $id]);
                    $response = ['status' => 'success', 'message' => 'Product updated successfully.'];
                }
                break;

            case 'get_product':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                $response = ['status' => 'success', 'data' => $product];
                break;
            
            case 'delete_product':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $response = ['status' => 'success', 'message' => 'Product deleted.'];
                break;
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    echo json_encode($response);
    exit;
}

$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id ORDER BY p.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Manage Products</h2>
    <button id="add-product-btn" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700"><i class="fas fa-plus mr-2"></i>Add New Product</button>
</div>

<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Image</th><th class="py-3 px-6 text-left">Name</th><th class="py-3 px-6 text-left">Category</th><th class="py-3 px-6 text-left">Price</th><th class="py-3 px-6 text-center">Stock</th><th class="py-3 px-6 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($products as $product): ?>
            <tr id="prod-row-<?= $product['id'] ?>" class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6"><img src="../<?= htmlspecialchars($product['image']) ?>" class="w-16 h-16 rounded object-cover"></td>
                <td class="py-3 px-6 font-semibold"><?= htmlspecialchars($product['name']) ?></td>
                <td class="py-3 px-6"><?= htmlspecialchars($product['category_name']) ?></td>
                <td class="py-3 px-6 font-bold text-green-600">₹<?= number_format($product['price']) ?></td>
                <td class="py-3 px-6 text-center"><?= $product['stock'] ?></td>
                <td class="py-3 px-6 text-right">
                    <button onclick="editProduct(<?= $product['id'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteProduct(<?= $product['id'] ?>)" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden overflow-y-auto p-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <h3 id="modal-title" class="text-xl font-bold mb-4">Add Product</h3>
        <form id="product-form" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" id="form-action">
            <input type="hidden" name="product_id" id="product-id">
            <input type="hidden" name="existing_image" id="existing-image">
            <select name="cat_id" id="cat_id" class="w-full p-2 border rounded-md" required>
                <option value="">Select Category</option>
                <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="name" id="name" placeholder="Product Name" class="w-full p-2 border rounded-md" required>
            <textarea name="description" id="description" placeholder="Description" rows="4" class="w-full p-2 border rounded-md" required></textarea>
            <div class="grid grid-cols-2 gap-4">
                <input type="number" step="0.01" name="price" id="price" placeholder="Price (₹)" class="w-full p-2 border rounded-md" required>
                <input type="number" name="stock" id="stock" placeholder="Stock" class="w-full p-2 border rounded-md" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Image</label>
                <input type="file" name="image" id="image" class="mt-1 block w-full text-sm">
                <img id="image-preview" class="mt-2 h-20 rounded hidden">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="cancel-btn" class="bg-gray-300 py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-lg">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('product-modal');
const form = document.getElementById('product-form');
document.getElementById('add-product-btn').onclick = () => {
    document.getElementById('modal-title').innerText = 'Add New Product';
    document.getElementById('form-action').value = 'add_product';
    form.reset();
    document.getElementById('image-preview').classList.add('hidden');
    modal.classList.remove('hidden');
};
document.getElementById('cancel-btn').onclick = () => modal.classList.add('hidden');

async function editProduct(id) {
    const fd = new FormData(); fd.append('action', 'get_product'); fd.append('id', id);
    const res = await ajaxRequest('product.php', fd);
    if(res.status === 'success'){
        const p = res.data;
        document.getElementById('modal-title').innerText = 'Edit Product';
        document.getElementById('form-action').value = 'edit_product';
        document.getElementById('product-id').value = p.id;
        document.getElementById('cat_id').value = p.cat_id;
        document.getElementById('name').value = p.name;
        document.getElementById('description').value = p.description;
        document.getElementById('price').value = p.price;
        document.getElementById('stock').value = p.stock;
        document.getElementById('existing-image').value = p.image;
        const preview = document.getElementById('image-preview');
        preview.src = `../${p.image}`;
        preview.classList.remove('hidden');
        modal.classList.remove('hidden');
    }
}
async function deleteProduct(id) {
    if (!confirm('Are you sure?')) return;
    const fd = new FormData(); fd.append('action', 'delete_product'); fd.append('id', id);
    const res = await ajaxRequest('product.php', fd);
    if (res.status === 'success') {
        document.getElementById(`prod-row-${id}`).remove();
        alert(res.message);
    } else { alert(res.message); }
}

form.onsubmit = async (e) => {
    e.preventDefault();
    const res = await ajaxRequest('product.php', new FormData(form));
    if (res.status === 'success') {
        alert(res.message);
        modal.classList.add('hidden');
        location.reload();
    } else { alert(res.message); }
}
</script>

<?php require_once 'common/bottom.php'; ?>