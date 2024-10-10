@extends('layouts.app')

@section('title')
Pengajuan
@endsection

@push('style-page')
    <style>
        .custom-select{
            width: 70px !important;
        }
    </style>
@endpush

@section('content')
    <div>
        <h1>Daftar Pengajuan</h1>
        <div class="d-flex justify-content-between mb-2">
            <div>
                @if(session('import_errors'))
                    <div class="alert alert-danger">
                        <ul>
                            @foreach(session('import_errors') as $failure)
                                <li>{{ $failure->errors()[0] }} di baris {{ $failure->row() }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="mb-2">
                    <a class="btn btn-outline-primary" href="#" data-toggle="modal" data-target="#tambahPengajuanModal">
                        Tambah Pengajuan
                        <i class="fas fa-plus"></i>
                    </a>
                    <a type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#uploadModal">
                        Upload Excel
                        <i class="fa fa-upload"></i>
                    </a>
                    <a type="button" class="btn btn-outline-info" href="{{ route('pengajuan.index').'?export=true' }}">
                        Export Excel
                        <i class="fa fa-download"></i>
                    </a>
                </div>
            </div>
            <div class="d-flex">
                <div class="form-group mr-2">
                    <select id="filter-tahun" class="form-select select2" onchange="filterByYear()">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <select id="filter-prodi" class="form-select select2" onchange="filterByProdi()">
                        <option value="">Semua Prodi</option>
                        @foreach($prodi as $p)
                            <option value="{{ $p->id }}" {{ request('prodi') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <table class="table mt-4" id="customers">
            <thead>
            <tr>
                <th>No</th>
                <th>Prodi</th>
                <th>ISBN</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Tanggal Pengajuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @foreach($pengajuan as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->prodi->nama }}</td>
                    <td>
                        {{--data ini pernah diajukan di tahun --}}
                        @if($item->is_diajukan)
                            {{ $item->isbn }} <span class="badge badge-info">Pernah diajukan tahun  {{ \Illuminate\Support\Carbon::parse($item->date_pernah_diajukan)->format('d-m-Y')  }}</span>
                        @else
                            {{ $item->isbn }}
                        @endif
                    </td>
                    <td>{{ $item->judul }}</td>
                    <td>{{ $item->author }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($item->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}</td>
                    <td>
                        @if($item->is_approve)
                            <span class="badge badge-success">Disetujui Perpustakaan</span>
                        @elseif($item->is_reject)
                            <span class="badge badge-danger">Ditolak</span>
                        @else
                            <span class="badge badge-warning">Proses</span>
                        @endif
                    <td>
                        @can('view pengajuan')
                        <a href="{{ route('pengajuan.show', $item->id) }}" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="View">
                            <i class="fas fa-binoculars"></i>
                        </a>
                        @endcan
                        @if(!$item->is_approve && !$item->is_reject)
                            @can('edit pengajuan')
                            <a href="#" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPengajuanModal" data-id="{{ $item->id }}" data-prodi="{{ $item->prodi->id }}" data-judul="{{ $item->judul }}" data-edisi="{{ $item->edisi }}" data-isbn="{{ $item->isbn }}" data-penerbit="{{ $item->penerbit }}" data-author="{{ $item->author }}" data-tahun="{{ $item->tahun }}" data-eksemplar="{{ $item->eksemplar }}" data-toggle="tooltip" data-placement="top" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @can('delete pengajuan')
                            <form action="{{ route('pengajuan.destroy', $item->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')" data-toggle="tooltip" data-placement="top" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endcan
                            @can('approve pengajuan')
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal" data-all="{{ $item }}" data-id="{{ $item->id }}" data-jumlah="{{ $item->eksemplar }}" data-toggle="tooltip" data-placement="top" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endcan
                        @endif
                    </td>
                </tr>
                
            @endforeach
            </tbody>
        </table>

        @include('component.edit-modal')
        <!-- Include the Modal Component -->
        @include('component.tambah-pengajuan-modal')
        <!-- Single Approve Modal -->
        @include('component.approve-modal')
        <!-- Upload Modal -->
        @include('component.upload-modal')
    </div>
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#customers').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
            });
        });

        $('#approveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var allData = button.data('all');
            var modal = $(this);
            var form = modal.find('#approveForm');
            var actionUrl = '{{ route('pengajuan.storeApproval', ':id') }}'.replace(':id', id);
            form.attr('action', actionUrl);

            modal.find('.modal-body #nama_prodi').val(allData.nama_prodi);
            modal.find('.modal-body #isbn').val(allData.isbn);
            modal.find('.modal-body #judul').val(allData.judul);
            modal.find('.modal-body #penerbit').val(allData.penerbit);
            modal.find('.modal-body #tahun').val(allData.tahun);
            modal.find('.modal-body #eksemplar').val(allData.eksemplar);
        });

        $('#approveForm').on('submit', function (event) {
            event.preventDefault();

            var form = $(this);
            var actionUrl = form.attr('action');
            var formData = form.serialize();


            var action = $(this).find('button[type="submit"][clicked=true]').val();
            var reason = $('#reason').val();

            // masukan action ke form
            formData += '&action=' + action;
            

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    }).then(() => {
                        $('#approveModal').modal('hide');
                        location.reload();
                    });
                },
                error: function (xhr, status, error) {
                    var response = JSON.parse(xhr.responseText);
                    var errorMessage = response.message || 'An error occurred while approving the pengajuan.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                }
            });
        });

        $('#approveForm button[type="submit"]').on('click', function() {
            $('#approveForm button[type="submit"]').removeAttr('clicked');
            $(this).attr('clicked', 'true');
        });

        $('#editPengajuanModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var prodi = button.data('prodi');
            var judul = button.data('judul');
            var edisi = button.data('edisi');
            var isbn = button.data('isbn');
            var penerbit = button.data('penerbit');
            var author = button.data('author');
            var tahun = button.data('tahun');
            var eksemplar = button.data('eksemplar');
            var modal = $(this);
            var form = modal.find('#editPengajuanForm');
            var actionUrl = '{{ route('pengajuan.update', ':id') }}'.replace(':id', id);
            form.attr('action', actionUrl);
            modal.find('.modal-body #prodi').val(prodi);
            modal.find('.modal-body #judul').val(judul);
            modal.find('.modal-body #edisi').val(edisi);
            modal.find('.modal-body #isbn').val(isbn);
            modal.find('.modal-body #penerbit').val(penerbit);
            modal.find('.modal-body #author').val(author);
            modal.find('.modal-body #tahun').val(tahun);
            modal.find('.modal-body #eksemplar').val(eksemplar);
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 when the modal is shown
            $('#tambahPengajuanModal').on('shown.bs.modal', function () {
                $('.select2').select2({
                    placeholder: "Pilih opsi",
                    allowClear: true,
                    dropdownParent: $('#tambahPengajuanModal') // Ensure the dropdown is appended to the modal
                });
            });

            $('#editPengajuanModal').on('shown.bs.modal', function () {
                $('.select2').select2({
                    placeholder: "Pilih opsi",
                    allowClear: true,
                    dropdownParent: $('#editPengajuanModal') // Ensure the dropdown is appended to the modal
                });
            });

        });
        function filterByYear() {
            var year = $('#filter-tahun').val(); // Ambil tahun yang dipilih
            var url = new URL(window.location.href); // Ambil URL saat ini
            url.searchParams.set('year', year); // Set parameter tahun
            window.location.href = url; // Reload halaman dengan parameter baru
        }
        function filterByProdi() {
            var prodi = $('#filter-prodi').val(); // Ambil prodi yang dipilih
            var url = new URL(window.location.href); // Ambil URL saat ini
            url.searchParams.set('prodi', prodi); // Set parameter prodi
            window.location.href = url; // Reload halaman dengan parameter baru
        }
    </script>
    @if ($errors->any())
        <script type="text/javascript">
            $(document).ready(function() {
                $('#tambahPengajuanModal').modal('show');
            });
        </script>
    @endif
@endpush
