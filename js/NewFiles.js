document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('file_upload');
    const newPreview = document.getElementById('new-file-preview');

    fileInput.addEventListener('change', function (event) {
        const files = event.target.files;

        newPreview.innerHTML = ''; // Rimuove solo i nuovi caricamenti

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileType = file.type;
            const fileName = file.name;

            const fileContainer = document.createElement('div');
            fileContainer.classList.add('file-container');

            if (fileType.startsWith('image/')) {
                const img = document.createElement('img');
                img.classList.add('preview-image');
                img.style.cursor = 'pointer';

                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                img.addEventListener('click', () => {
                    openModal(img.src, 'image');
                });

                fileContainer.appendChild(img);

            } else if (fileType === 'application/pdf') {
                const label = document.createElement('p');
                label.textContent = fileName;
                fileContainer.appendChild(label);

                const canvas = document.createElement('canvas');
                canvas.classList.add('pdf-preview');
                fileContainer.appendChild(canvas);

                const reader = new FileReader();
                reader.onload = function (e) {
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

            } else if (fileName.endsWith('.txt')) {
                const icon = document.createElement('div');
                icon.classList.add('file-icon');
                icon.textContent = fileName;
                fileContainer.appendChild(icon);

                fileContainer.addEventListener('click', () => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        openModal(e.target.result, 'txt');
                    };
                    reader.readAsText(file);
                });

            } else {
                const icon = document.createElement('div');
                icon.classList.add('file-icon');
                icon.textContent = fileName;
                fileContainer.appendChild(icon);
            }

            newPreview.appendChild(fileContainer);
        }
    });
});
