@extends('layouts.app')
@section('content')
<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Edit Tunjangan & Gaji</h3>
                </div>
              </div>

              <div class="card shadow mb-4">
                <div class="card-body">
                <form name="saveform" action="{{route('updategaji', $data->id)}}" method="post" onsubmit="return validateForm()">
                    @csrf                
                    
                    <div class="form-group mb-4">
                    <label for="" class="form-label" style="color:black;">Nama Karyawan</label>
                    <select name="karyawan_id" id="karyawan" style="color:black;" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example">
                    <option value="" selected disabled>Pilih Karyawan</option>
        @foreach ($karyawan as $item)
            <option value="{{$item->id}}"{{ old('karyawan_id', $data->karyawan_id) == $item->id ? 'selected' : '' }}>{{$item->nama_karyawan}}</option>
        @endforeach
</select>
</div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="card shadow mb-4">
                        <div class="card-header" style="font-weight:bold;">
                            Gaji
                        </div>
                          <div class="card-body">
                            <div class="form-group mb-4">
                              <label for="gaji" class="form-label" style="color:black;">Nominal Gaji</label>
                              <input type="number" name="gaji" id="gaji" class="form-control" style="color:black;" oninput="validasiNumber(this)" value="{{$data->gaji}}">
                            </div>
                            <h5 class="card-title">Periode Gaji</h5>
                            <div class="form-group mb-4">
                              <label for="tanggal_mulai_gaji" class="form-label" style="color:black;">Tanggal Mulai</label>
                              <input type="date" name="tanggal_mulai_gaji" id="tanggal_mulai_gaji" class="form-control" style="color:black;" value="{{$data->tanggal_mulai_gaji}}">
                            </div>
                            <div class="form-group mb-4">
                              <label for="tanggal_selesai_gaji" class="form-label" style="color:black;">Tanggal Selesai</label>
                              <input type="date" name="tanggal_selesai_gaji" id="tanggal_selesai_gaji" class="form-control" style="color:black;" value="{{$data->tanggal_selesai_gaji}}">
                            </div>
                          </div>
                        </div>
                      </div>

                    <div class="col-md-6">
                      <div class="card shadow mb-4">
                        <div class="card-header" style="font-weight:bold;">
                            Tunjangan
                        </div>
                          <div class="card-body">
                            <div class="form-group mb-4">
                              <label for="tunjangan" class="form-label" style="color:black;">Nominal Tunjangan</label>
                              <input type="number" name="tunjangan" id="tunjangan" class="form-control" style="color:black;" oninput="validasiNumber(this)" value="{{$data->tunjangan}}">
                            </div>
                            <h5 class="card-title">Periode Tunjangan</h5>
                            <div class="form-group mb-4">
                              <label for="tanggal_mulai_tunjangan" class="form-label" style="color:black;">Tanggal Mulai</label>
                              <input type="date" name="tanggal_mulai_tunjangan" id="tanggal_mulai_tunjangan" class="form-control" style="color:black;" value="{{$data->tanggal_mulai_tunjangan}}">
                            </div>
                            <div class="form-group mb-4">
                              <label for="tanggal_selesai_tunjangan" class="form-label" style="color:black;">Tanggal Selesai</label>
                              <input type="date" name="tanggal_selesai_tunjangan" id="tanggal_selesai_tunjangan" class="form-control" style="color:black;" value="{{$data->tanggal_selesai_tunjangan}}">
                            </div>
                          </div>
                      </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            var startDateGaji = document.getElementById('tanggal_mulai_gaji');
            var endDateGaji = document.getElementById('tanggal_selesai_gaji');
            var startDateTunjangan = document.getElementById('tanggal_mulai_tunjangan');
            var endDateTunjangan = document.getElementById('tanggal_selesai_tunjangan');
            var currentDate = new Date().toISOString().split('T')[0];
            var nominalgaji = document.getElementById('gaji');
            var nominaltunjangan =document.getElementById('tunjangan');
            var karyawan = document.getElementById('karyawan');

            if (startDateGaji.value <= currentDate || endDateGaji.value <= currentDate || startDateTunjangan.value <= currentDate || endDateTunjangan.value <= currentDate) {
                karyawan.setAttribute('readonly', true);
                karyawan.style.pointerEvents = 'none'; // Prevents dropdown from appearing
                karyawan.onfocus = function() { this.blur(); }; // Prevents focus
            }

            if (startDateGaji.value <= currentDate) {
                startDateGaji.setAttribute('readonly', true);
                nominalgaji.setAttribute('readonly', true);
            }

            if (startDateTunjangan.value <= currentDate) {
                startDateTunjangan.setAttribute('readonly', true);
                nominaltunjangan.setAttribute('readonly', true);
            }

            if (endDateGaji.value <= currentDate) {
                endDateGaji.setAttribute('readonly', true);
                nominalgaji.setAttribute('readonly', true);
            }

            if (endDateTunjangan.value <= currentDate) {
                endDateTunjangan.setAttribute('readonly', true);
                nominalgaji.setAttribute('readonly', true);
            }
        });
    </script>

<script>
function validasiNumber(input) {
    // Hapus karakter titik (.) dari nilai input
    input.value = input.value.replace(/\./g, '');

    // Pastikan hanya karakter angka yang diterima
    input.value = input.value.replace(/\D/g, '');
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

<script>
function validateForm() {
    let karyawan = document.forms["saveform"]["karyawan_id"].value;
    let gaji =document.forms["saveform"]["gaji"].value;
    let mulaigaji = document.forms["saveform"]["tanggal_mulai_gaji"].value;   
    let selesaigaji =document.forms["saveform"]["tanggal_selesai_gaji"].value;
    let tunjangan =document.forms["saveform"]["tunjangan"].value;
    let mulaitunjangan =document.forms["saveform"]["tanggal_mulai_tunjangan"].value;
    let selesaitunjangan =document.forms["saveform"]["tanggal_selesai_tunjangan"].value;
    
    if (karyawan == "" || karyawan == "Pilih Karyawan" ) {
    alert("Karyawan harus diisi.");
    return false;
    }else if(gaji == "") {
    alert("Nominal gaji harus diisi.");
    return false;
    }else if(mulaigaji == "") {
    alert("Tanggal mulai gaji harus diisi.");
    return false;
    }else if(selesaigaji == "") {
    alert("Tanggal selesai gaji harus diisi.");
    return false;
    }else if (mulaigaji == selesaigaji ) {
      alert("Tanggal mulai gaji tidak boleh sama dengan tanggal selesai gaji.");
      return false;
     }else if (mulaigaji > selesaigaji) {
      alert("Tanggal mulai gaji tidak boleh lebih dari tanggal selesai gaji.");
      return false;
     } else if(tunjangan == "") {
    alert("Nominal tunjangan harus diisi.");
    return false;
    }else if(mulaitunjangan == "") {
    alert("Tanggal mulai tunjangan harus diisi.");
    return false;
    }else if(selesaitunjangan == "") {
    alert("Tanggal selesai tunjangan harus diisi.");
    return false;
    } else if (mulaitunjangan == selesaitunjangan) {
      alert("Tanggal mulai tunjangan tidak boleh lebih dari tanggal selesai tunjangan.");
      return false;
     }else if (mulaitunjangan > selesaitunjangan) {
      alert("Tanggal mulai tunjangan tidak boleh lebih dari tanggal selesai tunjangan.");
      return false;
     } 
}
</script>

@endsection
