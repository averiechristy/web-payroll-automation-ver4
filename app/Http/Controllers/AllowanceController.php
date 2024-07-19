<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\DetailAllowance;
use App\Models\Invoice;
use App\Models\Karyawan;
use App\Models\Payroll;
use App\Models\TesterManual;
use DateTime;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allowance = Allowance::orderBy('created_at','desc')->get();
        return view ('allowance.index',[
            'allowance' => $allowance,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $karyawan = Karyawan::all();
        return view('allowance.create', [
            'karyawan' => $karyawan
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
        
        $insentifstatus = $request->insentif_status;

        if($insentifstatus==="tidak"){
        
            $existingAllowance = Allowance::where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->first();

          
            if ($existingAllowance) {
           
                $bulan = DateTime::createFromFormat('!m', $request->bulan)->format('F');
                $request->session()->flash('error', 'Uang saku & tunjangan pada bulan ' . $bulan . ' tahun ' . $request->tahun . ' sudah terdaftar.');
                return redirect()->route('allowance');
            }

            $allowance = Allowance::create([
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'insentif_status' => $request->insentif_status,
                'created_by' => $loggedInUsername,
            ]);
    

        }else if($insentifstatus==="ya"){

            foreach ($request->karyawan_id as $index => $karyawan_id) {
                // $existingAllowance = Allowance::where('bulan', $request->bulan)
                //     ->where('tahun', $request->tahun)
                //     ->whereHas('detailallowance', function ($query) use ($karyawan_id) {
                //         $query->where('karyawan_id', $karyawan_id);
                //     })
                //     ->first();

                $existingAllowance = Allowance::where('bulan', $request->bulan)
                ->where('tahun', $request->tahun)
                ->first();
        
                // If an existing record is found, flash an error message and redirect back
                if ($existingAllowance) {
                    $bulan = DateTime::createFromFormat('!m', $request->bulan)->format('F');
                    $request->session()->flash('error', 'Uang saku & tunjangan pada bulan ' . $bulan . ' tahun ' . $request->tahun . ' sudah terdaftar.');
                    return redirect()->route('allowance');
                }
            }
        
            $allowance = Allowance::create([
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'insentif_status' => $request->insentif_status,
                'created_by' => $loggedInUsername,
            ]);
    
            foreach ($request->karyawan_id as $index => $karyawan_id) {
                DetailAllowance::create([
                    'allowance_id' => $allowance->id,
                    'karyawan_id' => $karyawan_id,
                    'uang_saku' => $request->uang_saku[$index],
                    'insentif' => $request->insentif[$index],
                ]);
            }

        }


     
 
        $request->session()->flash('success', 'Uang saku & inesntif berhasil ditambahkan.');
        return redirect()->route('allowance');


    }

    public function tampildetail ($id){
        $allowance = Allowance::find($id);
        $detailallowance = DetailAllowance::with('allowance')->where('allowance_id', $id)->get();



        return view('allowance.detail',[

            'allowance' => $allowance,
            'detailallowance' => $detailallowance,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $allowance = Allowance::find($id);
        $detailallowance = DetailAllowance::with('allowance')->where('allowance_id', $id)->get();

        $karyawan = Karyawan::all();

        return view ('allowance.edit',[
            'allowance' => $allowance,
            'karyawan' => $karyawan,
            'detailallowance' => $detailallowance,
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

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
        
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $datapayroll = Payroll::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_payroll', 'Closing')
        ->first();



        if($datapayroll){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan payroll sudah closing');
            return redirect()->route('allowance');
        }


        $datainvoice = Invoice::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_invoice', 'Closing')
        ->first();

        if($datainvoice){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice sudah closing');
            return redirect()->route('allowance');
        }

        $datainvoicetm = TesterManual::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_invoicetm', 'Closing')
        ->first();

        if($datainvoicetm){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice tester manual sudah closing');
            return redirect()->route('allowance');
        }

        $insentifstatus = $request->insentif_status;
    
        // Find the existing allowance by ID
        $allowance = Allowance::find($id);
    
        // Check if insentif_status is "tidak"
        if ($insentifstatus === "tidak") {


            $existingAllowance = Allowance::where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->where('id', '<>', $id)
            ->first();

          
            if ($existingAllowance) {
           
                $bulan = DateTime::createFromFormat('!m', $request->bulan)->format('F');
                $request->session()->flash('error', 'Uang saku & tunjangan pada bulan ' . $bulan . ' tahun ' . $request->tahun . ' sudah terdaftar.');
                return redirect()->route('allowance');
            }

            // Update the allowance record
            $allowance->update([
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'insentif_status' => $request->insentif_status,
                'updated_by' => $loggedInUsername,
            ]);
    
            // Delete any existing detail allowances associated with this allowance
            DetailAllowance::where('allowance_id', $id)->delete();
    
        } else if ($insentifstatus === "ya") {
            // Update the allowance record


            $existingAllowance = Allowance::where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->where('id', '<>', $id)
            ->first();

          
            if ($existingAllowance) {
           
                $bulan = DateTime::createFromFormat('!m', $request->bulan)->format('F');
                $request->session()->flash('error', 'Uang saku & tunjangan pada bulan ' . $bulan . ' tahun ' . $request->tahun . ' sudah terdaftar.');
                return redirect()->route('allowance');
            }

            $allowance->update([
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'insentif_status' => $request->insentif_status,
                'updated_by' => $loggedInUsername,
            ]);
    
            // Delete any existing detail allowances associated with this allowance
            DetailAllowance::where('allowance_id', $id)->delete();
    
            // Create new detail allowance records
            foreach ($request->karyawan_id as $index => $karyawan_id) {
                DetailAllowance::create([
                    'allowance_id' => $allowance->id,
                    'karyawan_id' => $karyawan_id,
                    'uang_saku' => $request->uang_saku[$index],
                    'insentif' => $request->insentif[$index],
                ]);
            }
        }
    
        // Flash a success message and redirect back to the allowance page
        $request->session()->flash('success', 'Uang saku & insentif berhasil diubah.');
        return redirect()->route('allowance');
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
