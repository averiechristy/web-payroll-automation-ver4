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
                    <label for="" class="form-label" style="color:black;">Nama Unit Kerja</label>
                    <input name="nama_unit_kerja" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                    </div>
                              

            <div class="form-group mb-4">
                <label for="" class="form-label" style="color:black;">Wilayah</label>
                <input name="wilayah" type="text"  class="form-control " style="border-color: #01004C;" value="" />
                </div>


                <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Divisi</label>
                    <input name="divisi" type="text"  class="form-control " style="border-color: #01004C;" value="" />
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
                    
                    <div class="form-group mb-4 ml-4">
    <div class="form-check">
        <input name ="flexCheckIndeterminate" class="form-check-input" type="checkbox" value="Yes" id="flexCheckIndeterminate" onclick="this.value=this.checked ? 'Yes' : 'No';">
        <label class="form-check-label" for="flexCheckDefault">
            Hitung Tunjangan
        </label>
    </div>
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
    
    let kodeorange = document.forms["saveform"]["kode_orange"].value;
    let namaunit = document.forms["saveform"]["nama_unit_kerja"].value;
    let wilayah = document.forms["saveform"]["wilayah"].value;
    let divisi = document.forms["saveform"]["divisi"].value;
    let kcuinduk = document.forms["saveform"]["kcu_induk"].value;
    let kodecabang =document.forms["saveform"]["kode_cabang_pembayaran"].value;
    let rcc = document.forms["saveform"]["rcc_pembayaran"].value;
    let singkatan = document.forms["saveform"]["singkatan_divisi"].value;
    let kodeslid = document.forms["saveform"]["kode_slid"].value;

  if (kodeorange == "" ) {
    alert("Kode orange harus diisi.");
    return false;
  } else if (namaunit == "") {
    alert("Nama unit kerja harus diisi.");
    return false;
  } else if ( wilayah ==""){
    alert("Wilayah harus diisi.");
    return false;
  } else if ( divisi ==""){
    alert ("Divisi harus diisi.");
    return false;
  } else if (kcuinduk==""){
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
@endsection