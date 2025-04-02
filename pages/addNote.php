<?php
    include "db.php";
    session_start();

    $stmt = $conn->prepare("SELECT * FROM Materia");
    $stmt->execute();
    $result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <!--Link CSS-->
    <link rel="stylesheet" href="../css/notes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Note</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/display/placeholder.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/selection/active-line.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/matchbrackets.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Adding Note</h1>
        <form action="save_note.php" method="post">
            <div class="form-group">
                <input type="text" name="title" placeholder="Titolo..." required>
                <select name="materia" id="materia">
                    <?php
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['ID']}'> {$row['Nome']} </option>";
                        }
                    ?>
                </select>
            </div>
            <div class="editor-container">
                <textarea id="editor" name="content" placeholder="Scrivi la tua nota..." ></textarea>
            </div>
            <div class="form-group">
                <input type="text" name="tags" placeholder="Aggiungi tag (#tag1 #tag2)" class="tags-input">
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
