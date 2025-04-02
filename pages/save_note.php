<?php
    include "db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        $user_id = $_SESSION['user_id'];
        $materia_id = 1; // Puoi far scegliere la materia tramite un select nel form

    }
?>
