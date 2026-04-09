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
        $start = $interview->scheduled_at?->copy() ?? now();
        $end = $start->copy()->addMinutes(max(15, (int) ($interview->duration_minutes ?: 60)));
        $application = $interview->application;
        $candidate = $application?->candidate;
        $job = $application?->job;
        $location = $this->resolveLocation($interview);
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

        return implode("\r\n", [
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
            'STATUS:CONFIRMED',
            'SEQUENCE:' . max(0, (int) $interview->updated_at?->timestamp),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
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
        return $date
            ->copy()
            ->shiftTimezone($this->interviewTimezone())
            ->utc()
            ->format('Ymd\THis\Z');
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
            ->replace(',', '\,')
            ->replace(';', '\;')
            ->replace("\r\n", '\n')
            ->replace("\n", '\n')
            ->toString();
    }
}
