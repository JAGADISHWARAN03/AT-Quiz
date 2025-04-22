<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mail Center</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .glass {
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
  </style>
  <script>
    // JavaScript to dynamically load email content
    function loadEmailContent(emailId) {
        fetch(`view_mail.php?email_id=${emailId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('email-viewer').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching email content:', error);
                document.getElementById('email-viewer').innerHTML = '<p class="text-red-500">Failed to load email content.</p>';
            });
    }

    // JavaScript to load the reply form
    function replyToEmail(emailId) {
        fetch(`reply_mail.php?email_id=${emailId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('email-viewer').innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading reply form:', error);
                document.getElementById('email-viewer').innerHTML = '<p class="text-red-500">Failed to load reply form.</p>';
            });
    }
  </script>
</head>
<body class="bg-gradient-to-tr from-purple-300 via-pink-200 to-yellow-100 min-h-screen font-sans">

<div class="container mx-auto py-10 px-6">
  <div class="flex gap-6 flex-wrap md:flex-nowrap">

    <!-- Sidebar -->
    <div class="w-full md:w-1/4 glass p-6 rounded-2xl shadow-xl">
      <h2 class="text-3xl font-bold text-pink-600 mb-6">📬 Mailbox</h2>
      <ul class="space-y-4 text-lg text-gray-800">
        <li class="hover:text-pink-500 cursor-pointer transition">📥 Inbox</li>
        <li class="hover:text-pink-500 cursor-pointer transition">✉️ Sent</li>
        <li class="hover:text-pink-500 cursor-pointer transition">📝 Drafts</li>
        <li class="hover:text-pink-500 cursor-pointer transition">🗑️ Trash</li>
      </ul>
    </div>

    <!-- Main Panel -->
    <div class="flex-1 glass rounded-2xl shadow-xl p-6 space-y-6">
      
      <!-- Toolbar -->
      <div class="flex justify-between items-center">
        <input type="text" placeholder="🔍 Search your vibe..." class="w-1/3 px-4 py-2 rounded-xl border border-purple-300 focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white/70 backdrop-blur-sm shadow-inner">
        <button class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-full transition">🔄 Refresh</button>
      </div>

      <div class="flex flex-col md:flex-row gap-4 h-[70vh]">
        
        <!-- Mail List -->
        <div class="w-full md:w-1/3 overflow-y-auto bg-white/40 rounded-xl p-4 space-y-4 shadow-inner">
          <?php
          $hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
          $username = 'jagadishbit0@gmail.com';
          $password = 'ughe ebfb ewky gqep';

          $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP: ' . imap_last_error());
          $emails = imap_search($inbox, 'SUBJECT "New User Registration"');

          if ($emails) {
              rsort($emails);
              foreach ($emails as $email_number) {
                  $overview = imap_fetch_overview($inbox, $email_number, 0);
                  echo '<div class="p-4 bg-white/80 rounded-xl hover:bg-pink-100 transition shadow cursor-pointer" onclick="loadEmailContent(' . $email_number . ')">';
                  echo '<h4 class="font-bold text-purple-700">' . htmlspecialchars($overview[0]->from) . '</h4>';
                  echo '<p class="text-sm text-gray-600">' . htmlspecialchars($overview[0]->subject) . '</p>';
                  echo '</div>';
              }
          } else {
              echo '<p class="text-gray-700">No emails found.</p>';
          }
          imap_close($inbox);
          ?>
        </div>

        <!-- Mail Viewer -->
        <div id="email-viewer" class="flex-1 bg-white/60 rounded-xl p-6 shadow-inner">
          <h2 class="text-2xl font-extrabold text-purple-700 mb-4">💌 Open Mail</h2>
          <p class="text-sm text-gray-500 mb-2">From: <span class="italic text-gray-700">-</span></p>
          <div class="text-gray-800">
            <p>No message selected yet... click one from the list! 🎉</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
