<?php
    include "../../includes/db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Recupero e sanitizzazione dei dati
            $nome = trim($_POST['nome']);
            $cognome = trim($_POST['cognome']);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Verifica se l'username o l'email esistono già
            $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE Username = :username OR Email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['message'] = "Username o email già esistenti!";
                header("Location: signup.php");
                exit();
            }

            // Query preparata per inserire i dati
            $stmt = $conn->prepare("INSERT INTO Users (Nome, Cognome, Username, Email, Password_hash) 
                                   VALUES (:nome, :cognome, :username, :email, :password)");
            
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cognome', $cognome);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            
            $stmt->execute();

            // Verifica se l'inserimento è avvenuto
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Registrazione avvenuta con successo!";
            } else {
                $_SESSION['message'] = "Errore nell'inserimento dei dati.";
            }
            
        } catch(PDOException $e) {
            $_SESSION['message'] = "Errore durante la registrazione: " . $e->getMessage();
        }
    }

    header("Location: ../../index.php");
    exit();
?>