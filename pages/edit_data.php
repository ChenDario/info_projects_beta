<?php
    include "../includes/db.php";
    session_start();

    // Verifica che l'utente sia loggato
    if (!isset($_SESSION['username'])) {
        header("Location: ../index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT Nome, Cognome, Email, Username  FROM Users WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="../css/login.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit</title>
</head>
<body>
    
    <div class="container">
        <form action="edit.php" method="post">
            <label for="">Nome</label>
            <input type="text" placeholder="Nome..." name="nome" value="<?=$user['Nome']?>">
            
            <label for="">Cognome</label>
            <input type="text" placeholder="Cognome..." name="cognome" value="<?=$user['Cognome']?>">
            
            <label for="">Email</label>
            <input type="email" placeholder="Email..." name="email" value="<?=$user['Email']?>">

            <label for="">Username</label>
            <input type="text" placeholder="Username..." name="username" value="<?=$user['Username']?>">

            <button type="submit"> Edit </button>
        </form>
    </div>
</body>
</html>