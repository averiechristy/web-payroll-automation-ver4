<?php

namespace App\Http\Controllers;

use App\Exports\TemplateOvertime;
use App\Imports\OvertimeImport;
use App\Models\Invoice;
use App\Models\Karyawan;
use App\Models\Kompensasi;
use App\Models\Lembur;
use App\Models\MAD;
use App\Models\Organisasi;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Penempatan;
use App\Models\TesterManual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'Branch',
            'Tanggal',
            'Overtime Duration',
            'Overtime Payment',
            'Overtime Multiplier',
            'Overtime Rate',

            ];

            if ($headingRow !== $expectedHeaders) {
              
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new OvertimeImport(), $file);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
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
            Excel::import(new OvertimeImport(), $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Overtime berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('overtime');
     }
    public function index()
    {

        $overtime = Overtime::orderBy('created_at','desc')->get();
        return view('overtime.index',[
            'overtime' => $overtime,
        ]);
    }
    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplateOvertime(), 'templateovertime.xlsx');
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

        $data = Overtime::find($id);
        $karyawan = Karyawan::all();

        return view('overtime.edit',[
            'data' => $data,
            'karyawan' => $karyawan,
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
       
        $data = Overtime::find($id);
        $data->karyawan_id = $request->karyawan_id;
        $data -> branch = $request->branch;
        $data->date = $request->date;

        $date = $request->date;
        $karyawanid = $request->karyawan_id;
        $datakaryawan = Karyawan::find($karyawanid);
        $penempatanid = $datakaryawan->penempatan_id;
        
        $datapenempatan = Penempatan::find($penempatanid);

        $organisasiid = $datapenempatan->organisasi_id;
        $dataorganisasi = Organisasi::find($organisasiid);
        $organisasiid = $dataorganisasi->id;

        $carbonDate = Carbon::parse($date);
        $bulan = $carbonDate->month; // Ambil bulan (dalam angka)
        $tahun = $carbonDate->year; // Ambil tahun (dalam angka)


        $datamad = MAD::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_mad', 'Closing')
        ->first();

        $datakompensasi = Kompensasi::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_kompensasi', 'Closing')
        ->first();

        $datalembur = Lembur::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('organisasi_id', $organisasiid)
        ->where('status_lembur', 'Closing')
        ->first();

        $datapayroll = Payroll::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_payroll', 'Closing')
        ->where('organisasi_id', $organisasiid)
        ->first();

        $datainvoice = Invoice::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_invoice', 'Closing')
        ->where('organisasi_id', $organisasiid)
        ->first();

        $datainvoicetm = TesterManual::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_invoicetm', 'Closing')
        ->first();

        if($datamad){

            $request->session()->flash('error', 'Tidak dapat merubah data, laporan MAD sudah closing');
            return redirect()->route('overtime');
        }

        if($datakompensasi){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan kompensasi sudah closing');
            return redirect()->route('overtime');
        }

        if($datalembur){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan lembur sudah closing');
            return redirect()->route('overtime');
        }

        if($datapayroll){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan payroll sudah closing');
            return redirect()->route('overtime');
        }

        
        if($datainvoice){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice sudah closing');
            return redirect()->route('overtime');
        }

        if($datainvoicetm){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice tester manual sudah closing');
            return redirect()->route('overtime');
        }

        $data->overtime_duration = $request->overtime_duration;
        $data->overtime_payment = $request->overtime_payment;
        $data->overtime_multiplier = $request->overtime_multiplier;
        $data->overtime_rate = $request->overtime_rate;

        $data->save();

        $request->session()->flash('success', "Overtime berhasil diubah.");
    
        return redirect()->route('overtime');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
