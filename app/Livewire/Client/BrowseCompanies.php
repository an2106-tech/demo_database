<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Branch;
use App\Enums\StatusRecruitmentJobsEnum;
use Carbon\Carbon;
use Livewire\WithPagination;
use App\Enums\VietnamProvince; // Import Enum của bạn

class BrowseCompanies extends Component
{
    use WithPagination;

    // Các thuộc tính liên kết với bộ lọc
    public $search = ''; // Keyword tìm kiếm chính
    public $date_filter = 'all'; // Lọc theo ngày đăng: 'all', 'hour', '24h', '7d', '14d', '30d'
    public $salary_range = [0, 10000]; // Mặc định từ 0 - 10 nghìn (tùy chỉnh theo CSDL)
    // --- Thuộc tính mới cho Địa điểm ---
    public $selected_cities = [];      // Lưu các giá trị enum (vd: ['ha_noi', 'can_tho'])
    public $search_city_keyword = '';  // Keyword tìm kiếm trong dropdown
    public $applied_cities = [];       // Lưu giá trị thực sự dùng để query DB
    // Trong class BrowseCompanies
    public $salary_min = 0; // Giá trị lương tối thiểu người dùng chọn


    // Cấu hình query string để giữ trạng thái bộ lọc khi phân trang hoặc làm mới trang
    protected $queryString = [
        'search' => ['except' => ''],
        'date_filter' => ['except' => 'all'],
        'applied_cities' => ['as' => 'cities', 'except' => []],
    ];


    // Hàm này sẽ được gọi mỗi khi $search thay đổi để reset về trang 1
    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Layout('layouts.client')]
    
    // Hàm xóa tất cả
    public function clearAllCities()
    {
        $this->selected_cities = [];
        $this->applied_cities = [];
        $this->resetPage();
    }

    // Hàm nhấn nút "Áp dụng"
    public function applyCityFilter()
    {
        $this->applied_cities = $this->selected_cities;
        $this->resetPage();
        // Dispatch browser event để tự động đóng dropdown (nếu cần)
        $this->dispatch('close-city-dropdown');
    }

    public function render()
    {
        $now = Carbon::now();

        // 1. Lấy danh sách tỉnh thành từ Enum và lọc theo keyword tìm kiếm trong dropdown
        $provincesList = VietnamProvince::options();
        if (!empty($this->search_city_keyword)) {
            $provincesList = array_filter($provincesList, function ($label) {
                return str_contains(mb_strtolower($label), mb_strtolower($this->search_city_keyword));
            });
        }

        $query = Branch::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'image', 'city', 'address', 'email_contact', 'is_active']);

        // 2. Lọc theo KEYWORD chính (Search Box ngoài)
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%')
                    ->orWhereHas('recruitmentJobs', function ($sub) {
                        $sub->where('title', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // 3. Lọc theo ĐỊA ĐIỂM (Chỉ lọc khi đã nhấn Áp dụng)
        if (!empty($this->applied_cities)) {
            $query->whereIn('city', $this->applied_cities);
        }

        // --- BỘ LỌC NGÀY ĐĂNG ---
        if ($this->date_filter !== 'all') {
            $threshold = match ($this->date_filter) {
                'hour' => $now->copy()->subHour(),
                '24h'  => $now->copy()->subDay(),
                '7d'   => $now->copy()->subDays(7),
                '14d'  => $now->copy()->subDays(14),
                '30d'  => $now->copy()->subDays(30),
                default => null
            };

            if ($threshold) {
                $query->whereHas('recruitmentJobs', function ($q) use ($threshold) {
                    $q->where('created_at', '>=', $threshold);
                });
            }
        }

        // --- ĐIỀU KIỆN TIN TUYỂN DỤNG CHUNG (Status, Deadline, Salary) ---
        $jobCondition = function ($query) use ($now) {
            $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                ->where(function ($q) use ($now) {
                    $q->whereNull('deadline')->orWhere('deadline', '>=', $now);
                });

            // Lọc: Chỉ lấy các công ty có công việc mà lương MAX >= mức người dùng chọn
            // (Hoặc tùy biến theo logic của bạn: lương MIN >= $this->salary_min)
            if ($this->salary_min > 0) {
                $query->whereRaw("CAST(JSON_EXTRACT(salary_range, '$.max') AS UNSIGNED) >= ?", [$this->salary_min]);
            }
        };

        // Áp dụng điều kiện cho whereHas và withCount
        $query->whereHas('recruitmentJobs', $jobCondition)
            ->withCount(['recruitmentJobs as published_jobs_count' => $jobCondition])
            ->with(['recruitmentJobs' => function ($q) use ($jobCondition) {
                $jobCondition($q);
                $q->orderByDesc('created_at')->select(['id', 'branch_id', 'title', 'slug', 'salary_range', 'deadline', 'created_at']);
            }]);

        $branches = $query->latest()->paginate(10);

        return view('livewire.client.browse-companies', [
            'branches' => $query->latest()->paginate(10),
            'provincesList' => $provincesList
        ]);
    }
}
