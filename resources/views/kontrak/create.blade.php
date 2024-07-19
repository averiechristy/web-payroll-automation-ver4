@extends('layouts.app')
@section('content')
<div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Tambah Kontrak Karyawan</h3>
                </div>
              </div>

              <div class="card shadow mb-4">
                <div class="card-body">
                  <form name="saveform" action="{{route('kontrak.store')}}" method="post" onsubmit="return validateForm()">
                    @csrf                
                    
                    <div class="form-group mb-4">
                      <label for="karyawan" class="form-label" style="color:black;">Nama Karyawan</label>
                      <select name="karyawan_id" id="karyawan" style="color:black;" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example">
                        <option value="" selected disabled>Pilih Karyawan</option>
                        @foreach ($karyawan as $item)
                          <option value="{{$item->id}}">{{$item->nama_karyawan}}</option>
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

<div class="card shadow mb-4">
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
            var dateInput = document.getElementById('tanggal_mulai_gaji');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('tanggal_selesai_gaji');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
    </script>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('tanggal_mulai_tunjangan');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
        });
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.getElementById('tanggal_selesai_tunjangan');
            dateInput.addEventListener('click', function() {
                this.showPicker();
            });
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
    let awalkontrak = document.forms["saveform"]["tanggal_awal_kontrak"].value.trim();
    let akhirkontrak = document.forms["saveform"]["tanggal_akhir_kontrak"].value.trim();

    if (karyawan == "" || karyawan == "Pilih Karyawan") {
        alert("Karyawan harus diisi.");
        return false;
    } else if (awalkontrak == "") {
        alert("Tanggal awal kontrak harus diisi.");
        return false;
    } else if (akhirkontrak == "") {
        alert("Tanggal akhir kontrak harus diisi.");
        return false;
    } else if (awalkontrak > akhirkontrak) {
        alert("Tanggal awal kontrak tidak boleh lebih dari tanggal akhir kontrak.");
        return false;
    }else if (awalkontrak == akhirkontrak) {
        alert("Tanggal awal kontrak tidak boleh sama dengan tanggal akhir kontrak.");
        return false;
    }

    return true;
}

</script>

@endsection
