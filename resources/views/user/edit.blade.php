@extends('layouts.app')

@section('content')
<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit User</h3>
                </div>
            
              </div>

              <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('updateuser', $data->id)}}" method="post" onsubmit="return validateForm()">
                                        @csrf                
            <div class="form-group mb-4">
            <label for="" class="form-label" style="color:black;">Nama User</label>
            <input name="nama_user" type="text"  class="form-control " style="border-color: #01004C;" value="{{$data->nama_user}}" />
            </div>
                              
            <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Email</label>
                <input name="email" type="email"  class="form-control " style="border-color: #01004C;" value="{{$data->email}}" />
            </div>
          

            <div class="form-group mb-4">
                <label for="" class="form-label" style="color:black;">Password</label>
            <input name="password" type="text"  class="form-control " style="border-color: #01004C;" value="12345678" readonly />
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
    
    let namauser = document.forms["saveform"]["nama_user"].value;
    let email =document.forms["saveform"]["email"].value;

    if (namauser == "" ) {
    alert("Nama user harus diisi.");
    return false;
  }else if(email == ""){
    alert("Email harus diisi.");
    return false;
  }
}
</script>
@endsection