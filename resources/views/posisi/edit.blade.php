@extends('layouts.app')

@section('content')

<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit Posisi</h3>
                </div>
            
              </div>

              <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('updateposisi', $data->id)}}" method="post" onsubmit="return validateForm()">
            @csrf
            <div class="form-group mb-4">
            <label for="" class="form-label" style="color:black;">Kode Orange</label>
            <input name="kode_orange" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->kode_orange}}" />
            </div>

             
            <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Jenis Pekerjaan</label>
                    <input name="jenis_pekerjaan" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->jenis_pekerjaan}}" />
                    </div>
                
            
   
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Posisi</label>
                    <input name="posisi" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->posisi}}" />
                    </div>
                  
                    
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Standarisasi Upah</label>
                    <input name="standarisasi_upah" type="number" min="0"  class="form-control " style="border-color: #01004C;" value="{{$data->standarisasi_upah}}"  oninput="validasiNumber(this)"/>
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
    
    let kodeorange = document.forms["saveform"]["kode_orange"].value.trim();
    let jenispekerjaan =document.forms["saveform"]["jenis_pekerjaan"].value.trim();
    let posisi =document.forms["saveform"]["posisi"].value.trim();
    let standarisasiupah = document.forms["saveform"]["standarisasi_upah"].value.trim();

  if (kodeorange == "" ) {
    alert("Kode orange harus diisi.");
    return false;
  } else  if (jenispekerjaan == "" ) {
    alert("Jenis pekerjaan harus diisi.");
    return false;
  } else  if (posisi == "" ) {
    alert("Posisi harus diisi.");
    return false;
  } else  if (standarisasiupah == "" ) {
    alert("Standarisasi upah harus diisi.");
    return false;
  } 

}
</script>
@endsection