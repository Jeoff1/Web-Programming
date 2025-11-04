<?php

require_once 'database.php';

Class Staff extends Database{
    //attributes

    public $user_id;
    public $firstname;
    public $lastname;
    // for this example, roles are only staff and admin
    public $role;
    public $username;
    public $password;
    public $is_active;

    //Methods

    function addStaff(){
        $sql = "INSERT INTO user (username, firstname, lastname, password, role, is_active) VALUES 
        (:username, :firstname, :lastname, :password, :role, :is_active);";

        $query=$this->connect()->prepare($sql);
        $query->bindParam(':firstname', $this->firstname);
        $query->bindParam(':lastname', $this->lastname);
        $query->bindParam(':role', $this->role);
        $query->bindParam(':username', $this->username);
        // Hash the password securely using password_hash
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $query->bindParam(':password', $hashedPassword);
        $query->bindParam(':is_active', $this->is_active);
        
        if($query->execute()){
            return true;
        }
        else{
            return false;
        }	
    }

    function getStaffByUsername(){
        $sql = "SELECT * FROM user WHERE username = :username;";
        $query=$this->connect()->prepare($sql);
        $query->bindParam(':username', $this->username);
        if($query->execute()){
            $data = $query->fetch();
        }
        return $data;
    }
}


?>