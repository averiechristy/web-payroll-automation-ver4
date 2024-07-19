@extends('layouts.app')

@section('content')
<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit Uang Saku</h3>
                </div>
            
              </div>

              <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('updateuangsaku', $data->id)}}" method="post" onsubmit="return validateForm()">
                                        @csrf                
            
                                        <div class="form-group mb-4">
                  <p>Nama Karyawan</p>
                    <select name="karyawan_id" id="karyawan" style="color:black;" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example">
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
                                            <option value="{{ $key }}" {{ old('bulan', $data->bulan) == $key ? 'selected' : '' }}>{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                        <div class="form-group mb-4">
                                    <p>Tahun</p>
                                    <select name ="tahun" id="filterYear" class="form-control" style="color:black;">
                                        @php
                                            $currentYear = date('Y');
                                            $startYear = $currentYear - 1;
                                        @endphp
                                        <option value="" disabled selected>Pilih Tahun</option>
                                        @for ($year = $startYear; $year <= $currentYear; $year++)
                                            <option value="{{ $year }}" {{ old('tahun', $data->tahun) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
     $(document).ready(function() {
        $('#organisasi_id').select2();
    });

    $(document).ready(function() {
        $('#filterMonth').select2();
    });

    $(document).ready(function() {
        $('#filterYear').select2();
    });
</script>

<div class="form-group mb-4">
                              <label for="uang_saku" class="form-label" style="color:black;">Jumlah Uang Saku Perjalanan Dinas</label>
                              <input type="number" name="uang_saku" id="uang_saku" class="form-control" style="color:black;" value="{{ old('uang_saku', $data->uang_saku) }}" oninput="validasiNumber(this)">
                            </div>


<script>
function validasiNumber(input) {
    // Hapus karakter titik (.) dari nilai input
    input.value = input.value.replace(/\./g, '');

    // Pastikan hanya karakter angka yang diterima
    input.value = input.value.replace(/\D/g, '');
}
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
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
      
        <!-- partial -->
      </div>

<script>

function validateForm() {
    
  let karyawan = document.forms["saveform"]["karyawan_id"].value;
  let bulan =document.forms["saveform"]["bulan"].value;
  let tahun =document.forms["saveform"]["tahun"].value;
  let uangsaku =document.forms["saveform"]["uang_saku"].value;


  if (karyawan == "" ) {
    alert("Karyawan harus diisi.");
    return false;
  } else if (bulan == ""){
    alert("Bulan harus diisi.");
    return false;
  }else if (tahun == ""){
    alert("Tahun harus diisi.");
    return false;
  }else if(uangsaku == ""){
    alert("Uang saku harus diisi.");
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
