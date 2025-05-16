function fetchQuizTitles(categoryId) {
    const quizDropdown = document.getElementById('quiz_title');
    quizDropdown.innerHTML = '<option value="">Loading...</option>';
    if (categoryId) {
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
    } else {
        quizDropdown.innerHTML = '<option value="">Select a quiz</option>';
    }
}

function toggleQuizStatus(quizId, newStatus) {
    const onButton = document.getElementById(`on-btn-${quizId}`);
    const offButton = document.getElementById(`off-btn-${quizId}`);
    onButton.innerHTML = 'Updating...';
    offButton.innerHTML = 'Updating...';
    fetch('toggle_quiz_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `quiz_id=${quizId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (newStatus === 1) {
                onButton.classList.remove('hidden');
                offButton.classList.add('hidden');
            } else {
                onButton.classList.add('hidden');
                offButton.classList.remove('hidden');
            }
            alert(data.message || 'Quiz status updated successfully.');
        } else {
            alert(data.message || 'Failed to update quiz status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the quiz status.');
    })
    .finally(() => {
        onButton.innerHTML = 'On';
        offButton.innerHTML = 'Off';
    });
}

function generateQuizLink(quizId) {
    const link = `user_form.php?quiz_title_id=${quizId}`;
    const linkSpan = document.getElementById(`quiz-link-${quizId}`);
    linkSpan.innerHTML = `
        <a href="${link}" class="text-blue-500 underline hover:text-blue-700" target="_blank">
            View Quiz
        </a>
    `;
    linkSpan.classList.remove('hidden');
}

function deleteQuiz(quizId) {
    if (confirm('Are you sure you want to delete this quiz?')) {
        fetch(`delete_quiz.php?id=${quizId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Quiz deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete quiz: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting quiz:', error);
            alert('An error occurred while deleting the quiz.');
        });
    }
}

function openEditModal(quizId, title, description, timer) {
    document.getElementById('edit-quiz-id').value = quizId;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-timer').value = timer;
    document.getElementById('edit-modal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}

function edit_form_submit(e) {
    e.preventDefault();
    const quizId = document.getElementById('edit-quiz-id').value;
    const title = document.getElementById('edit-title').value;
    const description = document.getElementById('edit-description').value;
    const timer = document.getElementById('edit-timer').value;

    fetch('update_quiz.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            quiz_id: quizId,
            title,
            description,
            timer
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Quiz updated successfully!');
            const row = document.querySelector(`tr[data-id="${quizId}"]`);
            if (row) {
                row.querySelector('.quiz-title').textContent = title;
                row.querySelector('.quiz-description').textContent = description;
                row.querySelector('.quiz-timer').textContent = `${timer} minutes`;
            }
            closeEditModal();
        } else {
            alert('Failed to update quiz: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating quiz:', error);
        alert('An error occurred while updating the quiz.');
    });
}