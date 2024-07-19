@extends('layouts.app')
@section('content')

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
           <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit Attendance</h3>
                </div>            
              </div>

              <div class="card shadow mb-4">
                  <div class="card-body">
                      <form name="saveform" action="{{route('updateattendance', $data->id)}}" method="post" onsubmit="return validateForm()">
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
                              <label for="" class="form-label" style="color:black;">Tanggal</label>
                              <input name="date" id="date" type="date" class="form-control" style="border-color: #01004C; width:30%;" value="{{$data->date}}" />
                          </div>

                          <script>
                              document.addEventListener('DOMContentLoaded', function() {
                                  var dateInput = document.getElementById('date');
                                  dateInput.addEventListener('click', function() {
                                      this.showPicker();
                                  });
                              });
                          </script>

                          <!-- Check In, Check Out, Overtime Checkin & Overtime Checkout in one card -->
                          <div class="card  shadow mb-4">
                              <div class="card-body">
                                  <div class="row">
                                      <div class="col-md-6">
                                          <div class="form-group mb-4">
                                              <label for="check_in" class="form-label" style="color:black;">Check In</label>
                                              <input name="check_in" id="check_in" type="text" class="form-control" style="border-color: #01004C;" value="{{$data->check_in}}"/>
                                          </div>
                                          <div class="form-group mb-4">
                                              <label for="overtime_checkin" class="form-label" style="color:black;">Overtime Checkin</label>
                                              <input name="overtime_checkin" id="overtime_checkin" type="text" class="form-control" style="border-color: #01004C;" value="{{$data->overtime_checkin}}"/>
                                          </div>
                                      </div>
                                      <div class="col-md-6">
                                      <div class="form-group mb-4">
                                              <label for="check_out" class="form-label" style="color:black;">Check Out</label>
                                              <input name="check_out" id="check_out" type="text" class="form-control" style="border-color: #01004C;" value="{{$data->check_out}}" />
                                          </div>
                                          
                                          <div class="form-group mb-4">
                                              <label for="overtime_checkout" class="form-label" style="color:black;">Overtime Checkout</label>
                                              <input name="overtime_checkout" id="overtime_checkout" type="text" class="form-control" style="border-color: #01004C;" value="{{$data->overtime_checkout}}" />
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <!-- End of Check In, Check Out, Overtime Checkin & Overtime Checkout in one card -->

                          <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

                          <script>
                              document.addEventListener('DOMContentLoaded', function() {
                                  flatpickr("#check_in", {
                                      enableTime: true,
                                      noCalendar: true,
                                      dateFormat: "H:i",
                                      time_24hr: true,
                                      minuteIncrement: 1
                                  });

                                  flatpickr("#check_out", {
                                      enableTime: true,
                                      noCalendar: true,
                                      dateFormat: "H:i",
                                      time_24hr: true,
                                      minuteIncrement: 1
                                  });

                                  flatpickr("#overtime_checkin", {
                                      enableTime: true,
                                      noCalendar: true,
                                      dateFormat: "H:i",
                                      time_24hr: true,
                                      minuteIncrement: 1
                                  });

                                  flatpickr("#overtime_checkout", {
                                      enableTime: true,
                                      noCalendar: true,
                                      dateFormat: "H:i",
                                      time_24hr: true,
                                      minuteIncrement: 1
                                  });
                              });
                          </script>

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
    let tanggal = document.forms["saveform"]["date"].value;
    let check_in = document.forms["saveform"]["check_in"].value;
    let check_out = document.forms["saveform"]["check_out"].value;
    let overtime_checkout = document.forms["saveform"]["overtime_checkout"].value;

    if (karyawan == "") {
        alert("Karyawan harus diisi.");
        return false;
    } else if (tanggal == "") {
        alert("Tanggal harus diisi.");
        return false;
    }

    let checkInTime = parseTime(check_in);
    let checkOutTime = parseTime(check_out);
    let overtimeCheckOutTime = parseTime(overtime_checkout);

    if (checkOutTime < checkInTime) {
        alert("Check Out tidak boleh kurang dari Check In.");
        return false;
    }

    if (overtimeCheckOutTime < checkInTime) {
        alert("Overtime Check Out tidak boleh kurang dari Check In.");
        return false;
    }

    return true;
}

function parseTime(timeStr) {
    let [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
}
</script>

@endsection
