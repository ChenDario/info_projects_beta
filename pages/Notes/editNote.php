<?php
    include "../../includes/db.php";
    session_start();

    $row = "";
    if(isset($_GET['id'])) {
        $note_id = (int)$_GET['id'];

        $query = "
            SELECT N.Title, N.Content AS Content, M.ID AS MateriaID, M.Nome AS Materia, A.Nome AS Argomento
            FROM Notes N
            LEFT JOIN Materia M ON M.ID = N.Materia_ID
            LEFT JOIN appunti_argomento AA ON AA.IDNote = N.ID
            LEFT JOIN Argomento A ON A.ID = AA.IDArgomento
            WHERE N.ID = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="../../css/notes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
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

        <form action="update_note.php" method="post">
            <div class="form-group">
                <input type="hidden" name="note_id" value="<?= $note_id ?>">

                <input type="text" name="title" id="title" placeholder="Titolo..." value="<?=$row['Title']?>" required>

                <div class="container-materia">
                    <select name="materia" id="materia">
                        <?php
                            $materie_query = "SELECT ID, Nome FROM Materia";
                            $materie_result = $conn->query($materie_query);
                            while($materia = $materie_result->fetch_assoc()) {
                                $selected = ($materia['ID'] == ($row['MateriaID'] ?? null)) ? 'selected' : '';
                                echo "<option value='{$materia['ID']}' $selected> {$materia['Nome']} </option>";
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="editor-container">
                <textarea id="editor" name="content" placeholder="Scrivi la tua nota..."><?php echo htmlspecialchars($row['Content'] ?? '');?></textarea>
            </div>

            <div class="form-group">
                <input type="text" name="argomento" placeholder="Argomento..." class="tags-input"
                    value="<?php echo htmlspecialchars($row['Argomento'] ?? ''); ?>">
                <div class="btn">
                    <button type="submit"> Save </button>
                </div>
            </div>
        </form>

    </div>
    <script>
        var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
            mode: "text/x-php",
            lineNumbers: true,
            styleActiveLine: true,
            matchBrackets: true,
            autoCloseTags: true,
            placeholder: "Scrivi la tua nota...",
            theme: "default"
        });
        editor.setSize("100%", "100%");
    </script>
</body>
</html>
