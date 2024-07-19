@extends('layouts.app')

@section('content')
<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Tambah Divisi</h3>
                </div>
            
              </div>

              <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('divisi.store')}}" method="post" onsubmit="return validateForm()">
                                        @csrf                
            <div class="form-group mb-4">
            <label for="" class="form-label" style="color:black;">Divisi</label>
            <input name="divisi" type="text"  class="form-control " style="border-color: #01004C;" value="" />
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
    
    let divisi = document.forms["saveform"]["divisi"].value.trim();
   
    if (divisi == "" ) {
    alert("Divisi harus diisi.");
    return false;
  }
}
</script>
@endsection