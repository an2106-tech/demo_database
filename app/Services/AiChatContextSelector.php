<?php

namespace App\Services;

use Illuminate\Support\Str;

class AiChatContextSelector
{
    private const MAX_SOURCES = 12;

    private const MAX_CHARACTERS = 18000;

    /**
     * @param  array<int, array{key: string, label: string, content: string, url: string|null}>  $context
     * @return array<int, array{key: string, label: string, content: string, url: string|null}>
     */
    public function select(array $context, string $question, string $audience): array
    {
        $terms = $this->terms($question);
        $essentialKeys = $audience === 'candidate'
            ? ['candidate-profile']
            : ['operational-workload', 'recruitment-pipeline'];

        $ranked = collect($context)
            ->map(function (array $source, int $position) use ($terms, $essentialKeys): array {
                $label = $this->normalize($source['label']);
                $content = $this->normalize($source['content']);
                $score = in_array($source['key'], $essentialKeys, true) ? 1000 : 0;

                foreach ($terms as $term) {
                    $score += str_contains($label, $term) ? 12 : 0;
                    $score += str_contains($content, $term) ? 3 : 0;
                }

                return ['source' => $source, 'score' => $score, 'position' => $position];
            })
            ->sort(function (array $left, array $right): int {
                return $right['score'] <=> $left['score'] ?: $left['position'] <=> $right['position'];
            });

        $selected = [];
        $characters = 0;

        foreach ($ranked as $item) {
            $source = $item['source'];
            $length = mb_strlen($source['label'].$source['content']);
            if (count($selected) >= self::MAX_SOURCES || ($selected !== [] && $characters + $length > self::MAX_CHARACTERS)) {
                continue;
            }

            $selected[] = $source;
            $characters += $length;
        }

        return $selected;
    }

    private function terms(string $value): array
    {
        $stopWords = ['cho', 'cua', 'toi', 'nay', 'nao', 'nhung', 'cac', 'dang', 'hien', 'tai', 'the', 'voi', 'mot'];
        $tokens = preg_split('/\s+/u', $this->normalize($value)) ?: [];

        return array_values(array_unique(array_filter(
            $tokens,
            fn (string $token): bool => mb_strlen($token) >= 3 && ! in_array($token, $stopWords, true)
        )));
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii(trim($value)));
    }
}
