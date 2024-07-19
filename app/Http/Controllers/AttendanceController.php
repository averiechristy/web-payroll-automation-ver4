<?php

namespace App\Http\Controllers;

use App\Exports\TemplateAttendance;
use App\Imports\AttendanceImport;
use App\Models\Attendance;
use App\Models\Invoice;
use App\Models\Karyawan;
use App\Models\Kompensasi;
use App\Models\Lembur;
use App\Models\MAD;
use App\Models\Organisasi;
use App\Models\Payroll;
use App\Models\Penempatan;
use App\Models\TesterManual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{

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
                'Tanggal',
                'Shift',
                'Schedule In',
                'Schedule Out',
                'Attendance Code',
                'Check In',
                'Check Out',
                'Overtime Check In',
                'Overtime Check Out',

            ];

           

            if ($headingRow !== $expectedHeaders) {
              
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new AttendanceImport, $file);

           

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
            Excel::import(new AttendanceImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Attendance berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('attendance');
     }
     public function download()
     {
          // Panggil class export Anda, sesuaikan dengan struktur data Anda
          return Excel::download(new TemplateAttendance(), 'templateattendance.xlsx');
     }

    public function index()
    {
        $attendance = Attendance::orderBy('created_at','desc')->get();
        return view('attendance.index',[
            'attendance' => $attendance,
        ]);
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
        $data = Attendance::find($id);
        $karyawan = Karyawan::all();

        return view('attendance.edit',[
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



        $data = Attendance::find($id);
        $karyawanid = $request->karyawan_id;
        $date = $request->date;
        $checkin = $request->check_in;
        $checkout = $request->check_out;
        $overtimecheckin = $request->overtime_checkin;
        $overtimecheckout = $request->overtime_checkout;

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
            return redirect()->route('attendance');
        }

        if($datakompensasi){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan kompensasi sudah closing');
            return redirect()->route('attendance');
        }

        if($datalembur){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan lembur sudah closing');
            return redirect()->route('attendance');
        }

        if($datapayroll){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan payroll sudah closing');
            return redirect()->route('attendance');
        }

        if($datainvoice){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice sudah closing');
            return redirect()->route('attendance');
        }

        if($datainvoicetm){
            $request->session()->flash('error', 'Tidak dapat merubah data, laporan invoice tester manual sudah closing');
            return redirect()->route('attendance');
        }

        $data -> karyawan_id = $karyawanid;
        $data -> check_in = $checkin;
        $data -> check_out = $checkout;
        $data -> overtime_checkin = $overtimecheckin;
        $data -> overtime_checkout = $overtimecheckout;


        $existingdata = Attendance::where('karyawan_id', $karyawanid)
        ->where('date', $date)
        ->where('id', '!=', $id)
        ->first();

        if($existingdata) {
            $request->session()->flash('error', 'Attendance sudah terdaftar.');
            return redirect()->route('attendance');
        }

        $data->save();

        $request->session()->flash('success', 'Attendance berhasil diubah.');
        return redirect()->route('attendance');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
