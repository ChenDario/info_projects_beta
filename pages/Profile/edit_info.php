<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $nome = trim($_POST['nome']);
            $cognome = trim($_POST['cognome']);
            $email = trim($_POST['email']);
            $username = trim($_POST['username']);
        
            // Verifica se l'username o email sono già usati da altri utenti
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE (Username = :username OR Email = :email) AND ID != :user_id");
            $check_stmt->bindParam(':username', $username);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $_SESSION['flash_message'] = "Username o email già in uso da un altro utente!";
                header("Location: profile.php");
                exit();
            }
        
            // Aggiorna i dati
            $update_stmt = $conn->prepare("UPDATE Users SET Nome = :nome, Cognome = :cognome, Email = :email, Username = :username, Updated_at = NOW() WHERE ID = :user_id");
            
            $update_stmt->bindParam(':nome', $nome);
            $update_stmt->bindParam(':cognome', $cognome);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':username', $username);
            $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($update_stmt->execute()) {
                // Aggiorna anche i dati in sessione se necessario
                $_SESSION['username'] = $username;
                $_SESSION['flash_message'] = "Dati aggiornati con successo!";
            } else {
                $_SESSION['flash_message'] = "Nessun dato modificato o errore durante l'aggiornamento.";
            }
        } catch(PDOException $e) {
            $_SESSION['flash_message'] = "Errore durante l'aggiornamento: " . $e->getMessage();
        }
        
        header("Location: profile.php");
        exit();
    }
?>