<?php

namespace App\Exports;

use App\Models\DetailKompensasi;
use App\Models\DetailLembur;
use App\Models\ReportLembur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LemburDownload implements FromCollection, WithHeadings, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $organisasi;
    protected $status_lembur;

    protected $dataLembur;

    public function __construct($bulan, $tahun, $organisasi, $status_lembur, $dataLembur)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->organisasi = $organisasi;
        $this->status_lembur = $status_lembur;
        $this->dataLembur = $dataLembur;
    }

    public function collection()
    {
        if ($this->status_lembur === "Closing") {
        return DetailLembur::with('karyawan.penempatan.organisasi')
            ->whereHas('karyawan.penempatan.organisasi', function($query) {
                $query->where('organisasi_id', $this->organisasi);
            })
            ->get()
            ->map(function ($detail, $key) {
                return [
                    $detail->karyawan->nik,
                    $detail->karyawan->payroll_code,
                    $detail->karyawan->nama_karyawan,
                    $detail->karyawan->jabatan,
                    $detail->karyawan->penempatan->organisasi->organisasi,
                    $detail->karyawan->leader,
                    $detail->karyawan->status_karyawan,
                    $detail->total_jam_pertama_hari_kerja,
                    $detail->total_biaya_jam_pertama_hari_kerja,
                    $detail->total_jam_kedua_hari_kerja,
                    $detail->total_biaya_jam_kedua_hari_kerja,
                    $detail->total_jam_kedua_hari_libur,
                    $detail->total_biaya_jam_kedua_hari_libur,
                    $detail->total_jam_ketiga_hari_libur,
                    $detail->total_biaya_jam_ketiga_hari_libur,
                    $detail->total_jam_keempat_hari_libur,
                    $detail->total_biaya_jam_keempat_hari_libur,
                    $detail->total_jam,
                    $detail->total_biaya_lembur,
                ];
            });
        } else {

          
            return collect($this->dataLembur)->map(function ($detail, $key)  {
                return [
                    $detail['nik'],
                    $detail['payroll_code'],
                    $detail['nama_karyawan'],
                    $detail['jabatan'],
                    $detail['organisasi'],
                    $detail['leader'],
                    $detail['status_karyawan'],
                    $detail['work_days']['first_hour'] ?? '0', 
                    $detail['work_days']['first_hour_cost'] ?? '0', 
                    $detail['work_days']['second_hour'] ?? '0', 
                    $detail['work_days']['second_hour_cost'] ?? '0', 
                    $detail['holidays']['second_hour'] ?? '0', 
                    $detail['holidays']['second_hour_cost'] ?? '0', 
                    $detail['holidays']['third_hour'] ?? '0', 
                    $detail['holidays']['third_hour_cost'] ?? '0', 
                    $detail['holidays']['fourth_hour'] ?? '0', 
                    $detail['holidays']['fourth_hour_cost'] ?? '0', 
                    $detail['total_hours'],
                    $detail['total_cost'],
                ];
            });
        }
            
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Kode Payroll',
            'Nama Karyawan',
            'Jabatan',
            'Organisasi',
            'Leader',
            'Status Karyawan',
            'Jam 1 (Hari Kerja)',
            'Biaya Jam 1 (Hari Kerja)',
            'Jam 2 (Hari Kerja)',
            'Biaya Jam 2 (Hari Kerja)',
            'Jam 2 (Hari Libur)',
            'Biaya Jam 2 (Hari Libur)',
            'Jam 3 (Hari Libur)',
            'Biaya Jam 3 (Hari Libur)',
            'Jam 4 (Hari Libur)',
            'Biaya Jam 4 (Hari Libur)',
            'Total Jam',
            'Total Biaya Lembur',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Menambahkan 6 baris kosong sebelum heading
                $sheet->insertNewRowBefore(1, 4);
    
                // Menulis judul dan periode
                $sheet->setCellValue('A2', 'Laporan Lembur');
              
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
                $monthName = $months[(int)$this->bulan];
              
                $sheet->setCellValue('A3', 'Periode: ' . $monthName . ' ' . $this->tahun);
    
                $sheet->getStyle('A2:A3')->getFont()->setBold(true)->setSize(14);

                foreach (range('A', 'G') as $column) {
                    $sheet->getColumnDimension($column)->setWidth(20);
                }

                foreach (range('H', 'Q') as $column) {
                    $sheet->getColumnDimension($column)->setWidth(30);
                }

                foreach (range('R', 'S') as $column) {
                    $sheet->getColumnDimension($column)->setWidth(20);
                }

                $sheet->getStyle('A5:S5')->getFont()->setBold(true);
                $sheet->getStyle('A5:S5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells('A' . ($lastRow + 1) . ':G' . ($lastRow + 1));
$sheet->setCellValue('A' . ($lastRow + 1), 'Total');
$sheet->getStyle('A' . ($lastRow + 1))->getFont()->setBold(true);
$sheet->getStyle('A' . ($lastRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);



$sumRow = $lastRow + 1;
$columnsToSum = [
'H' => 'Jam 1 (Hari Kerja)',
'I' =>  'Biaya Jam 1 (Hari Kerja)',
'J' =>'Jam 2 (Hari Kerja)',
'K' => 'Biaya Jam 2 (Hari Kerja)',
'L' => 'Jam 2 (Hari Libur)',
'M' => 'Biaya Jam 2 (Hari Libur)',
'N' => 'Jam 3 (Hari Libur)',
'O' => 'Biaya Jam 3 (Hari Libur)',
'P' =>  'Jam 4 (Hari Libur)',
'Q' => 'Biaya Jam 4 (Hari Libur)',
'R' => 'Total Jam',
'S' =>'Total Biaya Lembur',  
];

// Insert the sum formulas
foreach ($columnsToSum as $column => $header) {
    $sheet->setCellValue($column . $sumRow, '=SUM(' . $column . '6:' . $column . $lastRow . ')');
}

// Apply yellow background color to the entire row
$highestColumn = 'S'; // Adjust this to your highest column that needs coloring
$range = 'A' . $sumRow . ':' . $highestColumn . $sumRow;
$sheet->getStyle($range)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                   ->getStartColor()->setARGB('FFFF00');

// Set the currency format for the columns
$currencyColumns = ['I', 'K','M','O', 'Q','S'];
foreach ($currencyColumns as $column) {
    $sheet->getStyle($column . '6:' . $column . $sumRow)
          ->getNumberFormat()
          ->setFormatCode('#,##0');

          $sheet->getStyle($column . '6:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
          $sheet->getStyle($column . '6:' . $column . $sumRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
}

$nonCurrencyColumns = [ 'R', 'H', 'J', 'L', 'N', 'P'];
foreach ($nonCurrencyColumns as $column) {
    $sheet->getStyle($column . '6:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
}

// Apply left alignment to columns A-G for rows other than the total row
$leftAlignColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
for ($row = 6; $row <= $sumRow - 1; $row++) {
    foreach ($leftAlignColumns as $column) {
        $sheet->getStyle($column . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    }
                }
            },  
        ];      
    }
}
