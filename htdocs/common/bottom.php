</div> <!-- End Main Content Wrapper -->

<!-- Bottom Navigation -->
<nav class="fixed bottom-0 left-0 right-0 bg-white shadow-[0_-2px_10px_-3px_rgba(0,0,0,0.1)] px-4 py-2 flex justify-around items-center z-30">
    <a href="index.php" class="flex flex-col items-center text-gray-500 hover:text-indigo-600 transition-colors">
        <i class="fas fa-home text-xl"></i>
        <span class="text-xs mt-1">Home</span>
    </a>
    <a href="cart.php" class="relative flex flex-col items-center text-gray-500 hover:text-indigo-600 transition-colors">
        <i class="fas fa-shopping-cart text-xl"></i>
        <span class="text-xs mt-1">Cart</span>
        <?php
            $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
            if ($cart_count > 0) {
                echo "<span class='absolute -top-1 -right-2 bg-red-500 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center'>{$cart_count}</span>";
            }
        ?>
    </a>
    <a href="profile.php" class="flex flex-col items-center text-gray-500 hover:text-indigo-600 transition-colors">
        <i class="fas fa-user text-xl"></i>
        <span class="text-xs mt-1">Profile</span>
    </a>
</nav>


<script>
    // --- GLOBAL JS ---

    // Disable context menu, selection, and zoom
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('touchstart', function(event) {
        if (event.touches.length > 1) {
            event.preventDefault();
        }
    }, { passive: false });
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);


    // Loader functions
    const loadingModal = document.getElementById('loading-modal');
    function showLoader() {
        loadingModal.classList.remove('hidden');
        loadingModal.classList.add('flex');
    }
    function hideLoader() {
        loadingModal.classList.add('hidden');
        loadingModal.classList.remove('flex');
    }
    
    // AJAX Helper
    async function ajaxRequest(url, formData) {
        showLoader();
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('AJAX Error:', error);
            alert('An error occurred. Please try again.');
            return { status: 'error', message: 'Network or server error.' };
        } finally {
            hideLoader();
        }
    }
</script>
</body>
</html>