<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportDataMAD implements FromCollection, WithHeadings, WithMapping, WithEvents, WithTitle, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'No PBB/Amandemen',
            'NIK (sesuai KTP)',
            'Nama (sesuai KTP)',
            'Unit Kerja Penempatan',
            'Posisi',
            'Kode Cabang Pembayaran',
            'RCC Pembayaran',
            'Tanggal Lembur',
            'Cek Tanggal',
            'L/K',
            'Upah Pokok',
            'Tunjangan Supervisor',
            'Jam Mulai',
            'Jam Selesai',
            'Jumlah Jam Lembur per Hari',
            'Jam Pertama',
            'Jam Kedua',
            'Jam Ketiga',
            'Jam Keempat',
            'Biaya Jam Pertama',
            'Biaya Jam Kedua',
            'Biaya Jam Ketiga',
            'Biaya Jam Keempat',
            'Subtotal',
            'Management Fee (%)',
            'Management Fee (besaran)',
            'Total Sebelum PPN',
            'Keterangan Lembur',
            'Keterangan Perbaikan',
            'Kode SLID',
        ];
    }

    public function map($item): array
    {
        
        return [
            $item['no_amandemen'],
            $item['nik'],
            $item['nama_karyawan'],
            $item['nama_penempatan'],
            $item['nama_posisi'],
            $item['kodepembayaran'],
            $item['rcc'],
            $item['tanggal_lembur'],
            $item['jenis_hari'],
            $item['jenis_hari'] == "Kerja" ? "K" : ($item['jenis_hari'] == "Libur" ? "L" : ""),
            $item['upah'],
            $item['tunjanganamount'],
            $item['jam_mulai'],
            $item['jam_selesai'],
            $item['jumlah_jam_lembur'],
            $item['jam_pertama'] ?: '0',
            $item['jam_kedua'] ?: '0',
            $item['jam_ketiga'] ?: '0',
            $item['jam_keempat'] ?: '0',
            $item['biaya_jam_pertama'],
            $item['biaya_jam_kedua'], 
            $item['biaya_jam_ketiga'], 
            $item['biaya_jam_keempat'], 
            $item['subtotal'], 
            $item['management_fee'] * 100,
            $item['amount_management'], 
            $item['total_sebelum_ppn'], 
            $item['keterangan_lembur'],
            $item['keterangan_perbaikan'],
            $item['kodeslid'],
        ];
    }

    public function registerEvents(): array
    {

        
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 6); // Menambahkan 6 baris kosong sebelum heading
                $sheet->setCellValue('A1', 'Perhitungan Tambahan Biaya untuk Lembur'); // Menulis pada baris A1
                $sheet->setCellValue('A3', 'PT.EXA MITRA SOLUSI');
                $months = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                ];
                $monthName = $months[(int) $this->month];
                $sheet->setCellValue('A5', 'Periode: ' . $monthName . ' ' . $this->year); // Menulis periode pada baris A5
                $sheet->getStyle('A7:AD7')->getFont()->setBold(true); // Membuat heading menjadi bold
                
                $sheet->getStyle('A7:AD7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Get the highest row number
                $lastRow = $sheet->getHighestRow();

                // Define columns to sum
                $columnsToSum = [
                    'O' => 'Jumlah Jam Lembur per Hari',
                    'P' => 'Jam Pertama',
                    'Q' => 'Jam Kedua',
                    'R' => 'Jam Ketiga',
                    'S' => 'Jam Keempat',
                    'T' => 'Biaya Jam Pertama',
                    'U' => 'Biaya Jam Kedua',
                    'V' => 'Biaya Jam Ketiga',
                    'W' => 'Biaya Jam Keempat',
                    'X' => 'Subtotal',
                    'Z' => 'Management Fee (besaran)',
                    'AA' => 'Total Sebelum PPN',
                ];

                $sumRow = $lastRow + 1;

                // Insert the sum formulas
                foreach ($columnsToSum as $column => $header) {
                    $sheet->setCellValue($column . $sumRow, '=SUM(' . $column . '8:' . $column . $lastRow . ')');
                }

                // Apply yellow background color to the entire row
                $sheet->getStyle('A' . $sumRow . ':AD' . $sumRow)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFFF00']
                    ]
                ]);
            }
        ];
    }

    public function title(): string
    {
        return 'Data MAD';
    }
}
