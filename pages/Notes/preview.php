<?php
    session_start();
    $title = $_POST['title'] ?? 'Titolo non disponibile';
    $content = $_POST['content'] ?? 'Contenuto non disponibile';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <!-- Link CSS -->
    <link rel="stylesheet" href="../../css/noteDetail.css">
    <link rel="stylesheet" href="../../css/fileVisualization.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Preview </title>
</head>
<body>
    <div class="container">
        <h1 id="pageTitle"><?= htmlspecialchars($title) ?></h1>
        <div class="note-content">
            <p><?=nl2br($content)?></p>    
        </div>
    </div>

    <!-- Script CodeMirror (textarea) -->
    <script src="../../js/CodeMirror.js"></script>
    <!-- Script File Anteprima -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="../../js/FileVisualization.js"></script>
    <!-- MathJax: supporto LaTeX -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async
    src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
</body>
</html>
