<?php

namespace App\Exports;

use App\Models\Karyawan;
use App\Models\Penempatan;
use App\Models\Posisi; // Assuming you have a model for Posisi
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class TemplateAttendance implements FromCollection, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Tanggal',
            'Shift',
            'Schedule In',
            'Schedule Out',
            'Attendance Code',
            'Check In',
            'Check Out',
            'Overtime Check In',
            'Overtime Check Out',
            

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
                
                $event->sheet->getStyle('1:5000')->getProtection()->setLocked(false);
                $sheet->getStyle('A1:XFD1')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
    
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
                $sheet->getColumnDimension('F')->setWidth(50);
                $sheet->getColumnDimension('G')->setWidth(40);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);


               
              
                $spreadsheet = $sheet->getParent();
                $hiddenSheet = new Worksheet($spreadsheet, 'HiddenSheet');
                $spreadsheet->addSheet($hiddenSheet);
                $nama = Karyawan::pluck('nama_karyawan')->toArray();
                
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
               
            }
        ];
    }
    
}
