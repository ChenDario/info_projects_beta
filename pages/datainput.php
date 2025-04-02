<?php
    include "db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Protezione da SQL Injection usando prepare
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash della password

        // Query preparata per inserire i dati nel database
        $stmt = $conn->prepare("INSERT INTO Users (Nome, Cognome, Username, Email, Password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $cognome, $username, $email, $password);
        $stmt->execute();

        // Verifica se l'inserimento è avvenuto correttamente
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Registrazione avvenuta con successo!";
        } else {
            $_SESSION['message'] = "Errore nell'inserimento dei dati.";
        }

        $stmt->close();  // Chiude lo statement preparato
    }

    session_destroy();
    header("Location: ../index.php");
?>