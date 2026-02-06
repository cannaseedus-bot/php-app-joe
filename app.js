// Register PWA
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js');
}

// Session check (for auth)
fetch('/application/controllers/User.php?action=session_check', {credentials: 'same-origin'})
    .then(res => res.json())
    .then(data => {
        if (!data.logged_in) {
            showModal('login');
        }
    });

// Global functions for modals, etc.
function showModal(type) {
    document.getElementById('modal').classList.add('active');
    document.getElementById('modal-content').innerHTML = fetchModalContent(type);
}