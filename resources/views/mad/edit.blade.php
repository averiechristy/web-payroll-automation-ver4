@extends('layouts.app')
@section('content')

<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit MAD</h3>
                </div>
              </div>
            <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('updatemad', $data->id)}}" method="post" onsubmit="return validateForm()">
              @csrf
    
              <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Nama Karyawan</label>
                    <select name ="karyawan_id" id="karyawan" style="color:black;" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example">
                    <option value="" selected disabled>Pilih Karyawan</option>
        @foreach ($karyawan as $item)
            <option value="{{$item->id}}"{{ old('karyawan_id', $data->karyawan_id) == $item->id ? 'selected' : '' }}>{{$item->nama_karyawan}}</option>
        @endforeach
</select>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
     $(document).ready(function() {
        $('#karyawan').select2();
    });
</script>
                 

<div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Tanggal Lembur</label>
                    <input name="tanggal_lembur" id="tanggal_lembur" type="date"  class="form-control " style="border-color: #01004C; width:30%;" value="{{$data->tanggal_lembur}}" />
                    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('tanggal_lembur');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
    </script>

<div class="row">
  <div class="col">

    <div class="form-group mb-4">
      <label for="jam_mulai" class="form-label" style="color:black;">Jam Mulai</label>
      <input name="jam_mulai" id="jam_mulai" type="text" value="{{$data->jam_mulai}}" class="form-control" style="border-color: #01004C;" />
    </div>

  </div>

  <div class="col">

    <div class="form-group mb-4">
      <label for="jam_selesai" class="form-label" style="color:black;">Jam Selesai</label>
      <input name="jam_selesai" id="jam_selesai" type="text" value="{{$data->jam_selesai}}"  class="form-control" style="border-color: #01004C;" />
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    flatpickr("#jam_mulai", {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true,
      minuteIncrement: 1
    });

    flatpickr("#jam_selesai", {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true,
      minuteIncrement: 1
    });
  });
</script>


  
  <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Keterangan Lembur</label>
                    <input name="keterangan_lembur" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->keterangan_lembur}}" />
                    </div>


                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Keterangan Perbaikan</label>
                    <input name="keterangan_perbaikan" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data -> keterangan_perbaikan}}" />
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
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
      
        <!-- partial -->
      </div>

      <script>

function validateForm() {
    
    let nama = document.forms["saveform"]["karyawan_id"].value;
    let tanggallembur =document.forms["saveform"]["tanggal_lembur"].value;
    let jammulai =document.forms["saveform"]["jam_mulai"].value;
    let jamselesai =document.forms["saveform"]["jam_selesai"].value;
    let keterangan =document.forms["saveform"]["keterangan_lembur"].value;

    if (nama == "" || nama == "Pilih Karyawan" ) {
    alert("Karyawan harus diisi.");
    return false;
  }
 else if (tanggallembur == "") {
    alert("Tanggal lembur harus diisi.");
    return false;
  }
  else if (jammulai == "") {
    alert("Jam mulai harus diisi.");
    return false;
  }
  else if (jamselesai == "") {
    alert("Jam selesai harus diisi.");
    return false;
  }
  else if (keterangan == "") {
    alert("Keterangan harus diisi.");
    return false;
  }



}
</script>

      <style>
        /* Customize the Select2 container */
        .select2-container .select2-selection--single {
            height: 45px; /* Match the height of your form-control */
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        /* Customize the placeholder text */
        .select2-container .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
            line-height: 50px; /* Match the height of your form-control */
            
        }

        /* Customize the selected value */
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 50px; /* Match the height of your form-control */
            margin-top: -19px;
            margin-left: -15px;
            color: black;
        }

        /* Customize the dropdown arrow */
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 50px; /* Match the height of your form-control */
        }

        /* Customize the dropdown menu */
        .select2-container .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
    </style>




@endsection