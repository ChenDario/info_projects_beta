<?php   
    include "../includes/db.php";
    session_start();

    // Controlla se i dati del form di login sono stati inviati
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (!password_verify($password, $user['Password_hash'])) {
                $_SESSION['message'] = "Password errata";
                header("Location: ../index.php");
                exit();
            }
            $_SESSION['user_id'] = $user['ID'];
        } else {
            $_SESSION['message'] = "Username errato";
            header("Location: ../index.php");
            exit();
        }
    }

    // Ottieni i dati dell'utente
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT Username FROM Users WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/home.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <div>
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-profile">
                <?php echo strtoupper(substr($userData['Username'], 0, 1)); ?>
            </div>
            <h3 class="username"><a href="profile.php"><?= $userData['Username'] ?></a></h3>

            <div class="sidebar-sections">
                <div class="sidebar-item">
                    <p class="item-content">La derivata è il limite del rapporto incrementale...</p>
                </div>
                <div class="sidebar-item">
                    <p class="item-content">Pioniere del naturalismo letterario...</p>
                </div>
            </div>

            <div class="logout-container">
                <a href="logout.php" class="logout-link">
                    <img src="../images/logout.png" alt="Logout" class="logout-icon">
                </a>
            </div>
        </div>

        <!-- Navbar con barra di ricerca -->
        <div class="navbar">
            <form method="GET" action="">
                <input type="text" name="search" class="search-bar" placeholder="Cerca..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>

        <!-- Container note -->
        <div class="container">
            <?php
                $searchTerm = $_GET['search'] ?? '';

                if (!empty($searchTerm)) {
                    $stmt = $conn->prepare("SELECT * FROM Notes WHERE Title LIKE CONCAT('%', ?, '%') OR Content LIKE CONCAT('%', ?, '%')");
                    $stmt->bind_param("ss", $searchTerm, $searchTerm);
                } else {
                    $stmt = $conn->prepare("SELECT * FROM Notes");
                }

                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
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
                } else {
                    echo "<p>Nessuna nota trovata.</p>";
                }
            ?>
        </div>

        <!-- Pulsante per aggiungere nota -->
        <div class="btn">
            <button class="floating-button" onclick="location.href='addNote.php'">+</button>
        </div>
    </div>
</body>
</html>
