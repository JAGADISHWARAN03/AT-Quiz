<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mail Center</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function loadEmailContent(emailId) {
        fetch(`view_mail.php?email_id=${emailId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('email-viewer').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('email-viewer').innerHTML = '<p class="text-red-500">Failed to load content.</p>';
            });
    }

    function replyToEmail(emailId) {
        fetch(`reply_mail.php?email_id=${emailId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('email-viewer').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('email-viewer').innerHTML = '<p class="text-red-500">Failed to load reply form.</p>';
            });
    }
  </script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

<div class="flex flex-col p-6 md:p-10 space-y-6">
  <h1 class="text-3xl font-bold text-gray-700">📨 Mail Center</h1>

  <!-- Panels -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Mail List -->
    <div class="col-span-1 bg-white rounded-lg shadow p-4 overflow-y-auto max-h-[600px]">
      <h2 class="text-lg font-semibold mb-4 text-indigo-600">Inbox</h2>
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
              echo '<div class="border border-gray-200 rounded-lg p-3 hover:bg-indigo-50 cursor-pointer mb-2" onclick="loadEmailContent(' . $email_number . ')">';
              echo '<h4 class="font-medium truncate">' . htmlspecialchars($overview[0]->from) . '</h4>';
              echo '<p class="text-sm text-gray-500 truncate">' . htmlspecialchars($overview[0]->subject) . '</p>';
              echo '</div>';
          }
      } else {
          echo '<p class="text-gray-500">No emails found.</p>';
      }
      imap_close($inbox);
      ?>
    </div>

    <!-- Email Viewer -->
    <div id="email-viewer" class="col-span-2 bg-white rounded-lg shadow p-6 overflow-y-auto max-h-[600px]">
      <h2 class="text-lg font-semibold text-indigo-600 mb-4">Open Mail</h2>
      <p class="text-gray-500">Click on a message to view its contents.</p>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
