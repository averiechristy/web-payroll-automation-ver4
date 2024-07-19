<?php

namespace App\Exports;

use App\Models\DetailInvoice;
use App\Models\Organisasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoiceDownload implements FromCollection, WithHeadings, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $organisasi;
    protected $status_invoice;
    protected $dataInvoice;
    protected $managementfee;

    protected $tampilpenempatan;

    public function __construct($bulan, $tahun, $organisasi, $status_invoice, $dataInvoice, $managementfee, $tampilpenempatan)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->organisasi = $organisasi;
        $this->status_invoice = $status_invoice;
        $this->dataInvoice = $dataInvoice;
        $this->managementfee = $managementfee;
        $this->tampilpenempatan = $tampilpenempatan;
    }

    public function collection()
    {
        if ($this->status_invoice === "Closing") {
            return DetailInvoice::with('karyawan.penempatan.organisasi')
            ->whereHas('karyawan.penempatan.organisasi', function($query) {
                $query->where('organisasi_id', $this->organisasi);
            })
            ->get()
            ->map(function ($detail, $key) {
                return [
                    $key + 1, // No
                    $detail->karyawan->nama_karyawan,
                    $detail->karyawan->tanggal_awal_kontrak,
                    $detail->gajipokok,
                    $detail->tunjangan,
                    $detail->biayatransport,
                    $detail->bpjs_tk,
                    $detail->bpjs_kesehatan,
                    $detail->jaminan_pensiun,
                    $detail->subtotal_biaya_jasa,
                    $detail->management_fee,
                    '',
                    ''
                 
                ];
            });
        } else {
            return collect($this->dataInvoice)->map(function ($detail, $key) {
                return [
                    $key + 1, // No
                    $detail['nama'],
                    $detail['joindate'],
                    $detail['gajipokok'],
                    $detail['tunjangan'],
                    $detail['insentif'], // Biaya Transport
                    $detail['bpjstk'],
                    $detail['bpjskesehatan'],
                    $detail['jaminanpensiun'],
                    $detail['subtotalbiayajasa'],
                    $detail['managementfee'],
                    '', // Total Biaya Jasa (to be filled with formula)
                    '' // Keterangan
                ];
            });
        }
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Tanggal Bergabung',
            'Gaji Pokok',
            'Tunjangan',
            'Biaya Transport',
            'BPJS TK 4,24% dari Gaji Pokok',
            'BPJS Kesehatan 4% dari Gaji Pokok',
            'Jaminan Pensiun 2% dari Gaji Pokok',
            'Subtotal Biaya Jasa',
            'Biaya Manajemen ' . $this->managementfee . ' %',
            'Total Biaya Jasa',
            'Keterangan',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Menambahkan 4 baris kosong sebelum heading
                $sheet->insertNewRowBefore(1, 4);
    
                // Convert bulan number to month name
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
    
                $monthName = $months[(int)$this->bulan];
    
                $dataorg = Organisasi::find($this->organisasi);
                $namaorg = $dataorg->organisasi;
    
                // Menulis judul dan periode
                $sheet->setCellValue('A1', 'Invoice ' . $namaorg . '' .'( ' . $this->tampilpenempatan .' )');
                $sheet->setCellValue('A2', 'PT EXA MITRA SOLUSI');
                $sheet->setCellValue('A3', 'Periode: ' . $monthName . ' ' . $this->tahun);
    
                $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);
    
                $sheet->getStyle('A5:M5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
    
                $sheet->getStyle('A5:M5')->getFont()->setBold(true);
    
                // Set border for headings
                $sheet->getStyle('A5:M5')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
    
                foreach (range('A', 'M') as $column) {
                    $sheet->getColumnDimension($column)->setWidth(20);
                }
    
                $highestRow = $event->sheet->getHighestRow();
                $sheet->getStyle('A6:M' . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    
                $lastRow = $sheet->getHighestRow();
                $sumRow = $lastRow + 1;
    

                $sheet->setCellValue('B' . ($lastRow + 1), 'Total Biaya Jasa');
                $sheet->getStyle('B' . ($lastRow + 1))->getFont()->setBold(true);
$sheet->getStyle('B' . ($lastRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


$columnsToSum = ['D', 'F', 'K'];

foreach ($columnsToSum as $column) {
    $sheet->setCellValue($column . $sumRow, "=SUM({$column}6:{$column}{$lastRow})");
}

                $currencyColumns = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
                  
                foreach ($currencyColumns as $column) {
                    $sheet->getStyle($column . '6:' . $column . $sumRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
    
                    // Align Rp to left and numbers to right
                    $sheet->getStyle($column . '6:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($column . '6:' . $column . $sumRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
                }
    
                // Set border for content rows
                for ($row = 6; $row <= $highestRow; $row++) {
                    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                // Start from row 6 since row 1-3 are titles and 4-5 are empty
                for ($row = 6; $row <= $highestRow; $row++) {
                    // Subtotal Biaya Jasa formula
                    

                    // Total Biaya Jasa formula
                    $event->sheet->getCell("L{$row}")->setValue("=SUM(J{$row}:K{$row})");
                    
                }
                
                $sheet->setCellValue('B' . ($lastRow + 3), 'Keterangan');
                $sheet->getStyle('B' . ($lastRow + 3))->getFont()->setBold(true);
                $sheet->getStyle('B' . ($lastRow + 3))->getFont()->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
                $sheet->getStyle('B' . ($lastRow + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
                $sheet->setCellValue('B' . ($lastRow + 4), '* Biaya Transport Rp.40.000 / hari ');
                
                $sheet->getStyle('B' . ($lastRow + 4))->getFont()->setItalic(true);

                $sheet->setCellValue('B' . ($lastRow + 5), '* Perhitungan belum termasuk PPN ');
                
                $sheet->getStyle('B' . ($lastRow + 5))->getFont()->setItalic(true);


                $sheet->setCellValue('B' . ($lastRow + 6), 'Tanggal : ');

                $sheet->getStyle('B' . ($lastRow + 6))->getFont()->setBold(true);


   $sheet->setCellValue('B' . ($lastRow + 8), 'Pembuat,  ');
   $sheet->getStyle('B' . ($lastRow + 8))->getFont()->setBold(true);
   $sheet->getStyle('B' . ($lastRow + 8))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

   $sheet->setCellValue('B' . ($lastRow + 13), '(Sondang Esteria Resta)');
   $sheet->getStyle('B' . ($lastRow + 13))->getFont()->setBold(true);
   $sheet->getStyle('B' . ($lastRow + 13))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);



   $sheet->setCellValue('F' . ($lastRow + 8), 'Mengetahui,  ');
   $sheet->getStyle('F' . ($lastRow + 8))->getFont()->setBold(true);
   $sheet->getStyle('F' . ($lastRow + 8))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

   $sheet->setCellValue('F' . ($lastRow + 13), '(Cynthia Widjaja)');
   $sheet->getStyle('F' . ($lastRow + 13))->getFont()->setBold(true);
   $sheet->getStyle('F' . ($lastRow + 13))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


            }
        ];
    }
    
}
