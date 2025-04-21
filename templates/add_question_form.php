<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h2 class="text-2xl font-bold mb-4">Add Question</h2>
        <form method="POST" action="dashboard.php?action=add_question">
            <div class="mb-4">
                <label for="question_text" class="block font-medium mb-2">Question Text</label>
                <textarea name="question_text" id="question_text" class="w-full p-2 border rounded" rows="2" required></textarea>
            </div>
            <div class="mb-4">
                <label for="question_type" class="block font-medium mb-2">Question Type</label>
                <select name="question_type" id="question_type" class="w-full p-2 border rounded" onchange="toggleOptionsFields()" required>
                    <option value="">Select Question Type</option>
                    <option value="text">Text Input</option>
                    <option value="radio">Radio Button</option>
                    <option value="checkbox">Checkbox</option>
                </select>
            </div>
            <div id="options_container" class="hidden">
                <div class="mb-4">
                    <label class="block font-medium mb-2">Options</label>
                    <div id="options_fields">
                        <div class="option mb-2">
                            <input type="text" name="option[]" class="w-full p-2 border rounded" placeholder="Option 1" required>
                        </div>
                    </div>
                    <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" onclick="addOption()">Add Option</button>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                    Add Question
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleOptionsFields() {
            const questionType = document.getElementById('question_type').value;
            const optionsContainer = document.getElementById('options_container');
            optionsContainer.classList.toggle('hidden', questionType === '');
        }

        function addOption() {
            const optionsFields = document.getElementById('options_fields');
            const newOption = document.createElement('div');
            newOption.classList.add('option', 'mb-2');
            newOption.innerHTML = '<input type="text" name="option[]" class="w-full p-2 border rounded" placeholder="Option">';
            optionsFields.appendChild(newOption);
        }
    </script>
</body>
</html>