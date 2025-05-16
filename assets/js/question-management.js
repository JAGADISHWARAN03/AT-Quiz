function openEditModal1(id, questionText, questionType, option1, option2, option3, option4, correctOption) {
    document.getElementById('edit-question-id').value = id;
    document.getElementById('edit-question-text').value = questionText;
    document.getElementById('edit-question-type').value = questionType;
    document.getElementById('edit-option-1').value = option1 || '';
    document.getElementById('edit-option-2').value = option2 || '';
    document.getElementById('edit-option-3').value = option3 || '';
    document.getElementById('edit-option-4').value = option4 || '';
    document.getElementById('edit-correct-option').value = correctOption || '';
    document.getElementById('edit-question-modal').classList.remove('hidden');
}

function closeEditModal1() {
    document.getElementById('edit-question-modal').classList.add('hidden');
}

function updateQuestion(e) {
    e.preventDefault();
    const questionId = document.getElementById('edit-question-id').value;
    const questionText = document.getElementById('edit-question-text').value;
    const questionType = document.getElementById('edit-question-type').value;
    const option1 = document.getElementById('edit-option-1').value;
    const option2 = document.getElementById('edit-option-2').value;
    const option3 = document.getElementById('edit-option-3').value;
    const option4 = document.getElementById('edit-option-4').value;
    const correctOption = document.getElementById('edit-correct-option').value;

    fetch('update_question.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: questionId,
            question_text: questionText,
            question_type: questionType,
            option_1: option1,
            option_2: option2,
            option_3: option3,
            option_4: option4,
            correct_option: correctOption
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Question updated successfully!');
            closeEditModal1();
            location.reload(); // Refresh to reflect changes
        } else {
            alert('Failed to update question: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating question:', error);
        alert('An error occurred while updating the question.');
    });
}

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        fetch(`delete_question.php?id=${questionId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Question deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete question: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting question:', error);
            alert('An error occurred while deleting the question.');
        });
    }
}

function openAddQuestionModal(categoryId, categoryName) {
    document.getElementById('add-question-modal').classList.remove('hidden');
    document.getElementById('modal-category-id').value = categoryId;
    document.getElementById('add-question-modal-title').textContent = `Add Questions for ${categoryName}`;
    const quizDropdown = document.getElementById('modal-quiz-title');
    quizDropdown.innerHTML = '<option value="">Loading...</option>';
    fetch(`fetch_quizzes.php?category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            quizDropdown.innerHTML = '<option value="">Select a quiz</option>';
            if (data.success && Array.isArray(data.quizzes)) {
                data.quizzes.forEach(quiz => {
                    const option = document.createElement('option');
                    option.value = quiz.id;
                    option.textContent = quiz.title;
                    quizDropdown.appendChild(option);
                });
            } else {
                quizDropdown.innerHTML = '<option value="">No quizzes found</option>';
            }
        })
        .catch(error => {
            console.error('Error fetching quizzes:', error);
            quizDropdown.innerHTML = '<option value="">Failed to load quizzes</option>';
        });
}

function closeAddQuestionModal() {
    document.getElementById('add-question-modal').classList.add('hidden');
    document.getElementById('modal-questions-container').innerHTML = '';
    document.getElementById('modal-question-success').classList.add('hidden');
    document.getElementById('modal-question-error').classList.add('hidden');
}