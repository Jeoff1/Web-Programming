<?php
require_once "database.php";

class Blotter extends Database{
    public $id = "";
    public $blotter_id = "";  // Add property to store the blotter_id after insertion
    public $admin_id = "";
    public $category = "";
    public $category_id = "";
    public $complainant_name = "";
    public $complainant_email = "";
    public $respondent_name = "";
    public $incident_date = "";
    public $incident_time = "";
    public $location = "";
    public $description = "";
    public $photo = "";
    public $status = "";
    public $status_reason = "";
    
    // For backward compatibility with old field names
    public $date = "";

    // Constructor not needed - parent class doesn't require it

    public function isBlotterExist($pdate, $pincident_time, $plocation, $pid=""){
        $sql = "SELECT COUNT(*) as total FROM blotter_case WHERE incident_date = :date AND incident_time = :incident_time AND location = :location AND blotter_id <> :blotter_id";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":date", $pdate);
        $query->bindParam(":incident_time", $pincident_time);
        $query->bindParam(":location", $plocation);
        $query->bindParam(":blotter_id", $pid);
        $record = null;

        if ($query->execute()) {
            $record = $query->fetch();
        }
        if($record["total"] > 0){
            return true;
        }else{
            return false;
        }
    }

    public function addBlotter($admin_id){
        $this->admin_id = $admin_id;
        
        // Use incident_date if set, otherwise fall back to date for backward compatibility
        $incident_date = !empty($this->incident_date) ? $this->incident_date : $this->date;
        
        if($this->isBlotterExist($incident_date, $this->incident_time, $this->location)){
            return false;
        }

        // Start transaction
        $conn = $this->connect();
        $conn->beginTransaction();

        try {
            // 1. Create or get complainant person
            $complainant_person_id = null;
            if (!empty($this->complainant_name)) {
                $complainant_person_id = $this->createOrGetPerson($conn, $this->complainant_name, $this->complainant_email);
            }

            // 2. Create or get respondent person
            $respondent_person_id = null;
            if (!empty($this->respondent_name)) {
                $respondent_person_id = $this->createOrGetPerson($conn, $this->respondent_name, null);
            }

            // 3. Insert into blotter_case
            $sql = "INSERT INTO blotter_case (admin_id, category_id, incident_date, incident_time, location, description, photo) 
                    VALUES (:admin_id, :category_id, :incident_date, :incident_time, :location, :description, :photo)";
            $query = $conn->prepare($sql);
            $query->bindParam(":admin_id", $this->admin_id);
            $query->bindParam(":category_id", $this->category_id);
            $query->bindParam(":incident_date", $incident_date);
            $query->bindParam(":incident_time", $this->incident_time);
            $query->bindParam(":location", $this->location);
            $query->bindParam(":description", $this->description);
            $query->bindParam(":photo", $this->photo);
            
            if (!$query->execute()) {
                throw new Exception("Failed to insert blotter case");
            }

            $blotter_id = $conn->lastInsertId();

            // 4. Link complainant to blotter
            if ($complainant_person_id) {
                $this->linkPersonToBlotter($conn, $blotter_id, $complainant_person_id, 'Complainant');
            }

            // 5. Link respondent to blotter
            if ($respondent_person_id) {
                $this->linkPersonToBlotter($conn, $blotter_id, $respondent_person_id, 'Respondent');
            }

            // 6. Set initial status to "Pending" (status_id = 1)
            $initial_status_id = 1; // Pending
            $this->addStatusHistory($conn, $blotter_id, $initial_status_id, $admin_id, "Case created");

            // Store the blotter_id for later use
            $this->blotter_id = $blotter_id;

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Blotter add error: " . $e->getMessage());
            return false;
        }
    }

    private function createOrGetPerson($conn, $full_name, $email = null) {
        // Try to find existing person by name and email
        $sql = "SELECT person_id FROM person WHERE full_name = :full_name";
        $params = [":full_name" => $full_name];
        
        if (!empty($email)) {
            $sql .= " AND email = :email";
            $params[":email"] = $email;
        }
        
        $query = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $query->bindParam($key, $value);
        }
        
        if ($query->execute()) {
            $result = $query->fetch();
            if ($result) {
                return $result['person_id'];
            }
        }

        // Create new person if not found
        $sql = "INSERT INTO person (full_name, email) VALUES (:full_name, :email)";
        $query = $conn->prepare($sql);
        $query->bindParam(":full_name", $full_name);
        $query->bindParam(":email", $email);
        
        if ($query->execute()) {
            return $conn->lastInsertId();
        }
        
        return null;
    }

    private function linkPersonToBlotter($conn, $blotter_id, $person_id, $role_type) {
        $sql = "INSERT INTO blotter_person (blotter_id, person_id, role_type) VALUES (:blotter_id, :person_id, :role_type)";
        $query = $conn->prepare($sql);
        $query->bindParam(":blotter_id", $blotter_id);
        $query->bindParam(":person_id", $person_id);
        $query->bindParam(":role_type", $role_type);
        
        return $query->execute();
    }

    private function addStatusHistory($conn, $blotter_id, $status_id, $changed_by, $reason = null) {
        $sql = "INSERT INTO case_status_history (blotter_id, status_id, changed_by, status_reason) 
                VALUES (:blotter_id, :status_id, :changed_by, :status_reason)";
        $query = $conn->prepare($sql);
        $query->bindParam(":blotter_id", $blotter_id);
        $query->bindParam(":status_id", $status_id);
        $query->bindParam(":changed_by", $changed_by);
        $query->bindParam(":status_reason", $reason);
        
        return $query->execute();
    }

    public function viewBlotters($search="", $category_id="", $status=""){
        // Join with category, persons, and current status
        $sql = "SELECT bc.*, 
                       c.category_name,
                       MAX(CASE WHEN bp.role_type = 'Complainant' THEN p.full_name END) as complainant_name,
                       MAX(CASE WHEN bp.role_type = 'Complainant' THEN p.email END) as complainant_email,
                       MAX(CASE WHEN bp.role_type = 'Respondent' THEN p.full_name END) as respondent_name,
                       cs.status_name as status
                FROM blotter_case bc
                LEFT JOIN category c ON bc.category_id = c.category_id
                LEFT JOIN blotter_person bp ON bc.blotter_id = bp.blotter_id
                LEFT JOIN person p ON bp.person_id = p.person_id
                LEFT JOIN (
                    SELECT blotter_id, status_id FROM case_status_history 
                    WHERE (blotter_id, changed_at) IN (
                        SELECT blotter_id, MAX(changed_at) FROM case_status_history GROUP BY blotter_id
                    )
                ) csh ON bc.blotter_id = csh.blotter_id
                LEFT JOIN case_status cs ON csh.status_id = cs.status_id
                GROUP BY bc.blotter_id
                HAVING (complainant_name LIKE CONCAT('%', :search, '%') 
                   OR bc.location LIKE CONCAT('%', :search, '%'))
                AND ((:category_id = '') OR bc.category_id = :category_id)
                AND ((:status = '') OR cs.status_name LIKE CONCAT('%', :status, '%'))
                ORDER BY bc.incident_date DESC, bc.incident_time DESC";
        
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":search", $search);
        $query->bindParam(":category_id", $category_id);
        $query->bindParam(":status", $status);

        if($query->execute()){  
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function fetchBlotter($pid){
        $sql = "SELECT bc.*, 
                       c.category_name,
                       MAX(CASE WHEN bp.role_type = 'Complainant' THEN p.full_name END) as complainant_name,
                       MAX(CASE WHEN bp.role_type = 'Complainant' THEN p.email END) as complainant_email,
                       MAX(CASE WHEN bp.role_type = 'Respondent' THEN p.full_name END) as respondent_name,
                       cs.status_name as status,
                       csh.status_reason,
                       csh.changed_at as status_changed_at
                FROM blotter_case bc
                LEFT JOIN category c ON bc.category_id = c.category_id
                LEFT JOIN blotter_person bp ON bc.blotter_id = bp.blotter_id
                LEFT JOIN person p ON bp.person_id = p.person_id
                LEFT JOIN (
                    SELECT blotter_id, status_id, status_reason, changed_at FROM case_status_history 
                    WHERE (blotter_id, changed_at) IN (
                        SELECT blotter_id, MAX(changed_at) FROM case_status_history GROUP BY blotter_id
                    )
                ) csh ON bc.blotter_id = csh.blotter_id
                LEFT JOIN case_status cs ON csh.status_id = cs.status_id
                WHERE bc.blotter_id = :blotter_id
                GROUP BY bc.blotter_id, c.category_name, cs.status_id, cs.status_name, csh.status_reason, csh.changed_at";
        
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":blotter_id", $pid);

        if ($query->execute()){
            return $query->fetch();
        }else{
            return null;
        }
    }

    /**
     * Edit blotter. If $updatePhoto is true, update the photo column as well.
     */
    public function editBlotter($pid, $updatePhoto = false){
        $conn = $this->connect();
        $conn->beginTransaction();

        try {
            // Use incident_date if set, otherwise fall back to date for backward compatibility
            $incident_date = !empty($this->incident_date) ? $this->incident_date : $this->date;

            // Update blotter_case table
            if($updatePhoto){
                $sql = "UPDATE blotter_case SET category_id=:category_id, incident_date=:incident_date, incident_time=:incident_time, location=:location, description=:description, photo=:photo WHERE blotter_id=:blotter_id";
                $query = $conn->prepare($sql);
                $query->bindParam(":photo", $this->photo);
            } else {
                $sql = "UPDATE blotter_case SET category_id=:category_id, incident_date=:incident_date, incident_time=:incident_time, location=:location, description=:description WHERE blotter_id=:blotter_id";
                $query = $conn->prepare($sql);
            }

            $query->bindParam(":category_id", $this->category_id);
            $query->bindParam(":incident_date", $incident_date);
            $query->bindParam(":incident_time", $this->incident_time);
            $query->bindParam(":location", $this->location);
            $query->bindParam(":description", $this->description);
            $query->bindParam(":blotter_id", $pid);

            if (!$query->execute()) {
                throw new Exception("Failed to update blotter case");
            }

            // Update persons if needed
            // Remove old persons
            $sql = "DELETE FROM blotter_person WHERE blotter_id = :blotter_id";
            $query = $conn->prepare($sql);
            $query->bindParam(":blotter_id", $pid);
            $query->execute();

            // Add new complainant
            if (!empty($this->complainant_name)) {
                $complainant_person_id = $this->createOrGetPerson($conn, $this->complainant_name, $this->complainant_email);
                $this->linkPersonToBlotter($conn, $pid, $complainant_person_id, 'Complainant');
            }

            // Add new respondent
            if (!empty($this->respondent_name)) {
                $respondent_person_id = $this->createOrGetPerson($conn, $this->respondent_name, null);
                $this->linkPersonToBlotter($conn, $pid, $respondent_person_id, 'Respondent');
            }

            // Update status if it changed
            if (!empty($this->status)) {
                // Get status_id from status name
                $sql = "SELECT status_id FROM case_status WHERE status_name = :status_name";
                $query = $conn->prepare($sql);
                $query->bindParam(":status_name", $this->status);
                $query->execute();
                $result = $query->fetch();
                
                if ($result) {
                    $this->addStatusHistory($conn, $pid, $result['status_id'], $this->admin_id, $this->status_reason);
                }
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Blotter edit error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBlotter($pid){
        $sql = "DELETE FROM blotter_case WHERE blotter_id=:blotter_id";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":blotter_id", $pid);

        return $query->execute();
    }

    // Helper to fetch category for select lists
    public function getCategories(){
        $sql = "SELECT category_id AS id, category_name AS name FROM category ORDER BY CASE WHEN category_name = 'Other' THEN 1 ELSE 0 END, category_name ASC";
        $query = $this->connect()->prepare($sql);
        if($query->execute()){
            return $query->fetchAll();
        }
        return [];
    }

    // KPI: Total blotter cases
    public function getTotalCases(){
        $sql = "SELECT COUNT(*) as total FROM blotter_case";
        $query = $this->connect()->prepare($sql);
        if($query->execute()){
            $result = $query->fetch();
            return $result['total'] ?? 0;
        }
        return 0;
    }

    // KPI: Cases per category
    public function getCasesPerCategory(){
        $sql = "SELECT c.category_name AS name, COUNT(bc.blotter_id) AS count 
                FROM blotter_case bc 
                LEFT JOIN category c ON bc.category_id = c.category_id 
                GROUP BY bc.category_id, c.category_name 
                ORDER BY count DESC";
        $query = $this->connect()->prepare($sql);
        if($query->execute()){
            return $query->fetchAll();
        }
        return [];
    }

    // KPI: Resolved vs Pending cases
    public function getResolvedVsPending(){
        $sql = "SELECT cs.status_name as status, COUNT(DISTINCT bc.blotter_id) as count 
                FROM blotter_case bc
                LEFT JOIN (
                    SELECT blotter_id, status_id FROM case_status_history 
                    WHERE (blotter_id, changed_at) IN (
                        SELECT blotter_id, MAX(changed_at) FROM case_status_history GROUP BY blotter_id
                    )
                ) csh ON bc.blotter_id = csh.blotter_id
                LEFT JOIN case_status cs ON csh.status_id = cs.status_id
                GROUP BY cs.status_id, cs.status_name";
        $query = $this->connect()->prepare($sql);
        if($query->execute()){
            return $query->fetchAll();
        }
        return [];
    }

    // KPI: Monthly report count (last 12 months)
    public function getMonthlyCasesCount(){
        $sql = "SELECT DATE_FORMAT(incident_date, '%Y-%m') AS month, COUNT(*) as count 
                FROM blotter_case 
                WHERE incident_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                GROUP BY DATE_FORMAT(incident_date, '%Y-%m') 
                ORDER BY month DESC";
        $query = $this->connect()->prepare($sql);
        if($query->execute()){
            return $query->fetchAll();
        }
        return [];
    }

    // Helper function to get current status of a blotter
    public function getCurrentStatus($blotter_id) {
        $sql = "SELECT cs.status_name, cs.status_id, csh.status_reason
                FROM case_status_history csh
                LEFT JOIN case_status cs ON csh.status_id = cs.status_id
                WHERE csh.blotter_id = :blotter_id
                ORDER BY csh.changed_at DESC
                LIMIT 1";
        
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":blotter_id", $blotter_id);
        
        if ($query->execute()) {
            return $query->fetch();
        }
        return null;
    }
}