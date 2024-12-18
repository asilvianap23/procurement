@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4 text-primary font-weight-bold">
            Edit Peran
        </h1>
        <hr class="my-4 border-top border-primary">
        
        <form action="{{ route('user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label for="password">Password (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password baru jika ingin mengubahnya" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Masukkan ulang password baru" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="prodi_id">Prodi</label>
                <select name="prodi_id" class="form-control select2">
                    <option value="">Pilih Prodi</option>
                    @foreach($prodis as $prodi)
                        <option value="{{ $prodi->id }}" {{ $prodi->id == $user->prodi_id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="roles">Roles</label>
                <select name="roles[]" class="form-control select2" multiple required>
                    <option value="">Pilih Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ in_array($role->name, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
@endsection