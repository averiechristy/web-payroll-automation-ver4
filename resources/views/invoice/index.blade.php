@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class ="font-weight-bold">Invoice</h3>
                    </div>
                </div>
                <!-- Card Buat Kompensasi -->
                <div class="card shadow mb-4">
                <div class="py-3 ml-3">
                        <h5 class ="font-weight-bold">Proses Invoice</h5>
                        <form name="saveform" action="{{route('invoice.store')}}" method="post" onsubmit="return validateForm()">
                        @csrf
                            <div class="row mb-3">
                            <div class="col-md-4">
                                    <p>Organisasi</p>
                                    <select name="organisasi_id"  id="organisasi_id" class="form-control" style="color:black;">
                                        <option value="" disabled selected>Pilih Organisasi</option>
                                        @foreach ($organisasi as $item)
                                            <option value="{{ $item->id }}" >{{ $item->organisasi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            <div class="col-md-4">
                                <p>Penempatan</p>                              
                                  <select name="penempatan_id" id="penempatan" class="form-control form-select-lg mb-3" aria-label=".form-select-lg example" style="color:black;">
                                    <option value="" selected disabled>Pilih Penempatan</option>
                                </select>
                            </div>


                                <div class="col-md-3">
            <div class="form-group mb-4">
            <p>Biaya Manajemen (%)</p>
            <input name="management_fee" type="number"  class="form-control " style="border-color: #01004C;" value="" />
        </div>
    </div>
</div>
<div class="row mb-3">
    
    
                            <div class="col-md-4">
                            <p>Template</p>
                            <select name="kode_invoice" class="form-select form-control" aria-label="Default select example" style="color:black;">
  <option selected disabled>Pilih template invoice</option>
  <option value="1">Template 1</option>
  <option value="2">Template 2</option>
 
</select>
</div>
                            
                                <div class="col-md-4">
                                    <p>Bulan</p>
                                    <select name="bulan" id="filterMonth" class="form-control" style="color:black;">
                                        @php
                                            $months = [
                                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                            ];
                                            $currentMonth = date('n');
                                        @endphp
                                        <option value="" disabled selected>Pilih Bulan</option>
                                        @foreach ($months as $key => $month)
                                            <option value="{{ $key }}" >{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
    <p>Tahun</p>
    <select name="tahun" id="filterYear" class="form-control" style="color:black;">
        @php
            $currentYear = date('Y');
            $previousYear = $currentYear - 1;
        @endphp
        <option value="" disabled selected>Pilih Tahun</option>
        <option value="{{ $previousYear }}">{{ $previousYear }}</option>
        <option value="{{ $currentYear }}">{{ $currentYear }}</option>
       
    </select>
</div>

</div>

<div class="row mb-3">

                            <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mt-4">Proses</button>
                            </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card Tabel Data Kompensasi -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="dataTables_length mb-3" id="myDataTable_length">
                            <label for="entries">Show
                                <select id="entries" name="myDataTable_length" aria-controls="myDataTable" onchange="changeEntries()" class>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                entries
                            </label>
                        </div>

                        <div id="myDataTable_filter" class="dataTables_filter">
                            <label for="search">search
                                <input id="search" placeholder>
                            </label>
                        </div>                            
                        
                        <div class="table-responsive">
                            @include('components.alert')
                            <table class="table">
                                <thead>                                                    
                                <tr>
                                    <th>Invoice</th>
                                 
                                    
                                    <th>Detail Invoice</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                               
                                <tbody>
                            @foreach ( $invoice as $item )                      
                                <tr>
                                    <td>{{$item->judul_invoice}}</td>
                           
                                    <td>
                                        @if ($item->kode_invoice == 1)
                                        <a href="{{route('tampilinvoice', $item->id)}}">
                                        Lihat Detail              
                                </a>
                                        @elseif ($item->kode_invoice ==2)
                                        <a href="{{route('tampilinvoice2', $item->id)}}">
                                        Lihat Detail              
                                </a>
                                        @elseif ($item->kode_invoice==3)
                                        <a href="{{route('tampilinvoice3', $item->id)}}">
                                        Lihat Detail              
                                </a>
                                        @endif
                                    </td>
                                    <td>{{$item->status_invoice}}</td>
                                    <td>{{$item->created_by}}</td>
                                    <td>{{$item->created_at}}</td>
                                    <td>
    @if ($item->status_invoice == "Closing")
    <form action="{{ route('batalkan.closing.invoice', $item->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-danger">
            Batalkan Closing
        </button>
    </form>
    @endif
</td>
                                </tr>
     
                                @endforeach
                                </tbody>
                            </table>

                            
                        </div>
                        <div class="dataTables_info" id="dataTableInfo" role="status" aria-live="polite">
                                Showing <span id="showingStart">1</span> to <span id="showingEnd">10</span> of <span id="totalEntries">0</span> entries
                            </div>
                
                            <div class="dataTables_paginate paging_simple_numbers" id="myDataTable_paginate">
                                <a href="#" class="paginate_button" id="doublePrevButton" onclick="doublePreviousPage()"><i class="ti-angle-double-left" aria-hidden="true"></i></a>
                                <a href="#" class="paginate_button" id="prevButton" onclick="previousPage()"><i class="ti-angle-left" aria-hidden="true"></i></a>
                                <span>
                                    <a id="pageNumbers" aria-controls="myDataTable" role="link" aria-current="page" data-dt-idx="0" tabindex="0"></a>
                                </span>
                                <a href="#" class="paginate_button" id="nextButton" onclick="nextPage()"><i class="ti-angle-right" aria-hidden="true"></i></a>
                                <a href="#" class="paginate_button" id="doubleNextButton" onclick="doubleNextPage()"><i class="ti-angle-double-right" aria-hidden="true"></i></a>
                            </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: .25em;
    }
    .paginate_button {
        box-sizing: border-box;
        display: inline-block;
        min-width: 1.5em;
        padding: .5em 1em;
        margin-left: 2px;
        text-align: center;
        text-decoration: none !important;
        cursor: pointer;
        color: inherit !important;
        border: 1px solid transparent;
        border-radius: 2px;
        background: transparent;
    }
    .dataTables_length {
        float: left;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #aaa;
        border-radius: 3px;
        padding: 5px;
        background-color: transparent;
        color: inherit;
        padding: 4px;
    }
    .dataTables_info {
        clear: both;
        float: left;
        padding-top: .755em;
    }    
    .dataTables_filter {
        float: right;
        text-align: right;
    }
    .dataTables_filter input {
        border: 1px solid #aaa;
        border-radius: 3px;
        padding: 5px;
        background-color: transparent;
        color: inherit;
        margin-left: 3px;
    }
    .btn-active {
        background-color: #007bff;
        color: #fff;
    }
    .dataTables_paginate {
        text-align: center;
    }
    .paginate_button {
        display: inline-block;
        margin: 5px;
        text-align: center;
        border: 1px solid #000; 
        padding: 5px 10px;
    }
    @media (max-width: 768px) {
        .paginate_button {
            padding: 3px 6px;
        }
    }
    @media (max-width: 576px) {
        .dataTables_paginate {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .paginate_button {
            padding: 2px 4px;
            margin: 2px;
        }
    }
</style>

<!-- Scripts -->
<script>
    var itemsPerPage = 10;
    var currentPage = 1;
    var filteredData = [];
    
    function initializeData() {
        var tableRows = document.querySelectorAll("table tbody tr");
        filteredData = Array.from(tableRows);
        updatePagination();
    }
    initializeData();
    
    function doublePreviousPage() {
        if (currentPage > 1) {
            currentPage = 1;
            updatePagination();
        }
    }
    
    function nextPage() {
        var totalPages = Math.ceil(document.querySelectorAll("table tbody tr").length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination();
        }
    }
    
    function doubleNextPage() {
        var totalPages = Math.ceil(document.querySelectorAll("table tbody tr").length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage = totalPages;
            updatePagination();
        }
    }
    
    function previousPage() {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    }
    
    function updatePagination() {
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;
        var tableRows = document.querySelectorAll("table tbody tr");
        tableRows.forEach(function (row) {
            row.style.display = 'none';
        });
        for (var i = startIndex; i < endIndex && i < filteredData.length; i++) {
            filteredData[i].style.display = 'table-row';
        }
        var totalPages = Math.ceil(filteredData.length / itemsPerPage);
        var pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';
        var totalEntries = filteredData.length;
        document.getElementById('showingStart').textContent = startIndex + 1;
        document.getElementById('showingEnd').textContent = Math.min(endIndex, totalEntries);
        document.getElementById('totalEntries').textContent = totalEntries;
        var pageRange = 3;
        var startPage = Math.max(1, currentPage - Math.floor(pageRange / 2));
        var endPage = Math.min(totalPages, startPage + pageRange - 1);
        for (var i = startPage; i <= endPage; i++) {
            var pageButton = document.createElement('button');
            pageButton.className = 'btn btn-primary btn-sm mr-1 ml-1';
            pageButton.textContent = i;
            if (i === currentPage) {
                pageButton.classList.add('btn-active');
            }
            pageButton.onclick = function () {
                currentPage = parseInt(this.textContent);
                updatePagination();
            };
            pageNumbers.appendChild(pageButton);
        }
    }

    function changeEntries() {
        var entriesSelect = document.getElementById('entries');
        var selectedEntries = parseInt(entriesSelect.value);
        itemsPerPage = selectedEntries;
        currentPage = 1;
        updatePagination();
    }

    function applySearchFilter() {
        var searchInput = document.getElementById('search');
        var filter = searchInput.value.toLowerCase();
        filteredData = Array.from(document.querySelectorAll("table tbody tr")).filter(function (row) {
            var rowText = row.textContent.toLowerCase();
            return rowText.includes(filter);
        });
        currentPage = 1;
        updatePagination();
    }

    updatePagination();
    document.getElementById('search').addEventListener('input', applySearchFilter);
</script>


<script>
function validateForm() {
    
    let bulan = document.forms["saveform"]["bulan"].value;
    let tahun = document.forms["saveform"]["tahun"].value;
    let organisasi =document.forms["saveform"]["organisasi_id"].value;
    let managementfee =document.forms["saveform"]["management_fee"].value;
    let penempatan =document.forms["saveform"]["penempatan_id"].value;
    
    if (organisasi == "" || organisasi == "Pilih Organisasi"){
        alert("Organisasi harus diisi.");
        return false;
    } else if (penempatan == "" || penempatan == "Pilih Penempatan"){
        alert("Penempatan harus diisi.");
        return false;
    } 
    
    else  if (bulan == "" || bulan == "Pilih Bulan" ) {
    alert("Bulan harus diisi.");
    return false;
    } else if (tahun == "" || tahun == "Pilih Tahun"){
        alert("Tahun harus diisi.");
        return false;
    } else if (managementfee == ""){
        alert("Biaya manajemen harus diisi.");
        return false;
    }
    

}
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>

<script>
$(document).ready(function() {


    $('#organisasi_id').on('change', function() {
        var organisasiID = $(this).val();
        if (organisasiID) {
            $.ajax({
                url: '/getPenempatanedit/' + organisasiID,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    $('#penempatan').empty();
                    $('#penempatan').append('<option value="" disabled selected>Pilih Penempatan</option>');
                    $('#penempatan').append('<option value="all">Pilih Seluruh Penempatan</option>');
                    $.each(data, function(key, value) {
                        $('#penempatan').append('<option value="' + value.id + '">' + value.nama_unit_kerja + '</option>');
                    });
                  
                }
            });
        } else {
            $('#penempatan').empty();
        }
    });
});
</script>


@endsection


