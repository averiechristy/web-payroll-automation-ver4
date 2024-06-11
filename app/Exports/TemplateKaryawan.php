<?php

namespace App\Exports;

use App\Models\Penempatan;
use App\Models\Posisi; // Assuming you have a model for Posisi
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class TemplateKaryawan implements FromCollection, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'NIK',
            'Payroll Code',
            'Nama',
            'No PBB/Amandemen',
            'NIK KTP',
            'Unit Kerja Penempatan',
            'Posisi',
            'Management Fee (%)',
            'Jabatan',
            'Bagian',
            'Leader',
            'Status',
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
                
                $event->sheet->getStyle('1:100000')->getProtection()->setLocked(false);
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
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->getColumnDimension('L')->setWidth(20);
              
                // Format 'Management Fee (%)' column as percentage
                $sheet->getStyle('J2:J100000')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
              
                // Create hidden sheet for data validation list
                $spreadsheet = $sheet->getParent();
                $hiddenSheet = new Worksheet($spreadsheet, 'HiddenSheet');
                $spreadsheet->addSheet($hiddenSheet);
                
                // Get penempatan values
                $penempatan = Penempatan::pluck('nama_unit_kerja')->toArray();
                $posisi = Posisi::pluck('posisi')->toArray(); // Assuming you have a model for Posisi
                
                // Write penempatan values to hidden sheet
                foreach ($penempatan as $index => $value) {
                    $hiddenSheet->setCellValue('A' . ($index + 1), $value);
                }
                
                // Write posisi values to hidden sheet
                foreach ($posisi as $index => $value) {
                    $hiddenSheet->setCellValue('B' . ($index + 1), $value);
                }
                
                // Apply data validation for Unit Kerja Penempatan
                $validationUnitKerja = new DataValidation();
                $validationUnitKerja->setType(DataValidation::TYPE_LIST);
                $validationUnitKerja->setErrorStyle(DataValidation::STYLE_STOP);
                $validationUnitKerja->setAllowBlank(false);
                $validationUnitKerja->setShowInputMessage(true);
                $validationUnitKerja->setShowErrorMessage(true);
                $validationUnitKerja->setShowDropDown(true);
                $validationUnitKerja->setFormula1('HiddenSheet!$A$1:$A$' . count($penempatan));
                
                // Apply the validation to the desired range for Unit Kerja Penempatan
                for ($i = 2; $i <= 100000; $i++) {
                    $sheet->getCell('F' . $i)->setDataValidation(clone $validationUnitKerja);
                }
                
                // Apply data validation for Posisi
                $validationPosisi = new DataValidation();
                $validationPosisi->setType(DataValidation::TYPE_LIST);
                $validationPosisi->setErrorStyle(DataValidation::STYLE_STOP);
                $validationPosisi->setAllowBlank(false);
                $validationPosisi->setShowInputMessage(true);
                $validationPosisi->setShowErrorMessage(true);
                $validationPosisi->setShowDropDown(true);
                $validationPosisi->setFormula1('HiddenSheet!$B$1:$B$' . count($posisi));
                
                // Apply the validation to the desired range for Posisi
                for ($i = 2; $i <= 100000; $i++) {
                    $sheet->getCell('G' . $i)->setDataValidation(clone $validationPosisi);
                }
                
                $hiddenSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            }
        ];
    }    
}
