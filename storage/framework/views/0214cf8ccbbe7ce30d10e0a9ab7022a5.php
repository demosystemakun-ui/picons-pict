<?php $__env->startSection('title', 'Procurement Requests'); ?>
<?php $__env->startSection('page-title', 'Procurement Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    
    <?php if(session('success')): ?>
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 dark:bg-emerald-950/30 dark:border-emerald-800/50 dark:text-emerald-300 shadow-sm transition">
            <div class="p-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <p class="text-sm font-medium"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Daftar Procurement Request</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola persetujuan dan pantau status dokumen pengadaan barang & jasa.</p>
        </div>
        <a href="<?php echo e(route('procurement.create')); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 active:bg-teal-800 rounded-xl shadow-sm transition-all focus:ring-2 focus:ring-teal-500/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Request
        </a>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50/75 dark:bg-gray-900/40 text-xs uppercase font-semibold text-gray-700 dark:text-gray-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-4">No. Dokumen</th>
                        <th class="px-5 py-4">Tanggal</th>
                        <th class="px-5 py-4">Request By</th>
                        <th class="px-5 py-4">Divisi</th>
                        <th class="px-5 py-4">Deskripsi</th>
                        <th class="px-4 py-4 text-center">Qty</th>
                        <th class="px-4 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusColors = [
                                'pending'  => 'bg-amber-50 border border-amber-200/60 text-amber-700 dark:bg-amber-950/40 dark:border-amber-900/40 dark:text-amber-300',
                                'approved' => 'bg-emerald-50 border border-emerald-200/60 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-900/40 dark:text-emerald-300',
                                'rejected' => 'bg-rose-50 border border-rose-200/60 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900/40 dark:text-rose-300',
                                'revision' => 'bg-orange-50 border border-orange-200/60 text-orange-700 dark:bg-orange-950/40 dark:border-orange-900/40 dark:text-orange-300',
                            ];
                            $statusLabels = [
                                'pending'  => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'revision' => 'Perlu Revisi',
                            ];
                            $currStatus = $r->status ?? 'pending';
                        ?>
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/40 transition">
                            <td class="px-5 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap"><?php echo e($r->no); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap"><?php echo e($r->date->format('d/m/Y')); ?></td>
                            <td class="px-5 py-4"><?php echo e($r->request_by); ?></td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700/70 text-gray-700 dark:text-gray-300">
                                    <?php echo e($r->division); ?>

                                </span>
                            </td>
                            <td class="px-5 py-4 max-w-xs truncate"><?php echo e($r->description); ?></td>
                            <td class="px-4 py-4 text-center font-semibold text-gray-900 dark:text-white"><?php echo e($r->quantity); ?></td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full <?php echo e($statusColors[$currStatus] ?? $statusColors['pending']); ?>">
                                    <?php echo e($statusLabels[$currStatus] ?? ucfirst($currStatus)); ?>

                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    
                                    <button type="button" 
                                            onclick="openActionModal('<?php echo e(route('procurement.status', $r)); ?>', 'approved', '<?php echo e($r->no); ?>')" 
                                            class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/50 rounded-lg transition" 
                                            title="Setujui Pengajuan">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    </button>

                                    <button type="button" 
                                            onclick="openActionModal('<?php echo e(route('procurement.status', $r)); ?>', 'revision', '<?php echo e($r->no); ?>')" 
                                            class="p-1.5 text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/50 rounded-lg transition" 
                                            title="Minta Revisi">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    </button>

                                    <button type="button" 
                                            onclick="openActionModal('<?php echo e(route('procurement.status', $r)); ?>', 'rejected', '<?php echo e($r->no); ?>')" 
                                            class="p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/50 rounded-lg transition" 
                                            title="Tolak Pengajuan">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>

                                    <span class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></span>

                                    
                                    <a href="<?php echo e(route('procurement.comparisons.index', $r)); ?>" class="p-1.5 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50 rounded-lg transition" title="Vendor Comparison">
                                        <i data-lucide="git-compare" class="w-4 h-4"></i>
                                    </a>

                                    
                                    <a href="<?php echo e(route('procurement.show', $r)); ?>" class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 rounded-lg transition" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    <button type="button" 
                                            onclick="printDirect('<?php echo e(route('procurement.print', $r)); ?>')" 
                                            class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/50 rounded-lg transition" 
                                            title="Print Form">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </button>

                                    <a href="<?php echo e(route('procurement.pdf', $r)); ?>" class="p-1.5 text-teal-600 hover:text-teal-700 hover:bg-teal-50 dark:text-teal-400 dark:hover:bg-teal-950/50 rounded-lg transition" title="Simpan PDF">
                                        <i data-lucide="file-down" class="w-4 h-4"></i>
                                    </a>

                                    <a href="<?php echo e(route('procurement.edit', $r)); ?>" class="p-1.5 text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/50 rounded-lg transition" title="Edit Data">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center text-gray-500 dark:text-gray-400">
                                <i data-lucide="inbox" class="w-9 h-9 mx-auto mb-2 text-gray-400 dark:text-gray-500"></i>
                                Belum ada data permohonan pengadaan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($requests->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <?php echo e($requests->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>


<div id="actionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeActionModal()"></div>

        
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200/80 dark:border-gray-700">
            <form id="actionModalForm" action="" method="POST" class="p-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <input type="hidden" name="status" id="modalStatusInput" value="">

                <div class="flex items-start gap-4">
                    
                    <div id="modalIconContainer" class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl">
                        <i id="modalIcon" data-lucide="help-circle" class="w-6 h-6"></i>
                    </div>

                    <div class="flex-1">
                        <h3 id="modalTitle" class="text-base font-bold text-gray-900 dark:text-white leading-6"></h3>
                        <p id="modalDocNumber" class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5"></p>
                    </div>
                </div>

                
                <div id="notesContainer" class="mt-5 space-y-1.5">
                    <label for="review_notes" id="notesLabel" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Catatan & Alasan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="review_notes" id="review_notes" rows="4" 
                        class="w-full text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-teal-500 focus:bg-white dark:focus:bg-gray-700 focus:outline-none transition shadow-inner" 
                        placeholder="Berikan alasan atau instruksi yang jelas..."></textarea>
                </div>

                
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeActionModal()" class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="modalSubmitBtn" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl text-white shadow-sm transition">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function printDirect(url) {
    let iframe = document.getElementById('printFrame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'printFrame';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }
    
    iframe.src = url;
    iframe.onload = function() {
        setTimeout(function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 300);
    };
}

function openActionModal(actionUrl, actionType, docNo) {
    const modal     = document.getElementById('actionModal');
    const form      = document.getElementById('actionModalForm');
    const title     = document.getElementById('modalTitle');
    const docText   = document.getElementById('modalDocNumber');
    const input     = document.getElementById('modalStatusInput');
    const btn       = document.getElementById('modalSubmitBtn');
    const iconCont  = document.getElementById('modalIconContainer');
    const icon      = document.getElementById('modalIcon');
    const notesCont = document.getElementById('notesContainer');
    const notesInput= document.getElementById('review_notes');
    const notesLabel= document.getElementById('notesLabel');

    form.action = actionUrl;
    input.value = actionType;
    docText.innerText = 'No. Dokumen: ' + docNo;
    modal.classList.remove('hidden');

    if (actionType === 'approved') {
        title.innerText = 'Persetujuan Pengadaan';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400';
        icon.setAttribute('data-lucide', 'check-circle-2');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition';
        btn.innerText = 'Ya, Setujui';
        
        notesLabel.innerHTML = 'Catatan Tambahan <span class="text-xs text-gray-400 normal-case font-normal">(Opsional)</span>';
        notesInput.required = false;
        notesInput.placeholder = 'Tambahkan catatan jika diperlukan...';
    } 
    else if (actionType === 'revision') {
        title.innerText = 'Minta Revisi Pengadaan';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400';
        icon.setAttribute('data-lucide', 'rotate-ccw');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition';
        btn.innerText = 'Kirim Revisi';
        
        notesLabel.innerHTML = 'Instruksi Revisi <span class="text-rose-500">*</span>';
        notesInput.required = true;
        notesInput.placeholder = 'Jelaskan bagian mana yang perlu diperbaiki/dilengkapi pemohon...';
    } 
    else if (actionType === 'rejected') {
        title.innerText = 'Tolak Pengajuan Pengadaan';
        iconCont.className = 'flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400';
        icon.setAttribute('data-lucide', 'x-circle');
        btn.className = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition';
        btn.innerText = 'Tolak Pengajuan';
        
        notesLabel.innerHTML = 'Alasan Penolakan <span class="text-rose-500">*</span>';
        notesInput.required = true;
        notesInput.placeholder = 'Jelaskan alasan pengajuan ini ditolak...';
    }

    if (window.lucide) {
        lucide.createIcons();
    }
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
    document.getElementById('review_notes').value = '';
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/procurement/index.blade.php ENDPATH**/ ?>