<?php
    //resume session here to fetch session values
    session_start();

require_once "./classes/blotter.php";
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
    <title>Welcome Staff</title>
    <link rel="stylesheet" href="blotters/style.css">
</head>
<body>
    <h1>Baranggay Blotter Dashboard</h1>

    <button type="button"><a href="./blotters/viewBlotter.php">View Details And Edit/Delete</a></button>

    <button type="button"><a href="./blotters/addBlotter.php">Add a Blotter</a></button>

    <button type="button"><a href="./admin/addStaff.php">Add a Staff Member</a></button>

    <button type="button"><a href="./account/login.php">Login</a></button>

    <button type="button"><a href="./account/logout.php">Logout</a></button>

    <form action="" method="get">
        
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

<table border="1">
    <div class="table-wrapper">
        <tr>
            <td>No.</td>
            <td>Incident Type</td>
            <td>Description</td>
            <td>Status</td>
        </tr>

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

    </table>
    </div>
</body>
