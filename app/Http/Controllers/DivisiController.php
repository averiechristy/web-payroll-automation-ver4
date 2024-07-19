<?php

namespace App\Http\Controllers;

use App\Exports\TemplateDivisi;
use App\Imports\DivisiImport;
use App\Models\Divisi;
use App\Models\Penempatan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisi = Divisi::orderBy('created_at','desc')->get();
        return view('divisi.index',[
            'divisi' => $divisi,
        ]);
    }


    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('file');
            $reader = Excel::toArray([], $file);
            $headingRow = $reader[0][0];

            $expectedHeaders = [
               'Nama Divisi'
            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }

            $data = Excel::toCollection(new DivisiImport, $file);

            // Filter baris yang hanya berisi nilai null
            $filteredData = $data->map(function ($sheet) {
                return $sheet->filter(function ($row) {
                    return $row->filter(function ($value) {
                        return !is_null($value);
                    })->isNotEmpty();
                });
            });
                        

            if ($filteredData->isEmpty() || $filteredData->first()->isEmpty()) {
                throw new \Exception("File harus diisi.");
            }
            $hasData = false;
            foreach ($data->first() as $row) {
                if ($row->filter()->isNotEmpty()) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                throw new \Exception("File harus diisi.");
            }
            // Lakukan impor
            Excel::import(new DivisiImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Divisi berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('divisi');
     }


    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplateDivisi(), 'templatedivisi.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('divisi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
    
        $divisi = $request->divisi;

        $existingDivisi = Divisi::where('divisi', $divisi)->first();

        if ($existingDivisi) {
            // Jika organisasi sudah ada, tampilkan pesan error
            $request->session()->flash('error', "Divisi sudah terdaftar.");
            return redirect()->route('divisi');

        }

        Divisi::create([

            'divisi' => $divisi,
            'created_by' => $loggedInUsername,

        ]);

        $request->session()->flash('success', "Divisi berhasil ditambahkan.");

        return redirect()->route('divisi');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Divisi::find($id);

        return view ('divisi.edit',[
            'data' => $data,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = Divisi::find($id);

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
        $divisi = $request->divisi;

        $existingDivisi = Divisi::where('divisi', $divisi)
        ->where('id', '!=', $id)
        ->first();

        if ($existingDivisi) {
            // Jika organisasi sudah ada, tampilkan pesan error
            $request->session()->flash('error', "Divisi sudah terdaftar.");
            return redirect()->route('divisi');

        }

        $data->divisi = $request->divisi;
        $data->updated_by = $loggedInUsername;
        $data->save();

        $request->session()->flash('success', 'Divisi berhasil diubah.');

        return redirect(route('divisi'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $divisi = Divisi::find($id);
        if (Penempatan::where('divisi_id', $divisi->id)->exists()) {
            $request->session()->flash('error', "Tidak dapat menghapus divisi, karena masih ada data penempatan yang berhubungan.");
            return redirect()->route('divisi');
        }
        $divisi->delete();

        $request->session()->flash('success', "Divisi berhasil dihapus.");
    
        return redirect()->route('divisi');

    }
}
