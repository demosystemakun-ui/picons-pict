@php
    $isEdit = $user->exists;
    $currentRole = old('role', $user->roles->first()->name ?? '');
    $input = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500';
@endphp

@csrf
@if ($isEdit) @method('PUT') @endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="{{ $input }}" required>
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $input }}" required>
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">
            Password @if ($isEdit) <span class="text-gray-400 font-normal">(kosongkan jika tidak diganti)</span> @endif
        </label>
        <input type="password" name="password" class="{{ $input }}" autocomplete="new-password" @required(!$isEdit)>
        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="{{ $input }}" autocomplete="new-password" @required(!$isEdit)>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Role</label>
        <select name="role" class="{{ $input }}" required>
            <option value="">-- Pilih Role --</option>
            @foreach ($roles as $r)
                <option value="{{ $r->name }}" @selected($currentRole == $r->name)>{{ $r->name }}</option>
            @endforeach
        </select>
        @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Departemen</label>
        <select name="department_id" class="{{ $input }}">
            <option value="">-- Tanpa Departemen --</option>
            @foreach ($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $user->department_id) == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
        @error('department_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex justify-end gap-2 mt-6">
    <a href="{{ route('users.index') }}" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700">Batal</a>
    <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
</div>