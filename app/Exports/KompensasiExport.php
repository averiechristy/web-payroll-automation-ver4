<?php
namespace App\Exports;

use App\Models\DetailKompensasi;
use App\Models\ReportKompensasi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KompensasiExport implements FromCollection, WithHeadings, WithEvents
{
    protected $bulan;
    protected $tahun;

    protected $status_kompensasi;

    protected $dataKompensasi;

    public function __construct($bulan, $tahun, $status_kompensasi, $dataKompensasi)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->status_kompensasi = $status_kompensasi;
        $this->dataKompensasi = $dataKompensasi;
    }

    public function collection()
    {

  if ($this->status_kompensasi === "Closing") {
    $startOfMonth = Carbon::createFromDate($this->tahun, $this->bulan, 1)->startOfMonth();
    $endOfMonth = Carbon::createFromDate($this->tahun, $this->bulan, 1)->endOfMonth();
    
    $data = DetailKompensasi::with(['karyawan.penempatan', 'karyawan.kontrakKaryawan' => function ($query) use ($startOfMonth, $endOfMonth) {
        $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('tanggal_awal_kontrak', [$startOfMonth, $endOfMonth])
              ->orWhereBetween('tanggal_akhir_kontrak', [$startOfMonth, $endOfMonth]);
        });
    }])->get()->map(function ($detail, $key) {
        $kontrak = $detail->karyawan->kontrakKaryawan->first();
        
        return [
            $key + 1, // Nomor Urut
            $detail->karyawan->nik,
            $detail->karyawan->nama_karyawan,
            $detail->karyawan->penempatan->nama_unit_kerja,
            'Rp ' . number_format($detail->gaji, 0, ',', '.'),
            'Rp ' . number_format($detail->tunjangan, 0, ',', '.'),
            optional($kontrak)->tanggal_awal_kontrak,
            optional($kontrak)->tanggal_akhir_kontrak,
            $detail->masa_kerja,
            $detail->total_kompensasi,
        ];
    });

    } else {

        $data = collect($this->dataKompensasi)->map(function ($detail, $key) {
           
            return [
                $key + 1, // Nomor Urut
                $detail['nik'],
                $detail['nama'],
                $detail['namapenempatan'],
                'Rp ' . number_format($detail['gaji'], 0, ',', '.'),
                'Rp ' . number_format($detail['tunjangan'], 0, ',', '.'),
                $detail['tanggalawal'],
                $detail['tanggalakhir'],
                $detail['masakerja'],
                $detail['totalkompensasi'],
            ];
        });
    }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No', // Kolom nom
            'NIK',
            'Nama',
            'Unit Kerja',
            'Upah',
            'Tunjangan',
            'Tanggal Awal Kontrak',
            'Tanggal Akhir Kontrak',
            'Masa Kerja',
            'Total Kompensasi'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Menambahkan judul pada baris pertama
                $sheet->insertNewRowBefore(1, 4); // Menambahkan 4 baris kosong sebelum heading
                $sheet->setCellValue('A1', 'Perhitungan Kompensasi TKAD');
                // Menggabungkan sel A1 sampai I1 untuk judul
                $sheet->mergeCells('A1:J1');
                // Mengatur gaya untuk judul
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                // Menambahkan periode pada baris ketiga
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
                $monthName = $months[(int)$this->bulan];
                $sheet->setCellValue('A3', 'Periode: ' . $monthName . ' ' . $this->tahun);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);

                // Mengatur warna untuk heading
                $sheet->getStyle('A5:J5')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFCC99'], // Warna oranye accent 2 60%
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Mengatur lebar kolom
                foreach (range('A', 'J') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setWidth(15);
                }

                // Mengatur border untuk collection
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:J' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Mengatur alignment rata kiri untuk isi collection
                $sheet->getStyle('A6:J' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // Menambahkan total pada kolom J di baris terakhir + 1
                $lastRow = $sheet->getHighestRow();
                $sheet->setCellValue('I' . ($lastRow + 1), 'Total');
                $sheet->setCellValue('J' . ($lastRow + 1), '=SUM(J6:J' . $lastRow . ')');

                // Mengatur format Rp untuk total sum
                $sheet->getStyle('J' . ($lastRow + 1))->getNumberFormat()->setFormatCode('"Rp"#,##0');

                $sheet->getStyle('J' . ($lastRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


                // Menambahkan tanda tangan
                $sheet->setCellValue('B' . ($lastRow + 4), 'Dibuat Oleh, ');
                $sheet->setCellValue('D' . ($lastRow + 4), 'Mengetahui, ');

                $sheet->setCellValue('B' . ($lastRow + 9), '(Sondang Esteria Resta)');
                $sheet->setCellValue('D' . ($lastRow + 9), '(Cynthia Widjaja)');

                $sheet->getStyle('B' . ($lastRow + 9) . ':D' . ($lastRow + 9))->getFont()->setBold(true);

                // Mengatur format Rp untuk kolom Total Kompensasi
                $sheet->getStyle('J6:J' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
            },
        ];
    }
}
