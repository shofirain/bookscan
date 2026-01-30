window.bookOcrWorker = null;

window.initBookOcrWorker = async function () {
    if (!window.bookOcrWorker) {
        window.bookOcrWorker = await Tesseract.createWorker('ind+eng', 1, {
            logger: m => updateBookOcrProgress(m),
        });
    }
    return window.bookOcrWorker;
};

window.updateBookOcrProgress = function (m) {
    const bar = document.getElementById('book-ocr-bar');
    const text = document.getElementById('book-ocr-text');

    if (!bar || !text) return;

    if (m.status === 'recognizing text') {
        const p = Math.round(m.progress * 100);
        bar.style.width = p + '%';
        text.textContent = `Membaca teks... ${p}%`;
    } else {
        text.textContent = m.status;
    }
};

window.findBookImages = function () {
    const images = [];

    document
        .querySelectorAll('img[src^="blob:"]')
        .forEach(img => {
            images.push(img.src);
        });

    console.log('OCR Images:', images);
    return images;
};

window.runBookOCR = async function () {
    const images = findBookImages();

    if (!images.length) {
        alert('Cover buku belum diupload');
        return;
    }

    document
        .getElementById('book-ocr-progress')
        ?.classList.remove('hidden');

    const worker = await initBookOcrWorker();
    let combinedText = '';

    for (const img of images) {
        const result = await worker.recognize(img);
        combinedText += '\n' + result.data.text;
    }

    const textarea =
        document.querySelector('textarea[name="ocr_result"]') ||
        document.querySelector('[wire\\:model$="ocr_result"]');

    if (textarea) {
        textarea.value = combinedText.trim();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    document
        .getElementById('book-ocr-progress')
        ?.classList.add('hidden');
};

