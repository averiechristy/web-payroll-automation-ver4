<?php

use App\Exports\InvoiceDownload;
use App\Exports\InvoiceDownloadKedua;
use App\Exports\KompensasiExport;
use App\Exports\LemburDownload;
use App\Exports\MADDownload;
use App\Exports\PayrollDownload;
use App\Exports\TesterManualDownload;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\GajiTMCOntroller;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\InsentifController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KompensasiController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\KontrakKaryawanController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\MADController;
use App\Http\Controllers\OrganisasiController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PenempatanController;
use App\Http\Controllers\PosisiController;
use App\Http\Controllers\TesterManualController;
use App\Http\Controllers\UangSakuController;
use App\Http\Controllers\UserController;
use App\Models\Organisasi;
use App\Models\Penempatan;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class,'index'])->name('login');
Route::post('/login', [AuthController::class,'login']);
Route::post('/logout', [AuthController::class,'logout'])->name('logout');


Route::get('/login', [AuthController::class,'index'])->name('login');

Route::middleware('auth')->group(function () {

Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
Route::get('/user', [UserController::class,'index'])->name('user');
Route::get('/user/create', [UserController::class,'create'])->name('user.create');
Route::post('/user/store', [UserController::class,'store'])->name('user.store');
Route::get('/user/show/{id}', [UserController::class,'show'])->name('showuser');
Route::post('/user/update/{id}', [UserController::class,'update'])->name('updateuser');
Route::delete('/deleteuser/{id}', [UserController::class, 'destroy'])->name('deleteuser');

Route::get('/penempatan', [PenempatanController::class,'index'])->name('penempatan');
Route::get('/penempatan/create', [PenempatanController::class,'create'])->name('penempatan.create');
Route::get('/downloadpenempatan', [PenempatanController::class,'download'])->name('downloadpenempatan');
Route::post('/penempatan/store', [PenempatanController::class,'store'])->name('penempatan.store');
Route::post('/importpenempatan', [PenempatanController::class,'import'])->name('importpenempatan');
Route::get('/penempatan/show/{id}', [PenempatanController::class,'show'])->name('showpenempatan');
Route::post('/penempatan/update/{id}', [PenempatanController::class,'update'])->name('updatepenempatan');
Route::delete('/deletepenempatan/{id}', [PenempatanController::class, 'destroy'])->name('deletepenempatan');

Route::get('/posisi', [PosisiController::class,'index'])->name('posisi');
Route::get('/posisi/create', [PosisiController::class,'create'])->name('posisi.create');
Route::get('/downloadposisi', [PosisiController::class,'download'])->name('downloadposisi');
Route::post('/posisi/store', [PosisiController::class,'store'])->name('posisi.store');
Route::post('/importposisi', [PosisiController::class,'import'])->name('importposisi');
Route::get('/posisi/show/{id}', [PosisiController::class,'show'])->name('showposisi');
Route::post('/posisi/update/{id}', [PosisiController::class,'update'])->name('updateposisi');
Route::delete('/deleteposisi/{id}', [PosisiController::class, 'destroy'])->name('deleteposisi');

Route::get('/karyawan', [KaryawanController::class,'index'])->name('karyawan');
Route::get('/karyawan/create', [KaryawanController::class,'create'])->name('karyawan.create');
Route::post('/karyawan/store', [KaryawanController::class,'store'])->name('karyawan.store');
Route::get('/downloadkaryawan', [KaryawanController::class,'download'])->name('downloadkaryawan');
Route::post('/importkaryawan', [KaryawanController::class,'import'])->name('importkaryawan');
Route::get('/karyawan/show/{id}', [KaryawanController::class,'show'])->name('showkaryawan');
Route::post('/karyawan/update/{id}', [KaryawanController::class,'update'])->name('updatekaryawan');
Route::delete('/deletekaryawan/{id}', [KaryawanController::class, 'destroy'])->name('deletekaryawan');
Route::post('/nonaktifkaryawan', [KaryawanController::class, 'nonaktifkaryawan'])->name('nonaktifkaryawan');



Route::get('/mad', [MADController::class,'index'])->name('mad');
Route::get('/mad/create', [MADController::class,'create'])->name('mad.create');
Route::post('/mad/store', [MADController::class,'store'])->name('mad.store');
Route::get('/downloadmad', [MADController::class,'download'])->name('downloadmad');
Route::post('/importmad', [MADController::class,'import'])->name('importmad');
Route::get('/mad/show/{id}', [MADController::class,'show'])->name('showmad');
Route::post('/mad/update/{id}', [MADController::class,'update'])->name('updatemad');
Route::delete('/deletemad/{id}', [MADController::class, 'destroy'])->name('deletemad');
Route::get('/exportmad', [MadController::class, 'exportMad'])->name('exportmad');
Route::get('/detailmad/{id}',[MADController::class,'tampildetail'])->name('detailmad');
Route::get('/tampilmad/{id}',[MADController::class,'tampilmad'])->name('tampilmad');
Route::post('/close-mad/{id}', [MADController::class, 'closeMad'])->name('close.mad');
Route::post('/batalkan-closing/{id}', [MADController::class, 'batalkanClosing'])->name('batalkan.closing');



Route::get('/download-mad', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $status_mad = request()->input('status_mad');
    

    // Daftar bulan dalam bentuk tulisan
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];

    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Laporan MAD $monthName $tahun.xlsx";
    $dataMAD = json_decode(request()->input('dataMAD'), true);
    
    return Excel::download(new MADDownload($bulan, $tahun, $status_mad, $dataMAD), $fileName);
})->name('download.mad');


Route::get('/holiday', [HolidayController::class,'index'])->name('holiday');
Route::get('/showholiday/{id}', [HolidayController::class,'show'])->name('showholiday');
Route::get('/holiday/create', [HolidayController::class,'create'])->name('holiday.create');
Route::post('/holiday/store', [HolidayController::class,'store'])->name('holiday.store');
Route::post('/holiday/update/{id}', [HolidayController::class,'update'])->name('updateholiday');
Route::get('/update-holidays', [HolidayController::class, 'updateholidayotomatis']);


Route::get('/changepassword', [AuthController::class,'showChangePasswordForm'])->name('password');
Route::post('/change', [AuthController::class,'changePassword'])->name('change-password');

Route::post('/user/{user}/reset-password', [UserController::class,'resetPassword'])->name('reset-password');

Route::get('/gaji', [GajiController::class,'index'])->name('gaji');
Route::get('/gaji/create', [GajiController::class,'create'])->name('gaji.create');
Route::post('/gaji/store', [GajiController::class,'store'])->name('gaji.store');
Route::get('/gaji/show/{id}', [GajiController::class,'show'])->name('showgaji');
Route::post('/gaji/update/{id}', [GajiController::class,'update'])->name('updategaji');
Route::delete('/deletegaji/{id}', [GajiController::class, 'destroy'])->name('deletegaji');
Route::get('/downloadgaji', [GajiController::class,'download'])->name('downloadgaji');
Route::post('/importgaji', [GajiController::class,'import'])->name('importgaji');

Route::get('/kompensasi', [KompensasiController::class,'index'])->name('kompensasi');
Route::post('/kompensasi/store', [KompensasiController::class,'store'])->name('kompensasi.store');
Route::get('/detailkompensasi/{id}',[KompensasiController::class,'tampildetail'])->name('detailkompensasi');
Route::get('/tampilkompensasi/{id}',[KompensasiController::class,'tampilkompensasi'])->name('tampilkompensasi');
Route::post('/close-kompensasi/{id}', [KompensasiController::class, 'closekompensasi'])->name('close.kompensasi');
Route::post('/batalkan-closing-kompensasi/{id}', [KompensasiController::class, 'batalkanClosingkompensasi'])->name('batalkan.closing.kompensasi');


Route::get('/download-kompensasi', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $status_kompensasi =request()->input('status_kompensasi');


    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $dataKompensasi = json_decode(request()->input('dataKompensasi'), true);


    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];

    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Laporan MAD $monthName $tahun.xlsx";
    return Excel::download(new KompensasiExport($bulan, $tahun, $status_kompensasi, $dataKompensasi), $fileName);
    
})->name('download.kompensasi');


Route::get('/organisasi', [OrganisasiController::class,'index'])->name('organisasi');
Route::get('/organisasi/create', [OrganisasiController::class,'create'])->name('organisasi.create');
Route::post('/organisasi/store', [OrganisasiController::class,'store'])->name('organisasi.store');
Route::get('/organisasi/show/{id}', [OrganisasiController::class,'show'])->name('showorganisasi');
Route::post('/organisasi/update/{id}', [OrganisasiController::class,'update'])->name('updateorganisasi');
Route::delete('/deleteorganisasi/{id}', [OrganisasiController::class, 'destroy'])->name('deleteorganisasi');


Route::get('/divisi', [DivisiController::class,'index'])->name('divisi');
Route::get('/divisi/create', [DivisiController::class,'create'])->name('divisi.create');
Route::post('/divisi/store', [DivisiController::class,'store'])->name('divisi.store');
Route::get('/divisi/show/{id}', [DivisiController::class,'show'])->name('showdivisi');
Route::post('/divisi/update/{id}', [DivisiController::class,'update'])->name('updatedivisi');
Route::delete('/deletedivisi/{id}', [DivisiController::class, 'destroy'])->name('deletedivisi');
Route::get('/downloaddivisi', [DivisiController::class,'download'])->name('downloaddivisi');
Route::post('/importdivisi', [DivisiController::class,'import'])->name('importdivisi');

Route::get('/attendance', [AttendanceController::class,'index'])->name('attendance');
Route::get('/downloadattendance', [AttendanceController::class,'download'])->name('downloadattendance');
Route::post('/importattendance', [AttendanceController::class,'import'])->name('importattendance');
Route::get('/attendance/show/{id}', [AttendanceController::class,'show'])->name('showattendance');
Route::post('/attendance/update/{id}', [AttendanceController::class,'update'])->name('updateattendance');


Route::get('/overtime', [OvertimeController::class,'index'])->name('overtime');
Route::get('/downloadovertime', [OvertimeController::class,'download'])->name('downloadovertime');
Route::post('/importovertime', [OvertimeController::class,'import'])->name('importovertime');
Route::get('/overtime/show/{id}', [OvertimeController::class,'show'])->name('showovertime');
Route::post('/overtime/update/{id}', [OvertimeController::class,'update'])->name('updateovertime');



Route::get('/konfigurasi', [KonfigurasiController::class,'index'])->name('konfigurasi');
Route::get('/konfigurasi/create', [KonfigurasiController::class,'create'])->name('konfigurasi.create');
Route::get('/getPenempatan/{organisasi_id}', [KonfigurasiController::class, 'getPenempatan']);
Route::post('/konfigurasi/store', [KonfigurasiController::class,'store'])->name('konfigurasi.store');
Route::get('/konfigurasi/show/{id}', [KonfigurasiController::class,'show'])->name('showkonfigurasi');
Route::post('/konfigurasi/update/{id}', [KonfigurasiController::class,'update'])->name('updatekonfigurasi');
Route::get('/detailkonfigurasi/{id}',[KonfigurasiController::class,'detail'])->name('detailkonfigurasi');


Route::get('/uangsaku', [UangSakuController::class,'index'])->name('uangsaku');
Route::get('/uangsaku/create', [UangSakuController::class,'create'])->name('uangsaku.create');
Route::post('/uangsaku/store', [UangSakuController::class,'store'])->name('uangsaku.store');
Route::get('/uangsaku/show/{id}', [UangSakuController::class,'show'])->name('showuangsaku');
Route::post('/uangsaku/update/{id}', [UangSakuController::class,'update'])->name('updateuangsaku');
Route::delete('/deleteuangsaku/{id}', [UangSakuController::class, 'destroy'])->name('deleteuangsaku');

Route::get('/insentif', [InsentifController::class,'index'])->name('insentif');
Route::get('/insentif/create', [InsentifController::class,'create'])->name('insentif.create');
Route::post('/insentif/store', [InsentifController::class,'store'])->name('insentif.store');
Route::get('/insentif/show/{id}', [InsentifController::class,'show'])->name('showinsentif');
Route::post('/insentif/update/{id}', [InsentifController::class,'update'])->name('updateinsentif');
Route::delete('/deleteinsentif/{id}', [InsentifController::class, 'destroy'])->name('deleteinsentif');


Route::get('/payroll', [PayrollController::class,'index'])->name('payroll');
Route::post('/payroll/store', [PayrollController::class,'store'])->name('payroll.store');
Route::get('/tampilpayroll/{id}',[PayrollController::class,'tampilpayroll'])->name('tampilpayroll');
Route::post('/close-payroll/{id}', [PayrollController::class, 'closepayroll'])->name('close.payroll');
Route::post('/batalkan-closing-payroll/{id}', [PayrollController::class, 'batalkanClosingpayroll'])->name('batalkan.closing.payroll');


Route::get('/download-payroll', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $organisasi = request()->input('organisasi_id');
    $status_payroll = request()->input('status_payroll');
    $dataorg = Organisasi::find($organisasi);
    $namaorg = $dataorg->organisasi;
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];

    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Laporan Payroll Organisasi $namaorg $monthName $tahun.xlsx";

    $dataPayroll = json_decode(request()->input('dataPayroll'), true);

    return Excel::download(new PayrollDownload($bulan, $tahun, $organisasi, $status_payroll, $dataPayroll), $fileName);
})->name('download.payroll');


Route::get('/lembur', [LemburController::class,'index'])->name('lembur');
Route::post('/lembur/store', [LemburController::class,'store'])->name('lembur.store');
Route::get('/detaillembur/{id}',[LemburController::class,'tampildetail'])->name('detaillembur');
Route::get('/tampillembur/{id}',[LemburController::class,'tampillembur'])->name('tampillembur');
Route::post('/batalkan-closing-lembur/{id}', [LemburController::class, 'batalkanClosinglembur'])->name('batalkan.closing.lembur');



Route::post('/close-lembur/{id}', [LemburController::class, 'closelembur'])->name('close.lembur');

Route::get('/download-lembur', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $organisasi = request()->input('organisasi_id');
    $status_lembur = request()->input('status_lembur');
    $dataorg = Organisasi::find($organisasi);
    $namaorg = $dataorg->organisasi;
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];
    $dataLembur = json_decode(request()->input('dataLembur'), true);
    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Laporan Lembur Organisasi $namaorg $monthName $tahun.xlsx";
    return Excel::download(new LemburDownload($bulan, $tahun, $organisasi, $status_lembur, $dataLembur), $fileName);
})->name('download.lembur');


Route::get('/allowance', [AllowanceController::class,'index'])->name('allowance');
Route::get('/allowance/create', [AllowanceController::class,'create'])->name('allowance.create');
Route::post('/allowance/store', [AllowanceController::class,'store'])->name('allowance.store');
Route::get('/detailallowance/{id}',[AllowanceController::class,'tampildetail'])->name('detailallowance');
Route::get('/allowance/show/{id}', [AllowanceController::class,'show'])->name('showallowance');
Route::post('/allowance/update/{id}', [AllowanceController::class,'update'])->name('updateallowance');


Route::get('/invoice', [InvoiceController::class,'index'])->name('invoice');
Route::get('/tampilinvoice/{id}',[InvoiceController::class,'tampilinvoice'])->name('tampilinvoice');
Route::get('/tampilinvoice2/{id}',[InvoiceController::class,'tampilinvoice2'])->name('tampilinvoice2');

Route::get('/tampilinvoice3/{id}',[InvoiceController::class,'tampilinvoice3'])->name('tampilinvoice3');

Route::get('/invoice/create', [InvoiceController::class,'create'])->name('invoice.create');
Route::post('/invoice/store', [InvoiceController::class,'store'])->name('invoice.store');
// Add this to your web.php or routes file
// routes/web.php
Route::get('/getPenempatanedit/{organisasi_id}', [InvoiceController::class, 'getPenempatanedit']);

Route::get('/download-invoice', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $managementfee = request()->input('management_fee');
    $organisasi = request()->input('organisasi_id');
    $status_invoice = request()->input('status_invoice');
    $dataorg = Organisasi::find($organisasi);
    $namaorg = $dataorg->organisasi;

    $penempatan = request()->input('penempatan');
    
    if($penempatan =="all"){
        $tampilpenempatan = "Seluruh Penempatan";
    } else {
        $datapenempatan = Penempatan::find($penempatan);
        $tampilpenempatan = $datapenempatan->nama_unit_kerja;
    }

    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];


  
    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];

    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Invoice $namaorg ($tampilpenempatan) $monthName $tahun.xlsx";

    $dataInvoice = json_decode(request()->input('dataInvoice'), true);

    return Excel::download(new InvoiceDownload($bulan, $tahun, $organisasi, $status_invoice, $dataInvoice, $managementfee, $tampilpenempatan), $fileName);
})->name('download.invoice');


Route::get('/download-invoicekedua', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $managementfee = request()->input('management_fee');
    $organisasi = request()->input('organisasi_id');
    $status_invoice = request()->input('status_invoice');
    $dataorg = Organisasi::find($organisasi);
    $namaorg = $dataorg->organisasi;
    $penempatan = request()->input('penempatan');
    if($penempatan =="all"){
        $tampilpenempatan = "Seluruh Penempatan";
    } else {
        $datapenempatan = Penempatan::find($penempatan);
        $tampilpenempatan = $datapenempatan->nama_unit_kerja;
    }

    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];


  
    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];

    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Invoice $namaorg ($tampilpenempatan) $monthName $tahun.xlsx";

    $dataInvoice = json_decode(request()->input('dataInvoice'), true);

   

    return Excel::download(new InvoiceDownloadKedua($bulan, $tahun, $organisasi, $status_invoice, $dataInvoice, $managementfee, $tampilpenempatan), $fileName);
})->name('download.invoicekedua');


Route::post('/close-invoice/{id}', [InvoiceController::class, 'closeinvoice'])->name('close.invoice');
Route::post('/close-invoicekedua/{id}', [InvoiceController::class, 'closeinvoicekedua'])->name('close.invoicekedua');
Route::post('/batalkan-closing-invoice/{id}', [InvoiceController::class, 'batalkanClosinginvoice'])->name('batalkan.closing.invoice');


Route::get('/gajitm', [GajiTMCOntroller::class,'index'])->name('gajitm');
Route::get('/downloadgajitm', [GajiTMCOntroller::class,'download'])->name('downloadgajitm');
Route::post('/importgajitm', [GajiTMCOntroller::class,'import'])->name('importgajitm');
Route::delete('/deletegajitm/{id}', [GajiTMCOntroller::class, 'destroy'])->name('deletegajitm');
Route::delete('/deletegajitm/{id}', [GajiTMCOntroller::class, 'destroy'])->name('deletegajitm');



Route::get('/testermanual', [TesterManualController::class,'index'])->name('testermanual');
Route::post('/testermanual/store', [TesterManualController::class,'store'])->name('testermanual.store');
Route::get('/tampiltestermanual/{id}',[TesterManualController::class,'tampiltestermanual'])->name('tampiltestermanual');
Route::get('/download-invoicetm', function () {
    $bulan = request()->input('bulan');
    $tahun = request()->input('tahun');
    $managementfee = request()->input('management_fee');
 
    $status_invoicetm = request()->input('status_invoicetm');
    
   
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];


  
    // Mengambil nama bulan berdasarkan input
    $monthName = $months[(int)$bulan];

    // Menggabungkan nama file sesuai format yang diinginkan
    $fileName = "Invoice Tester Manual $monthName $tahun.xlsx";

    $datainvoicetm = json_decode(request()->input('datainvoicetm'), true);

    return Excel::download(new TesterManualDownload($bulan, $tahun, $status_invoicetm, $datainvoicetm, $managementfee), $fileName);
})->name('download.invoicetm');

Route::post('/close-invoicetm/{id}', [TesterManualController::class, 'closeinvoicetm'])->name('close.invoicetm');
Route::post('/batalkan-closing-invoicetm/{id}', [TesterManualController::class, 'batalkanClosinginvoicetm'])->name('batalkan.closing.invoicetm');




Route::get('/kontrak', [KontrakKaryawanController::class,'index'])->name('kontrak');
Route::get('/kontrak/create', [KontrakKaryawanController::class,'create'])->name('kontrak.create');
Route::post('/kontrak/store', [KontrakKaryawanController::class,'store'])->name('kontrak.store');
Route::get('/kontrak/show/{id}', [KontrakKaryawanController::class,'show'])->name('showkontrak');
Route::post('/kontrak/update/{id}', [KontrakKaryawanController::class,'update'])->name('updatekontrak');
Route::delete('/deletekontrak/{id}', [KontrakKaryawanController::class, 'destroy'])->name('deletekontrak');
Route::get('/downloadkontrak', [KontrakKaryawanController::class,'download'])->name('downloadkontrak');
Route::post('/importkontrak', [KontrakKaryawanController::class,'import'])->name('importkontrak');
});