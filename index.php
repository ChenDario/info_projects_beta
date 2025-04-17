<?php
    session_start();
    if (isset($_SESSION['message'])) {
        echo "<script>alert('".$_SESSION['message']."');</script>";
        unset($_SESSION['message']); // Pulisce il messaggio dopo che è stato visualizzato
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="css/login.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Login </title>
</head>
<body>
    <div class="container">
        <form action="pages/home.php" method="post">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Username...">

            <label for="password">Password</label>
            <input type="password" name="password" id="pwd" placeholder="Password...">

            <button type="submit">Login</button>

            <div class="links">
                <a href="pages/forgot-password.php">Password dimenticata?</a>
                <a href="pages/SignUp/signup.php">Registrati</a>
            </div>
        </form>
    </div>
</body>
</html>
