<?php

class DataBase {

  private $servername = "localhost";
  private $username = "phpteszt";
  private $password = "iyD(VcxgbZ.6Pp]*";
  private $db = "szakdolgozat";
  public static $conn;

  function __construct()
  {
      // Create connection
      self::$conn = new mysqli($this->servername, $this->username, $this->password, $this->db);
  
      // Check connection
      if (self::$conn->connect_error) {
        die("Connection failed: " . $this->conn->connect_error);
      }
      
      self::$conn->set_charset("utf8");
  }

}



?> 