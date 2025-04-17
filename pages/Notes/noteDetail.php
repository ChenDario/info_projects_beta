<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    $note_content = "";
    $note_title = "";

    // Modifica: Ottieni l'ID dalla query string (GET)
    if(isset($_GET['id'])) {
        $note_id = (int)$_GET['id'];

        // Recupera i dettagli della nota
        $stmt = $conn->prepare("SELECT Title, Content, U.Username AS Username, DATE(N.Updated_at) AS NoteUpdate, DATE(N.Created_at) AS NoteCreate, M.Nome AS NomeMateria FROM Notes N INNER JOIN Users U ON U.ID = N.User_id INNER JOIN Materia M ON M.ID = Materia_ID WHERE N.ID = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $note_title = htmlspecialchars($row['Title']);
            $note_content = $row['Content'];
            $note_username = $row['Username'];
            $note_create = $row['NoteCreate'];
            $note_update = $row['NoteUpdate'];
            $materia = $row['NomeMateria'];
        } else {
            // Gestione nota non trovata
            $_SESSION['message'] = "Nota non trovata";
            header("Location: home.php");
            exit();
        }
    } else {
        // Gestione ID mancante
        $_SESSION['message'] = "ID nota non specificato";
        header("Location: home.php");
        exit();
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="../../css/noteDetail.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Note Detail </title>
</head>
<body>
    <div class="container">
        <div class="btn">
            <button class="floating-button" onclick="location.href='../home.php'">Home</button>
        </div>

        <h1><?php echo $note_title; ?></h1>

        <div class="info">
            <?php
                echo "
                    {$materia} &nbsp | &nbsp {$note_username} &nbsp | &nbsp Created: {$note_create} &nbsp | &nbsp Last Update: {$note_update}
                ";
            ?>
        </div>
        
        <div class="note-content">
            <p>
                <?php echo nl2br($note_content); ?>
            </p>    
        </div>
    </div>
</body>
</html>