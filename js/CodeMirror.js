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