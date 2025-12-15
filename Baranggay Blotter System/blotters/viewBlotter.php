<?php

session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}
require_once "../classes/blotter.php";
$blotterObj = new Blotter();

// load categories for filter
$categories = $blotterObj->getCategories();

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
    <title>View Blotter Records</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-shell">

        <header class="header card">
            <div class="brand">
                <div class="logo">BB</div>
                <h1>Barangay Blotter Records</h1>
            </div>
            <div class="nav-actions">
                <a class="btn" href="addBlotter.php">Add Blotter</a>
                <a class="btn secondary" href="../index.php">Back</a>
            </div>
        </header>

        <main class="card container">
            <div class="controls">
                <form action="" method="get" class="controls" style="width:100%">
                    <div class="search" style="min-width:220px;">
                        <input type="search" name="search" id="search" placeholder="Complainant, or Location" value="<?= $search ?>">
                    </div>
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
                            <option value="Pending" <?= (isset($status) && $status == "Pending")? "selected":"" ?>>Pending</option>
                            <option value="Resolved" <?= (isset($status) && $status == "Resolved")? "selected":"" ?>>Resolved</option>
                            <option value="Active" <?= (isset($status) && $status == "Active")? "selected":"" ?>>Active</option>
                            <option value="Under Investigation" <?= (isset($status) && $status == "Under Investigation")? "selected":"" ?>>Under Investigation</option>
                            <option value="Settled" <?= (isset($status) && $status == "Settled")? "selected":"" ?>>Settled</option>
                            <option value="Referred to Police" <?= (isset($status) && $status == "Referred to Police")? "selected":"" ?>>Referred to Police</option>
                            <option value="Closed" <?= (isset($status) && $status == "Closed")? "selected":"" ?>>Closed</option>
                        </select>
                    </div>
                    <div style="min-width:120px;"><input class="btn" type="submit" value="Search"></div>
                </form>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Incident Type</th>
                            <th>Complainant</th>
                            <th>Complainant Email</th>
                            <th>Respondent</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Photo</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>ID of Official Attended</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                $no = 1;
                foreach($blotterObj->viewBlotters($search, $category, $status) as $blotter) {
                        $message = "Are you sure you want to delete this blotter record involving ". $blotter["complainant_name"] . "?";
                ?>

                <tr>
                        <td> <?= $no++ ?> </td>
                        <td> <?= htmlspecialchars($blotter["category_name"] ?? $blotter["category"]) ?> </td>
                        <td> <?= $blotter["complainant_name"] ?> </td>
                        <td> <?= !empty($blotter["complainant_email"]) ? htmlspecialchars($blotter["complainant_email"]) : "-" ?> </td>
                        <td> <?= $blotter["respondent_name"] ?> </td>
                        <td> <?= date('M d, Y', strtotime($blotter["incident_date"])) ?> </td>
                        <td> <?= date('h:i A', strtotime($blotter["incident_time"])) ?> </td>
            <td>
                <?php if(!empty($blotter['photo'])): ?>
                    <?php $imgUrl = '../uploads/' . htmlspecialchars($blotter['photo']); ?>
                    <img src="<?= $imgUrl ?>" alt="photo" class="thumbnail" />
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
                        <td> <?= $blotter["location"] ?> </td>
                        <td> <?= substr($blotter["description"], 0, 50) . (strlen($blotter["description"]) > 50 ? "..." : "") ?> </td>
                        <td> <?= $blotter["status"] ?> </td>
                        <td> <?= $blotter["admin_id"] ?> </td>
                        <td class="actions-cell">
                            <a href="editBlotter.php?blotter_id=<?= $blotter["blotter_id"] ?>">EDIT</a>
                            <a href="deleteBlotter.php?blotter_id=<?= $blotter["blotter_id"] ?>" onclick="return confirm('<?= $message ?>')">DELETE</a>
                            <a href="printReceipt.php?blotter_id=<?= $blotter["blotter_id"] ?>" target="_blank">PRINT</a>
                        </td>
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
</html>