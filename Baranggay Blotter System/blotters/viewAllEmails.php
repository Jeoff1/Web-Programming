<?php

session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}

require_once "../classes/blotter.php";
require_once "notify_util.php";

$emails_dir = __DIR__ . '/../logs/emails';
$all_emails = [];

// Get all saved emails
if(is_dir($emails_dir)){
    $files = array_reverse(glob($emails_dir . '/*.eml'));
    foreach($files as $file){
        $all_emails[] = [
            'filename' => basename($file),
            'path' => $file,
            'timestamp' => filectime($file),
            'size' => filesize($file)
        ];
    }
}

// Sort by timestamp (newest first)
usort($all_emails, function($a, $b){
    return $b['timestamp'] - $a['timestamp'];
});

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Emails - Email System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .email-list { display: flex; flex-direction: column; gap: 10px; }
        .email-card { background: white; border: 1px solid #ddd; border-radius: 5px; padding: 15px; cursor: pointer; transition: all 0.3s; }
        .email-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-color: #1e90ff; }
        .email-card.unread { border-left: 4px solid #ff9800; background: #fffbf0; }
        .email-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .email-to { font-weight: bold; color: #333; }
        .email-subject { color: #666; font-size: 14px; margin: 5px 0; }
        .email-time { font-size: 12px; color: #999; }
        .email-body { display: none; background: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 10px; border: 1px solid #eee; max-height: 400px; overflow-y: auto; font-size: 13px; line-height: 1.6; }
        .email-body.show { display: block; }
        .email-body pre { white-space: pre-wrap; word-break: break-all; font-family: monospace; }
        .empty-state { text-align: center; padding: 40px 20px; color: #999; }
        .stats { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .filter-section { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .filter-section input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; flex: 1; min-width: 200px; }
        .filter-section button { padding: 8px 16px; background: #1e90ff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .filter-section button:hover { background: #1873cc; }
        .email-actions { display: flex; gap: 10px; }
        .btn-small { padding: 5px 10px; font-size: 12px; background: #1e90ff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .btn-small:hover { background: #1873cc; }
        .btn-delete { background: #f44336; }
        .btn-delete:hover { background: #da190b; }
    </style>
</head>
<body>
    <div class="app-shell">
        <header class="header card">
            <div class="brand">
                <div class="logo">BB</div>
                <h1>All Emails</h1>
            </div>
            <div class="nav-actions">
                <a class="btn secondary" href="viewBlotter.php">Back to Blotters</a>
                <a class="btn secondary" href="email_debug.php">Debug Page</a>
            </div>
        </header>

        <main class="card container">
            <div class="stats">
                <strong>Total Emails Sent:</strong> <?= count($all_emails) ?>
                <?php if(!empty($all_emails)): ?>
                    | <strong>Latest:</strong> <?= date('Y-m-d H:i:s', $all_emails[0]['timestamp']) ?>
                    | <strong>Oldest:</strong> <?= date('Y-m-d H:i:s', $all_emails[count($all_emails)-1]['timestamp']) ?>
                <?php endif; ?>
            </div>

            <div class="filter-section">
                <input type="text" id="search_filter" placeholder="Search by email, subject, or filename..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 100%;">
                <button onclick="clearSearch()" class="btn">Clear Search</button>
            </div>

            <?php if(empty($all_emails)): ?>
                <div class="empty-state">
                    <p>📭 No emails have been sent yet</p>
                    <p style="font-size: 14px; margin-top: 10px;">
                        Go to <a href="email_debug.php">Email Debug Page</a> and send a test email to see it here.
                    </p>
                </div>
            <?php else: ?>
                <div class="email-list" id="email-list">
                    <?php foreach($all_emails as $index => $email): ?>
                        <?php $content = file_get_contents($email['path']); ?>
                        <?php
                        preg_match('/^TO:\s*(.+)$/m', $content, $to_match);
                        preg_match('/^SUBJECT:\s*(.+)$/m', $content, $subj_match);
                        $to = trim($to_match[1] ?? 'Unknown');
                        $subject = trim($subj_match[1] ?? 'No Subject');
                        ?>
                        <div class="email-card" data-email="<?= htmlspecialchars($to) ?>" data-subject="<?= htmlspecialchars($subject) ?>" data-filename="<?= htmlspecialchars($email['filename']) ?>" onclick="toggleEmail(this)">
                            <div class="email-header">
                                <div>
                                    <div class="email-to">📧 <?= htmlspecialchars($to) ?></div>
                                    <div class="email-subject">Subject: <?= htmlspecialchars($subject) ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <div class="email-time"><?= date('Y-m-d H:i:s', $email['timestamp']) ?></div>
                                    <div style="font-size: 12px; color: #999; margin-top: 5px;"><?= round($email['size'] / 1024, 2) ?> KB</div>
                                </div>
                            </div>
                            <div class="email-body">
                                <pre><?= htmlspecialchars($content) ?></pre>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function toggleEmail(element) {
            const body = element.querySelector('.email-body');
            body.classList.toggle('show');
        }

        function clearSearch() {
            document.getElementById('search_filter').value = '';
            filterEmails();
        }

        document.getElementById('search_filter').addEventListener('keyup', filterEmails);

        function filterEmails() {
            const searchTerm = document.getElementById('search_filter').value.toLowerCase();
            const emails = document.querySelectorAll('.email-card');
            let visibleCount = 0;

            emails.forEach(email => {
                const emailTo = email.dataset.email.toLowerCase();
                const subject = email.dataset.subject.toLowerCase();
                const filename = email.dataset.filename.toLowerCase();

                if (emailTo.includes(searchTerm) || subject.includes(searchTerm) || filename.includes(searchTerm)) {
                    email.style.display = 'block';
                    visibleCount++;
                } else {
                    email.style.display = 'none';
                }
            });

            // Show message if no results
            const list = document.getElementById('email-list');
            let noResults = list.querySelector('.no-results');
            if (!noResults && visibleCount === 0) {
                noResults = document.createElement('div');
                noResults.className = 'no-results';
                noResults.style.cssText = 'text-align: center; padding: 20px; color: #999;';
                noResults.innerHTML = '<p>❌ No emails match your search</p>';
                list.appendChild(noResults);
            } else if (noResults && visibleCount > 0) {
                noResults.remove();
            }
        }
    </script>
</body>
</html>
