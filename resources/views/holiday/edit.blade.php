@extends('layouts.app')

@section('content')
<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Tambah User</h3>
                </div>
            
              </div>

              <div class="card shadow mb-4">
            <div class="card-body">
            <form name="saveform" action="{{route('updateholiday', $data->id)}}" method="post" onsubmit="return validateForm()">
                                        @csrf                
            <div class="form-group mb-4">
            <label for="" class="form-label" style="color:black;">Tanggal</label>
            <input name="date" type="date"  id ="date"class="form-control " style="border-color: #01004C;" value="{{$data->date}}" readonly />
            </div>

            <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('date');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
    </script>
                              
                              <div class="form-group mb-4">
    <label for="jenisHari" class="form-label" style="color:black;">Jenis Hari</label>
    <select name="description" class="form-control" style="border-color: black;color:black;">
        <option value="Libur" <?php echo ($data->description == 'Libur') ? 'selected' : ''; ?>>Libur</option>
        <option value="Kerja" <?php echo ($data->description == 'Kerja') ? 'selected' : ''; ?>>Kerja</option>
    </select>
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
    
    let tanggal = document.forms["saveform"]["date"].value;
    let jenishari =document.forms["saveform"]["description"].value;

    if (tanggal == "" ) {
    alert("Tanggal harus diisi.");
    return false;
  }else if(jenishari == ""){
    alert("Jenis hari harus diisi.");
    return false;
  }
}
</script>
@endsection