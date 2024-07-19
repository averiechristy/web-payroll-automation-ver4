<?php

namespace App\Exports;

use App\Models\Divisi;
use App\Models\Organisasi;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;


class TemplatePenempatan implements FromCollection, WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
          'Kode Orange',
          'Organisasi',
          'Divisi',
          'KCU Induk',
          'Nama Unit Kerja Penempatan',
          'Kode Cabang Pembayaran untuk Vendor MAD',
          'RCC Pembayaran untuk Vendor MAD',
          'Singkatan Divisi',            
          'Kode SLID'     
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
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(20);
             
                $spreadsheet = $sheet->getParent();
                $hiddenSheet = new Worksheet($spreadsheet, 'HiddenSheet');
                $spreadsheet->addSheet($hiddenSheet);


                $organisasi = Organisasi::pluck('organisasi')->toArray();
                $divisi = Divisi::pluck('divisi')->toArray(); // Assuming you have a model for Posisi
                
                // Write penempatan values to hidden sheet
                foreach ($organisasi as $index => $value) {
                    $hiddenSheet->setCellValue('A' . ($index + 1), $value);
                }
                
                // Write posisi values to hidden sheet
                foreach ($divisi as $index => $value) {
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
                $validationUnitKerja->setFormula1('HiddenSheet!$A$1:$A$' . count($organisasi));
                
                // Apply the validation to the desired range for Unit Kerja Penempatan
                for ($i = 2; $i <= 5000; $i++) {
                    $sheet->getCell('B' . $i)->setDataValidation(clone $validationUnitKerja);
                }
                
                // Apply data validation for Posisi
                $validationPosisi = new DataValidation();
                $validationPosisi->setType(DataValidation::TYPE_LIST);
                $validationPosisi->setErrorStyle(DataValidation::STYLE_STOP);
                $validationPosisi->setAllowBlank(false);
                $validationPosisi->setShowInputMessage(true);
                $validationPosisi->setShowErrorMessage(true);
                $validationPosisi->setShowDropDown(true);
                $validationPosisi->setFormula1('HiddenSheet!$B$1:$B$' . count($divisi));
                
                // Apply the validation to the desired range for Posisi
                for ($i = 2; $i <= 5000; $i++) {
                    $sheet->getCell('C' . $i)->setDataValidation(clone $validationPosisi);
                }
                
                $hiddenSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

            }

        ];
    }    
}