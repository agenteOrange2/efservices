export function confirmDelete(event, deleteUrl) {
    event.preventDefault();
    const modal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = deleteUrl;
    modal.classList.remove('hidden');
}

export function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
}
