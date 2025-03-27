<?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "progetto_chen";

    $conn = new mysqli($host, $user, $pass, $dbname);

    // Verifica se la connessione è riuscita
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>