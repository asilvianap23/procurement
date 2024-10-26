@extends('admin.layouts.app')

@section('title')
Buat Pengajuan
@endsection

@section('content')
    <div class="container">
        <h1>Tambah Pengajuan Baru</h1>
        <form action="{{ route('pengajuan.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="prodi">Prodi/Unit/</label>
                <select class="form-control" id="prodi" name="prodi" required>
                    @foreach($prodi as $item)
                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                    @endforeach
                </select>
                @error('prodi')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" value="{{ old('judul') }}" required>
                @error('judul')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="edisi">Edisi</label>
                <input type="text" class="form-control" id="edisi" name="edisi" value="{{ old('edisi') }}">
                @error('edisi')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="{{ old('isbn') }}">
                @error('isbn')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="penerbit">Penerbit</label>
                <input type="text" class="form-control" id="penerbit" name="penerbit" value="{{ old('penerbit') }}">
                @error('penerbit')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="author">Penulis</label>
                <input type="text" class="form-control" id="author" name="author" value="{{ old('author') }}" required>
                @error('author')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="tahun">Tahun Terbit</label>
                <input type="number" class="form-control" id="tahun" name="tahun" value="{{ old('tahun') }}">
                @error('tahun')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <div class="form-group">
                <label for="eksemplar">Jumlah Eksemplar</label>
                <input type="number" class="form-control" id="eksemplar" name="eksemplar" value="{{ old('eksemplar') }}" required>
                @error('eksemplar')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
@endsection