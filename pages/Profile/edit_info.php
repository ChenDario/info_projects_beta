<?php
    include "../../includes/db.php";
    session_start();

    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $email = $_POST['email'];
        $username = $_POST['username'];
    
        // Prepara e esegui l'update
        $update_stmt = $conn->prepare("UPDATE Users SET Nome = ?, Cognome = ?, Email = ?, Username = ? WHERE ID = ?");
        $update_stmt->bind_param("ssssi", $nome, $cognome, $email, $username, $user_id);
    
        if ($update_stmt->execute()) {
            $_SESSION['flash_message'] = "Dati aggiornati con successo!";
        } else {
            $_SESSION['flash_message'] = "Errore durante l'aggiornamento dei dati.";
        }
    
        header("Location: profile.php");
        exit();
    }
    
?>