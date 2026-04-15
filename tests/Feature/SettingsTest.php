<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function settings_page_requires_authentication(): void
    {
        $this->get(route('settings.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_settings_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Settings');
    }

    /** @test */
    public function settings_page_shows_existing_company_values(): void
    {
        Setting::create(['key' => 'company_name', 'value' => 'Epoch Mines and Resources']);
        Setting::create(['key' => 'company_location', 'value' => 'Filabusi, Zimbabwe']);

        $this->actingAs($this->adminUser())
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Epoch Mines and Resources')
            ->assertSee('Filabusi, Zimbabwe');
    }

    /** @test */
    public function can_update_company_settings(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('settings.update'), [
                'company_name'     => 'Epoch Mines and Resources',
                'company_location' => 'Filabusi, Zimbabwe',
                'company_address'  => 'P.O. Box 1',
                'company_phone'    => '+263 77 000 0000',
                'company_email'    => 'info@epoch.co.zw',
                'company_website'  => 'https://epoch.co.zw',
                'zesa_daily'       => '633',
                'diesel_daily'     => '428',
                'labour_daily'     => '0',
            ])
            ->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('settings', ['key' => 'company_name', 'value' => 'Epoch Mines and Resources']);
        $this->assertDatabaseHas('settings', ['key' => 'company_location', 'value' => 'Filabusi, Zimbabwe']);
        $this->assertDatabaseHas('settings', ['key' => 'zesa_daily', 'value' => '633']);
    }

    /** @test */
    public function company_name_is_required(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('settings.update'), [
                'company_name'  => '',
                'zesa_daily'    => '633',
                'diesel_daily'  => '428',
                'labour_daily'  => '0',
                'mining_levels' => 'Shaft',
            ])
            ->assertSessionHasErrors('company_name');
    }

    /** @test */
    public function logo_upload_stores_file_and_updates_setting(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension not installed.');
        }
        Storage::fake('public');

        $this->actingAs($this->adminUser())
            ->post(route('settings.update'), [
                'company_name'  => 'Epoch Mines',
                'zesa_daily'    => '633',
                'diesel_daily'  => '428',
                'labour_daily'  => '0',
                'mining_levels' => 'Shaft',
                'logo'          => UploadedFile::fake()->image('logo.png', 100, 100),
            ])
            ->assertRedirect(route('settings.index'));

        $logoPaths = Setting::where('key', 'logo_path')->value('value');
        $this->assertNotEmpty($logoPaths);
        Storage::disk('public')->assertExists($logoPaths);
    }

    /** @test */
    public function invalid_logo_mime_type_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('settings.update'), [
                'company_name'  => 'Epoch Mines',
                'zesa_daily'    => '633',
                'diesel_daily'  => '428',
                'labour_daily'  => '0',
                'mining_levels' => 'Shaft',
                'logo'          => UploadedFile::fake()->create('malware.exe', 100),
            ])
            ->assertSessionHasErrors('logo');
    }

    /** @test */
    public function daily_cost_fields_must_be_numeric(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('settings.update'), [
                'company_name'  => 'Epoch Mines',
                'zesa_daily'    => 'not-a-number',
                'diesel_daily'  => '428',
                'labour_daily'  => '0',
                'mining_levels' => 'Shaft',
            ])
            ->assertSessionHasErrors('zesa_daily');
    }
}
