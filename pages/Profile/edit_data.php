<?php
    include "../../includes/db.php";
    session_start();

    // Verifica che l'utente sia loggato
    if (!isset($_SESSION['username'])) {
        header("Location: ../../index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("SELECT Nome, Cognome, Email, Username FROM Users WHERE ID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception("Utente non trovato");
        }
    } catch(PDOException $e) {
        die("Errore nel recupero dei dati utente: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="../../css/form.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
</head>
<body>
    <div class="logout-container">
        <a href="profile.php" class="arrow-back">
            <img src="../../images/arrow_back.png" alt="Logout" class="back-icon">
        </a>
    </div>
    <div class="container">
        <form action="edit_info.php" method="post">
            <label for="">Nome</label>
            <input type="text" placeholder="Nome..." name="nome" value="<?=htmlspecialchars($user['Nome'])?>">
            
            <label for="">Cognome</label>
            <input type="text" placeholder="Cognome..." name="cognome" value="<?=htmlspecialchars($user['Cognome'])?>">
            
            <label for="">Email</label>
            <input type="email" placeholder="Email..." name="email" value="<?=htmlspecialchars($user['Email'])?>">

            <label for="">Username</label>
            <input type="text" placeholder="Username..." name="username" value="<?=htmlspecialchars($user['Username'])?>">

            <button type="submit"> Edit </button>
        </form>
    </div>
</body>
</html>