@extends('layouts.app')
@section('content')

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
           <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit Overtime</h3>
                </div>            
              </div>

              <div class="card shadow mb-4">
                  <div class="card-body">
                  <form name="saveform" action="{{route('updateovertime', $data->id)}}" method="post" onsubmit="return validateForm()">
                  @csrf                
                          
                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Nama Karyawan</label>
                              <select name="karyawan_id" id="karyawan" style="color:black;" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example">
                                  <option value="" selected disabled>Pilih Karyawan</option>
                                  @foreach ($karyawan as $item)
                                      <option value="{{$item->id}}" {{ old('karyawan_id', $data->karyawan_id) == $item->id ? 'selected' : '' }}>{{$item->nama_karyawan}}</option>
                                  @endforeach
                              </select>
                          </div>

                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Branch</label>
                              <input name="branch" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->branch}}" />
                          </div>
                          
                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Tanggal</label>
                              <input name="date" id="date" type="date" class="form-control" style="border-color: #01004C; width:30%;" value="{{$data->date}}" />
                          </div>

                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Overtime Duration</label>
                              <input name="overtime_duration" type="number"  class="form-control " style="border-color: #01004C;" value="{{$data->overtime_duration}}" />
                          </div>

                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Overtime Payment</label>
                              <input name="overtime_payment" type="number"  class="form-control " style="border-color: #01004C;" value="{{$data->overtime_payment}}" />
                          </div>

                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Overtime Multiplier</label>
                              <input name="overtime_multiplier" type="number"  class="form-control " style="border-color: #01004C;" value="{{$data->overtime_multiplier}}" />
                          </div>

                          <div class="form-group mb-4">
                              <label for="" class="form-label" style="color:black;">Overtime Rate</label>
                              <input name="overtime_rate" type="number"  class="form-control " style="border-color: #01004C;" value="{{$data->overtime_rate}}" />
                          </div>

                          <div class="form-group mb-4">
                              <button type="submit" class="btn btn-primary btn-fw">Simpan</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>
        </div>
    </div>
</div>

<script>
    function validateForm() {
        let karyawan = document.forms["saveform"]["karyawan_id"].value;
        let branch = document.forms["saveform"]["branch"].value;
        let tanggal = document.forms["saveform"]["date"].value;
        let otduration = document.forms["saveform"]["overtime_duration"].value;
        let otpayment = document.forms["saveform"]["overtime_payment"].value;
        let otmultiplier = document.forms["saveform"]["overtime_multiplier"].value;
        let otrate = document.forms["saveform"]["overtime_rate"].value;

        if (karyawan == "") {
            alert("Karyawan harus diisi.");
            return false;
        } else if (branch == "") {
            alert("Branch harus diisi.");
            return false;
        } else if (tanggal == "") {
            alert("Tanggal harus diisi.");
            return false;
        } else if (otduration == "") {
            alert("Overtime duration harus diisi.");
            return false;
        } else if (otpayment == "") {
            alert("Overtime payment harus diisi.");
            return false;
        } else if (otmultiplier == "") {
            alert("Overtime multiplier harus diisi.");
            return false;
        } else if (otrate == "") {
            alert("Overtime rate harus diisi.");
            return false;
        }
        return true;
    }
</script>

@endsection
