@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Activity Logs</h1>
        <table class="table mt-4" id="customers">
            <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Model</th>
                <th>Timestamp</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($activityLogs as $log)
                @php
                    // Menetapkan kelas berdasarkan aksi
                    $actionClass = '';
                    if (stripos($log->action, 'Mengubah') !== false) {
                        $actionClass = 'text-warning';
                    } elseif (stripos($log->action, 'Membuat') !== false || stripos($log->action, 'Menambah Approve Keuangan') !== false) {
                        $actionClass = 'text-primary'; // Ini mencakup juga aksi menambah approve keuangan
                    } elseif (stripos($log->action, 'Menyetujui') !== false) {
                        $actionClass = 'text-success';
                    } elseif (stripos($log->action, 'Menolak') !== false) {
                        $actionClass = 'text-danger';
                    } elseif (stripos($log->action, 'Menghapus') !== false) {
                        $actionClass = 'text-danger';
                    } elseif (stripos($log->action, 'Import') !== false) {
                        $actionClass = 'text-info';
                    }
                @endphp
                <tr>
                    <td class="{{ $actionClass }}">{{ $log->id }}</td>
                    <td class="{{ $actionClass }}">{{ $log->user->name ?? 'N/A' }}</td>
                    <td class="{{ $actionClass }}">{{ $log->action }}</td>
                    <td class="{{ $actionClass }}">
                        @if($log->data)
                            {{ class_basename($log->data) }}
                        @else
                            {{ class_basename($log->model) }}
                        @endif
                    </td>
                    <td class="{{ $actionClass }}">
                        {{ \Illuminate\Support\Carbon::parse($log->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y H:i:s') }}
                    </td>                    
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('style-page')
    <style>
        .text-success { color: green !important; }
        .text-warning { color: orange !important; }
        .text-danger { color: red !important; }
        .text-primary { color: blue !important; }
        .text-info { color: deepskyblue !important; }
    </style>
@endpush

@push('script-page')
    <script>
        $(document).ready(function() {
            $('#customers').DataTable();
        });
    </script>
@endpush
