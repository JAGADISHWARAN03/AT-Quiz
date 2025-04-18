document.addEventListener("DOMContentLoaded", function () {
    const startQuizButton = document.getElementById("startQuiz");

    async function requestMicAccess(retry = false) {
        try {
            // Request access to Microphone & Camera
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });

            if (stream) {
                console.log("✅ Microphone & Camera access granted.");
                startQuizButton.disabled = false; // Enable start button
                return true;
            }
        } catch (error) {
            console.error("❌ Microphone access denied:", error);
            alert("Microphone access is required to start the quiz. Please enable it.");

            if (error.name === "NotAllowedError" || error.name === "PermissionDeniedError") {
                alert(
                    "⚠️ You denied microphone access.\n\n🔹 To enable it:\n1️⃣ Click the 🔒 lock icon in the browser address bar.\n2️⃣ Find 'Microphone' and set it to 'Allow'.\n3️⃣ Reload the page."
                );

                // Retry automatically after 3 seconds
                if (!retry) {
                    setTimeout(() => {
                        requestMicAccess(true);
                    }, 3000);
                } else {
                    alert("❗ You need to manually enable the microphone in your browser settings.");
                }
            }

            startQuizButton.disabled = true; // Keep start button disabled
            return false;
        }
        return false;
    }

    // Automatically request permission when the page loads
    requestMicAccess();

    // Re-check permission when the user clicks "Start Quiz"
    startQuizButton.addEventListener("click", async function (event) {
        event.preventDefault();
        const isAllowed = await requestMicAccess();
        if (isAllowed) {
            window.location.href = "quiz.php"; // Redirect to Quiz Page
        }
    });
});
  

document.addEventListener("DOMContentLoaded", function () {
    let currentQuestionIndex = 0;
    let answers = {};
    let timer = 300;

    const timerDisplay = document.getElementById("timer");
    const questionContainer = document.getElementById("question-container");
    const prevButton = document.getElementById("prev");
    const nextButton = document.getElementById("next");
    const statusDivs = document.querySelectorAll("#question-status div");

    function loadQuestion(index) {
        const question = questions[index];
        let html = `<h3 class='text-lg font-bold mb-2'>${index + 1}. ${question.question}</h3>`;
        question.options.forEach(option => {
            const checked = answers[index] === option ? "checked" : "";
            html += `<label class='block p-2 border rounded cursor-pointer'>
                        <input type='radio' name='q${index}' value='${option}' ${checked}>
                        ${option}
                     </label>`;
        });
        questionContainer.innerHTML = html;
        highlightCurrentQuestion();
    }

    function highlightCurrentQuestion() {
        statusDivs.forEach(div => div.classList.remove("bg-blue-500", "text-white"));
        statusDivs[currentQuestionIndex].classList.add("bg-blue-500", "text-white");
    }

    function updateAnsweredCount() {
        let answered = Object.keys(answers).length;
        document.getElementById("question-status").setAttribute("data-answered", answered);
    }

    nextButton.addEventListener("click", () => {
        saveAnswer();
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            loadQuestion(currentQuestionIndex);
        }
    });

    prevButton.addEventListener("click", () => {
        saveAnswer();
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            loadQuestion(currentQuestionIndex);
        }
    });

    function saveAnswer() {
        const selected = document.querySelector(`input[name='q${currentQuestionIndex}']:checked`);
        if (selected) {
            answers[currentQuestionIndex] = selected.value;
            statusDivs[currentQuestionIndex].classList.add("bg-green-500", "text-white");
        }
        updateAnsweredCount();
    }

    setInterval(() => {
        if (timer > 0) {
            timer--;
            let minutes = Math.floor(timer / 60);
            let seconds = timer % 60;
            timerDisplay.textContent = `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;
        } else {
            alert("Time's up! Submitting the quiz.");
            document.getElementById("quiz-form").submit();
        }
    }, 1000);

    loadQuestion(currentQuestionIndex);
});
