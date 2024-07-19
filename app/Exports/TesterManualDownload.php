<?php

namespace App\Exports;

use App\Models\DetailInvoice;
use App\Models\Organisasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TesterManualDownload implements FromCollection, WithHeadings, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $status_invoicetm;
    protected $datainvoicetm;
    protected $managementfee;

    public function __construct($bulan, $tahun, $status_invoicetm, $datainvoicetm, $managementfee)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->status_invoicetm = $status_invoicetm;
        $this->datainvoicetm = $datainvoicetm;
        $this->managementfee = $managementfee;
    }

    public function collection()
    {
        if ($this->status_invoicetm === "Closing") {
            return collect([]);
        } else {
            return collect($this->datainvoicetm)->map(function ($detail, $key) {
                return [
                    $key + 1, // No
                    $detail['nama'],
                    $detail['tanggal_lembur'],
                    $detail['totaljamharikerja'],
                    $detail['totaljamharilibur'],
                    $detail['biayalembur'],
                    ''
                ];
            });
        }
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Tanggal Lembur',
            'Total Jam Lembur(Hari Kerja)',
            'Total Jam Lembur (Hari Libur)',
            'Biaya lembur',
            'Keterangan',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 5);
                $highestRow = $event->sheet->getHighestRow();

                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];

                $monthName = $months[(int)$this->bulan];

                // Menulis judul dan periode
                $sheet->setCellValue('A1', 'Invoice Unit Tester Manual');
                $sheet->setCellValue('A2', 'PT EXA MITRA SOLUSI');
                $sheet->setCellValue('A3', 'Periode: ' . $monthName . ' ' . $this->tahun);
                $sheet->setCellValue('A5', 'Rekap Lembur');
                $sheet->getStyle('A5')->getFont()->setBold(true);


                // Mengatur gaya judul
                $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(10);

                // Menghitung total biaya lembur
                $lastRow = $sheet->getHighestRow();
                $sumRow = $lastRow + 1;
                $sheet->setCellValue('E' . ($lastRow + 1), 'Total Biaya Lembur');
                $sheet->getStyle('E' . $lastRow+1)->getFont()->setBold(true);
                $columnsToSum = ['F'];
                foreach ($columnsToSum as $column) {
                    $sheet->setCellValue($column . $sumRow, "=SUM({$column}7:{$column}{$lastRow})");

                    $sheet->getStyle($column . $sumRow)->getFont()->setBold(true);
                }

                $currencyColumns = ['F'];
                foreach ($currencyColumns as $column) {
                    $sheet->getStyle($column . '7:' . $column . $sumRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                    $sheet->getStyle($column . '7:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($column . '7:' . $column . $sumRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
                }

                // Mengatur tabel rekap lembur
                $sheet->getStyle('A6:G6')->getAlignment()->setWrapText(true)->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A6:G6')->getFont()->setBold(true);
                $sheet->getStyle('A6:G6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

                // Mengatur kolom tanggal lembur
                $sheet->getStyle('C7:C' . $lastRow)->getAlignment()->setWrapText(true);

                $sheet->setCellValue('A' . ($sumRow + 1), 'Rekap Absensi');

                // Mengatur gaya judul tabel
                $sheet->getStyle('A' . ($sumRow + 1) . ':I' . ($sumRow + 1))->getFont()->setBold(true);

                // Insert tabel baru
                $sheet->setCellValue('A' . ($sumRow + 2), 'No');
                $sheet->setCellValue('B' . ($sumRow + 2), 'Nama');
                $sheet->setCellValue('C' . ($sumRow + 2), 'Total Hari Kerja');
                $sheet->setCellValue('D' . ($sumRow + 2), 'Realisasi Hari Kerja');
                $sheet->setCellValue('E' . ($sumRow + 2), 'Absen');
                $sheet->setCellValue('F' . ($sumRow + 2), 'Persentase Kehadiran');
                $sheet->setCellValue('G' . ($sumRow + 2), 'Biaya Lembur');
                $sheet->setCellValue('H' . ($sumRow + 2), 'Biaya Jasa Per Bulan');
                $sheet->setCellValue('I' . ($sumRow + 2), 'Realisasi Invoice');

                // Mengatur tabel rekap absensi
                $sheet->getStyle('A' . ($sumRow + 2) . ':I' . ($sumRow + 2))->getAlignment()->setWrapText(true)->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . ($sumRow + 2) . ':I' . ($sumRow + 2))->getFont()->setBold(true);
                $sheet->getStyle('A' . ($sumRow + 2) . ':I' . ($sumRow + 2))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

                // Mengisi data dari $datainvoicetm
                $row = $sumRow + 3; // Baris pertama data setelah heading
                foreach ($this->datainvoicetm as $key => $detail) {
                    $sheet->setCellValue('A' . $row, $key + 1);
                    $sheet->setCellValue('B' . $row, $detail['nama']);
                    $sheet->setCellValue('C' . $row, $detail['totalhari']);
                    $sheet->setCellValue('D' . $row, $detail['realisasiharikerja']);
                    $sheet->setCellValue('E' . $row, $detail['absen']);
                    $sheet->setCellValue('F' . $row, $detail['presentasekehadiran'] . '%');
                    $sheet->setCellValue('G' . $row, $detail['biayalembur']);
                    $sheet->setCellValue('H' . $row, $detail['biayajasaperbulan']);
                    $sheet->setCellValue('I' . $row, $detail['realisasiinvoice']);
                    $row++;
                }

                $lastRowData = $sheet->getHighestRow();
                $totalBiayaJasaRow = $lastRowData + 1;
                $sheet->setCellValue('H' . $totalBiayaJasaRow, 'Total Biaya Jasa');
                $sheet->getStyle('H' . $totalBiayaJasaRow)->getFont()->setBold(true);
                $columnsToSumdua = ['I'];
                foreach ($columnsToSumdua as $columndua) {
                    $sheet->setCellValue($columndua . $totalBiayaJasaRow, "=SUM({$columndua}12:{$columndua}{$lastRowData})");
                    $sheet->getStyle($columndua . $totalBiayaJasaRow)->getFont()->setBold(true);
                }

                $currencyColumnsdua = ['G', 'H', 'I'];
                foreach ($currencyColumnsdua as $columndua) {
                    $sheet->getStyle($columndua . '12:' . $columndua . $totalBiayaJasaRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                    $sheet->getStyle($columndua . '12:' . $columndua . $totalBiayaJasaRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($columndua . '12:' . $columndua . $totalBiayaJasaRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
                }

                // Mengatur isian data rata kiri
                $sheet->getStyle('A7:I' . $totalBiayaJasaRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // Mengatur lebar kolom
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(20);

                // Mengatur agar teks terbungkus
                $sheet->getStyle('A6:I' . $totalBiayaJasaRow)->getAlignment()->setWrapText(true);


                $sheet->setCellValue('A' . ($lastRowData + 3), 'Keterangan : ');
                $sheet->getStyle('A' . ($lastRowData + 3))->getFont()->setBold(true);
                $sheet->getStyle('A' . ($lastRowData + 3))->getFont()->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
                $sheet->getStyle('A' . ($lastRowData + 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           
                $sheet->setCellValue('A' . ($lastRowData + 4), '* Perhitungan belum termasuk PPN ');
                
                $sheet->getStyle('A' . ($lastRowData + 4))->getFont()->setItalic(true);


                $sheet->setCellValue('A' . ($lastRowData + 6), 'Tanggal : ');

                $sheet->getStyle('A' . ($lastRowData + 6))->getFont()->setBold(true);


   $sheet->setCellValue('A' . ($lastRowData + 8), 'Pembuat,  ');
   $sheet->getStyle('A' . ($lastRowData + 8))->getFont()->setBold(true);
   $sheet->getStyle('A' . ($lastRowData + 8))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

   $sheet->setCellValue('A' . ($lastRowData + 13), '(Sondang Esteria Resta)');
   $sheet->getStyle('A' . ($lastRowData + 13))->getFont()->setBold(true);
   $sheet->getStyle('A' . ($lastRowData + 13))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);



   $sheet->setCellValue('C' . ($lastRowData + 8), 'Mengetahui,  ');
   $sheet->getStyle('C' . ($lastRowData + 8))->getFont()->setBold(true);
   $sheet->getStyle('C' . ($lastRowData + 8))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

   $sheet->setCellValue('C' . ($lastRowData + 13), '(Cynthia Widjaja)');
   $sheet->getStyle('C' . ($lastRowData + 13))->getFont()->setBold(true);
   $sheet->getStyle('C' . ($lastRowData + 13))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

           
            }
        ];
    }
}
