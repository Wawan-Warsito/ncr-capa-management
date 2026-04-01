<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display a listing of all settings.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Setting::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $settings = $query->orderBy('category')->orderBy('setting_key')->get();

        // Transform to include typed value
        $data = $settings->map(function ($setting) {
            $s = $setting->toArray();
            $s['value'] = $setting->typed_value;
            return $s;
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get public settings for frontend configuration.
     */
    public function getPublicSettings()
    {
        $settings = Setting::public()->get();

        $config = $settings->mapWithKeys(function ($setting) {
            return [$setting->setting_key => $setting->typed_value];
        });

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Update a setting.
     */
    public function update(Request $request, $key)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'value' => 'required', // Type validation depends on setting_type, handled loosely here or strictly if we check type
            'description' => 'nullable|string',
            'is_public' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $setting = Setting::where('setting_key', $key)->firstOrFail();

            // Validate value type if necessary
            // For now, we rely on the helper or simple casting
            
            $value = $validated['value'];
            
            // If the setting expects a boolean but we got string "true"/"false" or 1/0
            if ($setting->setting_type === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            
            // If setting expects json/array
            if ($setting->setting_type === 'json' || $setting->setting_type === 'array') {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
            }

            // Using the helper set method might create a new one, but we want to update existing fields too like description
            // So we'll update manually to keep control
            
            $oldValue = $setting->setting_value;
            
            $setting->setting_value = (string) $value; // Store as string
            if (isset($validated['description'])) {
                $setting->description = $validated['description'];
            }
            if (isset($validated['is_public'])) {
                $setting->is_public = $validated['is_public'];
            }
            $setting->updated_by_user_id = $user->id;
            $setting->save();

            ActivityLog::logActivity(
                'Setting',
                $setting->id,
                'Updated',
                "Setting {$key} updated",
                ['value' => $oldValue],
                ['value' => $setting->setting_value],
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully',
                'data' => [
                    'key' => $setting->setting_key,
                    'value' => $setting->typed_value
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch update settings.
     */
    public function updateBatch(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $count = 0;
            // Support both [{key, value}] and {key: value} formats
            $settingsToUpdate = [];
            
            // Check if associative array (map) or sequential array (list)
            $isAssociative = array_keys($validated['settings']) !== range(0, count($validated['settings']) - 1);

            if ($isAssociative) {
                foreach ($validated['settings'] as $key => $value) {
                    $settingsToUpdate[] = ['key' => $key, 'value' => $value];
                }
            } else {
                $settingsToUpdate = $validated['settings'];
            }

            foreach ($settingsToUpdate as $item) {
                // Handle case where item might be just key=>value if array was mixed, but we normalized above.
                // If the original input was [{key:..., value:...}], $item has those keys.
                
                $key = $item['key'] ?? null;
                $value = $item['value'] ?? null;

                if (!$key) continue;
                
                $setting = Setting::where('setting_key', $key)->first();
                if ($setting) {
                    if ($setting->setting_type === 'boolean') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                    } elseif (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    
                    $setting->setting_value = (string)$value;
                    $setting->updated_by_user_id = $user->id;
                    $setting->save();
                    $count++;
                }
            }

            ActivityLog::logActivity(
                'Setting',
                0,
                'Batch_Updated',
                "$count settings updated",
                [],
                [],
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }
}
