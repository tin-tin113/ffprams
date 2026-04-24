<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordAttachmentRequest;
use App\Models\Allocation;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\RecordAttachment;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecordAttachmentController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function storeForEvent(RecordAttachmentRequest $request, DistributionEvent $event): RedirectResponse
    {
        return $this->storeForTarget(
            $request,
            $event,
            fn () => redirect()->to(route('distribution-events.show', $event) . '#tab-documents'),
        );
    }

    public function viewForEvent(DistributionEvent $event, RecordAttachment $recordAttachment): BinaryFileResponse
    {
        return $this->viewForTarget($event, $recordAttachment);
    }

    public function downloadForEvent(DistributionEvent $event, RecordAttachment $recordAttachment): BinaryFileResponse
    {
        return $this->downloadForTarget($event, $recordAttachment);
    }

    public function destroyForEvent(DistributionEvent $event, RecordAttachment $recordAttachment): RedirectResponse
    {
        return $this->destroyForTarget(
            $event,
            $recordAttachment,
            fn () => redirect()->to(route('distribution-events.show', $event) . '#tab-documents'),
        );
    }

    public function storeForAllocation(RecordAttachmentRequest $request, Allocation $allocation): RedirectResponse
    {
        return $this->storeForTarget(
            $request,
            $allocation,
            fn () => redirect()->route('allocations.show', $allocation),
        );
    }

    public function viewForAllocation(Allocation $allocation, RecordAttachment $recordAttachment): BinaryFileResponse
    {
        return $this->viewForTarget($allocation, $recordAttachment);
    }

    public function downloadForAllocation(Allocation $allocation, RecordAttachment $recordAttachment): BinaryFileResponse
    {
        return $this->downloadForTarget($allocation, $recordAttachment);
    }

    public function destroyForAllocation(Allocation $allocation, RecordAttachment $recordAttachment): RedirectResponse
    {
        return $this->destroyForTarget(
            $allocation,
            $recordAttachment,
            fn () => redirect()->route('allocations.show', $allocation),
        );
    }

    public function storeForDirectAssistance(RecordAttachmentRequest $request, DirectAssistance $directAssistance): RedirectResponse
    {
        return $this->storeForTarget(
            $request,
            $directAssistance,
            fn () => redirect()->route('direct-assistance.show', $directAssistance),
        );
    }

    public function viewForDirectAssistance(DirectAssistance $directAssistance, RecordAttachment $recordAttachment): BinaryFileResponse
    {
        return $this->viewForTarget($directAssistance, $recordAttachment);
    }

    public function downloadForDirectAssistance(DirectAssistance $directAssistance, RecordAttachment $recordAttachment): BinaryFileResponse
    {
        return $this->downloadForTarget($directAssistance, $recordAttachment);
    }

    public function destroyForDirectAssistance(DirectAssistance $directAssistance, RecordAttachment $recordAttachment): RedirectResponse
    {
        return $this->destroyForTarget(
            $directAssistance,
            $recordAttachment,
            fn () => redirect()->route('direct-assistance.show', $directAssistance),
        );
    }

    /**
     * @param  callable(): RedirectResponse  $redirectTo
     */
    private function storeForTarget(RecordAttachmentRequest $request, Model $target, callable $redirectTo): RedirectResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to upload attachments.');
        }

        $uploadedFile = $request->file('attachment');
        $disk = 'record_documents';
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension() ?: '');
        $storedName = (string) Str::uuid().'_'.now()->format('YmdHis').($extension !== '' ? '.'.$extension : '');
        $directory = Str::kebab(class_basename($target)).'-'.$target->getKey().'/'.now()->format('Y/m');
        $path = $uploadedFile->storeAs($directory, $storedName, ['disk' => $disk]);

        if (! $path) {
            return $redirectTo()->with('error', 'Attachment upload failed. Please try again.');
        }

        $sha256 = hash_file('sha256', $uploadedFile->getRealPath());

        $attachment = RecordAttachment::create([
            'attachable_type' => $target::class,
            'attachable_id' => (int) $target->getKey(),
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
            'record_attachments',
            $attachment->id,
            [],
            $attachment->toArray(),
        );

        return $redirectTo()->with('success', 'Attachment uploaded successfully.');
    }

    private function viewForTarget(Model $target, RecordAttachment $attachment): BinaryFileResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to view attachments.');
        }

        $this->assertAttachmentBelongsToTarget($attachment, $target);

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'Attachment file not found in storage.');
        }

        $this->audit->log(
            $userId,
            'viewed',
            'record_attachments',
            $attachment->id,
            [],
            [
                'attachable_type' => $attachment->attachable_type,
                'attachable_id' => $attachment->attachable_id,
                'path' => $attachment->path,
                'original_name' => $attachment->original_name,
            ],
        );

        return response()->file(
            Storage::disk($attachment->disk)->path($attachment->path),
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    private function downloadForTarget(Model $target, RecordAttachment $attachment): BinaryFileResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to download attachments.');
        }

        $this->assertAttachmentBelongsToTarget($attachment, $target);

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'Attachment file not found in storage.');
        }

        $this->audit->log(
            $userId,
            'downloaded',
            'record_attachments',
            $attachment->id,
            [],
            [
                'attachable_type' => $attachment->attachable_type,
                'attachable_id' => $attachment->attachable_id,
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

    /**
     * @param  callable(): RedirectResponse  $redirectTo
     */
    private function destroyForTarget(Model $target, RecordAttachment $attachment, callable $redirectTo): RedirectResponse
    {
        $userId = Auth::id();
        if ($userId === null) {
            abort(403, 'Authentication is required to delete attachments.');
        }

        $this->assertAttachmentBelongsToTarget($attachment, $target);

        $oldValues = $attachment->toArray();

        DB::transaction(function () use ($attachment, $oldValues, $userId): void {
            if (Storage::disk($attachment->disk)->exists($attachment->path)) {
                Storage::disk($attachment->disk)->delete($attachment->path);
            }

            $attachment->delete();

            $this->audit->log(
                $userId,
                'deleted',
                'record_attachments',
                $attachment->id,
                $oldValues,
                [],
            );
        });

        return $redirectTo()->with('success', 'Attachment deleted successfully.');
    }

    private function assertAttachmentBelongsToTarget(RecordAttachment $attachment, Model $target): void
    {
        abort_unless(
            $attachment->attachable_type === $target::class
                && (int) $attachment->attachable_id === (int) $target->getKey(),
            404,
        );
    }
}
