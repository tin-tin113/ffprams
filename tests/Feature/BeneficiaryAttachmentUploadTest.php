<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\BeneficiaryAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeneficiaryAttachmentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_upload_documents_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $beneficiary = $this->makeBeneficiaryFixture();

        $response = $this->actingAs($admin)
            ->get(route('beneficiaries.attachments.create', $beneficiary));

        $response
            ->assertOk()
            ->assertSee('Upload Supporting Document')
            ->assertSee($beneficiary->full_name);
    }

    public function test_admin_can_upload_beneficiary_attachment(): void
    {
        Storage::fake('beneficiary_documents');

        $admin = User::factory()->create(['role' => 'admin']);
        $beneficiary = $this->makeBeneficiaryFixture();

        $response = $this->actingAs($admin)->post(route('beneficiaries.attachments.store', $beneficiary), [
            'document_type' => 'Valid ID',
            'attachment' => UploadedFile::fake()->create('valid-id.pdf', 200, 'application/pdf'),
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('success');

        $attachment = BeneficiaryAttachment::query()->firstOrFail();

        $this->assertSame($beneficiary->id, $attachment->beneficiary_id);
        $this->assertSame('Valid ID', $attachment->document_type);

        $this->assertTrue(Storage::disk('beneficiary_documents')->exists($attachment->path));
    }

    public function test_attachment_upload_rejects_disallowed_file_type(): void
    {
        Storage::fake('beneficiary_documents');

        $admin = User::factory()->create(['role' => 'admin']);
        $beneficiary = $this->makeBeneficiaryFixture();

        $response = $this->actingAs($admin)->post(route('beneficiaries.attachments.store', $beneficiary), [
            'document_type' => 'Executable',
            'attachment' => UploadedFile::fake()->create('payload.exe', 128, 'application/octet-stream'),
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['attachment']);

        $this->assertDatabaseCount('beneficiary_attachments', 0);
    }

    public function test_admin_can_download_and_delete_beneficiary_attachment(): void
    {
        Storage::fake('beneficiary_documents');

        $admin = User::factory()->create(['role' => 'admin']);
        $beneficiary = $this->makeBeneficiaryFixture();

        $path = 'beneficiary-'.$beneficiary->id.'/2026/04/example.pdf';
        Storage::disk('beneficiary_documents')->put($path, 'example document content');

        $attachment = BeneficiaryAttachment::create([
            'beneficiary_id' => $beneficiary->id,
            'uploaded_by' => $admin->id,
            'document_type' => 'Barangay Certification',
            'original_name' => 'barangay-certification.pdf',
            'stored_name' => 'stored-example.pdf',
            'path' => $path,
            'disk' => 'beneficiary_documents',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => strlen('example document content'),
            'sha256' => hash('sha256', 'example document content'),
        ]);

        $downloadResponse = $this->actingAs($admin)
            ->get(route('beneficiaries.attachments.download', [$beneficiary, $attachment]));

        $downloadResponse
            ->assertOk()
            ->assertHeader('content-disposition');

        $deleteResponse = $this->actingAs($admin)
            ->delete(route('beneficiaries.attachments.destroy', [$beneficiary, $attachment]));

        $deleteResponse
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('beneficiary_attachments', ['id' => $attachment->id]);
        $this->assertFalse(Storage::disk('beneficiary_documents')->exists($path));
    }

    private function makeBeneficiaryFixture(): Beneficiary
    {
        $agency = Agency::create([
            'name' => 'DA',
            'full_name' => 'Department of Agriculture',
            'is_active' => true,
        ]);

        $barangay = Barangay::create([
            'name' => 'Attachment Test Barangay '.uniqid(),
            'latitude' => 10.20000000,
            'longitude' => 123.20000000,
        ]);

        return Beneficiary::create([
            'agency_id' => $agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'barangay_id' => $barangay->id,
            'classification' => 'Farmer',
            'contact_number' => '',
            'status' => 'Active',
            'registered_at' => now()->toDateString(),
        ]);
    }
}
