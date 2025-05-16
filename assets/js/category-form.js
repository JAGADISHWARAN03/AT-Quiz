document.addEventListener('DOMContentLoaded', () => {
    const addCategoryBtn = document.getElementById('add-category-btn');
    const addCategoryForm = document.getElementById('add-category-form');
    const saveCategoryBtn = document.getElementById('save-category-btn');
    const newCategoryName = document.getElementById('new-category-name');

    addCategoryBtn.addEventListener('click', () => {
        addCategoryForm.classList.toggle('hidden');
    });

    saveCategoryBtn.addEventListener('click', () => {
        const categoryName = newCategoryName.value.trim();
        if (categoryName) {
            saveCategoryBtn.innerHTML = 'Saving...';
            saveCategoryBtn.disabled = true;
            fetch('add_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `name=${encodeURIComponent(categoryName)}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Category added successfully!');
                    const categoryDropdown = document.querySelector('select[name="category_id"]');
                    const newOption = document.createElement('option');
                    newOption.value = data.category_id;
                    newOption.textContent = categoryName;
                    categoryDropdown.appendChild(newOption);
                    newCategoryName.value = '';
                    addCategoryForm.classList.add('hidden');
                } else {
                    alert('Failed to add category: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error adding category:', error);
                alert('An error occurred while adding the category.');
            })
            .finally(() => {
                saveCategoryBtn.innerHTML = 'Save';
                saveCategoryBtn.disabled = false;
            });
        } else {
            alert('Please enter a category name.');
        }
    });
});