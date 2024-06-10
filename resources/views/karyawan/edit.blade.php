@extends('layouts.app')

@section('content')

<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">

                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit Karyawan</h3>
                </div>
            
              </div>

              <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('updatekaryawan', $data->id)}}" method="post" onsubmit="return validateForm()">
         
             @csrf
            <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">NIK</label>
                    <input name="nik" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->nik}}" />
                    </div>

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Payroll Code</label>
                    <input name="payroll_code" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->payroll_code}}" />
                    </div>
                
           
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Nama Karyawan (sesuai KTP)</label>
                    <input name="nama_karyawan" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->nama_karyawan}}" />
                    </div>
                  
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">No Amandemen</label>
                    <input name="no_amandemen" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->no_amandemen}}" />
                    </div>

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">NIK (sesuai KTP)</label>
                    <input name="nik_ktp" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->nik_ktp}}" oninput="validasiNumber(this)"/>
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
    <label for="penempatan" class="form-label" style="color:black;">Penempatan</label>
    <select name="penempatan_id" id="penempatan" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example" style="color:black;">
        <option value="" selected disabled>Pilih Penempatan</option>
        @foreach ($penempatan as $item)
            <option value="{{$item->id}}"{{ old('penempatan_id', $data->penempatan_id) == $item->id ? 'selected' : '' }}>{{$item->nama_unit_kerja}}</option>
        @endforeach
    </select>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
     $(document).ready(function() {
        $('#penempatan').select2();
    });
</script>



<div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Posisi</label>
                    <select name ="posisi_id" id="posisi" style="color:black;" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example">
                    <option value="" selected disabled>Pilih Posisi</option>
        @foreach ($posisi as $item)
            <option value="{{$item->id}}"{{ old('posisi_id', $data->posisi_id) == $item->id ? 'selected' : '' }}>{{$item->posisi}}</option>
        @endforeach
</select>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
     $(document).ready(function() {
        $('#posisi').select2();
    });
</script>



<div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Jabatan</label>
                    <input name="jabatan" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->jabatan}}" />
                    </div>   
                    
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Bagian</label>
                    <input name="bagian" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->bagian}}"  />
                    </div>       
                    
                    
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Upah Pokok</label>
                    <input name="upah_pokok" type="number" min="0"  class="form-control " style="border-color: #01004C;"value="{{$data->upah_pokok}}"  oninput="validasiNumber(this)"/>
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
                    <label for="" class="form-label" style="color:black;">Tunjangan SPV</label>
                    <input name="tunjangan_spv" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->tunjangan_spv}}"   oninput="validasiNumber(this)"/>
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
                    <label for="" class="form-label" style="color:black;">Management Fee (%)</label>
                    <input name="management_fee" type="number" min="0"  class="form-control " style="border-color: #01004C;" value="{{$convertfee}}" />
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
                    <label for="" class="form-label" style="color:black;">Leader</label>
                    <input name="leader" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->leader}}"  />
                    </div>    

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Status Karyawan</label>
                    <input name="status_karyawan" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->status_karyawan}}" />
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

    let nik = document.forms["saveform"]["nik"].value;
    let payroll = document.forms["saveform"]["payroll_code"].value;
    let nama =document.forms["saveform"]["nama_karyawan"].value;
    let noamandemen =document.forms["saveform"]["no_amandemen"].value;
    let nikktp =document.forms["saveform"]["nik_ktp"].value;
    let penempatan =document.forms["saveform"]["penempatan_id"].value;
    let posisi =document.forms["saveform"]["posisi_id"].value;
    let jabatan =document.forms["saveform"]["jabatan"].value;
    let bagian =document.forms["saveform"]["bagian"].value;

    let upah =document.forms["saveform"]["upah_pokok"].value;

    let tunjangan =document.forms["saveform"]["tunjangan_spv"].value;
    let management =document.forms["saveform"]["management_fee"].value;
    let leader =document.forms["saveform"]["leader"].value;
    let status =document.forms["saveform"]["status_karyawan"].value;

  if (nik == "" ) {
    alert("NIK harus diisi.");
    return false;
  } else  if (payroll == "" ) {
    alert("Payroll code harus diisi.");
    return false;
  } else  if (nama == "" ) {
    alert("Nama karyawan harus diisi.");
    return false;
  } else  if (noamandemen == "" ) {
    alert("No amandemen code harus diisi.");
    return false;
  } else  if (nikktp == "" ) {
    alert("NIK sesuai KTP code harus diisi.");
    return false;
  } else  if (penempatan == "" || penempatan ==="Pilih Penempatan" ) {
    alert("Penempatan harus diisi.");
    return false;
  } else  if (posisi == "" || posisi ==="Pilih Posisi" ) {
    alert("Posisi harus diisi.");
    return false;
  } else  if (jabatan == "" ) {
    alert("Jabatan harus diisi.");
    return false;
  }

  else  if (bagian == "" ) {
    alert("Bagian harus diisi.");
    return false;
  }
  else  if (upah == "" ) {
    alert("Upah pokok harus diisi.");
    return false;
  }
  else  if (tunjangan == "" ) {
    alert("Tunjangan SPV harus diisi.");
    return false;
  }

  else  if (management == "" ) {
    alert("Management fee harus diisi.");
    return false;
  }
  else  if (leader == "" ) {
    alert("Leader harus diisi.");
    return false;
  }
  else  if (status == "" ) {
    alert("Status karyawan harus diisi.");
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