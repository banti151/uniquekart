<?php
require_once 'common/header.php'; // Includes config.php

// --- AJAX REQUEST HANDLER ---
// Yeh block tabhi chalega jab form submit hoga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An unknown error occurred.'];
    
    try {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $name = trim($_POST['name'] ?? '');
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                
                if (empty($name)) {
                    throw new Exception('Category name cannot be empty.');
                }

                $image_path = $_POST['existing_image'] ?? '';
                $is_add_action = ($_POST['action'] == 'add');

                // Check if a file was uploaded and there are no errors
                if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                    // Define the target directory with an absolute path for reliability
                    $target_dir = __DIR__ . "/../uploads/categories/";
                    
                    // Ensure the directory exists and is writable
                    if (!is_dir($target_dir)) {
                        // Create the directory if it doesn't exist
                        if (!mkdir($target_dir, 0775, true)) {
                            throw new Exception("Failed to create directory: " . $target_dir);
                        }
                    }
                    if (!is_writable($target_dir)) {
                        // This is a common issue on many servers
                        throw new Exception("Upload directory is not writable. Please check permissions for: " . $target_dir);
                    }

                    // Generate a unique name for the image to prevent overwriting files
                    $image_name = uniqid('cat_') . '_' . basename($_FILES["image"]["name"]);
                    $target_file = $target_dir . $image_name;
                    
                    // Move the uploaded file from the temporary directory to the target directory
                    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        throw new Exception('Failed to upload image. This might be a server permission issue.');
                    }
                    // This is the relative path we will store in the database
                    $image_path = 'uploads/categories/' . $image_name;

                } elseif ($is_add_action && empty($image_path)) {
                    // If it's a new category, an image is required
                    throw new Exception('Category image is required when adding a new category.');
                }

                // --- Database Operations ---
                if ($is_add_action) {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
                    $stmt->execute([$name, $image_path]);
                    $response = ['status' => 'success', 'message' => 'Category added successfully!'];
                } else { // Edit action
                    if (!empty($image_path) && $image_path != $_POST['existing_image']) {
                        // If a new image was uploaded for an existing category
                        $stmt = $pdo->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $image_path, $id]);
                    } else {
                        // If only the name is being updated
                        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                        $stmt->execute([$name, $id]);
                    }
                    $response = ['status' => 'success', 'message' => 'Category updated successfully!'];
                }
                break;
            
            case 'get':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $category = $stmt->fetch();
                if (!$category) throw new Exception('Category not found.');
                $response = ['status' => 'success', 'data' => $category];
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                // Optional: Delete the image file from the server as well
                $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $cat_image = $stmt->fetchColumn();
                if ($cat_image && file_exists(__DIR__ . "/../" . $cat_image)) {
                    unlink(__DIR__ . "/../" . $cat_image);
                }
                
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $response = ['status' => 'success', 'message' => 'Category deleted successfully.'];
                break;
        }
    } catch (PDOException $e) {
        // Specific message for database errors
        $response['message'] = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        // General errors (file upload, validation, etc.)
        $response['message'] = "Error: " . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// --- HTML PAGE DISPLAY ---
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Manage Categories</h2>
    <button id="add-category-btn" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add New Category
    </button>
</div>

<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Image</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody id="category-table-body">
            <?php foreach ($categories as $cat): ?>
            <tr id="cat-row-<?= $cat['id'] ?>">
                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                    <img src="../<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-16 h-16 object-cover rounded">
                </td>
                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm font-semibold"><?= htmlspecialchars($cat['name']) ?></td>
                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-right">
                    <button onclick="editCategory(<?= $cat['id'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="text-red-600 hover:text-red-900" title="Delete"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Category Modal -->
<div id="category-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 id="modal-title" class="text-xl font-bold mb-4">Add Category</h3>
        <div id="modal-alert" class="hidden p-3 mb-4 rounded-lg text-sm"></div>
        <form id="category-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="action" id="form-action">
            <input type="hidden" name="id" id="category-id">
            <input type="hidden" name="existing_image" id="existing-image">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                <input type="file" name="image" id="image" class="mt-1 block w-full text-sm" accept="image/png, image/jpeg, image/gif">
                <img id="image-preview" class="mt-2 h-20 rounded hidden">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="cancel-btn" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('category-modal');
const form = document.getElementById('category-form');
const addBtn = document.getElementById('add-category-btn');
const cancelBtn = document.getElementById('cancel-btn');
const modalAlert = document.getElementById('modal-alert');

function showAlertInModal(message, isSuccess) {
    modalAlert.textContent = message;
    modalAlert.className = 'p-3 mb-4 rounded-lg text-sm';
    modalAlert.classList.add(isSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
    modalAlert.classList.remove('hidden');
}

function showModal(title, action, id = null) {
    document.getElementById('modal-title').innerText = title;
    form.reset();
    document.getElementById('form-action').value = action;
    document.getElementById('category-id').value = id || '';
    modalAlert.classList.add('hidden');
    document.getElementById('image-preview').classList.add('hidden');
    modal.classList.remove('hidden');
}

addBtn.onclick = () => showModal('Add New Category', 'add');
cancelBtn.onclick = () => modal.classList.add('hidden');

async function editCategory(id) {
    const formData = new FormData();
    formData.append('action', 'get');
    formData.append('id', id);
    const result = await ajaxRequest('category.php', formData);
    if (result.status === 'success') {
        showModal('Edit Category', 'edit', id);
        document.getElementById('name').value = result.data.name;
        document.getElementById('existing-image').value = result.data.image;
        const preview = document.getElementById('image-preview');
        preview.src = `../${result.data.image}`;
        preview.classList.remove('hidden');
    } else {
        alert(result.message);
    }
}

async function deleteCategory(id) {
    if (!confirm('Are you sure? This action cannot be undone.')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    const result = await ajaxRequest('category.php', formData);
    if (result.status === 'success') {
        document.getElementById(`cat-row-${id}`).remove();
        alert(result.message);
    } else {
        alert(result.message);
    }
}

// Separate function for form submission
async function handleFormSubmit(event) {
    event.preventDefault();
    const result = await ajaxRequest('category.php', new FormData(form));
    
    showAlertInModal(result.message, result.status === 'success');

    if (result.status === 'success') {
        setTimeout(() => {
            modal.classList.add('hidden');
            location.reload(); 
        }, 1500); // 1.5 second wait
    }
}
</script>

<?php require_once 'common/bottom.php'; ?>