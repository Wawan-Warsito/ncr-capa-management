<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_can_be_created()
    {
        $setting = Setting::create([
            'setting_key' => 'app_name',
            'setting_value' => 'NCR Management System',
            'setting_type' => 'string',
        ]);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'app_name',
            'setting_value' => 'NCR Management System',
        ]);
    }

    public function test_get_setting_helper_works()
    {
        Setting::create([
            'setting_key' => 'app_name',
            'setting_value' => 'NCR Management System',
            'setting_type' => 'string',
        ]);

        $this->assertEquals('NCR Management System', Setting::get('app_name'));
        $this->assertEquals('Default', Setting::get('non_existent', 'Default'));
    }

    public function test_get_typed_value_attribute_works()
    {
        $stringSetting = new Setting(['setting_value' => 'Hello', 'setting_type' => 'string']);
        $this->assertEquals('Hello', $stringSetting->typed_value);

        $intSetting = new Setting(['setting_value' => '123', 'setting_type' => 'integer']);
        $this->assertEquals(123, $intSetting->typed_value);

        $boolSetting = new Setting(['setting_value' => 'true', 'setting_type' => 'boolean']);
        $this->assertTrue($boolSetting->typed_value);

        $jsonSetting = new Setting(['setting_value' => json_encode(['a' => 1]), 'setting_type' => 'json']);
        $this->assertEquals(['a' => 1], $jsonSetting->typed_value);
    }

    public function test_set_setting_helper_works()
    {
        $user = User::factory()->create();
        
        Setting::set('app_version', '1.0.0', 'string', $user);

        $this->assertDatabaseHas('settings', [
            'setting_key' => 'app_version',
            'setting_value' => '1.0.0',
            'updated_by_user_id' => $user->id,
        ]);

        // Test updating existing setting
        Setting::set('app_version', '1.1.0', 'string', $user);
        $this->assertEquals('1.1.0', Setting::get('app_version'));
    }

    public function test_set_setting_auto_types_works()
    {
        Setting::set('is_enabled', true);
        $setting = Setting::where('setting_key', 'is_enabled')->first();
        $this->assertEquals('boolean', $setting->setting_type);
        $this->assertTrue(Setting::get('is_enabled'));

        Setting::set('max_items', 10);
        $setting = Setting::where('setting_key', 'max_items')->first();
        $this->assertEquals('integer', $setting->setting_type);
        $this->assertEquals(10, Setting::get('max_items'));

        Setting::set('config', ['a' => 1]);
        $setting = Setting::where('setting_key', 'config')->first();
        $this->assertEquals('json', $setting->setting_type);
        $this->assertEquals(['a' => 1], Setting::get('config'));
    }
}
