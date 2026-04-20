<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\AgencyFormField;
use App\Models\AgencyFormFieldOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyReservedFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_form_fields_endpoint_includes_static_and_dynamic_agency_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = $this->makeAgency();

        $this->makeFormField($agency, 'rsbsa_number', 'RSBSA Number');
        $this->makeFormField($agency, 'custom_crop_code', 'Custom Crop Code');

        $response = $this->actingAs($admin)
            ->get(route('admin.settings.agencies.form-fields.index', $agency));

        $payload = $response->json();

        $response
            ->assertOk()
            ->assertJsonFragment(['field_name' => 'rsbsa_number'])
            ->assertJsonFragment(['field_name' => 'custom_crop_code'])
            ->assertJsonMissing(['field_name' => 'non_existent_field_name']);

        $this->assertIsArray($payload);
        $this->assertGreaterThanOrEqual(2, count($payload));
    }

    public function test_public_agency_form_fields_api_includes_static_and_dynamic_agency_fields(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $agency = $this->makeAgency();

        $this->makeFormField($agency, 'fishr_number', 'FishR Number');
        $this->makeFormField($agency, 'custom_registry_note', 'Custom Registry Note');

        $response = $this->actingAs($staff)
            ->get(route('api.agencies.form-fields', ['agencies' => (string) $agency->id]));

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $agency->id)
            ->assertJsonPath('0.form_fields.0.field_name', 'fishr_number')
            ->assertJsonPath('0.form_fields.1.field_name', 'custom_registry_note');
    }

    public function test_admin_can_add_reserved_field_name_for_agency_specific_static_field_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = $this->makeAgency();

        $response = $this->actingAs($admin)
            ->post(route('admin.settings.agencies.form-fields.store', $agency), [
                'field_name' => 'rsbsa_number',
                'display_label' => 'RSBSA Number',
                'field_type' => 'text',
                'is_required' => true,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('field.field_name', 'rsbsa_number');

        $this->assertDatabaseHas('agency_form_fields', [
            'agency_id' => $agency->id,
            'field_name' => 'rsbsa_number',
        ]);
    }

    public function test_admin_can_update_reserved_field_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = $this->makeAgency();
        $legacyReserved = $this->makeFormField($agency, 'cloa_ep_number', 'Legacy CLOA/EP');

        $response = $this->actingAs($admin)
            ->put(route('admin.settings.agencies.form-fields.update', [$agency, 'fieldId' => $legacyReserved->id]), [
                'display_label' => 'Edited Label',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('field.display_label', 'Edited Label');

        $legacyReserved->refresh();
        $this->assertSame('Edited Label', $legacyReserved->display_label);
    }

    public function test_cleanup_reserved_endpoint_removes_reserved_fields_and_their_options_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = $this->makeAgency();

        $reserved = $this->makeFormField($agency, 'farm_ownership', 'Legacy Farm Ownership', 'dropdown');
        $custom = $this->makeFormField($agency, 'custom_priority_code', 'Custom Priority Code', 'dropdown');

        AgencyFormFieldOption::create([
            'agency_form_field_id' => $reserved->id,
            'label' => 'Owned',
            'value' => 'owned',
            'sort_order' => 1,
        ]);
        AgencyFormFieldOption::create([
            'agency_form_field_id' => $reserved->id,
            'label' => 'Shared',
            'value' => 'shared',
            'sort_order' => 2,
        ]);
        AgencyFormFieldOption::create([
            'agency_form_field_id' => $custom->id,
            'label' => 'P1',
            'value' => 'p1',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.settings.agencies.form-fields.cleanup-reserved', $agency));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('deleted_fields', 1)
            ->assertJsonPath('deleted_options', 2);

        $this->assertDatabaseMissing('agency_form_fields', [
            'id' => $reserved->id,
        ]);
        $this->assertDatabaseMissing('agency_form_field_options', [
            'agency_form_field_id' => $reserved->id,
        ]);

        $this->assertDatabaseHas('agency_form_fields', [
            'id' => $custom->id,
            'field_name' => 'custom_priority_code',
        ]);
        $this->assertDatabaseHas('agency_form_field_options', [
            'agency_form_field_id' => $custom->id,
            'value' => 'p1',
        ]);
    }

    private function makeAgency(): Agency
    {
        return Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);
    }

    private function makeFormField(Agency $agency, string $fieldName, string $displayLabel, string $fieldType = 'text'): AgencyFormField
    {
        return AgencyFormField::create([
            'agency_id' => $agency->id,
            'field_name' => $fieldName,
            'display_label' => $displayLabel,
            'field_type' => $fieldType,
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 10,
            'form_section' => 'general_information',
        ]);
    }
}
