<?php

namespace App\Console\Commands;

use App\Models\PrayerSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncPrayerTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:prayer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync prayer times from Aladhan API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing prayer times...');

        $settings = PrayerSetting::getSettings();

        try {
            $today = now();
            $date = $today->format('d-m-Y');

            $response = Http::timeout(10)->get("https://api.aladhan.com/v1/timingsByCity/{$date}", [
                'city' => $settings->city,
                'country' => $settings->country,
                'method' => $settings->method,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['timings'])) {
                    $timings = $data['data']['timings'];

                    $times = [
                        'subuh' => $this->formatTime($timings['Fajr']),
                        'syuruk' => $this->formatTime($timings['Sunrise']),
                        'zohor' => $this->formatTime($timings['Dhuhr']),
                        'asar' => $this->formatTime($timings['Asr']),
                        'maghrib' => $this->formatTime($timings['Maghrib']),
                        'isyak' => $this->formatTime($timings['Isha']),
                    ];

                    $settings->update([
                        'cached_times' => $times,
                        'cached_date' => now()->toDateString(),
                    ]);

                    $this->info("Prayer times synced for {$settings->city}, {$settings->country}:");
                    foreach ($times as $prayer => $time) {
                        $this->line("  {$prayer}: {$time}");
                    }

                    return Command::SUCCESS;
                }
            }

            $this->error('Failed to fetch prayer times');

            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Format time string (remove timezone info)
     */
    private function formatTime($time)
    {
        return substr(explode(' ', $time)[0], 0, 5);
    }
}
