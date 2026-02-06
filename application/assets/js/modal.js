document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });
});

function fetchModalContent(type) {
    // AJAX fetch, simplified
    return `<div>Loading ${type} form...</div>`; // In real: fetch(`/submission/create/${type}`)
}

function toggleLike(id) {
    fetch(`/submission/view//${id}`, {method: 'POST', body: new FormData().append('action', 'like')});
    // Update UI
}

function showCommentModal(id) {
    showModal('comment'); // Form submits to /submission/comment/${id}
}