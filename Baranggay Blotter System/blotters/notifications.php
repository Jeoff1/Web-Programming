<?php

session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}

require_once "../classes/notification.php";
$notif = new Notification();

$user_id = $_SESSION['user']['user_id'];

// Handle mark as read
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])){
    if($_POST['action'] == 'mark_as_read' && isset($_POST['notification_id'])){
        $notif->markAsRead($_POST['notification_id']);
    } elseif($_POST['action'] == 'mark_all_as_read'){
        $notif->markAllAsRead($user_id);
    }
    header("Location: notifications.php");
    exit();
}

$notifications = $notif->getNotifications($user_id, 50);
$unreadCount = $notif->getUnreadCount($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .notification-container {
            max-width: 800px;
            margin: 20px auto;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        
        .notification-item {
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
            background: white;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-item.unread {
            background: #e3f2fd;
            border-left-color: #1976d2;
        }
        
        .notification-item.case_added {
            border-left-color: #4caf50;
        }
        
        .notification-item.case_edited {
            border-left-color: #ff9800;
        }
        
        .notification-item.case_resolved {
            border-left-color: #2196f3;
        }
        
        .notification-item.case_deleted {
            border-left-color: #f44336;
        }
        
        .notification-item.pending_old {
            border-left-color: #ff5722;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-message {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .notification-time {
            font-size: 12px;
            color: #999;
        }
        
        .notification-actions {
            margin-left: 15px;
        }
        
        .notification-actions form {
            display: inline;
        }
        
        .notification-actions button {
            padding: 5px 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .notification-actions button:hover {
            background: #0056b3;
        }
        
        .mark-all-btn {
            padding: 8px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .mark-all-btn:hover {
            background: #218838;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .unread-badge {
            display: inline-block;
            background: #f44336;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <header class="header card">
            <div class="brand">
                <div class="logo">BB</div>
                <h1>Notifications</h1>
            </div>
            <div class="nav-actions">
                <a class="btn secondary" href="../index.php">Back to Dashboard</a>
            </div>
        </header>

        <main class="card container">
            <div class="notification-container">
                <div class="notification-header">
                    <div>
                        <h3>All Notifications 
                            <?php if($unreadCount > 0): ?>
                                <span class="unread-badge"><?= $unreadCount ?> unread</span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <?php if($unreadCount > 0): ?>
                        <form method="post" style="margin: 0;">
                            <input type="hidden" name="action" value="mark_all_as_read">
                            <button type="submit" class="mark-all-btn">Mark all as read</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if(empty($notifications)): ?>
                    <div class="empty-state">
                        <p>No notifications yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach($notifications as $notif_item): ?>
                        <div class="notification-item <?= $notif_item['is_read'] == 0 ? 'unread' : '' ?> <?= htmlspecialchars($notif_item['type']) ?>">
                            <div class="notification-content">
                                <div class="notification-message"><?= htmlspecialchars($notif_item['message']) ?></div>
                                <div class="notification-time"><?= date('M d, Y h:i A', strtotime($notif_item['created_at'])) ?></div>
                            </div>
                            <?php if($notif_item['is_read'] == 0): ?>
                                <div class="notification-actions">
                                    <form method="post">
                                        <input type="hidden" name="action" value="mark_as_read">
                                        <input type="hidden" name="notification_id" value="<?= $notif_item['id'] ?>">
                                        <button type="submit">Mark as read</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
