<?php

require_once 'staff.php';

class Account extends Staff{

    public $user_id;
    public $username;
    public $password;

    function login(){
        $sql = "SELECT * FROM user WHERE username = :username and is_active = 1 LIMIT 1;";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(':username', $this->username);
    
        if ($query->execute()) {
            $accountData = $query->fetch();
    
            if ($accountData && password_verify($this->password, $accountData['password'])) {
                session_start();
                $SESSION['user_id'] = [
                    'user_id' => $accountData['user_id'],
                    'username' => $accountData['username'],
                    'role' => $accountData['role']

                //$this->user_id = $accountData['user_id'];
                ];
                return true;
            }
        }
    
        return false;
    }

}

?>