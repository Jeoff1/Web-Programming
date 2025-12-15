<?php
    //resume session here to fetch session values
    session_start();

require_once "./classes/blotter.php";
require_once "./classes/notification.php";

$blotterObj = new Blotter();
$notif = new Notification();

// Get unread notification count if user is logged in
$unreadNotifications = 0;
if(isset($_SESSION['user']) && isset($_SESSION['user']['user_id'])){
    $unreadNotifications = $notif->getUnreadCount($_SESSION['user']['user_id']);
}

// load categories for filter
$categories = $blotterObj->getCategories();

// Get KPI data
$totalCases = $blotterObj->getTotalCases();
$casesPerCategory = $blotterObj->getCasesPerCategory();
$statusData = $blotterObj->getResolvedVsPending();
$monthlyCases = $blotterObj->getMonthlyCasesCount();

// Calculate resolved and pending
$resolved = 0;
$pending = 0;
foreach($statusData as $s){
    if(in_array(strtolower($s['status']), ['resolved', 'settled', 'closed', 'referred to police'])){
        $resolved += $s['count'];
    } else if(strtolower($s['status']) === 'pending'){
        $pending += $s['count'];
    }
}

$search = $category = $status = "";

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $search = isset($_GET["search"]) ? trim(htmlspecialchars($_GET["search"])): "";
    $category = isset($_GET["category"]) ? trim(htmlspecialchars($_GET["category"])): "";
    $status = isset($_GET["status"]) ? trim(htmlspecialchars($_GET["status"])): "";
}
?>

<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Staff</title>
    <link rel="stylesheet" href="blotters/style.css">
</head>
<body>
<div class="app-shell">

    <header class="header card">
        <div class="brand">
            <div class="logo">BB</div>
            <h1>Barangay Blotter Dashboard</h1>
        </div>
        <div class="nav-actions">
            <a class="btn" href="./blotters/viewBlotter.php">View Records</a>
            <a class="btn" href="./blotters/addBlotter.php">Add Blotter</a>
            <?php if($unreadNotifications > 0): ?>
                <a class="btn" href="./blotters/notifications.php" style="background: #f44336;">
                    🔔 Notifications (<?= $unreadNotifications ?>)
                </a>
            <?php else: ?>
                <a class="btn secondary" href="./blotters/notifications.php">🔔 Notifications</a>
            <?php endif; ?>
            <a class="btn secondary" href="./admin/addStaff.php">Add Staff</a>
            <a class="btn secondary" href="./account/login.php">Login</a>
            <a class="btn secondary" href="./account/logout.php">Logout</a>
        </div>
    </header>

    <main class="card container">
        <!-- KPI Dashboard Section -->
        <div style="margin-bottom: 32px;">
            <h2 style="color: #111827; margin-bottom: 16px;">Dashboard</h2>
            <div class="kpi-grid">
                <!-- Total Cases -->
                <div class="kpi-card">
                    <div class="kpi-label">Total Blotter Cases</div>
                    <div class="kpi-value"><?= $totalCases ?></div>
                </div>

                <!-- Resolved Cases -->
                <div class="kpi-card">
                    <div class="kpi-label">Resolved Cases</div>
                    <div class="kpi-value" style="color: #10b981;"><?= $resolved ?></div>
                </div>

                <!-- Pending Cases -->
                <div class="kpi-card">
                    <div class="kpi-label">Pending Cases</div>
                    <div class="kpi-value" style="color: #f59e0b;"><?= $pending ?></div>
                </div>

                <!-- Cases This Month -->
                <div class="kpi-card">
                    <div class="kpi-label">Cases This Month</div>
                    <div class="kpi-value"><?= $monthlyCases[0]['count'] ?? 0 ?></div>
                </div>
            </div>

            <!-- Cases per Category -->
            <div style="margin-top: 24px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                <h3 style="color: #111827; margin-top: 0;">Cases per Category</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <?php foreach($casesPerCategory as $cat): ?>
                        <div style="padding: 12px; background: white; border-radius: 6px; border-left: 4px solid #1e90ff;">
                            <div style="font-size: 13px; color: #6b7280;"><?= htmlspecialchars($cat['name'] ?? 'Uncategorized') ?></div>
                            <div style="font-size: 20px; font-weight: 700; color: #1e90ff;"><?= $cat['count'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <hr style="margin: 24px 0; border: none; border-top: 1px solid #e6eefc;">

        <div class="controls">
            <form action="" method="get" class="controls" style="width:100%">
                <div style="min-width:200px;">
                    <select name="category" id="category">
                        <option value="">All Types</option>
                        <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($category) && $category == $cat['id'])? "selected":"" ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="min-width:180px;">
                    <select name="status" id="status">
                            <option value="">All Status</option>
                            <option value="Active" <?= (isset($status) && $status == "Active")? "selected":"" ?>>Active</option>
                            <option value="Under Investigation" <?= (isset($status) && $status == "Under Investigation")? "selected":"" ?>>Under Investigation</option>
                            <option value="Settled" <?= (isset($status) && $status == "Settled")? "selected":"" ?>>Settled</option>
                            <option value="Referred to Police" <?= (isset($status) && $status == "Referred to Police")? "selected":"" ?>>Referred to Police</option>
                            <option value="Closed" <?= (isset($status) && $status == "Closed")? "selected":"" ?>>Closed</option>
                    </select>
                </div>

                <div style="min-width:120px;">
                    <input class="btn" type="submit" value="Search">
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Incident Type</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach($blotterObj->viewBlotters($search, $category, $status) as $blotter) {
                ?>
                <tr>
                        <td> <?= $no++ ?> </td>
                        <td> <?= htmlspecialchars($blotter["category_name"] ?? $blotter["category"] ?? "") ?> </td>
                        <td> <?= substr($blotter["description"], 0, 50) . (strlen($blotter["description"]) > 50 ? "..." : "") ?> </td>
                        <td> <?= $blotter["status"] ?> </td>
                </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">&copy; <?= date('Y') ?> Barangay Blotter</footer>
</div>
</body>
