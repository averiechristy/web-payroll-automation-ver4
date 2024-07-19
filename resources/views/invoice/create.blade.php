@extends('layouts.app')

@section('content')

<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Proses Invoice</h3>
                </div>
              </div>

<div class="card shadow mb-4">
            <div class="card-body">
            <div class="py-3 ml-3">
                        <h5 class ="font-weight-bold">Proses Invoice</h5>
                        <form name="saveform" action="{{route('invoice.store')}}" method="post" onsubmit="return validateForm()">
                        @csrf
                            <div class="row mb-3">
                            <div class="col-md-3">
                                    <p>Organisasi</p>
                                    <select name="organisasi_id"  id="organisasi_id" class="form-control" style="color:black;">
                                        <option value="" disabled selected>Pilih Organisasi</option>
                                        @foreach ($organisasi as $item)
                                            <option value="{{ $item->id }}" >{{ $item->organisasi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                    
<div class="col-md-3">
    <p>Template</p>
<select name="kode_invoice" class="form-select form-control" aria-label="Default select example" style="color:black;">
  <option selected disabled>Pilih template invoice</option>
  <option value="1">Template 1</option>
  <option value="2">Template 2</option>
  <option value="3">Template 3</option>
</select>
</div>
                                    
                                <div class="col-md-3">
                                    <p>Bulan</p>
                                    <select name="bulan" id="filterMonth" class="form-control" style="color:black;">
                                        @php
                                            $months = [
                                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                            ];
                                            $currentMonth = date('n');
                                        @endphp
                                        <option value="" disabled selected>Pilih Bulan</option>
                                        @foreach ($months as $key => $month)
                                            <option value="{{ $key }}" >{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <p>Tahun</p>
                                <select name="tahun" id="filterYear" class="form-control" style="color:black;">
    @php
        $currentYear = date('Y');
    @endphp
    <option value="" disabled selected>Pilih Tahun</option>
    <option value="{{ $currentYear }}">{{ $currentYear }}</option>
</select>

                                </div>

                                
                               
                            </div>
                            <div class="form-group mb-4">
            <label for="" class="form-label" style="color:black;">Management Fee</label>
            <input name="management_fee" type="number"  class="form-control " style="border-color: #01004C;" value="" />
            </div>

           <div class="form-group ">
                                    <button type="submit" class="btn btn-info mt-4">Tambah Biaya Jasa</button>
                                </div>


                           <div class="form-group ">
                                    <button type="submit" class="btn btn-primary mt-4">Proses</button>
                                </div>
                        </form>
                    </div>

                </div>

                </div>



            </div>
          </div>
        
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
      
        <!-- partial -->
      </div>



@endsection