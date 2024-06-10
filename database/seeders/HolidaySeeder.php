<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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

            $this->command->info('National holidays fetched successfully.');
        } else {
            $this->command->error('Failed to fetch national holidays.');
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

                Holiday::firstOrCreate([
                    'date' => $date,
                    'description' => $description
                ]);
            }
        }

        $this->command->info('All dates added with description successfully.');
    }
}
