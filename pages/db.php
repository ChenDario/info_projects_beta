<?php
    session_start();
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'Progetto_Chen';

    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

?>