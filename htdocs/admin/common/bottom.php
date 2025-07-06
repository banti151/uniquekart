        </main>
    </div> <!-- End Main Content -->

<script>
    // --- GLOBAL ADMIN JS ---
    document.addEventListener('contextmenu', event => event.preventDefault());
    
    // Sidebar Toggle for mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const openSidebarButton = document.getElementById('open-sidebar-button');

    openSidebarButton.addEventListener('click', () => {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('hidden');
    });
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
    });

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
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            alert('An error occurred. Please check console.');
            return { status: 'error', message: 'Network or server error.' };
        } finally {
            hideLoader();
        }
    }
</script>
</body>
</html>