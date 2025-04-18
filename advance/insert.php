<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Quiz PDF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-10">
    <div class="max-w-lg mx-auto bg-white shadow-lg p-6 rounded">
        <h2 class="text-xl font-semibold mb-4">Upload Quiz PDF</h2>
        <form action="upload_pdf.php" method="POST" enctype="multipart/form-data">
            <label class="block">Select PDF File:</label>
            <input type="file" name="pdf_file" accept=".pdf" class="w-full p-2 border rounded mb-3" required>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload & Process</button>
        </form>
    </div>
</body>
</html>