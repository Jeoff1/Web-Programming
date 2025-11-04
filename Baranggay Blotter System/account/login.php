<?php
    //resume session here to fetch session values
    session_start();

    if (isset($_SESSION['user']) && ($_SESSION['user']['role'] == 'Staff' || $_SESSION['user']['role'] == 'Admin')){
        header('location: ../index.php');
    }

    //if the login button is clicked
    require_once('../classes/account.php');
    $account = new Account();
    $error = "";
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $account->username = htmlentities($_POST['username']);
        $account->password = htmlentities($_POST['password']);
        if ($account->login()){
            $sql = "SELECT user_id, username, role FROM user WHERE username = :username LIMIT 1";
            $stmt = $account->connect()->prepare($sql);
            $stmt->bindParam(":username", $account->username);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // ✅ Store user info in session
            $_SESSION["user"] = [
            "user_id" => $userData["user_id"],
            "username" => $userData["username"],
            "role" => $userData["role"]
        ];

            
            $_SESSION["user"] = $account->getStaffByUsername();
            // for now will send user to view product
            header('location: ../index.php');
        }else{
            $error =  'Invalid username/password. Try again.';
        }
    }
    
    //if the above code is false then html below will be displayed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        label{ display: block; }
        span{ color: red; }
        p.error{ color: red; margin: 0; }
    </style>
</head>
<body>
    <h1>Admin Login</h1>
    <form method="post" action="">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php if(isset($_POST['username'])) { echo $_POST['username']; } ?>">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" value="<?php if(isset($_POST['password'])) { echo $_POST['password']; } ?>">
        <br>
        <p class="error"><?= $error ?></p>
        <br>
        <button type="submit" name="login">Login</button>
</body>
</html>