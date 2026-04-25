<?php

namespace Tests\Feature;

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
}

