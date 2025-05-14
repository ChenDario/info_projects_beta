const previewForm = document.getElementById('preview-form');
const previewTitle = document.getElementById('preview-title');
const previewContent = document.getElementById('preview-content');

previewForm.addEventListener('submit', function(e) {
    previewTitle.value = document.getElementById('title').value;
    previewContent.value = editor.getValue(); // Assuming you initialized CodeMirror as "editor"
});