<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    if (isset($_SESSION['flash_message'])) {
        echo "<script>alert('".htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES)."');</script>";
        unset($_SESSION['flash_message']);
    }

    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("SELECT Nome, Cognome, Email, DATE(Updated_at) AS LastUpdate, Username FROM Users WHERE ID = :user_id");
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
<html lang="it">
<head>
    <!--Link CSS General Structure-->
    <link rel="stylesheet" href="../../css/profile.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <div class="btn">
        <button class="floating-button" onclick="location.href='../home.php'">Home</button>
    </div>
    <div class="profile-container">
        <h1>
            <?=htmlspecialchars($user['Username'])?>
        </h1>

        <div class="info-block">
            <div class="icon">
                <img src="../../images/2815428.png" alt="User Icon" class="icon-img">
            </div>
            <div>
                <div class="info-label">Name</div>
                <div class="info-value"><?=htmlspecialchars($user['Nome'])?></div>
            </div>
        </div>

        <div class="info-block">
            <div class="icon">
                <img src="../../images/2815428.png" alt="User Icon" class="icon-img">
            </div>
            <div>
                <div class="info-label">Surname</div>
                <div class="info-value"><?=htmlspecialchars($user['Cognome'])?></div>
            </div>
        </div>

        <div class="info-block">
            <div class="icon">
                <img src="../../images/mail.png" alt="User Icon" class="icon-mail">
            </div>
            <div>
                <div class="info-label">Email</div>
                <div class="info-value"><?=htmlspecialchars($user['Email'])?></div>
            </div>
        </div>

        <div>
            <button class="edit-button" onclick="location.href='./edit_data.php'">
                <img src="../../images/edit.png" alt="Edit Icon" class="edit-icon">
                Edit Data
            </button>
        </div>

        <div class="update-date">Last Update: <?=htmlspecialchars($user['LastUpdate'])?></div>
    </div>
</body>
</html>