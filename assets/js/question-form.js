document.addEventListener('DOMContentLoaded', () => {
    const questionsContainer = document.getElementById('questions-container');
    const addNewQuestionButton = document.getElementById('add-new-question');

    function addNewQuestionForm() {
        const questionIndex = questionsContainer.children.length;
        const questionForm = document.createElement('div');
        questionForm.classList.add('mb-4', 'p-4', 'border', 'rounded-lg', 'bg-white', 'shadow-md');
        questionForm.innerHTML = `
            <h3 class="font-semibold text-lg mb-2 text-[var(--primary-color)]">Question ${questionIndex + 1}</h3>
            <div class="mb-4">
                <label class="block font-medium mb-2 text-gray-700">Question Text</label>
                <textarea name="questions[${questionIndex}][question_text]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" rows="2" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-2 text-gray-700">Question Type</label>
                <select name="questions[${questionIndex}][question_type]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] question-type" required onchange="updateInputFields(this)">
                    <option value="radio">Radio Button</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="text">Input Text</option>
                </select>
            </div>
            <div class="dynamic-input-fields grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"></div>
            <button type="button" class="bg-[var(--secondary-color)] text-white px-4 py-2 rounded-lg hover:bg-red-700 remove-question">
                Remove Question
            </button>
        `;
        questionsContainer.appendChild(questionForm);
        questionForm.querySelector('.remove-question').addEventListener('click', () => {
            questionForm.remove();
        });
        updateInputFields(questionForm.querySelector('.question-type'));
    }

    addNewQuestionButton.addEventListener('click', addNewQuestionForm);

    window.updateInputFields = function(selectElement) {
        const questionIndex = selectElement.name.match(/\[(\d+)\]/)[1];
        const questionType = selectElement.value;
        const inputFieldsContainer = selectElement.closest('div').nextElementSibling;
        inputFieldsContainer.innerHTML = '';

        if (questionType === 'checkbox' || questionType === 'radio') {
            for (let i = 1; i <= 4; i++) {
                const optionDiv = document.createElement('div');
                const inputName = `questions[${questionIndex}][options][${i}][text]`;
                const correctName = questionType === 'checkbox' ?
                    `questions[${questionIndex}][options][${i}][correct]` :
                    `questions[${questionIndex}][correct_option]`;
                optionDiv.innerHTML = `
                    <label class="flex items-center">
                        <input type="${questionType}" name="${correctName}" value="${i}" class="mr-2" ${questionType === 'checkbox' ? '' : 'required'}>
                        <input type="text" name="${inputName}" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" placeholder="Option ${i}" required>
                    </label>
                `;
                inputFieldsContainer.appendChild(optionDiv);
            }
        } else if (questionType === 'text') {
            const textInputDiv = document.createElement('div');
            textInputDiv.innerHTML = `
                <label class="block font-medium mb-2 text-gray-700">Answer</label>
                <input type="text" name="questions[${questionIndex}][text_answer]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" placeholder="Enter the answer" required>
            `;
            inputFieldsContainer.appendChild(textInputDiv);
        }
    };

    document.getElementById('back-to-quiz-info').addEventListener('click', function() {
        document.getElementById('add-questions-section').classList.add('hidden');
        document.getElementById('quiz-info-section').classList.remove('hidden');
    });

    // Modal question form
    document.getElementById('modal-add-new-question').addEventListener('click', function() {
        const questionsContainer = document.getElementById('modal-questions-container');
        const questionIndex = questionsContainer.children.length;
        const questionForm = document.createElement('div');
        questionForm.classList.add('mb-4', 'p-4', 'border', 'rounded-lg', 'bg-white', 'shadow-md');
        questionForm.innerHTML = `
            <h3 class="font-semibold text-lg mb-2 text-[var(--primary-color)]">Question ${questionIndex + 1}</h3>
            <div class="mb-4">
                <label class="block font-medium mb-2 text-gray-700">Question Text</label>
                <textarea name="questions[${questionIndex}][question_text]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)]" rows="2" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-2 text-gray-700">Question Type</label>
                <select name="questions[${questionIndex}][question_type]" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] question-type" required onchange="updateInputFields(this)">
                    <option value="radio">Radio Button</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="text">Input Text</option>
                </select>
            </div>
            <div class="dynamic-input-fields grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"></div>
            <button type="button" class="bg-[var(--secondary-color)] text-white px-4 py-2 rounded-lg hover:bg-red-700 remove-question">
                Remove Question
            </button>
        `;
        questionsContainer.appendChild(questionForm);
        questionForm.querySelector('.remove-question').addEventListener('click', () => {
            questionForm.remove();
        });
        updateInputFields(questionForm.querySelector('.question-type'));
    });

    document.getElementById('add-questions-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const successDiv = document.getElementById('modal-question-success');
        const errorDiv = document.getElementById('modal-question-error');
        successDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');
        fetch('dashboard.php?action=create_quiz', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const msg = doc.querySelector('#success-message');
            if (msg) {
                successDiv.innerHTML = msg.innerHTML;
                successDiv.classList.remove('hidden');
                setTimeout(() => {
                    successDiv.classList.add('hidden');
                    closeAddQuestionModal();
                }, 2000);
                form.reset();
            } else {
                let errorMsg = doc.querySelector('.error-message')?.textContent ||
                    "Something went wrong. Please check your input and try again.";
                errorDiv.innerHTML = errorMsg;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(() => {
            errorDiv.innerHTML = "An unexpected error occurred. Please try again.";
            errorDiv.classList.remove('hidden');
        });
    });
});