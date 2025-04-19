window.addEventListener('DOMContentLoaded', () => {
    // Carica i PDF dopo il caricamento del DOM
    const pdfPreviews = document.querySelectorAll('.pdf-preview');
    pdfPreviews.forEach(canvas => {
        const url = canvas.dataset.pdf;
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(pdf => {
            pdf.getPage(1).then(page => {
                const viewport = page.getViewport({ scale: 1 });
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                page.render({ canvasContext: context, viewport: viewport });
            });
        });

        canvas.addEventListener('click', () => {
            openModal(url, 'pdf');
        });
    });
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
        fetch(fileUrl)
            .then(response => response.text())
            .then(text => {
                const pre = document.createElement('pre');
                pre.textContent = text;
                modalBody.appendChild(pre);
            });
    }
}

function openText(fileUrl) {
    openModal(fileUrl, 'txt');
}

function closeModal() {
    document.getElementById('modal-viewer').style.display = 'none';
    document.body.classList.remove('modal-open');
}
