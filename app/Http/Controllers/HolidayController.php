<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HolidayController extends Controller
{
    public function updateholidayotomatis(Request $request){
        Artisan::call('holidays:update');

        $request->session()->flash('success', "Data libur berhasil diupdate.");

        return redirect()->route('holiday');
    }
    public function index()
    {
// Mendapatkan tahun berjalan
$currentYear = date('Y');

// Mendapatkan data holiday berdasarkan tahun berjalan
$holiday = Holiday::whereYear('date', $currentYear)->get();


       return view('holiday.index',[

        'holiday' => $holiday,
       ]);
    }

    public function create(){
        return view('holiday.create');
    }

    public function store(Request $request){

        $date = $request->date;
        $jenishari = $request->description;

        $existingdata = Holiday::where('date', $date)->first();

        if ($existingdata){
            $request->session()->flash('error', "Tanggal sudah terdaftar.");

            return redirect()->route('holiday');
        }

        Holiday::create([

            'date' => $date,
            'description' => $jenishari,
        ]);

        $request->session()->flash('success', "Data libur berhasil ditambahkan.");

        return redirect()->route('holiday');
    }

    public function show($id){
        $data = Holiday::find($id);
        $pengecualian_organisasi = json_decode($data->pengecualian_organisasi, true) ?? [];
        $organisasi = Organisasi::all();

        return view('holiday.edit',[
            'data' => $data,
            'organisasi' => $organisasi,
            'pengecualian_organisasi' => $pengecualian_organisasi,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Display all request data for debugging purposes
      
    
        // Find the Holiday model by ID
        $data = Holiday::find($id);
    
        // Update the description field with the request data
        $data->description = $request->description;
    
        // Convert pengecualian_organisasi array to JSON and save it in the appropriate field
        if ($request->has('pengecualian_organisasi')) {
            
            $data->pengecualian_organisasi = json_encode($request->pengecualian_organisasi);
        } else {
            $data->pengecualian_organisasi  = null;
        }
    
        // Save the updated model
        $data->save();
    
        // Set a success message in the session
        $request->session()->flash('success', "Data Libur berhasil diubah.");
    
        // Redirect to the holiday route
        return redirect()->route('holiday');
    }
    

}
