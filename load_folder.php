<!-- filepath: c:\xampp\htdocs\AT-Quiz-main\load_folder.php -->
<?php
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'INBOX';

$hostname = "{imap.gmail.com:993/imap/ssl}$folder";
$username = 'jagadishbit0@gmail.com';
$password = 'ughe ebfb ewky gqep';

$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());
$emails = imap_search($inbox, 'SUBJECT "New User Registration"');

if ($emails) {
    rsort($emails);
    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0);
        echo '<div class="p-4 bg-white/80 rounded-xl hover:bg-pink-100 transition shadow cursor-pointer" onclick="loadEmailContent(' . $email_number . ', \'' . $folder . '\')">';
        echo '<h4 class="font-bold text-purple-700">' . htmlspecialchars($overview[0]->from) . '</h4>';
        echo '<p class="text-sm text-gray-600">' . htmlspecialchars($overview[0]->subject) . '</p>';
        echo '</div>';
    }
} else {
    echo '<p class="text-gray-700">No emails found in this folder.</p>';
}
imap_close($inbox);
?>