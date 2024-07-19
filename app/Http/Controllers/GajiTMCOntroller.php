<?php

namespace App\Http\Controllers;

use App\Exports\TemplateGajiTM;
use App\Imports\GajiTMImport;
use App\Models\GajiTMdanKnowledge;
use App\Models\Invoice;
use App\Models\TesterManual;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GajiTMCOntroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gajitm = GajiTMdanKnowledge::orderBy('created_at','desc')->get();
        return view('gajitm.index',[
            'gajitm' => $gajitm,
        ]);
    }


    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplateGajiTM(), 'Template Gaji dan Cadangan Transfer Knowledge Tester Manual.xlsx');
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
                'Nama Karyawan',
                'Bulan',
                'Tahun',
                'Gaji',
                'Cadangan Transfer Knowledge'
            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }

            $data = Excel::toCollection(new GajiTMImport(), $file);

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
            Excel::import(new GajiTMImport(), $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Gaji dan Cadangan Transfer Konwledge Tester Manual berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('gajitm');
     }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $gaji = GajiTMdanKnowledge::find($id);
    

        $currentmonth= $gaji->bulan;
        $currentyear = $gaji ->tahun;

        if ($currentmonth == 12) {
            $bulan = 1;
            $tahun = $currentyear + 1;
        } else {
            $bulan = $currentmonth + 1;
            $tahun = $currentyear;
        }
        $datainvoice = TesterManual::where('bulan', $bulan)
        ->where('status_invoicetm','Closing')
        ->where('tahun', $tahun)
        ->first();

        

        if($datainvoice){
            $request->session()->flash('error', "Tidak bisa menghapus data,  sudah ada invoice tester manual yang closing pada bulan $bulan tahun $tahun");

            return redirect()->route('gajitm');
        }

        $gaji->delete();
        $request->session()->flash('success', "Gaji dan Cadangan Transfer Knowledge berhasil dihapus.");

        return redirect()->route('gajitm');
    }
}
