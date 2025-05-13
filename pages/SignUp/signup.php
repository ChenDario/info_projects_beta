<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Link CSS General Structure-->
    <link rel="stylesheet" href="../../css/form.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sign-Up </title>
</head>
<body>
    <div class="logout-container">
        <a href="../../index.php" class="arrow-back">
            <img src="../../images/arrow_back.png" alt="Logout" class="back-icon">
        </a>
    </div>
    <div class="container">
        <form action="user_registration.php" method="post">
            <label for="">Nome</label>
            <input type="text" placeholder="Nome..." name="nome" required>
            
            <label for="">Cognome</label>
            <input type="text" placeholder="Cognome..." name="cognome" required>
            
            <label for="">Email</label>
            <input type="email" placeholder="Email..." name="email" required>

            <label for="">Username</label>
            <input type="text" placeholder="Username..." name="username" required>

            <label for="">Password</label>
            <input type="password" placeholder="Password..." name="password" required>

            <div class="checkbox-container">
                <input type="checkbox" id="privacy" name="privacy" required>
                <label for="privacy">Accetto i <a href="privacy.html" target="_blank">termini sulla privacy e il trattamento dei dati</a></label>
            </div>

            <button type="submit"> Submit </button>
        </form>
    </div>

</body>
</html>