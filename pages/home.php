<?php
    session_start();
    include "db.php";

    // Controlla se i dati del form sono stati inviati
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Recupera i dati dal form
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

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

            $_SESSION['username'] = $user['Username']; // Salva solo il nome utente
            $_SESSION['user_id'] = $user['ID']; // Salva anche l'ID se serve
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
    <!-- Link CSS -->
    <link rel="stylesheet" href="../css/home.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<style>
    ::selection {
        background: #45a049; /* Verde più scuro */
        color: #f1f1f1; /* Testo quasi bianco */
    }
</style>
<body>
    <div>
        <div class="sidebar"></div>
        <div class="navbar">
            <input type="text" class="search-bar" placeholder="Cerca...">
        </div>
        <div class="container">
            <?php
                // Recupera le note dell'utente loggato
                $stmt = $conn->prepare("SELECT * FROM Notes");
                $stmt->execute();
                $result = $stmt->get_result();

                // Mostra le note
                while ($row = $result->fetch_assoc()) {
                    echo "
                        <div class='card'>
                            <h2 class='card-title'>{$row['Title']}</h2>
                            <div class='card-content-wrapper'>
                                <p class='card-content'>{$row['Content']}</p>
                            </div>
                            <a href='noteDetail.php?id={$row['ID']}' class='read-more'>Leggi di più</a>
                        </div>
                    ";
                }
            ?>
        </div>
        <div class="btn">
            <button class="floating-button" onclick="location.href='addNote.php'">+</button>
        </div>
    </div>
</body>
</html>
