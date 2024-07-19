<?php

namespace App\Exports;

use App\Models\DetailInvoice;
use App\Models\Organisasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoiceDownloadKedua implements FromCollection, WithHeadings, WithEvents
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
                    $detail->subtotal_biaya_jasa,
                    $detail->management_fee,
                    '',
                    '',
                 
                ];
            });
        } else {
            return collect($this->dataInvoice)->map(function ($detail, $key) {
                return [
                    $key + 1, // No
                    $detail['nama'],
                    $detail['subtotalbiayajasa'],
                    $detail['managementfee'],
                    '',
                    '',
                ];
            });
        }
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Biaya Jasa Per Bulan',
            'Biaya Manajemen ' . $this->managementfee . ' %',
            'Subtotal Biaya Jasa',
            'Keterangan',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->insertNewRowBefore(1, 4);



                $highestRow = $event->sheet->getHighestRow();


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
    
                $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
    
                $sheet->getStyle('A5:F5')->getFont()->setBold(true);
    
                // Set border for headings
                $sheet->getStyle('A5:F5')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
                for ($row = 6; $row <= $highestRow; $row++) {
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
                
                foreach (range('C', 'F') as $column) {
                    $sheet->getColumnDimension($column)->setWidth(15);
                }

                $sheet->getColumnDimension('B')->setWidth(20);

                for ($row = 6; $row <= $highestRow; $row++) {
                    // Subtotal Biaya Jasa formula
                    $event->sheet->getCell("E{$row}")->setValue("=SUM(C{$row}:D{$row})");
    
                    // Total Biaya Jasa formula
                   
                }

                $lastRow = $sheet->getHighestRow();
                $sumRow = $lastRow + 1;

                $sheet->setCellValue('B' . ($lastRow + 1), 'Total Biaya Jasa');
                $sheet->getStyle('B' . ($lastRow + 1))->getFont()->setBold(true);
$sheet->getStyle('B' . ($lastRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


$columnsToSum = ['C', 'E'];

foreach ($columnsToSum as $column) {
    $sheet->setCellValue($column . $sumRow, "=SUM({$column}6:{$column}{$lastRow})");
}

                $currencyColumns = ['C', 'D', 'E'];
                  
                foreach ($currencyColumns as $column) {
                    $sheet->getStyle($column . '6:' . $column . $sumRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
    
                    // Align Rp to left and numbers to right
                    $sheet->getStyle($column . '6:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($column . '6:' . $column . $sumRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
                }
    
                $sheet->setCellValue('B' . ($lastRow + 3), 'Keterangan');
                $sheet->getStyle('B' . ($lastRow + 3))->getFont()->setBold(true);
                $sheet->getStyle('B' . ($lastRow + 3))->getFont()->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
                $sheet->getStyle('B' . ($lastRow + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            
               

                $sheet->setCellValue('B' . ($lastRow + 4), '* Perhitungan belum termasuk PPN ');
                
                $sheet->getStyle('B' . ($lastRow + 4))->getFont()->setItalic(true);


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
