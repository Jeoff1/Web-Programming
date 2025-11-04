<?php
require_once "database.php";

class Blotter extends Database{
    public $id = "";
    public $admin_id = "";
    public $category = "";
    public $category_id = "";
    public $complainant_name = "";
    public $respondent_name = "";
    public $date = "";
    public $incident_time = "";
    public $location = "";
    public $description = "";
    public $status = "";

    public function isBlotterExist($pdate, $pincident_time, $plocation, $pid=""){
        $sql = "SELECT COUNT(*) as total FROM blotter WHERE date = :date AND incident_time = :incident_time AND location = :location AND blotter_id <> :blotter_id";
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
        if($this->isBlotterExist($this->date, $this->incident_time, $this->location)){
            return false;
        }
        $sql = "INSERT INTO blotter (admin_id, category_id, complainant_name, respondent_name, date, incident_time, location, description, status) VALUES (:admin_id, :category_id, :complainant_name, :respondent_name, :date, :incident_time, :location, :description, :status)";

        $query = $this->connect()->prepare($sql);

        $query->bindParam(":admin_id", $this->admin_id);
        $query->bindParam(":category_id", $this->category_id);
        $query->bindParam(":complainant_name", $this->complainant_name);
        $query->bindParam(":respondent_name", $this->respondent_name);
        $query->bindParam(":date", $this->date);
        $query->bindParam(":incident_time", $this->incident_time);
        $query->bindParam(":location", $this->location);
        $query->bindParam(":description", $this->description);
        $query->bindParam(":status", $this->status);

        return $query->execute();
    }

    public function viewBlotters($search="", $category_id="", $status=""){
        // Join with category table to get category name. category_id filter is optional when empty string is passed.
        $sql = "SELECT blotter.*, category.category_name AS category_name FROM blotter LEFT JOIN category ON blotter.category_id = category.category_id WHERE (complainant_name LIKE CONCAT('%', :search, '%') OR location LIKE CONCAT('%', :search, '%')) AND ((:category_id = '') OR blotter.category_id = :category_id) AND status LIKE CONCAT('%', :status, '%') ORDER BY date DESC, incident_time DESC";
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
        $sql = "SELECT blotter.*, category.category_name AS category_name FROM blotter LEFT JOIN category ON blotter.category_id = category.category_id WHERE blotter_id = :blotter_id";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":blotter_id", $pid);

        if ($query->execute()){
            return $query->fetch();
        }else{
            return null;
        }
    }

    public function editBlotter($pid){
        $sql = "UPDATE blotter SET category_id=:category_id, complainant_name=:complainant_name, respondent_name=:respondent_name, date=:date, incident_time=:incident_time, location=:location, description=:description, status=:status WHERE blotter_id=:blotter_id";

        $query = $this->connect()->prepare($sql);

    $query->bindParam(":category_id", $this->category_id);
        $query->bindParam(":complainant_name", $this->complainant_name);
        $query->bindParam(":respondent_name", $this->respondent_name);
        $query->bindParam(":date", $this->date);
        $query->bindParam(":incident_time", $this->incident_time);
        $query->bindParam(":location", $this->location);
        $query->bindParam(":description", $this->description);
        $query->bindParam(":status", $this->status);
        $query->bindParam(":blotter_id", $pid);

        return $query->execute();
    }

    public function deleteBlotter($pid){
        $sql = "DELETE FROM blotter WHERE blotter_id=:blotter_id";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":blotter_id", $pid);

        return $query->execute();
    }

    // Helper to fetch category for select lists
    public function getCategories(){
        // return rows with keys 'id' and 'name' for use by the UI
        $sql = "SELECT category_id AS id, category_name AS name FROM category ORDER BY category_name ASC";
        $query = $this->connect()->prepare($sql);
        if($query->execute()){
            return $query->fetchAll();
        }
        return [];
    }
}