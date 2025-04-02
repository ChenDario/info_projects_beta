<?php
    include "db.php";
    session_start();

    $stmt = $conn->prepare("SELECT ");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Nota</title>
</head>
<body>
    <h2>Add a Note</h2>
    <form action="save_note.php" method="post">
        <input type="text" name="title" placeholder="Titolo..." required>
        <select name="materia" id="materia">

        </select>
        <br>
        <textarea name="content" placeholder="Scrivi la tua nota..." required></textarea><br>
        <button type="submit">Salva Nota</button>
    </form>
</body>
</html>
