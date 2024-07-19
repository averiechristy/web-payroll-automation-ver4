<?php

namespace App\Exports;

use App\Models\DetailKompensasi;
use App\Models\DetailLembur;
use App\Models\DetailPayroll;
use App\Models\ReportLembur;
use App\Models\ReportPayroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PayrollDownload implements FromCollection, WithHeadings, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $organisasi;
    protected $status_payroll;

    protected $dataPayroll;

    public function __construct($bulan, $tahun, $organisasi, $status_payroll, $dataPayroll)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->organisasi = $organisasi;
        $this->status_payroll = $status_payroll;
        $this->dataPayroll = $dataPayroll;
    }

    public function collection()
    {
        if ($this->status_payroll === "Closing") {
            return DetailPayroll::with('karyawan.penempatan.organisasi')
            ->whereHas('karyawan.penempatan.organisasi', function($query) {
                $query->where('organisasi_id', $this->organisasi);
            })
            ->get()
            ->map(function ($detail, $key) {
                return [
                    $detail->karyawan->nik,
                    $detail->karyawan->payroll_code,
                    $detail->karyawan->nama_karyawan,
                    $detail->karyawan->posisi->posisi,
                    $detail->karyawan->penempatan->organisasi->organisasi,
                    $detail->gajipokok,
                    $detail->adjusment_salary,
                    $detail->tunjangan,
                    $detail->uangsaku,
                    $detail->insentif,
                    $detail->overtime,
                    $detail->total_allowance,
                    $detail->kompensasi,
                    $detail->total,
                ];
            });
        } else {
            return collect($this->dataPayroll)->map(function ($detail, $key){
                return [
                    $detail['nik'],
                    $detail['payrollcode'],
                    $detail['namakaryawan'],
                    $detail['namaposisi'],
                    $detail['namaorganisasi'],
                    $detail['gajikaryawan'],
                    $detail['adjusment_salary'],
                    $detail['tunjangan'],
                    $detail['uangsaku'],
                    $detail['insentif'],
                    $detail['overtime'],
                    $detail['total_allowance'],
                    $detail['kompensasi'],
                    $detail['total'],
                ];
            });
        }
    }

    public function headings(): array
    {
        return [
            'ID Karyawan',
            'Kode Payroll',
            'Nama',
            'Posisi',
            'Organisasi',
            'Gaji Pokok',
            'Adjusment',
            'Tunjangan Jabatan',
            'Uang Saku Perjalanan Dinas',
            'Insentif',
            'Lembur',
            'Total Allowance',
            'Kompensasi',
            'Total'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Menambahkan 6 baris kosong sebelum heading
                $sheet->insertNewRowBefore(1, 3);
    
                // Menulis judul dan periode
                $sheet->setCellValue('A1', 'PT Exa Mitra Solusi');
                $sheet->setCellValue('A2', 'Salary Report ' . $this->bulan . ' ' . $this->tahun);
                $sheet->mergeCells('A1:N1');

                // Menggabungkan sel A2 sampai N2
                $sheet->mergeCells('A2:N2');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
                ]);

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setWidth(20);
                }

                $sheet->getStyle('A4:N4')
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
          
                $styleArray = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFFF00'],
                    ],
                ];
                $sheet->getStyle('A4:N4')->applyFromArray($styleArray);
          
                $lastRow = $sheet->getHighestRow();
                $sheet->mergeCells('A' . ($lastRow + 1) . ':E' . ($lastRow + 1));
                $sheet->setCellValue('A' . ($lastRow + 1), 'Grand Total');
                $sheet->getStyle('A' . ($lastRow + 1))->getFont()->setBold(true);
                $sheet->getStyle('A' . ($lastRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          
                $sumRow = $lastRow + 1;
                $columnsToSum = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
          
                // Insert the sum formulas
                foreach ($columnsToSum as $column) {
                    $sheet->setCellValue($column . $sumRow, '=SUM(' . $column . '5:' . $column . $lastRow . ')');
                }
          
                // Apply yellow background color to the entire row
                $highestColumn = 'N'; // Adjust this to your highest column that needs coloring
                $range = 'A' . $sumRow . ':' . $highestColumn . $sumRow;
                $sheet->getStyle($range)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                               ->getStartColor()->setARGB('E0E0E0');
          
                $currencyColumns = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
                foreach ($currencyColumns as $column) {
                    $sheet->getStyle($column . '6:' . $column . $sumRow)
                          ->getNumberFormat()
                          ->setFormatCode('#,##0');
          
                    $sheet->getStyle($column . '5:' . $column . $sumRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle($column . '5:' . $column . $sumRow)->getNumberFormat()->setFormatCode('"Rp"* #,##0');
                }

                // Apply conditional formatting to 'Adjustment' column for negative values
                $adjustmentColumn = 'G';
                for ($row = 5; $row <= $sumRow; $row++) {
                    $cell = $adjustmentColumn . $row;
                    $conditionalStyles = $sheet->getStyle($cell)->getConditionalStyles();

                    $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                    $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
                                ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN)
                                ->addCondition(0);
                    $conditional->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                    $conditional->getStyle()->getNumberFormat()->setFormatCode('"Rp"* (#,##0)');

                    $conditionalStyles[] = $conditional;
                    $sheet->getStyle($cell)->setConditionalStyles($conditionalStyles);
                }

                // Freeze the first 4 rows and columns A to E
                $sheet->freezePane('F5');
            },
        ];
    }
}
