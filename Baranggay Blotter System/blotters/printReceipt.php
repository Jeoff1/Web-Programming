<?php
session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}

require_once "../classes/blotter.php";
$blotterObj = new Blotter();

$blotter = null;
$adminName = '';

if(isset($_GET['blotter_id'])){
    $id = trim(htmlspecialchars($_GET['blotter_id']));
    $blotter = $blotterObj->fetchBlotter($id);
    if($blotter){
        // fetch admin name using the admin_id on the record
        try{
            $conn = $blotterObj->connect();
            $stmt = $conn->prepare("SELECT firstname, lastname FROM user WHERE user_id = :id LIMIT 1");
            $stmt->bindParam(':id', $blotter['admin_id']);
            if($stmt->execute()){
                $row = $stmt->fetch();
                if($row){
                    $adminName = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
                }
            }
        }catch(Exception $e){
            $adminName = '';
        }
    }
}

if(!$blotter){
    echo "<p>Blotter record not found. <a href=\"viewBlotter.php\">Back</a></p>";
    exit();
}

// Prepare fields for display
$caseNumber = htmlspecialchars($blotter['blotter_id']);
$complainants = htmlspecialchars($blotter['complainant_name']);
$complainantEmail = htmlspecialchars($blotter['complainant_email'] ?? '');
$respondents = htmlspecialchars($blotter['respondent_name']);
$incidentType = htmlspecialchars($blotter['category_name'] ?? '');
$description = nl2br(htmlspecialchars($blotter['description']));
$status = htmlspecialchars($blotter['status']);

// Date/time printed
$printedAt = date('M d, Y');

// Incident date/time from record
$incidentDate = '';
$incidentTime = '';
if(!empty($blotter['date'])){
    $incidentDate = date('M d, Y', strtotime($blotter['date']));
}
if(!empty($blotter['incident_time'])){
    $incidentTime = date('h:i A', strtotime($blotter['incident_time']));
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Blotter Receipt - Case #<?= $caseNumber ?></title>
<style>
    body{font-family: Arial, Helvetica, sans-serif; color:#111; padding:20px;}
    .receipt{max-width:720px;margin:0 auto;border:1px solid #ddd;padding:20px;border-radius:6px}
    .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
    .brand{font-weight:700;color:#1e90ff}
    .meta{font-size:13px;color:#6b7280}
    .section{margin-top:14px}
    .label{font-weight:700;color:#374151}
    .value{margin-top:6px}
    .actions{margin-top:18px;display:flex;gap:8px}
    .btn{background:#1e90ff;color:#fff;padding:8px 12px;border-radius:6px;border:0;cursor:pointer;text-decoration:none}
    .btn.secondary{background:#f3f4f6;color:#111;border:1px solid #e6e9ef}
    @media print{ .no-print{display:none} }
</style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <div>
            <div class="brand">Barangay Blotter</div>
            <div class="meta">Official Receipt</div>
        </div>
        <div class="meta">
            Case #: <?= $caseNumber ?><br>
            Printed: <?= $printedAt ?><br>
            Prepared by: <?= htmlspecialchars($adminName ?: ($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname'])) ?>
        </div>
    </div>

    <div class="section">
        <div class="label">Incident Type</div>
        <div class="value"><?= $incidentType ?></div>
    </div>

    <div class="section">
        <div class="label">Incident Date / Time</div>
        <div class="value"><?= htmlspecialchars($incidentDate) ?> <?= htmlspecialchars($incidentTime) ?></div>
    </div>

    <div class="section">
        <div class="label">Complainant(s)</div>
        <div class="value"><?= $complainants ?></div>
        <?php if(!empty($complainantEmail)): ?>
            <div class="meta" style="margin-top: 6px;">Email: <?= $complainantEmail ?></div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="label">Respondent(s)</div>
        <div class="value"><?= $respondents ?></div>
    </div>

    <div class="section">
        <div class="label">Narrative / Description</div>
        <div class="value"><?= $description ?></div>
    </div>

    <div class="section">
        <div class="label">Actions Taken / Status</div>
        <div class="value"><?= $status ?></div>
    </div>

    <div class="actions no-print">
        <button onclick="window.print()" class="btn">Print</button>
        <a href="viewBlotter.php" class="btn secondary">Back</a>
    </div>
</div>

<script>
// Optional: auto-open print dialog when page loads
window.addEventListener('load', function(){
    // small delay to allow render
    setTimeout(function(){
        // Uncomment the line below to auto-print when the receipt opens.
        // window.print();
    }, 400);
});
</script>
</body>
</html>
