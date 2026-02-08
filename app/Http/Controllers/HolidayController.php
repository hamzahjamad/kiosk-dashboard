<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HolidayController extends Controller
{
    /**
     * Get upcoming public holidays (visible only)
     */
    public function index()
    {
        // Sync from API if needed (once per day)
        $this->syncFromApiIfNeeded();

        // Get upcoming visible holidays from database
        $holidays = Holiday::visible()
            ->upcoming(30)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'name' => $holiday->name,
                    'date' => $holiday->date->format('Y-m-d'),
                    'type' => $holiday->type,
                    'source' => $holiday->source,
                ];
            });

        return response()->json([
            'success' => true,
            'holidays' => $holidays,
        ]);
    }

    /**
     * Get all holidays (for management)
     */
    public function all(Request $request)
    {
        $query = Holiday::orderBy('date', 'asc');

        if ($request->has('year')) {
            $query->whereYear('date', $request->year);
        } else {
            $query->whereYear('date', date('Y'));
        }

        $holidays = $query->get();

        return response()->json([
            'success' => true,
            'holidays' => $holidays,
        ]);
    }

    /**
     * Add a custom holiday
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $holiday = Holiday::updateOrCreate(
            [
                'date' => $validated['date'],
                'name' => $validated['name'],
            ],
            [
                'type' => 'custom',
                'source' => 'manual',
                'is_visible' => true,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Holiday added successfully',
            'holiday' => $holiday,
        ]);
    }

    /**
     * Toggle holiday visibility
     */
    public function toggleVisibility(Holiday $holiday)
    {
        $holiday->update([
            'is_visible' => ! $holiday->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'message' => $holiday->is_visible ? 'Holiday is now visible' : 'Holiday is now hidden',
            'holiday' => $holiday,
        ]);
    }

    /**
     * Delete a holiday (only manual ones)
     */
    public function destroy(Holiday $holiday)
    {
        if ($holiday->source !== 'manual') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete API holidays. Use hide instead.',
            ], 400);
        }

        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Holiday deleted successfully',
        ]);
    }

    /**
     * Sync holidays from Calendarific API
     */
    public function sync()
    {
        $count = $this->syncFromApi();

        return response()->json([
            'success' => true,
            'message' => "Synced {$count} holidays from API",
        ]);
    }

    /**
     * Sync from API if not synced today
     */
    private function syncFromApiIfNeeded()
    {
        $cacheKey = 'holidays_synced_'.date('Y-m-d');

        if (! Cache::has($cacheKey)) {
            $this->syncFromApi();
            Cache::put($cacheKey, true, now()->endOfDay());
        }
    }

    /**
     * Sync holidays from Calendarific API to database
     */
    private function syncFromApi()
    {
        $apiKey = env('CALENDARIFIC_API_KEY');

        if (empty($apiKey)) {
            return 0;
        }

        $count = 0;
        $years = [date('Y')];

        // Also fetch next year if we're in the last 2 months
        if (date('n') >= 11) {
            $years[] = date('Y') + 1;
        }

        foreach ($years as $year) {
            try {
                $response = Http::get('https://calendarific.com/api/v2/holidays', [
                    'api_key' => $apiKey,
                    'country' => 'MY',
                    'year' => $year,
                    'type' => 'national,observance',
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['response']['holidays'])) {
                        foreach ($data['response']['holidays'] as $holiday) {
                            $date = $holiday['date']['iso'];

                            // Check if this holiday already exists (by date and name)
                            $existing = Holiday::where('date', $date)
                                ->where('name', $holiday['name'])
                                ->first();

                            if (! $existing) {
                                Holiday::create([
                                    'name' => $holiday['name'],
                                    'date' => $date,
                                    'type' => $holiday['type'][0] ?? 'national',
                                    'source' => 'calendarific',
                                    'source_id' => $holiday['uuid'] ?? null,
                                    'is_visible' => true,
                                ]);
                                $count++;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Calendarific sync error: '.$e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Clear sync cache (force re-sync)
     */
    public function clearCache()
    {
        Cache::forget('holidays_synced_'.date('Y-m-d'));

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared. Holidays will be re-synced on next request.',
        ]);
    }
}
