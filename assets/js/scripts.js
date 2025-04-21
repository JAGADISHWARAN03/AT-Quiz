// filepath: AT-Quiz-main/assets/js/scripts.js

document.addEventListener('DOMContentLoaded', () => {
    const questionTypeSelect = document.getElementById('question-type');
    const questionOptionsContainer = document.getElementById('question-options-container');

    questionTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        questionOptionsContainer.innerHTML = ''; // Clear previous options

        if (selectedType === 'text') {
            questionOptionsContainer.innerHTML = `
                <label for="question-text" class="block font-medium mb-2">Question Text</label>
                <input type="text" name="question_text" class="w-full p-2 border rounded" required>
            `;
        } else if (selectedType === 'radio') {
            for (let i = 1; i <= 4; i++) {
                questionOptionsContainer.innerHTML += `
                    <div class="mb-2">
                        <label class="flex items-center">
                            <input type="radio" name="correct_option" value="${i}" class="mr-2" required>
                            <input type="text" name="option_${i}" class="w-full p-2 border rounded" placeholder="Option ${i}" required>
                        </label>
                    </div>
                `;
            }
        } else if (selectedType === 'checkbox') {
            for (let i = 1; i <= 4; i++) {
                questionOptionsContainer.innerHTML += `
                    <div class="mb-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="correct_options[]" value="${i}" class="mr-2">
                            <input type="text" name="option_${i}" class="w-full p-2 border rounded" placeholder="Option ${i}" required>
                        </label>
                    </div>
                `;
            }
        }
    });
});