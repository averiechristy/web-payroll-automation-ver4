@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">

                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Tambah Konfigurasi</h3>
                    </div>            
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form name="saveform" action="{{route('konfigurasi.store')}}" method="post" onsubmit="return validateForm()">
                            @csrf

                            <div class="form-group mb-4">
                                <label for="organisasi" class="form-label" style="color:black; font-weight:bold;">Organisasi</label>
                                <select name="organisasi_id" id="organisasi" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example" style="color:black;">
                                    <option value="" selected disabled>Pilih Organisasi</option>
                                    @foreach ($organisasi as $item)
                                        <option value="{{$item->id}}">{{$item->organisasi}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4 ml-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pilihSeluruhPenempatan" name="pilih_seluruh_penempatan">
                                    <label class="form-check-label" for="pilihSeluruhPenempatan" style="font-weight:bold;">
                                        Pilih Seluruh Penempatan
                                    </label>
                                </div>
                            </div>

                            <div id="seluruhPenempatan" style="display:none;">
                                <div class="form-group mb-4 ml-4">
                                    <label for="penempatan" class="form-label" style="color:black;font-weight:bold;">Penempatan Dikecualikan</label>
                                    <div id="penempatanCheckboxes"></div>
                                </div>
                            </div>

                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h4 class="m-0 font-weight-bold text-primary">Pengaturan Konfigurasi</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                    Tunjangan
                                    <i class="ti-help icon-circle" data-toggle="modal" data-target="#tunjanganModal" style="cursor:pointer;"></i>
                                        </label>
                                        <div class="form-check ml-4">
                                            <input class="form-check-input" type="checkbox" id="hitungTunjangan" name="hitungtunjangan">
                                            <label class="form-check-label" for="hitungTunjangan">
                                                Hitung Tunjangan
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <label for="perhitunganPayroll" style="font-weight:bold;">Perhitungan Payroll</label>
                                        <div class="form-check ml-4">
                                            <input class="form-check-input" type="radio" name="perhitunganpayroll" id="harikerja" value="harikerja">
                                            <label class="form-check-label" for="harikerja">
                                                Hari Kerja
                                            </label>
                                        </div>
                                        <div class="form-check ml-4">
                                            <input class="form-check-input" type="radio" name="perhitunganpayroll" id="calendar" value="kalender">
                                            <label class="form-check-label" for="calendar">
                                                Kalender
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="buatInvoice" style="font-weight:bold;">Pembuatan Invoice</label>
                                        <div class="form-check ml-4">
                                            <input class="form-check-input" type="checkbox" id="buatInvoice" name="buatinvoice">
                                            <label class="form-check-label" for="buatInvoice">
                                                Buat Invoice
                                            </label>
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
</div>

<!-- Modal -->
<div class="modal fade" id="tunjanganModal" tabindex="-1" role="dialog" aria-labelledby="tunjanganModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tunjanganModalLabel">Konfigurasi Hitung Tunjangan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <p>Hitung Tunjangan merupakan pengaturan untuk mengatur apakah dalam proses pembuatan laporan menghitung tunjangan atau tidak.</p>
      
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('#organisasi').select2();

        $('#organisasi').on('change', function() {
            var organisasiID = $(this).val();
            if(organisasiID) {
                $.ajax({
                    url: '/getPenempatan/'+organisasiID,
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        $('#penempatanCheckboxes').empty();
                        $.each(data, function(key, value) {
                            $('#penempatanCheckboxes').append('<div class="form-check"><input class="form-check-input penempatan-checkbox" type="checkbox" id="penempatan_'+ value.id +'" name="penempatan_ids[]" value="'+ value.id +'"><label class="form-check-label" for="penempatan_'+ value.id +'">'+ value.nama_unit_kerja +'</label></div>');
                        });
                    }
                });
            } else {
                $('#penempatanCheckboxes').empty();
            }
        });

        $('#pilihSeluruhPenempatan').on('change', function() {
            if($(this).is(':checked')) {
                $('#seluruhPenempatan').show();
            } else {
                $('#seluruhPenempatan').hide();
                $('#penempatanCheckboxes').find('input:checkbox').prop('checked', false);
            }
        });
    });

    function validateForm() {
        let organisasi = document.forms["saveform"]["organisasi_id"].value;
        let hitungTunjangan = document.forms["saveform"]["hitungtunjangan"].checked;
        let perhitunganPayrollHariKerja = document.forms["saveform"]["perhitunganpayroll"][0].checked;
        let perhitunganPayrollKalender = document.forms["saveform"]["perhitunganpayroll"][1].checked;
        let seluruhpenempatan = document.forms["saveform"]["pilih_seluruh_penempatan"].checked;

        if (organisasi == "" || organisasi=="Pilih Organisasi") {
            alert("Organisasi harus diisi.");
            return false;
        } else if (!seluruhpenempatan){
            alert("Silahkan pilih seluruh penempatan.");
            return false;

        } else if (!perhitunganPayrollHariKerja && !perhitunganPayrollKalender) {
            alert("Perhitungan payroll harus diisi.");
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

    .icon-circle {
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        font-size: 10px;
    }
</style>

@endsection
