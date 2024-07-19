<?php

namespace App\Http\Controllers;

use App\Models\DetailKonfigurasi;
use App\Models\Invoice;
use App\Models\Kompensasi;
use App\Models\Konfigurasi;
use App\Models\Lembur;
use App\Models\MAD;
use App\Models\Organisasi;
use App\Models\Payroll;
use App\Models\Penempatan;
use App\Models\TesterManual;
use Illuminate\Http\Request;

class KonfigurasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {        
        $konfigurasi = Konfigurasi::orderBy('created_at','desc')->get();
        
        return view('konfigurasi.index',[
            'konfigurasi' => $konfigurasi,
            
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $penempatan = Penempatan::all();
        $organisasi = Organisasi::all();

        return view('konfigurasi.create',[
            'penempatan' => $penempatan,
            'organisasi' => $organisasi,
        ]);
    }

    public function getPenempatan($organisasi_id)
{
    $penempatan = Penempatan::where('organisasi_id', $organisasi_id)->get();
    return response()->json($penempatan);
}

    /**
     * Store a newly created resource in storage.
     */


     public function store(Request $request)
     {
         $loggedInUser = auth()->user();
         $loggedInUsername = $loggedInUser->nama_user;
     
         $organisasiId = $request->organisasi_id;
         $dataorganisasi = Organisasi::find($organisasiId);
         $namaorganisasi = $dataorganisasi->organisasi;
     
         // Retrieve all penempatan IDs for the selected organisasi
         $penempatanIds = Penempatan::where('organisasi_id', $organisasiId)->pluck('id')->toArray();
         $excludedPenempatanIds = $request->penempatan_ids;
     
         // Exclude the specified penempatan IDs
         if (!empty($excludedPenempatanIds)) {
             $penempatanIds = array_diff($penempatanIds, $excludedPenempatanIds);
         }
     
         $canHitung = $request->has('hitungtunjangan');
         $hitungTunjangan = $canHitung ? "Yes" : "No";
         $perhitunganPayroll = $request->perhitunganpayroll;
     

         $caninvoice = $request->has('buatinvoice');

         $buatInvoice = $caninvoice ? "Yes" : "No";

         // Check for existing konfigurasi
         $existingKonfigurasi = Konfigurasi::where('organisasi_id', $organisasiId)
         ->latest()
         ->first();

         if ($existingKonfigurasi) {

             // Check for existing penempatan_id in DetailKonfigurasi
             $existingPenempatanIds = DetailKonfigurasi::where('konfigurasi_id', $existingKonfigurasi->id)
                 ->whereIn('penempatan_id', $penempatanIds)
                 ->pluck('penempatan_id')
                 ->toArray();

                
             if (!empty($existingPenempatanIds)) {
                 // Retrieve the names of the conflicting penempatan
                 $penempatanNames = Penempatan::whereIn('id', $existingPenempatanIds)->pluck('nama_unit_kerja')->toArray();
                 $penempatanNamesString = implode(', ', $penempatanNames);

                 return redirect()->route('konfigurasi')->with('error', "Konfigurasi untuk $penempatanNamesString berikut sudah terdaftar");
             }

         }

         // Save the configuration
         $konfigurasi = Konfigurasi::create([
             'organisasi_id' => $organisasiId,
             'hitung_tunjangan' => $hitungTunjangan,
             'perhitungan_payroll' => $perhitunganPayroll,
             'created_by' => $loggedInUsername,
             'buat_invoice' => $buatInvoice,
         ]);
     
         // Save the detail configuration for each penempatan ID
         foreach ($penempatanIds as $penempatanId) {
             DetailKonfigurasi::create([
                 'konfigurasi_id' => $konfigurasi->id,
                 'penempatan_id' => $penempatanId,
                 'hitung_tunjangan' => $hitungTunjangan,
                 'perhitungan_payroll' => $perhitunganPayroll,
                 'buat_invoice' => $buatInvoice,
             ]);
         }
     
         return redirect()->route('konfigurasi')->with('success', 'Konfigurasi berhasil disimpan.');
     }
     
     
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Konfigurasi::find($id);
        $penempatan = Penempatan::all();
        $organisasi = Organisasi::all();

        $konfigurasiid = $data->id;

        $detail = DetailKonfigurasi::where('konfigurasi_id', $konfigurasiid)->get();


        return view('konfigurasi.edit',[
            'data' => $data,
            'organisasi' => $organisasi,
            'penempatan' => $penempatan,
            'detail' => $detail,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function detail( $id)
    {
        $data = Konfigurasi::find($id);
        $detail = DetailKonfigurasi::where('konfigurasi_id', $data->id)->get();
       

        return view('konfigurasi.detail',[
           'data' => $data,
           'detail' => $detail, 
        ]);
    }

   
    public function update(Request $request, string $id)
    {
       
        $data = Konfigurasi::find($id);

        $organisasiid = $data->organisasi_id;

        $datamad = MAD::where('status_mad', 'Closing')
        ->first();

        $datakompensasi = Kompensasi::where('status_kompensasi', 'Closing')
        ->first();

        $datalembur = Lembur::where('organisasi_id', $organisasiid)
        ->where('status_lembur', 'Closing')
        ->first();

        $datapayroll = Payroll::where('status_payroll', 'Closing')
        ->where('organisasi_id', $organisasiid)
        ->first();

        $datainvoice = Invoice::where('status_invoice', 'Closing')
        ->where('organisasi_id', $organisasiid)
        ->first();

        $datainvoicetm = TesterManual::where('status_invoicetm', 'Closing')
        ->first();

        if($datamad){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan MAD sudah closing');
            return redirect()->route('konfigurasi');
        }

        if($datakompensasi){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan kompensasi sudah closing');
            return redirect()->route('konfigurasi');
        }

        if($datalembur){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan lembur sudah closing');
            return redirect()->route('konfigurasi');
        }

        if($datapayroll){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan payroll sudah closing');
            return redirect()->route('konfigurasi');
        }


        if($datainvoice){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice sudah closing');
            return redirect()->route('konfigurasi');
        }

        if($datainvoicetm){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice tester manual sudah closing');
            return redirect()->route('konfigurasi');
        }
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $organisasiId = $request->organisasi_id;

        $dataorganisasi = Organisasi::find($organisasiId);
        $namaorganisasi = $dataorganisasi->organisasi;

        $penempatanId = $request->penempatan_id;
        $canhitung = $request->has('hitungtunjangan');

        if($canhitung == true){
             $hitungtunjangan ="Yes";
        } else if($canhitung == false) {
             $hitungtunjangan ="No";
        }
 
        $perhitunganPayroll = $request->perhitunganpayroll;

        $caninvoice = $request->has('buatinvoice');

        $buatInvoice = $caninvoice ? "Yes" : "No";

        $penempatanIds = Penempatan::where('organisasi_id', $organisasiId)->pluck('id')->toArray();
        $excludedPenempatanIds = $request->penempatan_ids;
    
        // Exclude the specified penempatan IDs
        if (!empty($excludedPenempatanIds)) {
            $penempatanIds = array_diff($penempatanIds, $excludedPenempatanIds);
        }
    
        $canHitung = $request->has('hitungtunjangan');
        $hitungTunjangan = $canHitung ? "Yes" : "No";
        $perhitunganPayroll = $request->perhitunganpayroll;
    
        // Check for existing konfigurasi
        $existingKonfigurasi = Konfigurasi::where('organisasi_id', $organisasiId)
        ->where('id', '<>', $id)
        ->first();
    
        if ($existingKonfigurasi) {
            // Check for existing penempatan_id in DetailKonfigurasi
            $existingPenempatanIds = DetailKonfigurasi::where('konfigurasi_id', $existingKonfigurasi->id)
                ->whereIn('penempatan_id', $penempatanIds)
                ->pluck('penempatan_id')
                ->toArray();

            if (!empty($existingPenempatanIds)) {
                // Retrieve the names of the conflicting penempatan
                $penempatanNames = Penempatan::whereIn('id', $existingPenempatanIds)->pluck('nama_unit_kerja')->toArray();
                $penempatanNamesString = implode(', ', $penempatanNames);

                return redirect()->route('konfigurasi')->with('error', "Konfigurasi untuk $penempatanNamesString berikut sudah terdaftar");
            }
        }
    
        $data -> organisasi_id = $organisasiId;
        $data -> hitung_tunjangan = $hitungtunjangan;
        $data -> perhitungan_payroll = $perhitunganPayroll;
        $data -> updated_by = $loggedInUsername;
        $data ->buat_invoice = $buatInvoice;
        $data->save();

        DetailKonfigurasi::where('konfigurasi_id', $data->id)->delete();

        foreach ($penempatanIds as $penempatanId) {
            DetailKonfigurasi::create([
                'konfigurasi_id' => $data->id,
                'penempatan_id' => $penempatanId,
                'hitung_tunjangan' => $hitungTunjangan,
                'buat_invoice' => $buatInvoice,
                'perhitungan_payroll' => $perhitunganPayroll,
            ]);
        }

        $request->session()->flash('success', 'Konfigurasi berhasil diubah.');

        return redirect(route('konfigurasi'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
