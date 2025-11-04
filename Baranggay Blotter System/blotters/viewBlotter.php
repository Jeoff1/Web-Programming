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
    <h1>Barangay Blotter Records</h1>

    <form action="" method="get">
        <label for="">Search:</label>
        <input type="search" name="search" id="search" placeholder="Complainant, or Location" value="<?= $search ?>">
        
        <select name="category" id="category">
            <option value="">All Types</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($category) && $category == $cat['id'])? "selected":"" ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status" id="status">
            <option value="">All Status</option>
            <option value="Active" <?= (isset($status) && $status == "Active")? "selected":"" ?>>Active</option>

            <option value="Under Investigation" <?= (isset($status) && $status == "Under Investigation")? "selected":"" ?>>Under Investigation</option>

            <option value="Settled" <?= (isset($status) && $status == "Settled")? "selected":"" ?>>Settled</option>

            <option value="Referred to Police" <?= (isset($status) && $status == "Referred to Police")? "selected":"" ?>>Referred to Police</option>

            <option value="Closed" <?= (isset($status) && $status == "Closed")? "selected":"" ?>>Closed</option>
        </select>
        
        <input type="submit" value="Search">
    </form>
    <button><a href="addBlotter.php">Add Blotter Record</a></button><span><button type="button"><a href="../index.php">Back</a></button></span>

    <table border="1">
        <div class="table-wrapper">
        <tr>
            <td>No.</td>
            <td>Incident Type</td>
            <td>Complainant</td>
            <td>Respondent</td>
            <td>Date</td>
            <td>Time</td>
            <td>Location</td>
            <td>Description</td>
            <td>Status</td>
            <td>ID of Official Attended</td>
            <td>Action</td>
        </tr>

        <?php
        $no = 1;
        foreach($blotterObj->viewBlotters($search, $category, $status) as $blotter) {
            $message = "Are you sure you want to delete this blotter record involving ". $blotter["complainant_name"] . "?";
        ?>

        <tr>
            <td> <?= $no++ ?> </td>
            <td> <?= htmlspecialchars($blotter["category_name"] ?? $blotter["category"]) ?> </td>
            <td> <?= $blotter["complainant_name"] ?> </td>
            <td> <?= $blotter["respondent_name"] ?> </td>
            <td> <?= date('M d, Y', strtotime($blotter["date"])) ?> </td>
            <td> <?= date('h:i A', strtotime($blotter["incident_time"])) ?> </td>
            <td> <?= $blotter["location"] ?> </td>
            <td> <?= substr($blotter["description"], 0, 50) . (strlen($blotter["description"]) > 50 ? "..." : "") ?> </td>
            <td> <?= $blotter["status"] ?> </td>
            <td> <?= $blotter["admin_id"] ?> </td>
            <td>
                <a href="editBlotter.php?blotter_id=<?= $blotter["blotter_id"] ?>">EDIT</a>
                <a href="deleteBlotter.php?blotter_id=<?= $blotter["blotter_id"] ?>" onclick="return confirm('<?= $message ?>')">DELETE</a>
            </td>
        </tr>

        <?php
        }
        ?>
    </table>
    </div>
</body>
</html>