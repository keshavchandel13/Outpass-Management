<?php
    $Servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "college";
    // Connection to the db
    $conn = new mysqli($Servername, $username, $password, $dbname);

    // Check connection
    if($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
    }
?>