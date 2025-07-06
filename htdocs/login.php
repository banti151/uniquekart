<?php
// Step 1: Config file ko include karna
require_once 'common/config.php';

// Step 2: Logout ka logic
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Step 3: Agar user pehle se logged in hai, to use index.php par bhej do
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Step 4: Login aur Signup ke AJAX requests ko handle karna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

    try {
        if ($_POST['action'] == 'login') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required.');
            }

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $response = ['status' => 'success', 'message' => 'Login successful!', 'redirect' => 'index.php'];
            } else {
                throw new Exception('Invalid email or password.');
            }
        }
        elseif ($_POST['action'] == 'signup') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($name) || empty($phone) || empty($email) || empty($password)) {
                throw new Exception('All fields are required for signup.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format.');
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            if ($stmt->fetch()) {
                throw new Exception('Email or phone number already registered.');
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $phone, $email, $hashed_password])) {
                $response = ['status' => 'success', 'message' => 'Signup successful! Please log in.'];
            } else {
                throw new Exception('Could not register user. Please try again.');
            }
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <!-- Title mein bhi naam theek kar diya hai -->
    <title>Login - Uniquekart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { -webkit-user-select: none; user-select: none; -webkit-tap-highlight-color: transparent; }
        .tab-active { border-bottom-color: #4f46e5; color: #4f46e5; }
        .tab-inactive { border-bottom-color: transparent; color: #6b7280; }
        .loader-dots div { animation: loader-dots 1s infinite linear; }
        @keyframes loader-dots { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex-col items-center justify-center hidden">
        <div class="flex space-x-2"><div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0s;"></div><div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0.2s;"></div><div class="w-4 h-4 bg-white rounded-full loader-dots" style="animation-delay: 0.4s;"></div></div><p class="text-white mt-4">Processing...</p>
    </div>

    <div class="w-full max-w-md p-6 bg-white rounded-2xl shadow-lg m-4">
        <div class="text-center mb-8">
            <!-- HEADING - YAHAN CHANGE KIYA GAYA HAI -->
            <h1 class="text-3xl font-bold text-indigo-600">Uniquekart</h1>
            <p class="text-gray-500">Welcome! Please login or create an account.</p>
        </div>

        <!-- Tabs -->
        <div class="flex border-b mb-6">
            <button id="login-tab-btn" class="flex-1 py-3 text-center font-semibold border-b-2 transition-colors tab-active">Login</button>
            <button id="signup-tab-btn" class="flex-1 py-3 text-center font-semibold border-b-2 transition-colors tab-inactive">Sign Up</button>
        </div>

        <div id="alert-box" class="p-3 mb-4 rounded-lg text-sm hidden"></div>

        <!-- Login Form -->
        <form id="login-form" class="space-y-4">
            <input type="hidden" name="action" value="login">
            <div>
                <label for="login-email" class="text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="login-email" name="email" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="you@example.com" required>
            </div>
            <div>
                <label for="login-password" class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="login-password" name="password" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">Login</button>
        </form>

        <!-- Signup Form -->
        <form id="signup-form" class="space-y-4 hidden">
            <input type="hidden" name="action" value="signup">
            <div>
                <label for="signup-name" class="text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="signup-name" name="name" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg" placeholder="John Doe" required>
            </div>
            <div>
                <label for="signup-phone" class="text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" id="signup-phone" name="phone" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg" placeholder="9876543210" required>
            </div>
            <div>
                <label for="signup-email" class="text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="signup-email" name="email" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg" placeholder="you@example.com" required>
            </div>
            <div>
                <label for="signup-password" class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="signup-password" name="password" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">Create Account</button>
        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const loginTabBtn = document.getElementById('login-tab-btn');
    const signupTabBtn = document.getElementById('signup-tab-btn');
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const alertBox = document.getElementById('alert-box');

    loginTabBtn.addEventListener('click', () => {
        loginTabBtn.classList.add('tab-active');
        loginTabBtn.classList.remove('tab-inactive');
        signupTabBtn.classList.add('tab-inactive');
        signupTabBtn.classList.remove('tab-active');
        loginForm.classList.remove('hidden');
        signupForm.classList.add('hidden');
        alertBox.classList.add('hidden');
    });

    signupTabBtn.addEventListener('click', () => {
        signupTabBtn.classList.add('tab-active');
        signupTabBtn.classList.remove('tab-inactive');
        loginTabBtn.classList.add('tab-inactive');
        loginTabBtn.classList.remove('tab-active');
        signupForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
        alertBox.classList.add('hidden');
    });

    // Loader
    const loadingModal = document.getElementById('loading-modal');
    function showLoader() { loadingModal.classList.remove('hidden'); loadingModal.classList.add('flex'); }
    function hideLoader() { loadingModal.classList.add('hidden'); loadingModal.classList.remove('flex'); }

    // Alert
    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.className = 'p-3 mb-4 rounded-lg text-sm'; // Reset classes
        if (type === 'success') {
            alertBox.classList.add('bg-green-100', 'text-green-800');
        } else {
            alertBox.classList.add('bg-red-100', 'text-red-800');
        }
        alertBox.classList.remove('hidden');
    }

    // AJAX Form Submission
    async function handleFormSubmit(event) {
        event.preventDefault();
        showLoader();
        const form = event.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch('login.php', { method: 'POST', body: formData });
            const result = await response.json();

            showAlert(result.message, result.status);

            if (result.status === 'success') {
                if(result.redirect) {
                    setTimeout(() => { window.location.href = result.redirect; }, 1000);
                } else {
                    // Switch to login tab on successful signup
                    loginTabBtn.click();
                    document.getElementById('login-email').value = formData.get('email');
                }
            }
        } catch (error) {
            showAlert('An unexpected error occurred.', 'error');
            console.error('Error:', error);
        } finally {
            hideLoader();
        }
    }

    loginForm.addEventListener('submit', handleFormSubmit);
    signupForm.addEventListener('submit', handleFormSubmit);

    // Disable zoom and right-click
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('touchstart', (e) => { if (e.touches.length > 1) e.preventDefault(); }, { passive: false });
});
</script>

</body>
</html>