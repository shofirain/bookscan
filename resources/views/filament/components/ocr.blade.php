<div class="space-y-3">
    <button
        type="button"
        class="px-4 py-2 bg-primary-600 text-white rounded"
        @click="runBookOCR()"
    >
        Proses OCR
    </button>

    <div id="book-ocr-progress" class="hidden">
        <div class="w-full bg-gray-200 rounded">
            <div id="book-ocr-bar" class="bg-primary-600 h-2 rounded w-0"></div>
        </div>
        <p id="book-ocr-text" class="text-sm mt-1"></p>
    </div>
</div>
