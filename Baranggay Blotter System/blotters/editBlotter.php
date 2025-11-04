<?php

session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}
require_once "../classes/blotter.php";
$blotterObj = new Blotter();

// load categories for select
$categories = $blotterObj->getCategories();

$blotter = [];
$errors = [];

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["blotter_id"])){
        $pid = trim(htmlspecialchars($_GET["blotter_id"]));
        $blotter = $blotterObj->fetchBlotter($pid);
        if(!$blotter){
            echo "<a href='viewBlotter.php'>View Blotter Records</a>";
            exit("Blotter Record Not Found");
        }
    }else{
        echo "<a href='viewBlotter.php'>View Blotter Records</a>";
        exit("Blotter Record not Found");
    }
}
elseif($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pid = trim(htmlspecialchars($_POST["id"]));
    $blotter["category"] = trim(htmlspecialchars($_POST["category"]));
    $blotter["complainant_name"] = trim(htmlspecialchars($_POST["complainant_name"]));
    $blotter["respondent_name"] = trim(htmlspecialchars($_POST["respondent_name"]));
    $blotter["date"] = trim(htmlspecialchars($_POST["date"]));
    $blotter["incident_time"] = trim(htmlspecialchars($_POST["incident_time"]));
    $blotter["location"] = trim(htmlspecialchars($_POST["location"]));
    $blotter["description"] = trim(htmlspecialchars($_POST["description"]));
    $blotter["status"] = trim(htmlspecialchars($_POST["status"]));

    if(empty($blotter["category"])) {
        $errors["category"] = "Incident Type is required.";
    }

    if(empty($blotter["complainant_name"])) {
        $errors["complainant_name"] = "Complainant Name is required.";
    }


    if(empty($blotter["date"])) {
        $errors["date"] = "Incident Date is required.";
    } elseif($blotter["date"] > date("Y-m-d")) {
        $errors["date"] = "Incident date must not be in the Future.";
    }

    if(empty($blotter["incident_time"])) {
        $errors["incident_time"] = "Incident Time is required.";
    }

    if(empty($blotter["location"])) {
        $errors["location"] = "Location is required.";
    }

    if(empty($blotter["description"])) {
        $errors["description"] = "Description is required.";
    }

    if(empty($blotter["status"])) {
        $errors["status"] = "Status is required.";
    }

    if($blotterObj->isBlotterExist($blotter["date"], $blotter["incident_time"], $blotter["location"], $pid)){
        $errors["general"] = "A blotter record already exists for this date, time, and location.";
    }

    if(empty(array_filter($errors))){
        // category holds the category id
        $blotterObj->category_id = $blotter["category"];
        $blotterObj->complainant_name = $blotter["complainant_name"];
        $blotterObj->respondent_name = $blotter["respondent_name"];
        $blotterObj->date = $blotter["date"];
        $blotterObj->incident_time = $blotter["incident_time"];
        $blotterObj->location = $blotter["location"];
        $blotterObj->description = $blotter["description"];
        $blotterObj->status = $blotter["status"];
        
        if($blotterObj->editBlotter($pid)){
           header("Location: viewBlotter.php");
        } else {
            echo "failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blotter Record</title>
    <link rel="stylesheet" href="style.css">
    <style>
    label {display:block; }
    span, .error {color: red; margin: 0;}
    textarea {width: 100%; min-height: 100px; font-family: Arial, sans-serif;}
    </style>
</head>
<body>
    <h1>Edit Blotter Record</h1>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= $blotter["blotter_id"] ?? "" ?>">
        <label for="">Field with <span>*</span> is required</label>
        <br>

        <?php if(isset($errors["general"])): ?>
            <p class="error"><?= $errors["general"] ?></p>
        <?php endif; ?>
<div class= "container">
        <label for="category">Incident Type <span>*</span></label>
        <select name="category" id="category" class="pangalan">
            <option value="">Select Incident Type</option>
            <?php
                $selectedCat = $blotter['category'] ?? $blotter['category_id'] ?? '';
                foreach($categories as $cat):
            ?>
                <option value="<?= $cat['id'] ?>" <?= ($selectedCat == $cat['id'])? 'selected': '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <p class="error"><?= $errors["category"] ?? ""?></p>
        <br>

        <label for="complainant_name">Complainant Name <span>*</span></label>
        <input type="text" name="complainant_name" id="complainant_name" class="pangalan" value="<?= $blotter["complainant_name"] ?>">
        <p class="error"><?= $errors["complainant_name"] ?? ""?></p>
        <br>

        <label for="respondent_name">Respondent Name</label>
        <input type="text" name="respondent_name" id="respondent_name" value="<?= $blotter["respondent_name"] ?>">
        <br>

        <label for="date">Incident Date <span>*</span></label> 
        <input type="date" name="date" id="date" class="pangalan" value="<?= $blotter["date"] ?>">
        <p class="error"><?= $errors["date"] ?? ""?></p>
        <br>

        <label for="incident_time">Incident Time <span>*</span></label> 
        <input type="time" name="incident_time" id="incident_time" class="pangalan" value="<?= $blotter["incident_time"] ?>">
        <p class="error"><?= $errors["incident_time"] ?? ""?></p>
        <br>

        <label for="location">Location <span>*</span></label> 
        <input type="text" name="location" id="location" class="pangalan" value="<?= $blotter["location"] ?>">
        <p class="error"><?= $errors["location"] ?? ""?></p>
        <br>

        <label for="description">Description <span>*</span></label>
        <textarea name="description" id="description" class="pangalan"><?= $blotter["description"] ?></textarea>
        <p class="error"><?= $errors["description"] ?? ""?></p>
        <br>

        <label for="status">Status <span>*</span></label>
        <select name="status" id="status" class="pangalan">
            <option value="">Select Status</option>
            <option value="Active" <?= (isset($blotter["status"]) && $blotter["status"] == "Active")? "selected":"" ?>>Active</option>
            <option value="Under Investigation" <?= (isset($blotter["status"]) && $blotter["status"] == "Under Investigation")? "selected":"" ?>>Under Investigation</option>
            <option value="Settled" <?= (isset($blotter["status"]) && $blotter["status"] == "Settled")? "selected":"" ?>>Settled</option>
            <option value="Referred to Police" <?= (isset($blotter["status"]) && $blotter["status"] == "Referred to Police")? "selected":"" ?>>Referred to Police</option>
            <option value="Closed" <?= (isset($blotter["status"]) && $blotter["status"] == "Closed")? "selected":"" ?>>Closed</option>
        </select>
        <p class="error"><?= $errors["status"] ?? ""?></p>
        <br>

        <input type="submit" class="view-books" value="Update Record" class = "btn-primary"><span><button type="button"><a href="viewBlotter.php">View Blotter Records</a></button></span>
    </form>
        </div>
</body>
</html>