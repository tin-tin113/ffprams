<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeneficiaryAttachmentRequest;
use App\Models\Beneficiary;
use App\Models\BeneficiaryAttachment;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BeneficiaryAttachmentController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function create(Beneficiary $beneficiary): View
    {
        $beneficiary->load([
            'barangay',
            'attachments' => fn ($q) => $q->latest('id')->with('uploader:id,name'),
        ]);

        return view('beneficiaries.attachments', compact('beneficiary'));
    }

    public function store(BeneficiaryAttachmentRequest $request, Beneficiary $beneficiary): RedirectResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to upload attachments.');
        }

        $uploadedFile = $request->file('attachment');
        $disk = 'beneficiary_documents';
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension() ?: '');
        $storedName = (string) Str::uuid().'_'.now()->format('YmdHis').($extension !== '' ? '.'.$extension : '');
        $directory = 'beneficiary-'.$beneficiary->id.'/'.now()->format('Y/m');
        $path = $uploadedFile->storeAs($directory, $storedName, ['disk' => $disk]);

        if (! $path) {
            return redirect()->back()->with('error', 'Attachment upload failed. Please try again.');
        }

        $sha256 = hash_file('sha256', $uploadedFile->getRealPath());

        $attachment = BeneficiaryAttachment::create([
            'beneficiary_id' => $beneficiary->id,
            'uploaded_by' => $userId,
            'document_type' => $request->input('document_type'),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $uploadedFile->getClientMimeType() ?: 'application/octet-stream',
            'extension' => $extension ?: null,
            'size_bytes' => (int) $uploadedFile->getSize(),
            'sha256' => $sha256,
        ]);

        $this->audit->log(
            $userId,
            'uploaded',
            'beneficiary_attachments',
            $attachment->id,
            [],
            $attachment->toArray(),
        );

        return redirect()->back()->with('success', 'Attachment uploaded successfully.');
    }

    public function download(Beneficiary $beneficiary, BeneficiaryAttachment $attachment): BinaryFileResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to download attachments.');
        }

        abort_unless($attachment->beneficiary_id === $beneficiary->id, 404);

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'Attachment file not found in storage.');
        }

        $this->audit->log(
            $userId,
            'downloaded',
            'beneficiary_attachments',
            $attachment->id,
            [],
            [
                'beneficiary_id' => $beneficiary->id,
                'path' => $attachment->path,
                'original_name' => $attachment->original_name,
            ],
        );

        return response()->download(
            Storage::disk($attachment->disk)->path($attachment->path),
            $attachment->original_name,
            [
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    public function destroy(Beneficiary $beneficiary, BeneficiaryAttachment $attachment): RedirectResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to delete attachments.');
        }

        abort_unless($attachment->beneficiary_id === $beneficiary->id, 404);

        $oldValues = $attachment->toArray();

        DB::transaction(function () use ($attachment, $oldValues, $userId): void {
            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }

            $attachment->delete();

            $this->audit->log(
                $userId,
                'deleted',
                'beneficiary_attachments',
                $attachment->id,
                $oldValues,
                [],
            );
        });

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }
}
