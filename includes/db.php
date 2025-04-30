<?php
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'Progetto_Chen';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
?>