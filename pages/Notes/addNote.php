<?php
    include "../../includes/db.php";
    session_start();

    try {
        $stmt = $conn->query("SELECT * FROM Materia");
        $materie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Errore nel recupero delle materie: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note</title>
    <!-- Link CSS  General Structure -->
    <link rel="stylesheet" href="../../css/notes.css">
    <link rel="stylesheet" href="../../css/fileVisualization.css">
    <!-- Link CSS CodeMirror -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/display/placeholder.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/selection/active-line.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="floating-button" onclick="location.href='../home.php'">Home</button>
            <h1>Adding Note</h1>
        </div>

        <form action="save_note.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="text" name="title" id="title" placeholder="Titolo..." required>
                <div class="container-materia">
                    <select name="materia" id="materia">
                        <?php foreach($materie as $materia): ?>
                            <option value="<?=htmlspecialchars($materia['ID'])?>"><?=htmlspecialchars($materia['Nome'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="editor-container">
                <textarea id="editor" name="content" placeholder="Scrivi la tua nota..."></textarea>
            </div>
            <div class="form-group">
                <input type="text" name="argomento" placeholder="Argomento..." class="tags-input">
                
                <input type="file" id="file_upload" name="file_upload[]" multiple accept=".pdf,.doc,.docx,.txt,image/*" hidden>
                <label for="file_upload" class="custom-file-upload">Seleziona file</label>

                <div class="btn">
                    <button type="submit"> Save </button>
                </div>
            </div>
            <div id="file-preview" class="file-preview"></div>
            <!-- Modal Viewer -->
            <div id="modal-viewer" class="modal-viewer" style="display:none;" onclick="closeModal()">
                <div class="modal-content" onclick="event.stopPropagation();">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <div id="modal-body"></div>
                </div>
            </div>
        </form>
    </div>

    <!-- Script CodeMirror (textarea) -->
    <script src="../../js/CodeMirror.js"></script>
    <!-- Script File Anteprima -->
    <script src="../../js/Anteprimafile.js"></script>
</body>
</html>