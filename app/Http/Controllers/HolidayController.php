<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
       $holiday = Holiday::all();

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
        return view('holiday.edit',[
            'data' => $data,
        ]);
    }

    public function update(Request $request, $id){
        $data = Holiday::find($id);

        $data -> description = $request -> description;
        $data -> save();
        $request->session()->flash('success', "Data Libur berhasil diubah.");

        return redirect()->route('holiday');
    }

}
