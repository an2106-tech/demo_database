<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Branch;
use App\Enums\StatusRecruitmentJobsEnum;
use Carbon\Carbon;

class BrowseCompanies extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        // 1. Khởi tạo thời gian hiện tại để làm mốc so sánh cho các tin tuyển dụng (tránh dùng now() nhiều lần gây lệch giây)
        $now = Carbon::now();

        // 2. Bắt đầu xây dựng truy vấn lấy danh sách chi nhánh (Branch)
        $branches = Branch::query()
            // [WHERE] Chỉ lấy những chi nhánh được đánh dấu là đang hoạt động
            ->where('is_active', true)
            ->select(['id', 'name', 'image', 'city', 'address', 'is_active'])

            // [WHERE HAS] Chỉ lấy Chi nhánh nếu nó có ít nhất một tin tuyển dụng thỏa mãn các điều kiện:
            ->whereHas('recruitmentJobs', function ($query) use ($now) {
                // - Tin tuyển dụng phải ở trạng thái Đã xuất bản
                $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    // - Và phải còn hạn (deadline là rỗng HOẶC deadline lớn hơn hoặc bằng thời điểm hiện tại)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    });
            })

            // [WITH COUNT] Đếm số lượng tin tuyển dụng thỏa mãn điều kiện và đặt tên cột là 'published_jobs_count'
            ->withCount([
                'recruitmentJobs as published_jobs_count' => fn($query) => $query
                    ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    }),
            ])

            // [WITH / EAGER LOADING] Lấy danh sách chi tiết các tin tuyển dụng đi kèm (tránh lỗi N+1 query)
            ->with([
                'recruitmentJobs' => fn($query) => $query
                    // - Chỉ lấy tin đã xuất bản và còn hạn
                    ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    })
                    // - Sắp xếp tin mới nhất lên trên
                    ->orderByDesc('created_at')
                    // - Chỉ lấy các trường cần thiết của tin tuyển dụng để tiết kiệm tài nguyên
                    ->select(['id', 'branch_id', 'title', 'salary_range', 'deadline', 'created_at']),
            ])

            // [ORDER BY] Sắp xếp danh sách chi nhánh theo thời gian tạo mới nhất
            ->latest()

            // [GET] Thực thi truy vấn và chỉ lấy các cột cần thiết của bảng Chi nhánh
            ->get();

        // 3. Phân nhóm (Grouping) các chi nhánh đã lấy được theo chữ cái đầu tiên của tên
        $branchesByLetter = $branches->groupBy(function (Branch $branch) {
            // Chuyển tên về dạng chuỗi, nếu không có tên thì để rỗng
            $name = (string) ($branch->name ?? '');

            // Sử dụng mb_substr để lấy ký tự đầu tiên (hỗ trợ tốt các ký tự có dấu/UTF-8)
            $firstChar = function_exists('mb_substr')
                ? mb_substr($name, 0, 1, 'UTF-8')
                : substr($name, 0, 1);

            // Chuyển về chữ hoa. Nếu tên rỗng thì đưa vào nhóm ký tự đặc biệt '#'
            return strtoupper($firstChar !== '' ? $firstChar : '#');
        });

        // 4. Tạo mảng ký tự từ A đến Z để hiển thị bộ lọc nhanh trên giao diện
        $letters = range('A', 'Z');

        return view('livewire.client.browse-companies', [
            'branches' => $branches,
            'branchesByLetter' => $branchesByLetter,
            'letters' => $letters,
        ]);
    }
}
