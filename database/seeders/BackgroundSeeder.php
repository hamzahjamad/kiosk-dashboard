<?php

namespace Database\Seeders;

use App\Models\Background;
use App\Models\BackgroundSetting;
use Illuminate\Database\Seeder;

class BackgroundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default settings if not exists
        BackgroundSetting::getSettings();

        // Skip if backgrounds already exist
        if (Background::count() > 0) {
            $this->command->info('Backgrounds already exist, skipping import.');

            return;
        }

        $path = public_path('images/backgrounds');

        if (! is_dir($path)) {
            $this->command->warn('Backgrounds directory not found: '.$path);

            return;
        }

        $extensions = ['jpg', 'jpeg', 'png', 'webp'];
        $files = [];
        foreach ($extensions as $ext) {
            $files = array_merge($files, glob($path.'/*.'.$ext) ?: []);
        }
        $files = array_values(array_unique($files));

        if (empty($files)) {
            $this->command->info('No background images found to import.');

            return;
        }

        foreach ($files as $index => $file) {
            $filename = basename($file);

            Background::create([
                'filename' => $filename,
                'original_name' => $filename,
                'path' => 'images/backgrounds/'.$filename,
                'sort_order' => $index,
                'is_visible' => true,
            ]);

            $this->command->info("Imported: {$filename}");
        }

        $this->command->info('Imported '.count($files).' background images.');
    }
}
