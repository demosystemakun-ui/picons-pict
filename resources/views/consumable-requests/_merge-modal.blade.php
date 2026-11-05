{{-- resources/views/consumable-requests/_merge-modal.blade.php --}}
<div id="mergeModal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeMergeModal()"></div>

    <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Merge into Procurement Request (PR)</h3>
                <button type="button" onclick="closeMergeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">PR Number (Optional)</label>
                    <input type="text" id="mergePrNo" placeholder="e.g. 01/PR/OPS/PICT-XI-2026" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Background</label>
                    <textarea id="mergeBackground" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm px-3 py-2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Purpose</label>
                    <textarea id="mergePurpose" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm px-3 py-2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Required Spec</label>
                    <textarea id="mergeSpec" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm px-3 py-2"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2 bg-gray-50 dark:bg-gray-800/50">
                <button type="button" onclick="closeMergeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" onclick="processMerge()" class="px-4 py-2 text-sm font-medium text-white bg-slate-900 rounded-lg hover:bg-slate-800">Confirm & Merge</button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeMergeModal() {
        document.getElementById('mergeModal').classList.add('hidden');
    }

    function processMerge() {
        const no = document.getElementById('mergePrNo').value;
        const background = document.getElementById('mergeBackground').value;
        const purpose = document.getElementById('mergePurpose').value;
        const required_spec = document.getElementById('mergeSpec').value;

        closeMergeModal();

        // Kirim data ke form tersembunyi di index.blade.php
        window.dispatchEvent(new CustomEvent('pr-confirm', {
            detail: { no, background, purpose, required_spec }
        }));
    }

    // Dengarkan event 'pr-open' dari tombol Merge di index.blade.php
    window.addEventListener('pr-open', function () {
        document.getElementById('mergeModal').classList.remove('hidden');
    });
</script>