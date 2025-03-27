<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Link CSS -->
    <link rel="stylesheet" href="../css/login.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sign-Up </title>
</head>
<body>

    <div class="container">
        <form action="datainput.php" method="post">
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

            <button type="submit"> Submit </button>
        </form>
    </div>

</body>
</html>