<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    /**
     * Show settings index with categories
     */
    public function index()
    {
        $categories = [
            'fees' => 'Fees & Payment',
            'admin' => 'Admin Configuration',
            'early_finalization' => 'Early Finalization',
            'disputes' => 'Dispute Settings',
            'forum' => 'Forum Settings',
            'tickets' => 'Support Tickets',
            'platform' => 'Platform Settings',
            'security' => 'Security Settings',
        ];

        $settings = AppSetting::all()->groupBy('category');

        return view('admin.settings.index', compact('categories', 'settings'));
    }

    /**
     * Show settings for specific category
     */
    public function show(string $category)
    {
        $allCategories = [
            'fees' => 'Fees & Payment',
            'admin' => 'Admin Configuration',
            'early_finalization' => 'Early Finalization',
            'disputes' => 'Dispute Settings',
            'forum' => 'Forum Settings',
            'tickets' => 'Support Tickets',
            'platform' => 'Platform Settings',
            'security' => 'Security Settings',
        ];

        if (!in_array($category, array_keys($allCategories))) {
            abort(404);
        }

        $settings = AppSetting::where('category', $category)->get();

        return view('admin.settings.show', compact('category', 'allCategories', 'settings'));
    }

    /**
     * Update setting
     */
    public function update(Request $request, string $category)
    {
        $request->validate([
            'key' => 'required|string|exists:app_settings,key',
            'value' => 'required',
        ]);

        $setting = AppSetting::where('key', $request->key)->firstOrFail();

        // Validate value based on data type
        $rules = ['value' => $this->getValidationRule($setting->data_type)];
        $validated = $request->validate($rules);

        $setting->update(['value' => $this->serialize($validated['value'], $setting->data_type)]);

        return redirect()->route('admin.settings.show', $category)
            ->with('success', "Setting '{$setting->key}' updated successfully.");
    }

    /**
     * Bulk update multiple settings
     */
    public function updateBulk(Request $request, string $category)
    {
        $settings = AppSetting::where('category', $category)->get();

        foreach ($settings as $setting) {
            if ($request->has("settings.{$setting->key}")) {
                $value = $request->input("settings.{$setting->key}");

                // Validate based on data type
                $rules = ["settings.{$setting->key}" => $this->getValidationRule($setting->data_type)];
                $request->validate($rules);

                $setting->update([
                    'value' => $this->serialize($value, $setting->data_type),
                ]);
            }
        }

        return redirect()->route('admin.settings.show', $category)
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Get validation rule based on data type
     */
    private function getValidationRule(string $dataType): string
    {
        return match ($dataType) {
            'integer' => 'integer|min:0',
            'decimal', 'float' => 'numeric|min:0',
            'boolean' => 'boolean',
            'json' => 'json',
            default => 'string',
        };
    }

    /**
     * Serialize value to string for storage
     */
    private function serialize(mixed $value, string $dataType): string
    {
        return match ($dataType) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
