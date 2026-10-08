<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Reimbursement <?php echo e($reimbursement->number); ?></title>
    <style>
        @page { margin: 28px 30px; }
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; }
    </style>
</head>
<body>
    
<?php echo $__env->make('procurement.reimbursement._document', ['logo' => asset('logo/pict.png')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></body>
</html>
<?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/procurement/reimbursement/pdf.blade.php ENDPATH**/ ?>