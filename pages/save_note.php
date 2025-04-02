<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
    include "db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $user_id = $_SESSION['user_id'];
        $materia_id = $_POST['materia'];


        // Validazione: Controlliamo che i dati non siano vuoti
        if (empty($title) || empty($content) || empty($materia_id)) {
            die("Errore: Tutti i campi sono obbligatori.");
        }

        // Usa prepared statement per evitare SQL Injection
        $stmt = $conn->prepare("INSERT INTO Notes (User_id, Title, Materia_ID, Content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $title, $materia_id, $content);

        if ($stmt->execute()) {
            echo "Nota salvata con successo!";
        } else {
            echo "Errore durante il salvataggio: " . $stmt->error;
        }
    
        $stmt->close();

        header("Location: home.php");
    }
?>
