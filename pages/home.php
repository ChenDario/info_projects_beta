<?php   
    include "../includes/db.php";
    session_start();

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
<body>
    <div>
        <!-- Modifica la parte della sidebar nel body -->
        <div class="sidebar">
            <!-- Sezione profilo/icona -->
            <div class="sidebar-profile">
                <h3 class="username"><?php echo $_SESSION['username']; ?></h3>
            </div>

            <!-- Sezioni informative -->
            <div class="sidebar-sections">
                <div class="sidebar-item">
                    <div class="item-header">
                        <img src="../path/to/icon1.png" alt="Icon" class="item-icon">
                        <h4>Derivata</h4>
                    </div>
                    <p class="item-content">La derivata è il limite del rapporto incrementale...</p>
                    <a href="#" class="item-more">More</a>
                </div>

                <div class="sidebar-item">
                    <div class="item-header">
                        <img src="../path/to/icon2.png" alt="Icon" class="item-icon">
                        <h4>Emile Zola</h4>
                    </div>
                    <p class="item-content">Pioniere del naturalismo letterario...</p>
                    <a href="#" class="item-more">More</a>
                </div>
            </div>
        </div>
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
                            <a href='noteDetail.php?id={$row['ID']}' class='read-more'> More </a>
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
