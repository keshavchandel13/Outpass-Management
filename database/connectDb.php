<?php
    $Servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "college";
    // Connection to the db
    $conn = new mysqli($Servername, $username, $password, $dbname);

    // Check connection
    if($conn->connect_error){
        die("connection failed". $conn->connect->error);
    }
?>