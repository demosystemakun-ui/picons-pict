<?php
    $isEdit = $user->exists;
    $currentRole = old('role', $user->roles->first()->name ?? '');
    $input = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500';
?>

<?php echo csrf_field(); ?>
<?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Nama</label>
        <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="<?php echo e($input); ?>" required>
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="<?php echo e($input); ?>" required>
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">
            Password <?php if($isEdit): ?> <span class="text-gray-400 font-normal">(kosongkan jika tidak diganti)</span> <?php endif; ?>
        </label>
        <input type="password" name="password" class="<?php echo e($input); ?>" autocomplete="new-password" <?php if(!$isEdit): echo 'required'; endif; ?>>
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="<?php echo e($input); ?>" autocomplete="new-password" <?php if(!$isEdit): echo 'required'; endif; ?>>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Role</label>
        <select name="role" class="<?php echo e($input); ?>" required>
            <option value="">-- Pilih Role --</option>
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($r->name); ?>" <?php if($currentRole == $r->name): echo 'selected'; endif; ?>><?php echo e($r->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Departemen</label>
        <select name="department_id" class="<?php echo e($input); ?>">
            <option value="">-- Tanpa Departemen --</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($d->id); ?>" <?php if(old('department_id', $user->department_id) == $d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>

<div class="flex justify-end gap-2 mt-6">
    <a href="<?php echo e(route('users.index')); ?>" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700">Batal</a>
    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
</div><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/users/_form.blade.php ENDPATH**/ ?>