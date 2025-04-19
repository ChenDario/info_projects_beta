<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    $note_content = "";
    $note_title = "";
    $files = [];

    // Ottieni l'ID dalla query string
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
            $_SESSION['message'] = "Nota non trovata";
            header("Location: home.php");
            exit();
        }

        // Recupera i file allegati
        $stmt_files = $conn->prepare("SELECT Original_filename, Stored_filename FROM Files WHERE Note_id = ?");
        $stmt_files->bind_param("i", $note_id);
        $stmt_files->execute();
        $result_files = $stmt_files->get_result();

        while ($file = $result_files->fetch_assoc()) {
            $files[] = $file;
        }

        $stmt_files->close();
    } else {
        $_SESSION['message'] = "ID nota non specificato";
        header("Location: home.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <!--Link CSS General Structure-->
    <link rel="stylesheet" href="../../css/noteDetail.css">
    <!--Link CSS File Visualization-->
    <link rel="stylesheet" href="../../css/fileVisualization.css">
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

        <?php if (!empty($files)): ?>
            <h2>Allegati</h2>
            <div id="file-preview" class="file-preview">
                <?php foreach ($files as $file): 
                    $filePath = "../../uploads/" . $file['Stored_filename'];
                    $fileName = htmlspecialchars($file['Original_filename']);
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                    $isPDF = strtolower($ext) === 'pdf';
                    $isTxt = strtolower($ext) === 'txt';
                ?>
                    <div class="file-container">
                        <?php if ($isImage): ?>
                            <img class="preview-image" src="<?php echo $filePath; ?>" onclick="openModal('<?php echo $filePath; ?>', 'image')">
                        <?php elseif ($isPDF): ?>
                            <p><?php echo $fileName; ?></p>
                            <canvas class="pdf-preview" data-pdf="<?php echo $filePath; ?>"></canvas>
                        <?php elseif ($isTxt): ?>
                            <div class="file-icon" onclick="openText('<?php echo $filePath; ?>')"><?php echo $fileName; ?></div>
                        <?php else: ?>
                            <div class="file-icon"><?php echo $fileName; ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Modal -->
        <div id="modal-viewer" class="modal-viewer" style="display:none;" onclick="closeModal()">
            <div class="modal-content" onclick="event.stopPropagation();">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <div id="modal-body"></div>
            </div>
        </div>
    </div>
    
    <!-- Script CodeMirror (textarea) -->
    <script src="../../js/CodeMirror.js"></script>
    <!-- Script File Anteprima -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="../../js/FileVisualization.js"></script>
</body>
</html>
