document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('quiz-create-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const errorDiv = document.getElementById('quiz-error-message');
        errorDiv.classList.add('hidden');
        errorDiv.innerHTML = '';
        try {
            const response = await fetch('dashboard.php?action=create_quiz', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                throw new Error('Network error. Please try again later.');
            }
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const msg = doc.querySelector('#success-message');
            if (msg) {
                const successDiv = document.getElementById('quiz-success-message');
                successDiv.innerHTML = msg.innerHTML;
                successDiv.classList.remove('hidden');
                setTimeout(() => { successDiv.classList.add('hidden'); }, 5000);
                form.reset();
            } else {
                let errorMsg = doc.querySelector('.error-message')?.textContent ||
                    "Something went wrong. Please check your input and try again.";
                errorDiv.innerHTML = errorMsg;
                errorDiv.classList.remove('hidden');
            }
        } catch (err) {
            let userMsg = "An unexpected error occurred. Please try again.";
            errorDiv.innerHTML = userMsg;
            errorDiv.classList.remove('hidden');
        }
    });

    document.getElementById('go-to-add-questions').addEventListener('click', function() {
        document.getElementById('quiz-info-section').classList.add('hidden');
        document.getElementById('add-questions-section').classList.remove('hidden');
    });
});