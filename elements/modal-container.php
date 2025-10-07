<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-[10000] hidden">
    <div class="relative">
        <img id="modalImage" src="" class="max-h-[90vh] max-w-[90vw] rounded shadow-2xl" alt="Full view">
        <button onclick="closeImageModal()" class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white rounded-full p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>