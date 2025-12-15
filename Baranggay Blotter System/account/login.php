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

        //Store user info in session
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
    <title>Login - Barangay Blotter</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --muted: #6b7280;
            --accent: #1e90ff;
            --accent-600: #1977d6;
            --danger: #ef4444;
            --radius: 8px;
            --shadow: 0 6px 18px rgba(14,30,37,0.08);
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        
        body {
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: linear-gradient(180deg, var(--bg), #eef3fb 60%);
            color: #111827;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
            text-align: center;
            flex-direction: column;
        }

        .logo {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 24px;
        }

        .login-header h1 {
            font-size: 24px;
            margin: 0;
            color: #111827;
        }

        .login-header p {
            font-size: 14px;
            color: var(--muted);
            margin: 4px 0 0 0;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: var(--muted);
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6e9ef;
            border-radius: 8px;
            background: #fbfdff;
            color: #111827;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent);
            background: #fff;
        }

        p.error {
            color: var(--danger);
            font-size: 13px;
            margin: 8px 0 16px 0;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: var(--accent);
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        button[type="submit"]:hover {
            background: var(--accent-600);
        }

        button[type="submit"]:active {
            opacity: 0.9;
        }

        .footer {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">BB</div>
            <div>
                <h1>Barangay Blotter</h1>
                <p>Management System</p>
            </div>
        </div>

        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php if(isset($_POST['username'])) { echo htmlspecialchars($_POST['username']); } ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" value="<?php if(isset($_POST['password'])) { echo htmlspecialchars($_POST['password']); } ?>" required>
            </div>

            <?php if(!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <button type="submit" name="login">Login</button>

            <div class="footer">
                <p>&copy; 2025 Barangay Office. All rights reserved.</p>
            </div>
        </form>
    </div>
</body>
</html>