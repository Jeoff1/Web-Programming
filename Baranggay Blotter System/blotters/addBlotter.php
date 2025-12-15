<?php

session_start();

if(!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] != "Staff" && $_SESSION["user"]["role"] != "Admin")){
    header('location: ../account/login.php');
    exit();
}

require_once "../classes/blotter.php";
$blotterObj = new Blotter();

// Load categories for select
$categories = $blotterObj->getCategories();

$blotter = [];
$errors = [];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $blotter["category"] = trim(htmlspecialchars($_POST["category"])); // this holds category_id now
    $blotter["complainant_name"] = trim(htmlspecialchars($_POST["complainant_name"] ?? ""));
    $blotter["complainant_email"] = trim(htmlspecialchars($_POST["complainant_email"] ?? ""));
    $blotter["respondent_name"] = trim(htmlspecialchars($_POST["respondent_name"] ?? ""));
    $blotter["date"] = trim(htmlspecialchars($_POST["date"]));
    $blotter["incident_time"] = trim(htmlspecialchars($_POST["incident_time"]));
    $blotter["location"] = trim(htmlspecialchars($_POST["location"]));
    $blotter["description"] = trim(htmlspecialchars($_POST["description"]));
    $blotter["status"] = trim(htmlspecialchars($_POST["status"]));

    $admin_id = $_SESSION['user']['user_id'];  

    if(empty($blotter["category"])) {
        $errors["category"] = "Incident Type is required.";
    }

    if(empty($blotter["complainant_name"])) {
        $errors["complainant_name"] = "Complainant Name is required.";
    }

    if(!empty($blotter["complainant_email"]) && !filter_var($blotter["complainant_email"], FILTER_VALIDATE_EMAIL)) {
        $errors["complainant_email"] = "Please enter a valid email address.";
    }

    if(empty($blotter["date"])) {
        $errors["date"] = "Incident Date is required.";
    } elseif(strtotime($blotter["date"]) > time()) {
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

    if($blotterObj->isBlotterExist($blotter["date"], $blotter["incident_time"], $blotter["location"])){
        $errors["general"] = "A blotter record already exists for this date, time, and location.";
    }

    if(empty(array_filter($errors))){
        $blotterObj->category_id = $blotter["category"];
        $blotterObj->complainant_name = $blotter["complainant_name"];
        $blotterObj->complainant_email = $blotter["complainant_email"];
        $blotterObj->respondent_name = $blotter["respondent_name"];
        $blotterObj->date = $blotter["date"];
        $blotterObj->incident_time = $blotter["incident_time"];
        $blotterObj->location = $blotter["location"];
        $blotterObj->description = $blotter["description"];
        // Set default status to Pending if not provided
        $blotterObj->status = !empty($blotter["status"]) ? $blotter["status"] : "Pending";

        // optional file upload handling
        $uploadedFileName = null;
        if(isset($_FILES['photo']) && isset($_FILES['photo']['error']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE){
            $file = $_FILES['photo'];
            if($file['error'] === UPLOAD_ERR_OK){
                $allowed = ['image/jpeg','image/png','image/gif'];
                if(!in_array($file['type'], $allowed)){
                    $errors['photo'] = 'Only JPG, PNG, GIF images are allowed.';
                } elseif($file['size'] > 5 * 1024 * 1024){
                    $errors['photo'] = 'Image size must be <= 5MB.';
                } else {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $uploadedFileName = uniqid('blt_') . '.' . $ext;
                    $dest = __DIR__ . '/../uploads/' . $uploadedFileName;
                    if(!move_uploaded_file($file['tmp_name'], $dest)){
                        $errors['photo'] = 'Failed to move uploaded file.';
                        $uploadedFileName = null;
                    }
                }
            } else {
                $errors['photo'] = 'Error uploading file.';
            }
        }

        // if upload produced errors, do not proceed
        if(empty(array_filter($errors))){
            $blotterObj->photo = $uploadedFileName;
            if($blotterObj->addBlotter($admin_id)){
                // Notification: Case Added
                require_once __DIR__ . '/notify_util.php';
                $case_id = $blotterObj->blotter_id;  // Get blotter_id from the object property
                error_log("==================== ADD BLOTTER DEBUG ====================");
                error_log("addBlotter() successful - Case ID from blotter object: " . $case_id);
                error_log("Case ID type: " . gettype($case_id) . ", Value: " . var_export($case_id, true));
                error_log("=========================================================");
                notify_case_added($case_id, $admin_id);
                header("Location: viewBlotter.php");
                exit();
            } else {
                echo "failed";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blotter Record</title>
    <link rel="stylesheet" href="style.css">
    <style>
    label {display:block; }
    span, .error {color: red; margin: 0;}
    textarea {width: 100%; min-height: 100px; font-family: Arial, sans-serif;}
    </style>
</head>
<body>
  <div class="app-shell">

    <header class="header card">
      <div class="brand">
        <div class="logo">BB</div>
        <h1>Add Blotter Record</h1>
      </div>
      <div class="nav-actions">
        <a class="btn" href="viewBlotter.php">View Records</a>
        <a class="btn secondary" href="../index.php">Back</a>
      </div>
    </header>

    <main class="card container">
      <?php if(isset($errors["general"])): ?>
        <p class="error"><?= $errors["general"] ?></p>
      <?php endif; ?>

      <form action="" method="post" enctype="multipart/form-data">
        <div class="grid">
          <div>
            <div class="form-row">
              <label for="category">Incident Type <span>*</span></label>
              <select name="category" id="category">
                <option value="">Select Incident Type</option>
                <?php foreach($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= (isset($blotter["category"]) && $blotter["category"] == $cat['id'])? "selected":"" ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="error"><?= $errors["category"] ?? ""?></p>
            </div>

            <div class="form-row">
              <label for="complainant_name">Complainant Name <span>*</span></label>
              <input type="text" name="complainant_name[]" id="complainant_name" value="<?= $blotter["complainant_name"] ?? ""?>">
              <p class="error"><?= $errors["complainant_name"] ?? ""?></p>
            </div>

            <div class="form-row">
              <label for="complainant_email">Complainant Email</label>
              <input type="email" name="complainant_email" id="complainant_email" value="<?= $blotter["complainant_email"] ?? ""?>">
              <p class="error"><?= $errors["complainant_email"] ?? ""?></p>
            </div>

            <div id="complainants-container">
              <!-- Additional complainants will be added here -->
            </div>
            <button type="button" class="btn secondary" id="add-complainant-btn" style="margin-bottom: 12px;">+ Add More Complainant</button>

            <div class="form-row">
              <label for="respondent_name">Respondent Name</label>
              <input type="text" name="respondent_name[]" id="respondent_name" value="<?= $blotter["respondent_name"] ?? ""?>">
            </div>

            <div id="respondents-container">
              <!-- Additional respondents will be added here -->
            </div>
            <button type="button" class="btn secondary" id="add-respondent-btn" style="margin-bottom: 12px;">+ Add More Respondent</button>

            <div class="form-row">
              <label for="date">Date <span>*</span></label>
              <input type="date" name="date" id="date" value="<?= $blotter["date"] ?? ""?>">
              <p class="error"><?= $errors["date"] ?? ""?></p>
            </div>

            <div class="form-row">
              <label for="incident_time">Incident Time <span>*</span></label>
              <input type="time" name="incident_time" id="incident_time" value="<?= $blotter["incident_time"] ?? ""?>">
              <p class="error"><?= $errors["incident_time"] ?? ""?></p>
            </div>

            <div class="form-row">
              <label for="location">Location <span>*</span></label>
              <input type="text" name="location" id="location" value="<?= $blotter["location"] ?? ""?>">
              <p class="error"><?= $errors["location"] ?? ""?></p>
            </div>
          </div>

          <div>
            <div class="form-row">
              <label for="description">Description</label>
              <textarea name="description" id="description"><?= $blotter["description"] ?? ""?></textarea>
            </div>

            <div class="form-row">
              <label for="photo">Photo (optional)</label>
              <input type="file" name="photo" id="photo" accept="image/*">
              <p class="error"><?= $errors["photo"] ?? ""?></p>
            </div>

            <div class="form-row">
              <label for="status">Status <span>*</span></label>
              <select name="status" id="status" style="display:none;">
                <option value="Pending" selected>Pending</option>
              </select>
              <p style="color: #6b7280; font-size: 13px; margin: 0;">New blotters are set to <strong>Pending</strong> by default. Edit the blotter to change the status.</p>
              <p class="error"><?= $errors["status"] ?? ""?></p>
            </div>
          </div>
        </div>

        <div class="actions" style="margin-top:12px;">
          <input type="submit" value="Save Record" class="btn">
          <a class="btn secondary" href="viewBlotter.php">View Blotter Records</a>
          <a class="btn secondary" href="../index.php">Back</a>
        </div>
      </form>

    </main>

    <footer class="footer">&copy; <?= date('Y') ?> Barangay Blotter</footer>
  </div>

  <script>
  (function(){
    let complainantCount = 1;
    let respondentCount = 1;

    function addComplainant(){
      const container = document.getElementById('complainants-container');
      const div = document.createElement('div');
      div.className = 'form-row dynamic-field';
      div.innerHTML = `
        <label for="complainant_${complainantCount}">Complainant Name</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="text" name="complainant_name[]" id="complainant_${complainantCount}" style="flex:1;">
          <button type="button" class="btn danger remove-field" style="padding:8px 10px;font-size:12px;">Remove</button>
        </div>
      `;
      container.appendChild(div);
      complainantCount++;
      attachRemoveHandler(div);
    }

    function addRespondent(){
      const container = document.getElementById('respondents-container');
      const div = document.createElement('div');
      div.className = 'form-row dynamic-field';
      div.innerHTML = `
        <label for="respondent_${respondentCount}">Respondent Name</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="text" name="respondent_name[]" id="respondent_${respondentCount}" style="flex:1;">
          <button type="button" class="btn danger remove-field" style="padding:8px 10px;font-size:12px;">Remove</button>
        </div>
      `;
      container.appendChild(div);
      respondentCount++;
      attachRemoveHandler(div);
    }

    function attachRemoveHandler(el){
      const btn = el.querySelector('.remove-field');
      if(btn){
        btn.addEventListener('click', function(e){
          e.preventDefault();
          el.remove();
        });
      }
    }

    document.getElementById('add-complainant-btn').addEventListener('click', function(e){
      e.preventDefault();
      addComplainant();
    });

    document.getElementById('add-respondent-btn').addEventListener('click', function(e){
      e.preventDefault();
      addRespondent();
    });

    // On form submit, collect and join multiple inputs into single fields
    const form = document.querySelector('form');
    if(form){
      form.addEventListener('submit', function(e){
        const complainantInputs = Array.from(document.querySelectorAll('input[name="complainant_name[]"]'))
          .map(el => el.value.trim())
          .filter(v => v);
        const respondentInputs = Array.from(document.querySelectorAll('input[name="respondent_name[]"]'))
          .map(el => el.value.trim())
          .filter(v => v);

        // Create hidden fields to submit the concatenated values
        const complaintField = document.createElement('input');
        complaintField.type = 'hidden';
        complaintField.name = 'complainant_name';
        complaintField.value = complainantInputs.join(', ');
        form.appendChild(complaintField);

        const respondentField = document.createElement('input');
        respondentField.type = 'hidden';
        respondentField.name = 'respondent_name';
        respondentField.value = respondentInputs.join(', ');
        form.appendChild(respondentField);
      });
    }
  })();
  </script>
</body>
</html>