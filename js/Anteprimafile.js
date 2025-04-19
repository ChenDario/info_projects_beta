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

// File Preview
document.getElementById('file_upload').addEventListener('change', function(event) {
    const preview = document.getElementById('file-preview');
    preview.innerHTML = '';
    const files = event.target.files;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileType = file.type;
        const fileName = file.name;

        const fileContainer = document.createElement('div');
        fileContainer.classList.add('file-container');

        if (fileType.startsWith('image/')) {
            const img = document.createElement('img');
            img.classList.add('preview-image');
            img.file = file;
            img.style.cursor = 'pointer';

            img.addEventListener('click', () => {
                openModal(URL.createObjectURL(file), 'image');
            });

            fileContainer.appendChild(img);
            const reader = new FileReader();
            reader.onload = (function(aImg) {
                return function(e) {
                    aImg.src = e.target.result;
                };
            })(img);
            reader.readAsDataURL(file);

        } else if (fileType === 'application/pdf') {
            const label = document.createElement('p');
            label.textContent = fileName;
            fileContainer.appendChild(label);

            const canvas = document.createElement('canvas');
            canvas.classList.add('pdf-preview');
            fileContainer.appendChild(canvas);

            const reader = new FileReader();
            reader.onload = function(e) {
                const typedarray = new Uint8Array(e.target.result);
                pdfjsLib.getDocument(typedarray).promise.then(pdf => {
                    pdf.getPage(1).then(page => {
                        const viewport = page.getViewport({ scale: 1 });
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        page.render(renderContext);
                    });
                });

                fileContainer.addEventListener('click', () => {
                    openModal(URL.createObjectURL(file), 'pdf');
                });
            };
            reader.readAsArrayBuffer(file);

        } else if (file.name.endsWith('.txt')) {
            const icon = document.createElement('div');
            icon.classList.add('file-icon');
            icon.textContent = file.name;
            fileContainer.appendChild(icon);

            fileContainer.addEventListener('click', () => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    openModal(e.target.result, 'txt');
                };
                reader.readAsText(file);
            });

        } else {
            const icon = document.createElement('div');
            icon.classList.add('file-icon');
            icon.textContent = file.name;
            fileContainer.appendChild(icon);
        }

        preview.appendChild(fileContainer);
    }
});

function openModal(fileUrl, type) {
    const modal = document.getElementById('modal-viewer');
    const modalBody = document.getElementById('modal-body');
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    modalBody.innerHTML = '';

    if (type === 'image') {
        const img = document.createElement('img');
        img.src = fileUrl;
        img.style.maxWidth = '90vw';
        img.style.maxHeight = '90vh';
        modalBody.appendChild(img);

    } else if (type === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = fileUrl;
        iframe.style.width = '90vw';
        iframe.style.height = '90vh';
        iframe.style.border = 'none';
        modalBody.appendChild(iframe);

    } else if (type === 'txt') {
        const pre = document.createElement('pre');
        pre.textContent = fileUrl;
        modalBody.appendChild(pre);
    }
}

function closeModal() {
    document.getElementById('modal-viewer').style.display = 'none';
    document.body.classList.remove('modal-open'); // AGGIUNGI QUESTA LINEA
}
