<?php

namespace App\Services;

use App\Models\Interview;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InterviewCalendarService
{
    public function store(Interview $interview): string
    {
        $path = $this->pathFor($interview);

        Storage::disk('local')->put($path, $this->buildContent($interview));

        return $path;
    }

    public function pathFor(Interview $interview): string
    {
        return "interviews/interview-{$interview->id}.ics";
    }

    public function fileNameFor(Interview $interview): string
    {
        return "interview-schedule-{$interview->id}.ics";
    }

    public function buildContent(Interview $interview): string
    {
        return $this->buildRecipientContent($interview);
    }

    public function buildRecipientContent(
        Interview $interview,
        ?string $recipientEmail = null,
        ?string $recipientName = null,
    ): string {
        $start = $interview->scheduled_at?->copy() ?? now();
        $end = $start->copy()->addMinutes(max(15, (int) ($interview->duration_minutes ?: 60)));
        $application = $interview->application;
        $candidate = $application?->candidate;
        $job = $application?->job;
        $location = $this->resolveLocation($interview);
        $organizerEmail = (string) config('mail.from.address', 'no-reply@example.com');
        $organizerName = (string) config('mail.from.name', config('app.name', 'Laravel'));

        $summary = $this->escapeText(sprintf(
            'Phỏng vấn - %s - %s',
            $candidate?->name ?? 'Ứng viên',
            $job?->title ?? 'Vị trí ứng tuyển'
        ));

        $descriptionLines = array_filter([
            'Lịch phỏng vấn được tạo từ hệ thống tuyển dụng.',
            $job?->title ? "Vị trí: {$job->title}" : null,
            $candidate?->name ? "Ứng viên: {$candidate->name}" : null,
            $interview->interviewer?->name ? "Người phỏng vấn: {$interview->interviewer->name}" : null,
            $interview->type === 'online' && $interview->meeting_link ? "Link phỏng vấn: {$interview->meeting_link}" : null,
            $interview->notes ? "Ghi chú: {$interview->notes}" : null,
        ]);

        $description = $this->escapeText(implode("\n", $descriptionLines));
        $uidHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $attendeeLine = null;

        if (filled($recipientEmail)) {
            $attendeeLabel = $this->escapeText($recipientName ?: $recipientEmail);
            $attendeeLine = 'ATTENDEE;CN=' . $attendeeLabel . ';ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE:mailto:' . $recipientEmail;
        }

        $lines = array_filter([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Hackrathon//Interview Schedule//VI',
            'CALSCALE:GREGORIAN',
            'METHOD:REQUEST',
            'BEGIN:VEVENT',
            'UID:interview-' . $interview->id . '@' . $uidHost,
            'DTSTAMP:' . $this->formatUtcDate(now()),
            'DTSTART:' . $this->formatScheduledDate($start),
            'DTEND:' . $this->formatScheduledDate($end),
            'SUMMARY:' . $summary,
            'DESCRIPTION:' . $description,
            'LOCATION:' . $this->escapeText($location),
            'ORGANIZER;CN=' . $this->escapeText($organizerName) . ':mailto:' . $organizerEmail,
            $attendeeLine,
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
            'SEQUENCE:' . max(0, (int) $interview->updated_at?->timestamp),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        return implode("\r\n", $lines);
    }

    public function resolveLocation(Interview $interview): string
    {
        if ($interview->type === 'online') {
            return $interview->meeting_link ?: 'Online';
        }

        $parts = array_filter([
            $interview->workplace?->name,
            $interview->workplace?->room ? 'Phòng ' . $interview->workplace->room : null,
            $interview->workplace?->floor ? 'Tầng ' . $interview->workplace->floor : null,
        ]);

        return implode(' - ', $parts) ?: 'Tại văn phòng';
    }

    protected function formatScheduledDate(CarbonInterface $date): string
    {
        // $date is already a UTC Carbon instance (stored as UTC in DB).
        // shiftTimezone() only relabels the timezone without converting the clock value,
        // so calling ->utc() after it would produce a time 7 hours too early.
        // Simply convert to UTC and format directly.
        return $date->copy()->utc()->format('Ymd\THis\Z');
    }

    protected function formatUtcDate(CarbonInterface $date): string
    {
        return $date->copy()->utc()->format('Ymd\THis\Z');
    }

    protected function interviewTimezone(): string
    {
        return config('app.interview_timezone', 'Asia/Saigon');
    }

    protected function escapeText(string $value): string
    {
        return Str::of($value)
            ->replace('\\', '\\\\')
            ->replace(',', '\\,')
            ->replace(';', '\\;')
            ->replace("\r\n", '\\n')
            ->replace("\n", '\\n')
            ->toString();
    }
}
