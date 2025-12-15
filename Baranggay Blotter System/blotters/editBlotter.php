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
    $blotter["complainant_name"] = trim(htmlspecialchars($_POST["complainant_name"] ?? ""));
    $blotter["complainant_email"] = trim(htmlspecialchars($_POST["complainant_email"] ?? ""));
    $blotter["respondent_name"] = trim(htmlspecialchars($_POST["respondent_name"] ?? ""));
    $blotter["date"] = trim(htmlspecialchars($_POST["date"]));
    $blotter["incident_time"] = trim(htmlspecialchars($_POST["incident_time"]));
    $blotter["location"] = trim(htmlspecialchars($_POST["location"]));
    $blotter["description"] = trim(htmlspecialchars($_POST["description"]));
    $blotter["status"] = trim(htmlspecialchars($_POST["status"]));
    $blotter["status_reason"] = trim(htmlspecialchars($_POST["status_reason"] ?? ""));

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
        $blotterObj->complainant_email = $blotter["complainant_email"];
        $blotterObj->respondent_name = $blotter["respondent_name"];
        $blotterObj->date = $blotter["date"];
        $blotterObj->incident_time = $blotter["incident_time"];
        $blotterObj->location = $blotter["location"];
        $blotterObj->description = $blotter["description"];
        $blotterObj->status = $blotter["status"];
        $blotterObj->status_reason = $blotter["status_reason"];
        $blotterObj->admin_id = $_SESSION['user']['user_id'];  // Set admin_id for status history

        // optional photo upload handling for edit
        $updatePhoto = false;
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
                    if(move_uploaded_file($file['tmp_name'], $dest)){
                        $blotterObj->photo = $uploadedFileName;
                        $updatePhoto = true;
                    } else {
                        $errors['photo'] = 'Failed to move uploaded file.';
                    }
                }
            } else {
                $errors['photo'] = 'Error uploading file.';
            }
        }

        // Optional media upload for status reason
        $statusMediaFile = null;
        if(isset($_FILES['status_media']) && isset($_FILES['status_media']['error']) && $_FILES['status_media']['error'] !== UPLOAD_ERR_NO_FILE){
            $file = $_FILES['status_media'];
            if($file['error'] === UPLOAD_ERR_OK){
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 
                           'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                           'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                if(!in_array($file['type'], $allowed)){
                    $errors['status_media'] = 'Only images (JPG, PNG, GIF), PDF, Word, and Excel files are allowed.';
                } elseif($file['size'] > 5 * 1024 * 1024){
                    $errors['status_media'] = 'File size must be <= 5MB.';
                } else {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $statusMediaFile = uniqid('status_') . '.' . $ext;
                    $dest = __DIR__ . '/../uploads/' . $statusMediaFile;
                    if(!move_uploaded_file($file['tmp_name'], $dest)){
                        $errors['status_media'] = 'Failed to move uploaded file.';
                        $statusMediaFile = null;
                    }
                }
            } else {
                $errors['status_media'] = 'Error uploading file.';
            }
        }

        if(empty(array_filter($errors))){
            // Get the original blotter BEFORE editing to check for status changes
            $originalBlotter = $blotterObj->fetchBlotter($pid);
            error_log("==================== EDIT BLOTTER DEBUG ====================");
            error_log("Original blotter fetched for PID: $pid");
            error_log("Original status: " . ($originalBlotter['status'] ?? 'NULL'));
            error_log("New status: " . ($blotter["status"] ?? 'NULL'));
            
            // Append file path to status reason if media was uploaded
            if($statusMediaFile){
                $blotterObj->status_reason = $blotter["status_reason"] . "\n[Evidence/Document: " . $statusMediaFile . "]";
            }
            
            if($blotterObj->editBlotter($pid, $updatePhoto)){
                // Notification: Case Edited
                require_once __DIR__ . '/notify_util.php';
                
                // Send email if status has changed
                if ($originalBlotter && $originalBlotter['status'] !== $blotter["status"]) {
                    error_log("STATUS CHANGED - Sending email!");
                    error_log("Complainant name: " . ($blotter["complainant_name"] ?? 'NULL'));
                    error_log("Complainant email: " . ($blotter["complainant_email"] ?? 'NULL'));
                    // Email complainant with the new status and reason
                    send_email_status_changed(
                        $pid,
                        $blotter["complainant_name"],
                        $blotter["complainant_email"],
                        $originalBlotter['status'],
                        $blotter["status"],
                        $blotter["status_reason"] ?? ""
                    );
                } else {
                    error_log("STATUS NOT CHANGED - Skipping email");
                }
                
                // Add notification for status change
                if ($originalBlotter && $originalBlotter['status'] !== $blotter["status"]) {
                    notify_case_status_changed($pid, $_SESSION['user']['user_id'], $blotter["status"]);
                } else {
                    notify_case_edited($pid, $_SESSION['user']['user_id']);
                }
                error_log("=========================================================");
                
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
    <form action="" method="post" enctype="multipart/form-data">
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
        <input type="text" name="complainant_name[]" id="complainant_name" class="pangalan" value="<?= (is_array($blotter["complainant_name"] ?? null)) ? implode(', ', $blotter["complainant_name"]) : ($blotter["complainant_name"] ?? '') ?>">
        <p class="error"><?= $errors["complainant_name"] ?? ""?></p>
        <br>

        <label for="complainant_email">Complainant Email</label>
        <input type="email" name="complainant_email" id="complainant_email" class="pangalan" value="<?= $blotter["complainant_email"] ?? "" ?>">
        <p class="error"><?= $errors["complainant_email"] ?? ""?></p>
        <br>

        <div id="complainants-container">
          <!-- Additional complainants will be added here -->
        </div>
        <button type="button" class="btn secondary" id="add-complainant-btn">+ Add More Complainant</button>
        <br><br>

        <label for="respondent_name">Respondent Name</label>
        <input type="text" name="respondent_name[]" id="respondent_name" value="<?= (is_array($blotter["respondent_name"] ?? null)) ? implode(', ', $blotter["respondent_name"]) : ($blotter["respondent_name"] ?? '') ?>">
        <br>

        <div id="respondents-container">
          <!-- Additional respondents will be added here -->
        </div>
        <button type="button" class="btn secondary" id="add-respondent-btn">+ Add More Respondent</button>
        <br><br>

        <label for="date">Incident Date <span>*</span></label> 
        <input type="date" name="date" id="date" class="pangalan" value="<?= $blotter["incident_date"] ?? $blotter["date"] ?? "" ?>">
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

        <label for="photo">Photo (optional)</label>
        <?php if(!empty($blotter['photo'])): ?>
            <div><img src="<?= '../uploads/' . htmlspecialchars($blotter['photo']) ?>" alt="photo" style="max-width:150px;max-height:150px;"></div>
            <small>Upload a new image to replace the existing one.</small>
        <?php endif; ?>
        <input type="file" name="photo" id="photo" accept="image/*">
        <p class="error"><?= $errors["photo"] ?? ""?></p>
        <br>

        <label for="status">Status <span>*</span></label>
        <select name="status" id="status" class="pangalan">
            <option value="">Select Status</option>
            <option value="Pending" <?= (isset($blotter["status"]) && $blotter["status"] == "Pending")? "selected":"" ?>>Pending</option>
            <option value="Resolved" <?= (isset($blotter["status"]) && $blotter["status"] == "Resolved")? "selected":"" ?>>Resolved</option>
            <option value="Active" <?= (isset($blotter["status"]) && $blotter["status"] == "Active")? "selected":"" ?>>Active</option>
            <option value="Under Investigation" <?= (isset($blotter["status"]) && $blotter["status"] == "Under Investigation")? "selected":"" ?>>Under Investigation</option>
            <option value="Settled" <?= (isset($blotter["status"]) && $blotter["status"] == "Settled")? "selected":"" ?>>Settled</option>
            <option value="Referred to Police" <?= (isset($blotter["status"]) && $blotter["status"] == "Referred to Police")? "selected":"" ?>>Referred to Police</option>
            <option value="Closed" <?= (isset($blotter["status"]) && $blotter["status"] == "Closed")? "selected":"" ?>>Closed</option>
        </select>
        <p class="error"><?= $errors["status"] ?? ""?></p>
        <br>
            
        <label for="status_reason">Reason/Description for Status Update</label>
        <textarea name="status_reason" id="status_reason" class="pangalan" placeholder="Explain why you are changing the status of this case..." style="min-height: 80px;"><?= htmlspecialchars($blotter["status_reason"] ?? "") ?></textarea>
        <p style="font-size: 12px; color: #666;">This field is optional. Use it to document why you are updating the status.</p>
        <br>

        <label for="status_media">Upload Media/Evidence for Status Update (optional)</label>
        <input type="file" name="status_media" id="status_media" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">
        <p style="font-size: 12px; color: #666;">Accepted formats: Images (JPG, PNG, GIF), PDF, Word (.doc, .docx), Excel (.xls, .xlsx) - Max 5MB</p>
        <p class="error"><?= $errors["status_media"] ?? ""?></p>
        <br>

        <?php if(!empty($blotter["status_reason"])): ?>
        <div style="background: #f0f8ff; padding: 15px; border-radius: 8px; border-left: 4px solid #1e90ff; margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #1e90ff;">Current Status Reason/Description:</h3>
            <p style="white-space: pre-wrap; word-wrap: break-word; margin: 10px 0;"><?= htmlspecialchars($blotter["status_reason"]) ?></p>
            
            <?php 
            // Extract and display media files from status_reason if they exist
            if(preg_match_all('/\[Evidence\/Document:\s*([^\]]+)\]/', $blotter["status_reason"], $matches)):
                foreach($matches[1] as $mediaFile): 
                    $mediaPath = '../uploads/' . htmlspecialchars($mediaFile);
                    $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));
            ?>
                <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 6px;">
                    <?php if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <p style="margin: 0 0 8px 0; color: #666; font-size: 12px;"><strong>Image Evidence:</strong></p>
                        <img src="<?= $mediaPath ?>" alt="Evidence" style="max-width: 250px; max-height: 200px; border-radius: 4px; border: 1px solid #e6e9ef;">
                    <?php else: ?>
                        <p style="margin: 0; color: #666; font-size: 12px;">
                            <strong>📄 Document Evidence:</strong> 
                            <a href="<?= $mediaPath ?>" target="_blank" style="color: #1e90ff; text-decoration: none;">
                                <?= htmlspecialchars($mediaFile) ?> (download)
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php 
                endforeach;
            endif;
            ?>
        </div>
        <?php endif; ?>

        <input type="submit" class="view-books" value="Update Record" class = "btn-primary"><span><button type="button"><a href="viewBlotter.php">View Blotter Records</a></button></span>
    </form>
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