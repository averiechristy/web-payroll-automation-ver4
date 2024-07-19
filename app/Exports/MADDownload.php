<?php
namespace App\Exports;

use App\Models\DetailKompensasi;
use App\Models\DetailMAD;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MADDownload implements FromCollection, WithHeadings, WithEvents
{
    protected $bulan;
    protected $tahun;

    protected $status_mad;
    protected $dataMAD;
    public function __construct($bulan, $tahun, $status_mad, $dataMAD)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->status_mad = $status_mad;
        $this->dataMAD = $dataMAD;
    }

    public function collection()
    {

        if ($this->status_mad === "Closing") {
        $item = DetailMAD::with('karyawan.penempatan')->get()->map(function ($detail, $key) {
            return [
                    $detail->karyawan->no_amandemen,
                    $detail->karyawan->nik_ktp,
                    $detail->karyawan->nama_karyawan,
                    $detail->karyawan->penempatan->nama_unit_kerja,
                    $detail->karyawan->posisi->posisi,
                    $detail->karyawan->penempatan->kode_cabang_pembayaran,
                    $detail->karyawan->penempatan->rcc_pembayaran,
                    $detail->tanggal_lembur,
                    $detail->jenis_hari,
                    $detail->jenis_hari == "Kerja" ? "K" : ($detail->jenis_hari == "Libur" ? "L" : ""),
                    $detail->gaji,
                    $detail->tunjangan,
                    $detail->jam_mulai,
                    $detail->jam_selesai,
                    $detail->jumlah_jam_lembur,
                    $detail->jam_pertama ?: '0',
                    $detail->jam_kedua ?: '0',
                    $detail->jam_ketiga ?: '0',
                    $detail->jam_keempat ?: '0',
                    $detail->biaya_jam_pertama,
                    $detail->biaya_jam_kedua, 
                    $detail->biaya_jam_ketiga, 
                    $detail->biaya_jam_keempat, 
                    $detail->subtotal, 
                    $detail->karyawan->management_fee * 100,
                    $detail->management_fee_amount, 
                    $detail->total_sebelum_ppn, 
                    $detail->keterangan_lembur,
                    $detail->keterangan_perbaikan,
                    $detail->karyawan->penempatan->kode_slid,
                ];
           
        });

    } else {
      
        $item = collect($this->dataMAD)->map(function ($detail, $key) 
            {
            return [
                $detail['no_amandemen'],
                $detail['nik'],
                $detail['nama_karyawan'],
                $detail['nama_penempatan'],
                $detail['nama_posisi'],
                $detail['kodepembayaran'],
                $detail['rcc'],
                $detail['tanggal_lembur'],
                $detail['jenis_hari'],
                $detail['jenis_hari'] == "Kerja" ? "K" : ($detail['jenis_hari'] == "Libur" ? "L" : ""),
                $detail['upah'],
                $detail['tunjanganamount'],
                $detail['jam_mulai'],
                $detail['jam_selesai'],
                $detail['jumlah_jam_lembur'],
                $detail['jam_pertama'] ?? '0',
                $detail['jam_kedua'] ?? '0',
                $detail['jam_ketiga'] ?? '0',
                $detail['jam_keempat'] ?? '0',
                $detail['biaya_jam_pertama'],
                $detail['biaya_jam_kedua'],
                $detail['biaya_jam_ketiga'],
                $detail['biaya_jam_keempat'],
                $detail['subtotal'],
                $detail['management_fee'] * 100,
                $detail['amount_management'],
                $detail['total_sebelum_ppn'],
                $detail['keterangan_lembur'] ?? '', // default to empty string if key does not exist
                $detail['keterangan_perbaikan'] ?? '', // default to empty string if key does not exist
                $detail['kodeslid'],
                ];
           
        });
    }

        
        return $item;
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
            'Biaya Manajemen (%)',
            'Biaya Manajemen (besaran)',
            'Total Sebelum PPN',
            'Keterangan Lembur',
            'Keterangan Perbaikan',
            'Kode SLID',
        ];
    }

public function registerEvents(): array
{
    return [
        AfterSheet::class => function(AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            
            // Freeze pane untuk heading di kolom E
            $sheet->freezePane('F8');

            // Menambahkan 6 baris kosong sebelum heading
            $sheet->insertNewRowBefore(1, 6);

            // Menulis judul dan periode
            $sheet->setCellValue('A1', 'Perhitungan Tambahan Biaya untuk Lembur');
            $sheet->setCellValue('A3', 'PT.EXA MITRA SOLUSI');
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $monthName = $months[(int)$this->bulan];
            $sheet->setCellValue('A5', 'Periode: ' . $monthName . ' ' . $this->tahun);

            // Membuat heading menjadi bold
            $sheet->getStyle('A7:E7')->getFont()->setBold(true);
            
            // Set alignment for headings
            $sheet->getStyle('A7:E7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Set width for columns A-E
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setWidth(30);
            }
            $sheet->freezePane('F8');
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
                'Z' => 'Biaya Manajemen (besaran)',
                'AA' => 'Total Sebelum PPN',
            ];

            $sumRow = $lastRow + 1;

            // Insert the sum formulas
            foreach ($columnsToSum as $column => $header) {
                $sheet->setCellValue($column . $sumRow, '=SUM(' . $column . '8:' . $column . $lastRow . ')');
            }

            // Apply yellow background color to the entire row
            $highestColumn = 'AA';
            $range = 'A' . $sumRow . ':' . $highestColumn . $sumRow;
            $sheet->getStyle($range)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                               ->getStartColor()->setARGB('FFFF00');

            // Set the currency format for the columns
            $currencyColumns = ['T', 'U', 'V', 'W', 'X', 'Z', 'AA'];
            foreach ($currencyColumns as $column) {
                $sheet->getStyle($column . '8:' . $column . $sumRow)
                      ->getNumberFormat()
                      ->setFormatCode('#,##0');
                
                // Align Rp to left and numbers to right
                $sheet->getStyle($column . '8:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($column . '8:' . $column . $sumRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
            }

            // Menambahkan teks di sel Z
            $sheet->setCellValue('Z' . ($lastRow + 2), 'Total');
            $sheet->setCellValue('Z' . ($lastRow + 3), 'PPN');
            $sheet->setCellValue('Z' . ($lastRow + 4), 'Subtotal');

            // Membuat teks di sel Z bold
            $sheet->getStyle('Z' . ($lastRow + 2))->applyFromArray(['font' => ['bold' => true]]);
            $sheet->getStyle('Z' . ($lastRow + 3))->applyFromArray(['font' => ['bold' => true]]);
            $sheet->getStyle('Z' . ($lastRow + 4))->applyFromArray(['font' => ['bold' => true]]);

            // Formula untuk total, PPN, dan subtotal
            $sheet->setCellValue('AA' . ($lastRow + 2), '=AA' . ($lastRow + 1));
            $sheet->setCellValue('AA' . ($lastRow + 3), '=ROUND(AA' . ($lastRow + 1) . '*0.11, 0)');
            $sheet->setCellValue('AA' . ($lastRow + 4), '=AA' . ($lastRow + 2) . '+AA' . ($lastRow + 3));

            // Format currency untuk total, PPN, dan subtotal
            $currencyColumns = ['AA' . ($lastRow + 2), 'AA' . ($lastRow + 3), 'AA' . ($lastRow + 4)];
            foreach ($currencyColumns as $cell) {
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // Tambahkan nama dan tanda tangan
            $sheet->setCellValue('A' . ($lastRow + 7), 'Dibuat Oleh, ');
            $sheet->setCellValue('C' . ($lastRow + 7), 'Mengetahui, ');
            $sheet->setCellValue('A' . ($lastRow + 12), '(Sondang Esteria Resta)');
            $sheet->setCellValue('C' . ($lastRow + 12), '(Cynthia Widjaja)');
            
            // Set alignment to left for the item columns
            $columnsToAlignLeft = ['A', 'B', 'C', 'D', 'E'];
            foreach ($columnsToAlignLeft as $column) {
                $sheet->getStyle($column . '8:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            }
        },
    ];
}

}