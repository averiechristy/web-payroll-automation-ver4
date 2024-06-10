<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Holiday;
use Carbon\Carbon;

class UpdateHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update holidays for the current year';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('holidays:update command executed');
        
        // Fetch national holidays from API
        $nationalHolidays = $this->fetchNationalHolidays();

        // Add all dates with their respective description
        $this->addAllDatesWithDescription($nationalHolidays);
    }

    protected function fetchNationalHolidays()
    {
        $apiKey = env('CALENDARIFIC_API_KEY'); // API Key from .env
        $country = 'ID'; // Country code for Indonesia
        $year = Carbon::now()->year;

        $response = Http::get("https://calendarific.com/api/v2/holidays", [
            'api_key' => $apiKey,
            'country' => $country,
            'year' => $year
        ]);

        $nationalHolidays = [];

        if ($response->successful()) {
            $holidays = $response->json()['response']['holidays'];

            foreach ($holidays as $holiday) {
                $nationalHolidays[] = $holiday['date']['iso'];
            }

            $this->info('National holidays fetched successfully.');
        } else {
            $this->error('Failed to fetch national holidays.');
        }

        return $nationalHolidays;
    }

    protected function addAllDatesWithDescription($nationalHolidays)
    {
        $year = Carbon::now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                $description = 'Kerja';

                if (in_array($date, $nationalHolidays)) {
                    $description = 'Libur';
                } elseif (Carbon::create($year, $month, $day)->isSaturday() || Carbon::create($year, $month, $day)->isSunday()) {
                    $description = 'Libur';
                }

                Holiday::updateOrCreate([
                    'date' => $date,
                ], [
                    'description' => $description
                ]);
            }
        }

        $this->info('All dates added with description successfully.');
    }
}
