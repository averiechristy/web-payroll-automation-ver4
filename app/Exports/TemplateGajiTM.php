<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class TemplateGajiTM implements FromCollection, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Bulan',
            'Tahun',
            'Gaji',
            'Cadangan Transfer Knowledge',
        ];
    }

    public function collection()
    {
        // Mengembalikan koleksi kosong
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();
                
                // Unlock all cells
                $event->sheet->getStyle('1:5000')->getProtection()->setLocked(false);
                // Lock the header row
                $sheet->getStyle('A1:XFD1')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                    
                // Enable sheet protection
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setSelectLockedCells(false);
                $sheet->getProtection()->setSelectUnlockedCells(false);
                $sheet->getProtection()->setFormatCells(false);
                $sheet->getProtection()->setFormatColumns(false);
                $sheet->getProtection()->setFormatRows(false);
                $sheet->getProtection()->setInsertHyperlinks(false);
                $sheet->getProtection()->setInsertRows(false);
                $sheet->getProtection()->setDeleteRows(false);
                $sheet->getProtection()->setSort(false);
                $sheet->getProtection()->setAutoFilter(false);
                $sheet->getProtection()->setPivotTables(false);
                $sheet->getProtection()->setObjects(false);
                $sheet->getProtection()->setScenarios(false);
                
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
               
                // Get names from Karyawan model
                $nama = Karyawan::join('penempatans', 'karyawans.penempatan_id', '=', 'penempatans.id')
                ->join('organisasis', 'penempatans.organisasi_id', '=', 'organisasis.id')
                ->where('organisasis.organisasi', 'BCA Finance Tester Manual')
                ->pluck('karyawans.nama_karyawan')
                ->toArray();

                // Create a new worksheet for the names
                $namaSheet = new Worksheet($spreadsheet, 'NamaKaryawan');
                $spreadsheet->addSheet($namaSheet);
                
                // Add names to the new worksheet
                foreach ($nama as $index => $name) {
                    $namaSheet->setCellValue('A' . ($index + 1), $name);
                }

                // Hide the new worksheet
                $namaSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
                
                // Set data validation for Nama Karyawan column
                for ($i = 2; $i <= 5000; $i++) {
                    $sheet->getCell('A' . $i)->getDataValidation()
                        ->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('Input error')
                        ->setError('Value is not in list.')
                        ->setFormula1('NamaKaryawan!$A$1:$A$' . count($nama));
                }
                
                // Set data validation for Bulan column
                $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $bulanSheet = new Worksheet($spreadsheet, 'BulanList');
                $spreadsheet->addSheet($bulanSheet);
                
                // Add months to the new worksheet
                foreach ($bulanList as $index => $bulan) {
                    $bulanSheet->setCellValue('A' . ($index + 1), $bulan);
                }

                // Hide the new worksheet
                $bulanSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Set data validation for Bulan column
                for ($i = 2; $i <= 5000; $i++) {
                    $sheet->getCell('B' . $i)->getDataValidation()
                        ->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('Input error')
                        ->setError('Value is not in list.')
                        ->setFormula1('BulanList!$A$1:$A$12');
                }
            }
        ];
    }
}
