<?php
    include "../../includes/db.php";
    session_start();

    $row = [];
    $files = [];

    if(isset($_GET['id'])) {
        $note_id = (int)$_GET['id'];

        try {
            $query = "
                SELECT N.Title, N.Content AS Content, M.ID AS MateriaID, M.Nome AS Materia, A.Nome AS Argomento
                FROM Notes N
                LEFT JOIN Materia M ON M.ID = N.Materia_ID
                LEFT JOIN appunti_argomento AA ON AA.IDNote = N.ID
                LEFT JOIN Argomento A ON A.ID = AA.IDArgomento
                WHERE N.ID = :note_id
            ";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Recupera i file allegati
            $stmt_files = $conn->prepare("SELECT Original_filename, Stored_filename FROM Files WHERE Note_id = :note_id");
            $stmt_files->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt_files->execute();
            $files = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            die("Errore nel recupero dei dati: " . $e->getMessage());
        }
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <!-- Link CSS -->
    <link rel="stylesheet" href="../../css/notes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <link rel="stylesheet" href="../../css/fileVisualization.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
    <!-- Script JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/display/placeholder.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/selection/active-line.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/matchbrackets.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="floating-button" onclick="location.href='../home.php'">Home</button>
            <h1>Edit Note</h1>
        </div>

        <form action="update_note.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="hidden" name="note_id" value="<?= $note_id ?>">

                <input type="text" name="title" id="title" placeholder="Titolo..." value="<?=htmlspecialchars($row['Title'] ?? '')?>" required>

                <div class="container-materia">
                    <select name="materia" id="materia">
                        <?php
                            try {
                                $materie = $conn->query("SELECT ID, Nome FROM Materia")->fetchAll(PDO::FETCH_ASSOC);
                                foreach($materie as $materia) {
                                    $selected = ($materia['ID'] == ($row['MateriaID'] ?? null)) ? 'selected' : '';
                                    echo "<option value='".htmlspecialchars($materia['ID'])."' $selected>".htmlspecialchars($materia['Nome'])."</option>";
                                }
                            } catch(PDOException $e) {
                                die("Errore nel recupero delle materie: " . $e->getMessage());
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="editor-container">
                <textarea id="editor" name="content" placeholder="Scrivi la tua nota..."><?=htmlspecialchars($row['Content'] ?? '')?></textarea>
            </div>

            <div class="form-group">
                <input type="text" name="argomento" placeholder="Argomento..." class="tags-input" value="<?=htmlspecialchars($row['Argomento'] ?? '')?>">
                
                <input type="file" id="file_upload" name="file_upload[]" multiple accept=".pdf,.doc,.docx,.txt,image/*" hidden>
                <label for="file_upload" class="custom-file-upload">Seleziona file</label>

                <div class="btn">
                    <button type="submit"> Save </button>
                </div>
            </div>
            <div>
                <?php if (!empty($files)): ?>
                    <h3>Allegati Presenti</h3>
                    <div id="file-preview" class="file-preview">
                        <?php foreach ($files as $file): 
                            $filePath = "../../uploads/" . htmlspecialchars($file['Stored_filename']);
                            $fileName = htmlspecialchars($file['Original_filename']);
                            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                            $isPDF = strtolower($ext) === 'pdf';
                            $isTxt = strtolower($ext) === 'txt';
                        ?>
                            <div class="file-container">
                                <?php if ($isImage): ?>
                                    <img class="preview-image" src="<?=$filePath?>" onclick="openModal('<?=$filePath?>', 'image')">
                                <?php elseif ($isPDF): ?>
                                    <p><?=$fileName?></p>
                                    <canvas class="pdf-preview" data-pdf="<?=$filePath?>"></canvas>
                                <?php elseif ($isTxt): ?>
                                    <div class="file-icon" onclick="openText('<?=$filePath?>')"><?=$fileName?></div>
                                <?php else: ?>
                                    <div class="file-icon"><?=$fileName?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div id="new-file-preview" class="file-preview"></div>
            </div>
            <!-- Modal -->
            <div id="modal-viewer" class="modal-viewer" style="display:none;" onclick="closeModal()">
                <div class="modal-content" onclick="event.stopPropagation();">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <div id="modal-body"></div>
                </div>
            </div>
        </form>
    </div>
    <!-- Script -->
    <script src="../../js/CodeMirror.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="../../js/NewFiles.js"></script>
</body>
</html>