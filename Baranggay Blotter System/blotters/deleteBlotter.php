<?php 

session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}
require_once "../classes/blotter.php";
$blotterObj = new Blotter();

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["blotter_id"])){
        $pid = trim(htmlspecialchars($_GET["blotter_id"]));
        $blotter = $blotterObj->fetchBlotter($pid);
        if(!$blotter){
            echo "<a href='viewBlotter.php'>View Blotter Records</a>";
            exit("Blotter Record Not Found");
        }else{
            $blotterObj->deleteBlotter($pid);
            
            // Notification: Case Deleted
            require_once __DIR__ . '/notify_util.php';
            notify_case_deleted($pid, $_SESSION['user']['user_id']);
            
            header("Location: viewBlotter.php");
        }
    }else{
        echo "<a href='viewBlotter.php'>View Blotter Records</a>";
        exit("Blotter Record not Found");
    }
}
?>