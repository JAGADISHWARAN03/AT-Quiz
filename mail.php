<?php include 'includes/header.php'; // Include the header file ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-1/4 bg-white p-4 border-r shadow-md">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Mail</h2>
                <ul class="space-y-2">
                    <li class="text-blue-600 font-medium cursor-pointer hover:underline">Inbox</li>
                    <li class="cursor-pointer hover:underline">Sent</li>
                    <li class="cursor-pointer hover:underline">Drafts</li>
                    <li class="cursor-pointer hover:underline">Trash</li>
                </ul>
            </div>

            <!-- Email List + Viewer -->
            <div class="flex-1 flex flex-col bg-white shadow-md rounded-lg">
                <!-- Toolbar -->
                <div class="p-4 border-b flex items-center justify-between">
                    <input type="text" placeholder="Search mail" class="border rounded px-4 py-2 w-1/3 focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Content -->
                <div class="flex flex-1 overflow-hidden">
                    <!-- Email List -->
                    <div class="w-1/3 bg-gray-50 overflow-y-auto border-r">
                        <?php
                        // IMAP Configuration
                        $hostname = "{imap.gmail.com:993/imap/ssl}INBOX"; // Replace with your IMAP server
                        $username = 'jagadishbit0@gmail.com'; // Replace with your email
                        $password = 'ughe ebfb ewky gqep'; // Replace with your email password

                        // Connect to IMAP
                        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());

                        // Search for emails related to your application
                        $emails = imap_search($inbox, 'SUBJECT "New User Registration"'); // Filter emails with "New User Registration" in the subject

                        if ($emails) {
                            rsort($emails); // Sort emails in descending order
                            foreach ($emails as $email_number) {
                                $overview = imap_fetch_overview($inbox, $email_number, 0);
                                echo '<div class="p-4 border-b hover:bg-gray-100 cursor-pointer">';
                                echo '<p class="font-bold text-gray-800">' . htmlspecialchars($overview[0]->from) . '</p>';
                                echo '<p class="text-sm text-gray-600">' . htmlspecialchars($overview[0]->subject) . '</p>';
                                echo '<a href="view_mail.php?email_id=' . $email_number . '" class="text-blue-500 hover:underline">View</a>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-gray-600 p-4">No application-related emails found.</p>';
                        }

                        // Close IMAP connection
                        imap_close($inbox);
                        ?>
                    </div>

                    <!-- Email Viewer -->
                    <div class="flex-1 p-6 overflow-y-auto">
                        <h2 class="text-2xl font-semibold mb-2 text-gray-800">Select an email to view</h2>
                        <p class="text-sm text-gray-500 mb-4">From: -</p>
                        <div class="text-gray-800 space-y-4">
                            <p>No email selected.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; // Include the footer file ?>
</body>
</html>