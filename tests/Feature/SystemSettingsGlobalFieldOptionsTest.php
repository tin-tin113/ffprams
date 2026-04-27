<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\FormFieldOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsGlobalFieldOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_rename_field_group_when_updating_existing_option(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $option = FormFieldOption::create([
            'field_group' => 'old_group',
            'field_type' => FormFieldOption::FIELD_TYPE_DROPDOWN,
            'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
            'label' => 'Legacy Option',
            'value' => 'legacy_option',
            'sort_order' => 10,
            'is_required' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.form-fields.update', $option), [
                'field_group' => 'new_group',
                'field_type' => FormFieldOption::FIELD_TYPE_DROPDOWN,
                'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                'label' => 'Updated Option',
                'value' => 'updated_option',
                'is_required' => false,
                'is_active' => true,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('option.field_group', 'new_group');

        $this->assertDatabaseHas('form_field_options', [
            'id' => $option->id,
            'field_group' => 'new_group',
            'label' => 'Updated Option',
            'value' => 'updated_option',
        ]);
    }

    public function test_admin_can_create_option_with_value_inferred_from_label_when_blank(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post(route('admin.settings.form-fields.store'), [
                'field_group' => 'beneficiary_segment',
                'field_type' => FormFieldOption::FIELD_TYPE_DROPDOWN,
                'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                'label' => 'Organic Farmer',
                'value' => '',
                'is_required' => false,
                'is_active' => true,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('option.value', 'organic_farmer');

        $this->assertDatabaseHas('form_field_options', [
            'field_group' => 'beneficiary_segment',
            'label' => 'Organic Farmer',
            'value' => 'organic_farmer',
        ]);
    }

    public function test_admin_cannot_use_dar_placement_for_global_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post(route('admin.settings.form-fields.store'), [
                'field_group' => 'dar_global_attempt',
                'field_type' => FormFieldOption::FIELD_TYPE_DROPDOWN,
                'placement_section' => FormFieldOption::PLACEMENT_DAR_INFORMATION,
                'label' => 'DAR Global Attempt',
                'value' => 'dar_global_attempt',
                'is_required' => false,
                'is_active' => true,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.placement_section.0', 'DAR placement is not supported for global fields. Configure DAR fields under Agencies > Agency Fields.');
    }

    public function test_delete_archives_global_field_when_beneficiary_data_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $barangay = Barangay::create([
            'name' => 'Barangay Test',
            'latitude' => 10.20000000,
            'longitude' => 123.20000000,
        ]);

        $option = FormFieldOption::create([
            'field_group' => 'farm_note',
            'field_type' => FormFieldOption::FIELD_TYPE_TEXT,
            'placement_section' => FormFieldOption::PLACEMENT_FARMER_INFORMATION,
            'label' => 'Farm Note',
            'value' => 'farm_note',
            'sort_order' => 10,
            'is_required' => false,
            'is_active' => true,
        ]);

        $beneficiary = Beneficiary::create([
            'first_name' => 'Mara',
            'last_name' => 'Santos',
            'barangay_id' => $barangay->id,
            'classification' => 'Farmer',
            'contact_number' => '09171234567',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
            'custom_fields' => ['farm_note' => 'Keep this note'],
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.settings.form-fields.destroy', $option));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('archived', true)
            ->assertJsonPath('affected_records', 1);

        $this->assertDatabaseHas('form_field_options', [
            'id' => $option->id,
            'is_active' => false,
        ]);

        $this->assertSame('Keep this note', $beneficiary->fresh()->custom_fields['farm_note']);
    }

    public function test_delete_removes_unused_global_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $option = FormFieldOption::create([
            'field_group' => 'unused_note',
            'field_type' => FormFieldOption::FIELD_TYPE_TEXT,
            'placement_section' => FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
            'label' => 'Unused Note',
            'value' => 'unused_note',
            'sort_order' => 10,
            'is_required' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.settings.form-fields.destroy', $option));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('archived');

        $this->assertDatabaseMissing('form_field_options', [
            'id' => $option->id,
        ]);
    }
}
