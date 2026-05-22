<?php
include 'database.php';
session_strart();

class dashboard{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }
}

?>