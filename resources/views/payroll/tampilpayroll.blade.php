@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class ="font-weight-bold">Detail {{$payroll -> judul_payroll}}</h3>
                    </div>
                </div>

                <!-- Card Buat Kompensasi -->
                <div class="card shadow mb-4">

                </div>
              

                <div class="row mb-4">
    <div class="col-md-12">
        <a href="{{ route('download.payroll', ['organisasi_id' => $payroll->organisasi_id,'bulan' => $payroll->bulan, 'tahun' => $payroll->tahun, 'status_payroll' => $payroll->status_payroll, 'dataPayroll' => json_encode($dataToShow)]) }}" class="btn btn-success">Download Laporan Payroll</a>
    </div>
</div>


<form id="closepayrollForm" action="{{ route('close.payroll', $payroll->id)}}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="dataToShow" id="datapayroll">
</form>

<script>
  function closepayroll() {
    var dataToShow = @json($dataToShow); // Assuming $datapayroll is passed from your backend
    document.getElementById('datapayroll').value = JSON.stringify(dataToShow);
    document.getElementById('closepayrollForm').submit();
}

</script>
<div class="row mb-4">
    <div class="col-md-12">
        @if ($payroll->status_payroll == 'Closing')
        <button type="button" class="btn btn-primary" disabled>Closing Payroll</button>
        @else
        <button type="button" class="btn btn-primary" onclick="closepayroll()">Closing Payroll</button>
        @endif
    </div>
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
                            <table class="table table-bordered">
                                <thead>                                                    
                           <tr>
                           <tr>
    <th rowspan="2">ID Karyawan</th>
    <th rowspan="2">Kode Payroll</th>
    <th rowspan="2">Nama</th>
    <th rowspan="2">Posisi</th>
    <th rowspan="2">Organisasi</th>
    <th rowspan="2">Gaji Pokok</th>
    <th rowspan="2">Adjusment</th>
    <th colspan="4" style="text-align: center;">Allowance</th>
    <th rowspan="2">Total Allowance</th>
    <th rowspan="2">Kompensasi</th>  
    <th rowspan="2">Total</th>  
</tr>
  <tr>
  <td>Tunjangan Jabatan</td>
  <td>Uang Saku Perjalanan Dinas</td>
  <td>Insentif</td>
  <td>Lembur</td>

  </tr>
                        </tr>
                                </thead>
                                <tbody>
                                @foreach($dataToShow as $index => $data)                         
                                <tr>
                                <td>{{ $data['nik'] }}</td>
                                <td>{{ $data['payrollcode'] }}</td>
                                <td>{{ $data['namakaryawan'] }}</td>
                                <td>{{ $data['namaposisi'] }}</td>
                                <td>{{ $data['namaorganisasi'] }}</td>
                                <td>{{ $data['gajikaryawan'] }}</td>
                                <td>
    <?php
    if ($data['adjusment_salary'] < 0) {
        echo '<span style="color: red;">(' . 'Rp ' . number_format(($data['adjusment_salary']), 0, ',', '.') . ')</span>';
    } else {
        echo 'Rp ' . number_format($data['adjusment_salary'], 0, ',', '.');
    }
    ?>
</td>

                                <td>{{ 'Rp ' . number_format($data['tunjangan'], 0, ',', '.') }}</td>
                                <td>{{ 'Rp ' . number_format($data['uangsaku'], 0, ',', '.') }}</td>  
                                <td>{{ 'Rp ' . number_format($data['insentif'], 0, ',', '.') }}</td>
                                <td>{{ 'Rp ' . number_format($data['overtime'], 0, ',', '.') }}</td> 
                                <td>{{ 'Rp ' . number_format($data['total_allowance'], 0, ',', '.') }}</td>
                                <td>{{ 'Rp ' . number_format($data['kompensasi'], 0, ',', '.') }}</td>
                                <td>{{ 'Rp ' . number_format($data['total'], 0, ',', '.') }}</td>
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


@endsection
