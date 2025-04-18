<?php




$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
}


?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<div class="container mx-auto px-4 py-8">
    
    
    <?php if (!empty($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" enctype="multipart/form-data">
            <!-- Example Form Fields -->
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="name">Name</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2" type="text" name="name" id="name" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="email">Email</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2" type="email" name="email" id="email" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="password">Password</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2" type="password" name="password" id="password" required>
            </div>
            <div class="mb-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add User
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold mb-4">Recent Users Added</h3>
        <div class="overflow-x-auto">
            
        </div>
    </div>
</div>

