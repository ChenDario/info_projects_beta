<?php
    include "db.php";

    // Controlla se i dati del form sono stati inviati
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Recupera i dati dal form
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Usa prepared statement per evitare SQL Injection
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
        $stmt->bind_param("s", $username); // "s" indica una variabile di tipo stringa
        $stmt->execute();
        $result = $stmt->get_result();

        // Se l'utente esiste, controlla la password
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verifica se la password è corretta
            if (!password_verify($password, $user['Password_hash'])) {
                $_SESSION['message'] = "Password errato";
                header("Location: ../index.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Username errato";
            header("Location: ../index.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="../css/home.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <div>
        <div class="sidebar"></div>
        <div class="container">
            <div class="navbar">
                <input type="text" class="search-bar" placeholder="Cerca...">
            </div>

        </div>
    </div>
</body>
</html>