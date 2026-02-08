<?php

namespace App\Http\Controllers;

use App\Models\Background;
use App\Models\BackgroundSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BackgroundController extends Controller
{
    /**
     * Get backgrounds for dashboard (visible only)
     */
    public function index()
    {
        $backgrounds = Background::visible()->ordered()->get();
        $settings = BackgroundSetting::getSettings();

        return response()->json([
            'success' => true,
            'backgrounds' => $backgrounds->map(fn ($bg) => [
                'id' => $bg->id,
                'url' => $bg->url,
            ]),
            'settings' => [
                'slide_interval' => $settings->slide_interval * 1000, // Convert to ms
                'transition_duration' => $settings->transition_duration,
                'overlay_opacity' => $settings->overlay_opacity / 100, // Convert to decimal
            ],
        ]);
    }

    /**
     * Get all backgrounds for management
     */
    public function all()
    {
        $backgrounds = Background::ordered()->get();

        return response()->json([
            'success' => true,
            'backgrounds' => $backgrounds,
        ]);
    }

    /**
     * Get settings
     */
    public function settings()
    {
        $settings = BackgroundSetting::getSettings();

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'slide_interval' => 'required|integer|min:3|max:300',
            'transition_duration' => 'required|integer|min:1|max:10',
            'overlay_opacity' => 'required|integer|min:0|max:100',
        ]);

        $settings = BackgroundSetting::getSettings();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => $settings,
        ]);
    }

    /**
     * Upload a new background image
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240', // 10MB max
        ]);

        $file = $request->file('image');
        $originalName = $file->getClientOriginalName();
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        // Store in public/images/backgrounds
        $path = $file->move(public_path('images/backgrounds'), $filename);

        // Get max sort order
        $maxOrder = Background::max('sort_order') ?? 0;

        $background = Background::create([
            'filename' => $filename,
            'original_name' => $originalName,
            'path' => 'images/backgrounds/'.$filename,
            'sort_order' => $maxOrder + 1,
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Background uploaded successfully',
            'background' => $background,
        ]);
    }

    /**
     * Toggle background visibility
     */
    public function toggleVisibility(Background $background)
    {
        $background->update([
            'is_visible' => ! $background->is_visible,
        ]);

        return response()->json([
            'success' => true,
            'message' => $background->is_visible ? 'Background is now visible' : 'Background is now hidden',
            'background' => $background,
        ]);
    }

    /**
     * Update sort order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:backgrounds,id',
        ]);

        foreach ($request->order as $index => $id) {
            Background::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
        ]);
    }

    /**
     * Delete a background
     */
    public function destroy(Background $background)
    {
        // Delete the file
        $filePath = public_path($background->path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $background->delete();

        return response()->json([
            'success' => true,
            'message' => 'Background deleted successfully',
        ]);
    }

    /**
     * Seed existing backgrounds from filesystem
     */
    public function seedFromFilesystem()
    {
        $path = public_path('images/backgrounds');

        if (! is_dir($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Backgrounds directory not found',
            ]);
        }

        $files = glob($path.'/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        $added = 0;

        foreach ($files as $index => $file) {
            $filename = basename($file);

            // Skip if already exists
            if (Background::where('filename', $filename)->exists()) {
                continue;
            }

            Background::create([
                'filename' => $filename,
                'original_name' => $filename,
                'path' => 'images/backgrounds/'.$filename,
                'sort_order' => $index,
                'is_visible' => true,
            ]);

            $added++;
        }

        return response()->json([
            'success' => true,
            'message' => "Added {$added} backgrounds from filesystem",
        ]);
    }
}
