<?php

namespace Tests\Unit;

use App\Services\AiChatContextSelector;
use PHPUnit\Framework\TestCase;

class AiChatContextSelectorTest extends TestCase
{
    public function test_selector_keeps_essential_context_and_prioritizes_relevant_sources(): void
    {
        $context = [[
            'key' => 'operational-workload',
            'label' => 'Việc cần ưu tiên',
            'content' => 'CV chờ sàng lọc: 2',
            'url' => null,
        ]];

        foreach (range(1, 15) as $index) {
            $context[] = [
                'key' => 'job-'.$index,
                'label' => $index === 14 ? 'Tin Laravel Backend' : 'Tin kinh doanh '.$index,
                'content' => $index === 14 ? 'Cần kỹ năng Laravel và PHP' : 'Kinh doanh bán hàng',
                'url' => null,
            ];
        }

        $selected = (new AiChatContextSelector)->select($context, 'Tình hình tuyển Laravel PHP', 'employer');
        $keys = array_column($selected, 'key');

        $this->assertCount(12, $selected);
        $this->assertContains('operational-workload', $keys);
        $this->assertSame('job-14', $selected[1]['key']);
    }
}
