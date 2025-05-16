function toggleCategory(categoryId) {
    const toggleInput = document.querySelector(`input[onchange="toggleCategory(${categoryId})"]`);
    const newStatus = toggleInput.checked ? 1 : 0;
    fetch('toggle_category_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `category_id=${categoryId}&status=${newStatus}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category status updated successfully!');
        } else {
            alert('Failed to update category status: ' + data.message);
            toggleInput.checked = !newStatus; // Revert toggle on failure
        }
    })
    .catch(error => {
        console.error('Error toggling category:', error);
        alert('An error occurred while updating the category status.');
        toggleInput.checked = !newStatus;
    });
}

function openUpdateCategoryModal(categoryId, categoryName) {
    document.getElementById('update-category-id').value = categoryId;
    document.getElementById('update-category-name').value = categoryName;
    document.getElementById('update-category-modal').classList.remove('hidden');
}

function closeUpdateCategoryModal() {
    document.getElementById('update-category-modal').classList.add('hidden');
}

function updateCategory(event) {
    event.preventDefault();
    const categoryId = document.getElementById('update-category-id').value;
    const categoryName = document.getElementById('update-category-name').value.trim();
    if (!categoryName) {
        alert('Please enter a category name.');
        return;
    }
    fetch('update_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: categoryId,
            name: categoryName,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Category updated successfully!');
            const categoryElement = document.querySelector(`[data-category-id="${categoryId}"] .category-name`);
            categoryElement.textContent = categoryName;
            closeUpdateCategoryModal();
        } else {
            alert('Failed to update category: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating category:', error);
        alert('An error occurred while updating the category.');
    });
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category?')) {
        fetch(`delete_category.php?id=${categoryId}`, {
            method: 'GET',
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Category deleted successfully!');
                document.querySelector(`[data-category-id="${categoryId}"]`).remove();
            } else {
                alert('Failed to delete category: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting category:', error);
            alert('An error occurred while deleting the category.');
        });
    }
}

function loadCategories(page) {
    fetch(`fetch_categories.php?page=${page}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('categories-container').innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            document.getElementById('categories-container').innerHTML = '<p class="text-center text-red-500">Failed to load categories.</p>';
        });
}