<?php
session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}

require_once "../classes/staff.php";
$adminObj = new Staff();

$admin = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $admin["firstname"] = trim(htmlspecialchars($_POST["firstname"]));
    $admin["lastname"] = trim(htmlspecialchars($_POST["lastname"]));
    $admin["role"] = trim(htmlspecialchars($_POST["role"]));
    $admin["username"] = trim(htmlspecialchars($_POST["username"]));
    $admin["password"] = trim(htmlspecialchars($_POST["password"]));
    $admin["is_active"] = isset($_POST["is_active"]) ? trim(htmlspecialchars($_POST["is_active"])) : '';

    if (empty($admin["firstname"])) {
        $errors["firstname"] = "First name is required.";
    }

    if (empty($admin["lastname"])) {
        $errors["lastname"] = "Last name is required.";
    }

    if (empty($admin["role"])) {
        $errors["role"] = "Role is required.";
    }

    if (empty($admin["username"])) {
        $errors["username"] = "username is required.";      
    }

    if (empty($admin["password"])) {
        $errors["password"] = "Password is required.";      
    }

        if (empty($admin["is_active"])) {
        $errors["is_active"] = "This is required.";      
    }

    if (empty(array_filter($errors))) {
        $adminObj->firstname = $admin["firstname"];
        $adminObj->lastname = $admin["lastname"];
        $adminObj->role = $admin["role"];
        $adminObj->username = $admin["username"];
        $adminObj->password = $admin["password"];
        $adminObj->is_active = $admin["is_active"];

        if ($adminObj->addStaff()) {
            echo "Admin added successfully.";
        } else {
            echo "Failed to add admin. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <style>
    label {display:block;}
    span, .error {color: red; margin: 0;}
    </style>
</head>
<body>
    <h1>Add Admin</h1>

    <form action="" method="POST">
        <label for=""><h6>Field with <span>*</span> is required</h6></label><br>

        <label for="firstname">First Name <span>*</span></label>
            <input type="text" name="firstname" id="firstname" value="<?=$admin["firstname"] ?? "" ?>">
            <p class="error"><?=$errors["firstname"] ?? "" ?></p>
        
        <label for="lastname">Last Name<span>*</span></label>
            <input type="text" name="lastname" id="lastname" value="<?=$admin["lastname"] ?? "" ?>">
            <p class="error"><?=$errors["lastname"] ?? "" ?></p>

        <label for="username">Username<span>*</span></label>
            <input type="text" name="username" id="username" value="<?=$admin["username"] ?? "" ?>">
            <p class="error"><?=$errors["username"] ?? "" ?></p>

        <label for="password">Password<span>*</span></label>
            <input type="text" name="password" id="password" value="<?=$admin["password"] ?? "" ?>">
            <p class="error"><?=$errors["password"] ?? "" ?></p> <br><br>

        <label for="role">Role <span>*</span></label>
            <input type="radio" name="role" value="Admin" <?php if (isset($admin["role"]) && $admin["role"] == "Admin") echo "checked"; ?>> Admin
            <input type="radio" name="role" value="Staff" <?php if (isset($admin["role"]) && $admin["role"] == "Staff") echo "checked"; ?>> Staff
            <p class="error"><?=$errors["role"] ?? "" ?></p><br>
        
        <label for="is_active">Is Active<span>*</span></label>
        <input type="radio" name="is_active" value="1" <?php if (isset($admin["is_active"]) && $admin["is_active"] == "1") echo "checked"; ?>> Yes
        <input type="radio" name="is_active" value="0" <?php if (isset($admin["is_active"]) && $admin["is_active"] == "0") echo "checked"; ?>> No  
        <p class="error"><?=$errors["is_active"] ?? "" ?></p> <br><br>

        <input type="submit" value="Save Admin"><span><button type="button"><a href="../account/login.php">Log In</a></button></span><span><button type="button"><a href="../index.php">Back</a></button></span>

    </form>
</body>
</html>