@extends('layouts.app')
@section('content')

<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">

              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Tambah Karyawan</h3>
                </div>            
              </div>

          <div class="card shadow mb-4">
            <div class="card-body">
              <form name="saveform" action="{{route('karyawan.store')}}" method="post" onsubmit="return validateForm()">
         
             @csrf
            <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">NIK</label>
                    <input name="nik" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Payroll Code</label>
                    <input name="payroll_code" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>
                           
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Nama Karyawan (sesuai KTP)</label>
                    <input name="nama_karyawan" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>
                  
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">No Amanden</label>
                    <input name="no_amandemen" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">NIK (sesuai KTP)</label>
                    <input name="nik_ktp" type="text"  class="form-control " style="border-color: #01004C;" value="" oninput="validasiNumber(this)"/>
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
            <option value="{{$item->id}}">{{$item->nama_unit_kerja}}</option>
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
            <option value="{{$item->id}}">{{$item->posisi}}</option>
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
                    <input name="jabatan" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                  </div>   
                    
                  <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Bagian</label>
                    <input name="bagian" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                  </div>       
                    
                    
              
                    
                  

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Biaya Manajemen (%)</label>
                    <input name="management_fee" type="number" min="0"  class="form-control " style="border-color: #01004C;" value="5" />
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
                    <input name="leader" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                  </div>    

                  <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Status Karyawan</label>
                    <input name="status_karyawan" type="text"  class="form-control" style="border-color: #01004C;" value="" />
                  </div>    

                  <div class="form-group mb-4">
                            <label for="tanggal_bergabung" class="form-label" style="color:black;">Tanggal Bergabung</label>
                            <input name="tanggal_bergabung" id="tanggal_bergabung" type="date" class="form-control" style="border-color: #01004C;" />
                        </div>

                  <!-- <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="font-weight-bold">Periode Kontrak</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4">
                            <label for="tanggal_awal" class="form-label" style="color:black;">Tanggal Awal Kontrak</label>
                            <input name="tanggal_awal_kontrak" id="tanggal_awal_kontrak" type="date" class="form-control" style="border-color: #01004C;" />
                        </div>
                        <div class="form-group mb-4">
                            <label for="tanggal_akhir" class="form-label" style="color:black;">Tanggal Akhir Kontrak</label>
                            <input name="tanggal_akhir_kontrak" id="tanggal_akhir_kontrak" type="date" class="form-control" style="border-color: #01004C;" />
                        </div>
                    </div>
                </div> -->


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
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('tanggal_awal_kontrak');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('tanggal_akhir_kontrak');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
    </script>
      <script>

function validateForm() {


    let nik = document.forms["saveform"]["nik"].value.trim();
    let payroll = document.forms["saveform"]["payroll_code"].value.trim();
    let nama =document.forms["saveform"]["nama_karyawan"].value.trim();
    let noamandemen =document.forms["saveform"]["no_amandemen"].value.trim();
    let nikktp =document.forms["saveform"]["nik_ktp"].value.trim();
    let penempatan =document.forms["saveform"]["penempatan_id"].value.trim();
    let posisi =document.forms["saveform"]["posisi_id"].value.trim();
    let jabatan =document.forms["saveform"]["jabatan"].value.trim();
    let bagian =document.forms["saveform"]["bagian"].value.trim();
    let management =document.forms["saveform"]["management_fee"].value.trim();
    let leader =document.forms["saveform"]["leader"].value.trim();
    let status =document.forms["saveform"]["status_karyawan"].value.trim();
    let joindate =document.forms["saveform"]["tanggal_bergabung"].value.trim();
  

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

  else  if (management == "" ) {
    alert("Biaya manajemen harus diisi.");
    return false;
  }
  else  if (leader == "" ) {
    alert("Leader harus diisi.");
    return false;
  }
  else  if (status == "" ) {
    alert("Status karyawan harus diisi.");
    return false;
  }   else  if (joindate == "" ) {
    alert("Tanggal bergabung harus diisi.");
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