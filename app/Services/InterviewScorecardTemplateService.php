<?php

namespace App\Services;

use App\Models\ScorecardTemplate;
use Illuminate\Validation\ValidationException;

class InterviewScorecardTemplateService
{
    /**
     * @return array<int, string>
     */
    public function options(): array
    {
        return $this->selectionData()['options'];
    }

    /**
     * @return array{options: array<int, string>, criteria: array<int, array<int, string>>}
     */
    public function selectionData(): array
    {
        $templates = ScorecardTemplate::query()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'criteria']);

        return [
            'options' => $templates->pluck('name', 'id')->all(),
            'criteria' => $templates
                ->mapWithKeys(fn (ScorecardTemplate $template): array => [
                    $template->id => collect($template->criteria)
                        ->pluck('name')
                        ->filter()
                        ->values()
                        ->all(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array{name: string, criteria: array<int, array<string, mixed>>}
     */
    public function snapshot(int $templateId): array
    {
        $template = ScorecardTemplate::query()->find($templateId);
        $criteria = $template?->criteria;

        if (! $template || ! is_array($criteria) || $criteria === []) {
            throw ValidationException::withMessages([
                'scorecard_template_id' => 'Vui lòng chọn mẫu đánh giá hợp lệ.',
            ]);
        }

        return [
            'name' => $template->name,
            'criteria' => array_values($criteria),
        ];
    }
}
