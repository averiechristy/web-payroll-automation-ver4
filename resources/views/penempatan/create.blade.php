@extends('layouts.app')
@section('content')

<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Tambah Penempatan</h3>
                </div>
              </div>

            <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('penempatan.store')}}" method="post" onsubmit="return validateForm()">
              @csrf
            <div class="form-group mb-4">
            <label for="" class="form-label" style="color:black;">Kode Orange</label>
            <input name="kode_orange" type="number" min="0"  class="form-control " style="border-color: #01004C;" value="" oninput="validasiNumber(this)"/>
            </div>

<div class="form-group mb-4">
    <label for="organisasi" class="form-label" style="color:black;">Organisasi</label>
    <select name="organisasi_id" id="organisasi" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example" style="color:black;">
        <option value="" selected disabled>Pilih Organisasi</option>
        @foreach ($organisasi as $item)
            <option value="{{$item->id}}">{{$item->organisasi}}</option>
        @endforeach
    </select>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
     $(document).ready(function() {
        $('#organisasi').select2();
    });
</script>

<div class="form-group mb-4">
    <label for="divisi" class="form-label" style="color:black;">Divisi</label>
    <select name="divisi_id" id="divisi" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example" style="color:black;">
        <option value="" selected disabled>Pilih Divisi</option>
        @foreach ($divisi as $item)
            <option value="{{$item->id}}">{{$item->divisi}}</option>
        @endforeach
    </select>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
     $(document).ready(function() {
        $('#divisi').select2();
    });
</script>
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Nama Unit Kerja</label>
                    <input name="nama_unit_kerja" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>
                              
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">KCU Induk</label>
                    <input name="kcu_induk" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>

                
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Kode Cabang Pembayaran</label>
                    <input name="kode_cabang_pembayaran" type="number"  min="0" class="form-control " style="border-color: #01004C;" value="" oninput="validasiNumber(this)"/>
                    </div>
                            
                    
                     
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">RCC Pembayaran</label>
                    <input name="rcc_pembayaran" type="number" min="0" class="form-control " style="border-color: #01004C;" value="" oninput="validasiNumber(this)"/>
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
                    <label for="" class="form-label" style="color:black;">Singkatan Divisi</label>
                    <input name="singkatan_divisi" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>

                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Kode SLID</label>
                    <input name="kode_slid" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>
                    
                    <!-- <div class="form-group mb-4 ml-4">
    <div class="form-check">
        <input name ="flexCheckIndeterminate" class="form-check-input" type="checkbox" value="Yes" id="flexCheckIndeterminate" onclick="this.value=this.checked ? 'Yes' : 'No';">
        <label class="form-check-label" for="flexCheckDefault">
            Hitung Tunjangan
        </label>
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

function validateForm() {
    
    let kodeorange = document.forms["saveform"]["kode_orange"].value.trim();
    let namaunit = document.forms["saveform"]["nama_unit_kerja"].value.trim();
    let organisasi = document.forms["saveform"]["organisasi_id"].value.trim();
    let divisi =document.forms["saveform"]["divisi_id"].value.trim();
   
    let kcuinduk = document.forms["saveform"]["kcu_induk"].value.trim();
    let kodecabang =document.forms["saveform"]["kode_cabang_pembayaran"].value.trim();
    let rcc = document.forms["saveform"]["rcc_pembayaran"].value.trim();
    let singkatan = document.forms["saveform"]["singkatan_divisi"].value.trim();
    let kodeslid = document.forms["saveform"]["kode_slid"].value.trim();

  if (kodeorange == "" ) {
    alert("Kode orange harus diisi.");
    return false;
  } else if ( organisasi ==""){
    alert ("Organisasi harus diisi.");
    return false;
  }else if (divisi ==""){
    alert ("Divisi harus diisi.");
    return false;
  }else if (namaunit == "") {
    alert("Nama unit kerja harus diisi.");
    return false;
  }  else if (kcuinduk==""){
    alert ("KCU induk harus diisi.");
    return false;
  }else if(kodecabang ==""){
    alert ("Kode cabang pembayaran harus diisi.");
    return false;
  } else if (rcc==""){
    alert("RCC pembayaran harus diisi.");
    return false;
  }else if (singkatan==""){
    alert("Singkatan divisi harus diisi.");
    return false;
  } else if(kodeslid ==""){
    alert("Kode SLID harus diisi.");
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