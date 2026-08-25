<?php

namespace App\Livewire\Client;

use App\Models\Attachment;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Rules\CvUploadFile;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.client')]
class ManageCv extends Component
{
    use WithFileUploads;

    public int $candidateId;
    public ?Candidate $candidate = null;
    public ?CandidateResume $resume = null;

    // Primary CV Configuration
    public string $primaryCvType = 'online'; // 'online' or 'attachment'
    public string $primaryCvTemplate = 'fpt-modern';
    public ?int $primaryCvAttachmentId = null;

    // Upload new CV
    public $newCvUpload = null;
    public string $newCvTitle = '';

    public int $aiScore = 0;
    public array $aiReasons = [];

    public array $availableTemplates = [
        [
            'id' => 'fpt-modern',
            'name' => 'FPT Modern Pro',
            'desc' => 'Bố cục 2 cột hiện đại, chuẩn nhận diện FPT, sidebar navy sang trọng.',
            'color' => '#f37021',
            'badge' => 'Khuyên dùng',
            'badge_class' => 'badge-fpt',
        ],
        [
            'id' => 'ats-classic',
            'name' => 'ATS Classic Clean',
            'desc' => 'Định dạng 1 cột tiêu chuẩn quốc tế, tương thích 100% với hệ thống quét tự động ATS.',
            'color' => '#0f172a',
            'badge' => 'ATS Standard',
            'badge_class' => 'badge-ats',
        ],
        [
            'id' => 'tech-executive',
            'name' => 'Tech Executive',
            'desc' => 'Banner tối sang trọng với điểm nhấn xanh Emerald, dành cho vị trí Tech Lead / Senior.',
            'color' => '#10b981',
            'badge' => 'Tech Leader',
            'badge_class' => 'badge-tech',
        ],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $candidateService = app(CandidateAccountService::class);
        $this->candidate = $candidateService->resolveFor($user);
        $this->candidateId = $this->candidate->id;

        $this->resume = CandidateResume::query()->firstOrCreate(
            ['candidate_id' => $this->candidate->id],
            []
        );

        $this->loadPrimaryCvSettings();

        $this->aiScore = (int) ($this->candidate->match_score ?? 0);
        $this->aiReasons = is_array($this->candidate->match_reasons) ? $this->candidate->match_reasons : [];
    }

    private function loadPrimaryCvSettings(): void
    {
        $metadata = is_array($this->candidate->metadata) ? $this->candidate->metadata : [];
        $primaryCv = $metadata['primary_cv'] ?? [];

        $this->primaryCvType = $primaryCv['type'] ?? 'online';
        $this->primaryCvTemplate = $primaryCv['template'] ?? 'fpt-modern';
        $this->primaryCvAttachmentId = isset($primaryCv['attachment_id']) ? (int) $primaryCv['attachment_id'] : null;

        // If primary is set to attachment, verify the attachment still exists
        if ($this->primaryCvType === 'attachment' && $this->primaryCvAttachmentId) {
            $exists = $this->candidate->attachments()->where('id', $this->primaryCvAttachmentId)->exists();
            if (!$exists) {
                $this->primaryCvType = 'online';
                $this->primaryCvAttachmentId = null;
            }
        }
    }

    public function setPrimaryCv(string $type, ?string $template = null, ?int $attachmentId = null): void
    {
        if (!in_array($type, ['online', 'attachment'], true)) {
            return;
        }

        $metadata = is_array($this->candidate->metadata) ? $this->candidate->metadata : [];

        if ($type === 'online') {
            $template = in_array($template, ['fpt-modern', 'ats-classic', 'tech-executive'], true) ? $template : 'fpt-modern';
            $metadata['primary_cv'] = [
                'type' => 'online',
                'template' => $template,
                'attachment_id' => null,
                'title' => 'CV Online (' . $this->getTemplateName($template) . ')',
                'updated_at' => now()->toIso8601String(),
            ];
            $this->primaryCvType = 'online';
            $this->primaryCvTemplate = $template;
            $this->primaryCvAttachmentId = null;
        } else {
            $attachment = $this->candidate->attachments()->where('id', $attachmentId)->first();
            if (!$attachment) {
                $this->dispatch('app-notify', message: 'Không tìm thấy file CV đã chọn.', type: 'error');
                return;
            }

            $metadata['primary_cv'] = [
                'type' => 'attachment',
                'template' => null,
                'attachment_id' => $attachment->id,
                'title' => $attachment->original_filename ?? 'File CV Đính kèm',
                'updated_at' => now()->toIso8601String(),
            ];

            // Also synchronize cv_file for backwards compatibility
            $this->candidate->cv_file = $attachment->path;
            $this->primaryCvType = 'attachment';
            $this->primaryCvAttachmentId = $attachment->id;
        }

        $this->candidate->metadata = $metadata;
        $this->candidate->save();

        $this->dispatch('app-notify', message: '⭐ Đã đặt làm CV chính thành công!', type: 'success');
    }

    public function uploadNewCv(): void
    {
        $this->validate([
            'newCvUpload' => ['required', 'file', 'max:10240', new CvUploadFile()],
            'newCvTitle' => ['nullable', 'string', 'max:255'],
        ], [
            'newCvUpload.required' => 'Vui lòng chọn file CV cần tải lên.',
            'newCvUpload.max' => 'Dung lượng file CV không được vượt quá 10MB.',
        ]);

        $path = $this->newCvUpload->storePublicly("candidates/{$this->candidate->id}/cv", 'public');

        $attachment = $this->candidate->attachments()->create([
            'path' => $path,
            'type' => 'cv',
            'original_filename' => $this->newCvTitle ? (trim($this->newCvTitle) . '.' . $this->newCvUpload->getClientOriginalExtension()) : ($this->newCvUpload->getClientOriginalName() ?: 'CV_Upload.pdf'),
            'mime_type' => $this->newCvUpload->getMimeType() ?: 'application/pdf',
            'size_bytes' => $this->newCvUpload->getSize() ?: 0,
        ]);

        // If no primary CV set or user only had default online, offer/auto set if first attachment
        if ($this->candidate->attachments()->where('type', 'cv')->count() === 1 && $this->primaryCvType === 'online') {
            $this->setPrimaryCv('attachment', null, $attachment->id);
        }

        $this->newCvUpload = null;
        $this->newCvTitle = '';

        $this->dispatch('app-notify', message: 'Tải lên file CV thành công!', type: 'success');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = $this->candidate->attachments()->where('id', $attachmentId)->first();
        if (!$attachment) {
            return;
        }

        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        // If this attachment was primary CV, reset to online
        if ($this->primaryCvAttachmentId === $attachmentId) {
            $this->setPrimaryCv('online', 'fpt-modern');
        }

        $this->dispatch('app-notify', message: 'Đã xóa file CV đính kèm.', type: 'info');
    }

    public function getTemplateName(string $templateId): string
    {
        return match ($templateId) {
            'ats-classic' => 'ATS Classic Clean',
            'tech-executive' => 'Tech Executive',
            default => 'FPT Modern Pro',
        };
    }

    public function render()
    {
        $attachments = $this->candidate->attachments()
            ->where('type', 'cv')
            ->latest()
            ->get();

        $profileCompletion = app(CandidateAccountService::class)->profileCompletion($this->candidate);

        return view('livewire.client.manage-cv', [
            'attachments' => $attachments,
            'profileCompletion' => $profileCompletion,
        ]);
    }
}
