@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">

                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Edit Uang Saku & Insentif</h3>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                    <form name="saveform" action="{{route('updateallowance', $allowance->id)}}" method="post" onsubmit="return validateForm()">
                    @csrf
                    

                            <div class="form-group mb-4">
                                <p>Bulan</p>
                                <select name="bulan" id="filterMonth" class="form-control" style="color:black;">
                                    @php
                                        $months = [
                                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                        ];
                                    @endphp
                                    <option value="" disabled>Pilih Bulan</option>
                                    @foreach ($months as $key => $month)
                                        <option value="{{ $key }}" {{ $key == $allowance->bulan ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                                
                            <div class="form-group mb-4">
                                <p>Tahun</p>
                                <select name="tahun" id="filterYear" class="form-control" style="color:black;">
    @php
        $currentYear = date('Y');
    @endphp
    <option value="" disabled>Pilih Tahun</option>
    <option value="{{ $currentYear }}" {{ $currentYear == $allowance->tahun ? 'selected' : '' }}>{{ $currentYear }}</option>
</select>

                            </div>

                            <div class="form-group mb-4">
                                <p>Apakah ada insentif dan uang saku?</p>
                                <div>
                                    <input type="radio" id="insentif_ya" name="insentif_status" value="ya" {{ $allowance->insentif_status == 'ya' ? 'checked' : '' }} onclick="toggleEmployeeForm(true)">
                                    <label for="insentif_ya">Ya</label>
                                </div>
                                <div>
                                    <input type="radio" id="insentif_tidak" name="insentif_status" value="tidak" {{ $allowance->insentif_status == 'tidak' ? 'checked' : '' }} onclick="toggleEmployeeForm(false)">
                                    <label for="insentif_tidak">Tidak</label>
                                </div>
                            </div>

                            <div id="employee-form" style="display: {{ $allowance->insentif_status == 'ya' ? 'block' : 'none' }};">
                                <div class="karyawandinamis">
                                    @foreach($detailallowance as $index => $detail)
                                    <div class="form-group mb-4 dynamic-field row" id="dynamic-field-{{ $index + 1 }}">
                                        <div class="col-md-4">
                                            <p>Nama Karyawan</p>
                                            <select name="karyawan_id[]" id="karyawan-{{ $index + 1 }}" class="form-control select-karyawan" style="color:black; width:100%;">
                                                <option value="" selected disabled>Pilih Karyawan</option>
                                                @foreach ($karyawan as $item)
                                                    <option value="{{$item->id}}" {{ $item->id == $detail->karyawan_id ? 'selected' : '' }}>{{$item->nama_karyawan}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="uang_saku_{{ $index + 1 }}" class="form-label" style="color:black;">Uang Saku</label>
                                            <input type="number" name="uang_saku[]" id="uang_saku_{{ $index + 1 }}" class="form-control" style="color:black;" value="{{ $detail->uang_saku }}" oninput="validasiNumber(this)">
                                        </div>

                                        <div class="col-md-4">
                                            <label for="insentif_{{ $index + 1 }}" class="form-label" style="color:black;">Insentif</label>
                                            <input type="number" name="insentif[]" id="insentif_{{ $index + 1 }}" class="form-control" style="color:black;" value="{{ $detail->insentif }}" oninput="validasiNumber(this)">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="form-group mb-4">
                                    <button type="button" class="btn btn-info" id="add-button">Tambah Karyawan</button>
                                    <button type="button" class="btn btn-danger" id="remove-button" {{ count($detailallowance) == 1 ? 'disabled' : '' }}>Hapus Karyawan</button>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

    <script>
        let fieldIndex = {{ count($detailallowance) }};

        $(document).ready(function() {
            @foreach($detailallowance as $index => $detail)
                $('#karyawan-{{ $index + 1 }}').select2();
            @endforeach
            $('#filterMonth').select2();
            $('#filterYear').select2();

            $('#add-button').click(function() {
                fieldIndex++;
                const newField = `
                    <div class="form-group mb-4 dynamic-field row" id="dynamic-field-${fieldIndex}">
                        <div class="col-md-4">
                            <p>Nama Karyawan</p>
                            <select name="karyawan_id[]" id="karyawan-${fieldIndex}" class="form-control select-karyawan" style="color:black;">
                                <option value="" selected disabled>Pilih Karyawan</option>
                                @foreach ($karyawan as $item)
                                    <option value="{{$item->id}}">{{$item->nama_karyawan}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="uang_saku_${fieldIndex}" class="form-label" style="color:black;">Uang Saku</label>
                            <input type="number" name="uang_saku[]" id="uang_saku_${fieldIndex}" class="form-control" style="color:black;" oninput="validasiNumber(this)">
                        </div>
                        <div class="col-md-4">
                            <label for="insentif_${fieldIndex}" class="form-label" style="color:black;">Insentif</label>
                            <input type="number" name="insentif[]" id="insentif_${fieldIndex}" class="form-control" style="color:black;" oninput="validasiNumber(this)">
                        </div>
                    </div>
                `;
                $('.karyawandinamis').append(newField);
                $(`#karyawan-${fieldIndex}`).select2();
                $('#remove-button').prop('disabled', false);
            });

            $('#remove-button').click(function() {
                if (fieldIndex > 1) {
                    $(`#dynamic-field-${fieldIndex}`).remove();
                    fieldIndex--;
                }
                if (fieldIndex === 1) {
                    $('#remove-button').prop('disabled', true);
                }
            });
        });

        function validasiNumber(input) {
            input.value = input.value.replace(/\./g, '');
            input.value = input.value.replace(/\D/g, '');
        }

        function toggleEmployeeForm(show) {
            document.getElementById('employee-form').style.display = show ? 'block' : 'none';
        }

        function validateForm() {
    const bulan = document.forms["saveform"]["bulan"].value;
    const tahun = document.forms["saveform"]["tahun"].value;
    const insentif_status = document.forms["saveform"]["insentif_status"].value;

    if (bulan === "") {
        alert("Bulan harus diisi.");
        return false;
    }
    if (tahun === "") {
        alert("Tahun harus diisi.");
        return false;
    }
    if (!insentif_status) {
        alert("Pilih salah satu jawaban insentif atau uang saku.");
        return false;
    }

    if (insentif_status === "ya") {
        const karyawan_ids = document.querySelectorAll('select[name="karyawan_id[]"]');
        const uang_saku_values = document.querySelectorAll('input[name="uang_saku[]"]');
        const insentif_values = document.querySelectorAll('input[name="insentif[]"]');
        let isValidKaryawan = false;
        const selectedKaryawan = [];

        for (let i = 0; i < karyawan_ids.length; i++) {
            if (karyawan_ids[i].value != "") {
                isValidKaryawan = true;

                // Validasi uang saku
                if (uang_saku_values[i].value == "") {
                    alert("Uang saku harus diisi.");
                    return false;
                }

                // Validasi insentif
                if (insentif_values[i].value == "") {
                    alert("Insentif harus diisi.");
                    return false;
                }

                // Validasi karyawan yang sama tidak boleh dipilih lebih dari satu kali
                if (selectedKaryawan.includes(karyawan_ids[i].value)) {
                    alert("Karyawan yang sama tidak boleh dipilih lebih dari satu kali.");
                    return false;
                } else {
                    selectedKaryawan.push(karyawan_ids[i].value);
                }
            }
        }

        if (!isValidKaryawan) {
            alert("Minimal harus memilih satu karyawan.");
            return false;
        }
    }

    return true;
}

    </script>

    <style>
        .select2-container .select2-selection--single {
            height: 45px; 
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
            line-height: 50px; 
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 50px; 
            margin-top: -19px;
            margin-left: -15px;
            color: black;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 50px;
        }

        .select2-container .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
    </style>
@endsection
