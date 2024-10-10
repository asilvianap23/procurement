<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use App\Import\PengajuanImport;
use App\Exports\PengajuanExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PengajuanController extends Controller
{
	public function index(Request $request)
	{
		// Ambil tahun yang tersedia untuk filter
		$years = Pengajuan::selectRaw('YEAR(created_at) as year')
			->distinct()
			->pluck('year')
			->sort();
	
		// Ambil semua prodi untuk filter
		$prodi = Prodi::all();
	
		// Query dasar untuk menampilkan semua data pengajuan
		$pengajuanQuery = Pengajuan::haveProdi();
	
		// Jika ada filter tahun, tambahkan kondisi
		if ($request->filled('year')) {
			$pengajuanQuery->whereYear('created_at', $request->year);
		}
	
		// Jika ada filter prodi, tambahkan kondisi
		if ($request->filled('prodi')) {
			$pengajuanQuery->where('prodi_id', $request->prodi);
		}
	
		// Ambil semua pengajuan sesuai dengan query yang telah difilter
		$pengajuan = $pengajuanQuery->get();
	
		// Mapping untuk menambahkan detail dan menandai apakah sudah diajukan
		$pengajuan = $pengajuan->map(function ($item) {
			// Jika ISBN pernah diajukan, berikan tanda
			$item->is_diajukan = Pengajuan::where('isbn', $item->isbn)
				->where('isbn', '!=', null)
				->where('isbn', '!=', '-')
				->where('isbn', '!=', ' ')
				->where('prodi_id', $item->prodi_id)
				->count() > 1;
	
			if ($item->is_diajukan) {
				$item->date_pernah_diajukan = Pengajuan::where('isbn', $item->isbn)
					->orderBy('created_at', 'desc')
					->first()
					->created_at ?? null;
			}
	
			// Menambahkan informasi prodi
			$item->nama_prodi = $item->prodi->nama;
			$item->prodi_id = $item->prodi->id;
			return $item;
		});
	
		// Ekspor ke Excel jika diminta
		if ($request->has('export')) {
			$excelReport = new PengajuanExport($pengajuan);
			$fileName = 'daftar_pengajuan_' . date('Y-m-d_H-i-s') . '.xlsx';
			return Excel::download($excelReport, $fileName);
		}
	
		// Mengembalikan view dengan data yang diperlukan
		return view('pengajuan.index', compact('pengajuan', 'years', 'prodi'));
	}
	

    public function create()
    {
        // Menampilkan form untuk menambah pengajuan baru
		// $user = Auth::user();
		// $prodi = Prodi::when($user->prodi_id, function($query) use ($user){
		// 	return $query->where('id', $user->prodi_id);
 		//  });
		// return view('pengajuan.create', compact('prodi'));
        return view('pengajuan.create');
    }

    public function store(Request $request)
    {
        // Validasi input
	    $request->validate([
		    'prodi_id' => 'required|exists:prodi,id',
		    'judul' => 'required|max:255',
		    'edisi' => 'nullable|max:50',
		    'isbn' => 'nullable|max:20',
		    'penerbit' => 'nullable|max:100',
		    'author' => 'required|max:100',
		    'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
		    'eksemplar' => 'required|integer',
			'diterima' => 'nullable|integer',
			'harga' => 'nullable|numeric|min:0', // Tambahkan validasi untuk harga
	    ], [], [
		    'prodi_id' => 'Prodi',
		    'judul' => 'Judul',
		    'edisi' => 'Edisi',
		    'isbn' => 'ISBN',
		    'penerbit' => 'Penerbit',
		    'author' => 'Penulis',
		    'tahun' => 'Tahun',
		    'eksemplar' => 'Eksemplar',
			'diterima' => 'Diterima',
			'harga' => 'Harga',
	    ]);

        // Simpan data pengajuan
	    $pengajuan = Pengajuan::create($request->all());
	    
	    $this->setLogActivity('Membuat pengajuan', $pengajuan);
		
        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil ditambahkan.');
    }

    public function show(Pengajuan $pengajuan)
    {
		if(Auth::user()->can('view pengajuan')) {
	        // Menampilkan detail pengajuan tertentu
	        return view('pengajuan.show', compact('pengajuan'));
		}else{
			return redirect()->route('pengajuan.index')->with('error', 'Anda tidak memiliki akses untuk melihat detail pengajuan.');
		}
    }

    public function edit(Pengajuan $pengajuan)
    {
        // Menampilkan form untuk mengedit pengajuan
        return view('pengajuan.edit', compact('pengajuan'));
    }

    public function update(Request $request, Pengajuan $pengajuan)
    {
        // Validasi input
        $request->validate([
            'prodi_id' => 'required|max:100',
            'judul' => 'required|max:255',
            'edisi' => 'nullable|max:50',
            'penerbit' => 'nullable|max:100',
            'author' => 'required|max:100',
            'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
            'eksemplar' => 'required|integer',
			'diterima' => 'nullable|integer',
			'harga' => 'nullable|numeric|min:0', // Tambahkan validasi untuk harga
        ]);

        // Update data pengajuan
        $pengajuan->update($request->all());
		
		$this->setLogActivity('Mengubah pengajuan', $pengajuan);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil diupdate.');
    }

    public function destroy(Pengajuan $pengajuan)
    {
        // Hapus data pengajuan
	    $dump = $pengajuan;
        $pengajuan->delete();
		$this->setLogActivity('Menghapus pengajuan', $dump);
        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil dihapus.');
    }
    public function approve($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        return view('pengajuan.approve', compact('pengajuan'));
    }

	public function storeApproval(Request $request, Pengajuan $pengajuan)
	{
		$request->validate([
			'eksemplar' => 'required|integer|min:1',
			'harga' => 'required_if:action,approve|numeric',
			'reason' => 'required_if:action,reject|max:255', // Reason hanya wajib saat action adalah reject
		]);
	
		if ($request->action === 'approve') {
			// Logika jika pengajuan disetujui
			$store = $pengajuan->update([
				'is_approve' => true,
				'is_reject' => false, // Pastikan is_reject di-set ke false
				'approved_at' => now(),
				'diterima' => (int)$request->eksemplar, // Hanya set jika disetujui
				'harga' => $request->harga, // Simpan harga
				'approved_by' => Auth::user() ? Auth::user()->id : 0, // Id pengguna yang menyetujui
			]);
			
			$this->setLogActivity('Menyetujui pengajuan', $pengajuan);
			return response()->json(['message' => 'Pengajuan berhasil disetujui!']);
		} elseif ($request->action === 'reject') {
			// Logika jika pengajuan ditolak
			$store = $pengajuan->update([
				'is_approve' => false,
				'is_reject' => true, // Set is_reject menjadi true jika ditolak
				'rejected_at' => now(), // Tambahkan timestamp jika pengajuan ditolak
				'rejected_by' => Auth::user() ? Auth::user()->id : 0, // Id pengguna yang menolak
				'reason' => $request->reason, // Tambahkan alasan penolakan jika ada
				'diterima' => 0, // Set kolom diterima menjadi null jika ditolak
			]);
			
			$this->setLogActivity('Menolak pengajuan', $pengajuan);
			return response()->json(['message' => 'Pengajuan berhasil ditolak!']);
		}
	}
	
	
	public function importForm()
	{
		return view('pengajuan.import');
	}
	
	public function import(Request $request)
	{
		try {
			Excel::import(new PengajuanImport, $request->file('file'));
			$this->setLogActivity('Import data pengajuan', new Pengajuan());
			return redirect()->back()->with('success', 'Import berhasil.');
		} catch (\Exception $e) {
			return redirect()->back()->with('error', 'Terjadi kesalahan saat import.');
		}
	}
	
}
