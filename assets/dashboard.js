/**
 * Dashboard JavaScript for category management
 * Handles category list loading, modal interactions, updates, and deletions
 */

// Consolidated DOMContentLoaded listener to avoid duplicates
document.addEventListener('DOMContentLoaded', () => {
    // Ensure CSRF token is available (set by PHP)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    /**
     * Loads categories for a given page via AJAX
     * @param {number} page - The page number to load
     */
    window.loadCategories = function(page) {
        const categoriesContainer = document.getElementById('categories-container');
        if (!categoriesContainer) {
            console.error('Element #categories-container not found');
            return;
        }
        categoriesContainer.innerHTML = '<p class="text-center text-gray-500">Loading...</p>';
        fetch(`fetch_categories.php?page=${page}`, {
            method: 'GET',
            headers: {
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error ${response.status}`);
            return response.text();
        })
        .then(data => {
            if (!data.trim()) {
                throw new Error('Empty response from server');
            }
            categoriesContainer.innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            categoriesContainer.innerHTML = `<p class="text-center text-red-500">Failed to load categories: ${error.message}</p>`;
        });
    };

    /**
     * Opens the update category modal and populates fields
     * @param {number} categoryId - The ID of the category
     * @param {string} categoryName - The name of the category
     */
    window.openUpdateCategoryModal = function(categoryId, categoryName) {
        const modal = document.getElementById('update-category-modal');
        const idInput = document.getElementById('update-category-id');
        const nameInput = document.getElementById('update-category-name');
        if (!modal || !idInput || !nameInput) {
            console.error('Modal or inputs not found');
            alert('Error: Update form not available.');
            return;
        }
        idInput.value = categoryId;
        nameInput.value = categoryName;
        modal.classList.remove('hidden');
    };

    /**
     * Closes the update category modal
     */
    window.closeUpdateCategoryModal = function() {
        const modal = document.getElementById('update-category-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    /**
     * Updates a category via AJAX
     * @param {Event} event - The form submission event
     */
    window.updateCategory = function(event) {
        event.preventDefault();
        const idInput = document.getElementById('update-category-id');
        const nameInput = document.getElementById('update-category-name');
        if (!idInput || !nameInput) {
            console.error('Modal inputs not found');
            alert('Form error. Please try again.');
            return;
        }
        const id = idInput.value;
        const name = nameInput.value.trim();
        if (!name) {
            alert('Please enter a valid category name.');
            return;
        }
        fetch('update_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: `id=${id}&name=${encodeURIComponent(name)}`
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const catElem = document.querySelector(`[data-category-id="${id}"] .category-name`);
                if (catElem) {
                    catElem.textContent = name;
                } else {
                    console.warn(`Element with data-category-id="${id}" not found`);
                }
                closeUpdateCategoryModal();
                alert('Category updated successfully.');
            } else {
                alert(`Failed to update category: ${data.message || 'Unknown error'}`);
            }
        })
        .catch(err => {
            console.error('Error updating category:', err);
            alert(`An error occurred while updating: ${err.message}`);
        });
    };

    /**
     * Deletes a category via AJAX
     * @param {number} categoryId - The ID of the category to delete
     */
    window.deleteCategory = function(categoryId) {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }
        fetch(`delete_category.php?id=${categoryId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const categoryElem = document.querySelector(`[data-category-id="${id}"]`);
                if (categoryElem) {
                    categoryElem.remove();
                }
                alert('Category deleted successfully!');
            } else {
                alert(`Failed to delete category: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error deleting category:', error);
            alert(`An error occurred while deleting the category: ${error.message}`);
        });
    };

    // Initialize categories on page load (optional, if needed)
    const currentPage = document.querySelector('span.text-gray-700')?.textContent.match(/Page (\d+)/)?.[1];
    if (currentPage) {
        loadCategories(parseInt(currentPage));
    }
});