@extends('user.layouts.app-user')

@section('title')
Realisasi
@endsection

@section('content')
    <div class="container-fluid text-center my-4">
        <h1 class="rekap-title">Rekap Realisasi</h1>
    </div>
    <div>
        <div class="d-flex justify-content-between mb-2">
            <div>
                <a type="button" class="btn btn-outline-info" href="{{ route('home-rekap', array_merge(request()->query(), ['export' => true])) }}">
                    Export Excel
                    <i class="fa fa-download"></i>
                </a>
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
            </div>
        </div>

        <table class="table mt-4" id="customers">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th>Tahun Terbit</th>
                    <th>Jumlah di Ajukan</th>
                    <th>Jumlah di Terima</th>
                    <th>Tahun Pengadaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengajuan as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->author }}</td>
                        <td>{{ $item->penerbit }}</td>
                        <td>{{ $item->tahun }}</td>
                        <td>{{ $item->eksemplar }}</td>
                        <td>{{ $item->diterima }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->approved_at)->format('Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#customers').DataTable({
                // Aktifkan fitur sorting, search, pagination, dan length
                "paging": true,         // Aktifkan pagination
                "lengthChange": true,   // Tampilkan dropdown untuk panjang data
                "searching": true,      // Aktifkan kolom pencarian
                "ordering": true,       // Aktifkan sorting kolom
                "info": true,           // Tampilkan informasi jumlah data
                "autoWidth": false,     // Nonaktifkan lebar otomatis
            });
        });

        function filterByYear() {
            var year = $('#filter-tahun').val(); // Ambil tahun yang dipilih
            var url = new URL(window.location.href); // Ambil URL saat ini
            url.searchParams.set('year', year); // Set parameter tahun
            window.location.href = url; // Reload halaman dengan parameter baru
        }

        $('#approveModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id'); // Extract info from data-* attributes
            var allData = button.data('all'); // Extract the entire item data
            var modal = $(this);
            var form = modal.find('#approveForm');
            var actionUrl = '{{ route('pengajuan.storeApproval', ':id') }}'.replace(':id', id); // Replace :id with the actual ID
            form.attr('action', actionUrl); // Set the form action dynamically

            // Populate the modal fields
            modal.find('.modal-body #prodi').val(allData.prodi);
            modal.find('.modal-body #isbn').val(allData.isbn);
            modal.find('.modal-body #judul').val(allData.judul);
            modal.find('.modal-body #penerbit').val(allData.penerbit);
            modal.find('.modal-body #tahun').val(allData.tahun);
            modal.find('.modal-body #edisi').val(allData.edisi);
            modal.find('.modal-body #eksemplar').val(allData.eksemplar);
            modal.find('.modal-body #diterima').val(allData.diterima);
        });

        $('#approveForm').on('submit', function (event) {
            event.preventDefault(); // Prevent the default form submission

            var form = $(this);
            var actionUrl = form.attr('action');
            var formData = form.serialize();

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                success: function (response) {
                    // Handle success response
                    alert('Pengajuan approved successfully!');
                    $('#approveModal').modal('hide');
                    location.reload(); // Reload the page to reflect changes
                },
                error: function (xhr, status, error) {
                    // Handle error response
                    alert('An error occurred while approving the pengajuan.');
                }
            });
        });
        function filterByProdi() {
            var prodi = $('#filter-prodi').val(); // Ambil prodi yang dipilih
            var url = new URL(window.location.href); // Ambil URL saat ini
            url.searchParams.set('prodi', prodi); // Set parameter prodi
            window.location.href = url; // Reload halaman dengan parameter baru
        }
    </script>
@endpush
