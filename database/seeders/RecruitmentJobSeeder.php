<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\Workplace;
use Illuminate\Database\Seeder;

class RecruitmentJobSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->get();
        $workplaces = Workplace::query()->get();
        $creator = User::query()->first();

        if ($departments->isEmpty() || $workplaces->isEmpty() || ! $creator) {
            return;
        }

        $jobs = [
            [
                'title' => 'Lập trình viên PHP Laravel',
                'slug' => 'lap-trinh-vien-php-laravel',
                'description' => 'Phát triển hệ thống ứng dụng web với Laravel 10/11, xây dựng REST API và tích hợp dịch vụ bên thứ ba.',
                'salary_range' => ['min' => 1200, 'max' => 2200, 'currency' => 'USD'],
                'deadline_days' => 30,
                'positions_count' => 3,
                'thumbnail' => 'assets/img/company-logo-4.png',
            ],
            [
                'title' => 'Chuyên viên QA tự động',
                'slug' => 'chuyen-vien-qa-tu-dong',
                'description' => 'Thiết kế test case, viết test automation, tham gia CI/CD và đảm bảo chất lượng phần mềm.',
                'salary_range' => ['min' => 1000, 'max' => 1800, 'currency' => 'USD'],
                'deadline_days' => 25,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-2.png',
            ],
            [
                'title' => 'Frontend Developer ReactJS',
                'slug' => 'frontend-developer-reactjs',
                'description' => 'Xây dựng giao diện hiện đại với ReactJS, tối ưu trải nghiệm người dùng và hiệu năng hiển thị.',
                'salary_range' => ['min' => 1100, 'max' => 2000, 'currency' => 'USD'],
                'deadline_days' => 20,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-1.png',
            ],
            [
                'title' => 'Backend Developer Node.js',
                'slug' => 'backend-developer-nodejs',
                'description' => 'Phát triển service backend với Node.js, tối ưu database và xử lý các tác vụ bất đồng bộ.',
                'salary_range' => ['min' => 1300, 'max' => 2300, 'currency' => 'USD'],
                'deadline_days' => 35,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-3.png',
            ],
            [
                'title' => 'Business Analyst',
                'slug' => 'business-analyst',
                'description' => 'Thu thập yêu cầu nghiệp vụ, làm việc với stakeholders và chuyển hóa thành tài liệu phân tích rõ ràng.',
                'salary_range' => ['min' => 900, 'max' => 1600, 'currency' => 'USD'],
                'deadline_days' => 18,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-4.png',
            ],
            [
                'title' => 'UI UX Designer',
                'slug' => 'ui-ux-designer',
                'description' => 'Thiết kế wireframe, prototype và giao diện sản phẩm theo hướng hiện đại, tối ưu hành trình người dùng.',
                'salary_range' => ['min' => 850, 'max' => 1500, 'currency' => 'USD'],
                'deadline_days' => 22,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-2.png',
            ],
            [
                'title' => 'DevOps Engineer',
                'slug' => 'devops-engineer',
                'description' => 'Quản trị hạ tầng cloud, triển khai CI/CD, monitoring và tối ưu quy trình release cho đội phát triển.',
                'salary_range' => ['min' => 1500, 'max' => 2600, 'currency' => 'USD'],
                'deadline_days' => 28,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-3.png',
            ],
            [
                'title' => 'Chuyên viên Data Analyst',
                'slug' => 'chuyen-vien-data-analyst',
                'description' => 'Phân tích dữ liệu kinh doanh, xây dựng dashboard và hỗ trợ ra quyết định dựa trên số liệu.',
                'salary_range' => ['min' => 950, 'max' => 1700, 'currency' => 'USD'],
                'deadline_days' => 24,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-1.png',
            ],
            [
                'title' => 'Data Engineer',
                'slug' => 'data-engineer',
                'description' => 'Thiết kế pipeline dữ liệu, xử lý ETL và tối ưu kho dữ liệu phục vụ báo cáo và machine learning.',
                'salary_range' => ['min' => 1400, 'max' => 2400, 'currency' => 'USD'],
                'deadline_days' => 32,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-4.png',
            ],
            [
                'title' => 'Kỹ sư AI Machine Learning',
                'slug' => 'ky-su-ai-machine-learning',
                'description' => 'Xây dựng mô hình machine learning, fine-tune hệ thống AI và triển khai vào sản phẩm thực tế.',
                'salary_range' => ['min' => 1800, 'max' => 3200, 'currency' => 'USD'],
                'deadline_days' => 40,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-2.png',
            ],
            [
                'title' => 'Chuyên viên SEO Content',
                'slug' => 'chuyen-vien-seo-content',
                'description' => 'Lên kế hoạch nội dung chuẩn SEO, nghiên cứu từ khóa và tối ưu hiệu suất organic traffic.',
                'salary_range' => ['min' => 650, 'max' => 1100, 'currency' => 'USD'],
                'deadline_days' => 15,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-1.png',
            ],
            [
                'title' => 'Digital Marketing Executive',
                'slug' => 'digital-marketing-executive',
                'description' => 'Triển khai chiến dịch quảng cáo đa kênh, tối ưu ngân sách và đo lường hiệu quả chuyển đổi.',
                'salary_range' => ['min' => 700, 'max' => 1300, 'currency' => 'USD'],
                'deadline_days' => 16,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-3.png',
            ],
            [
                'title' => 'Kế toán tổng hợp',
                'slug' => 'ke-toan-tong-hop',
                'description' => 'Theo dõi sổ sách kế toán, lập báo cáo tài chính và phối hợp xử lý nghiệp vụ thuế định kỳ.',
                'salary_range' => ['min' => 650, 'max' => 1200, 'currency' => 'USD'],
                'deadline_days' => 21,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-4.png',
            ],
            [
                'title' => 'Nhân viên Hành chính Nhân sự',
                'slug' => 'nhan-vien-hanh-chinh-nhan-su',
                'description' => 'Phụ trách hồ sơ nhân sự, công tác hành chính văn phòng và hỗ trợ các hoạt động tuyển dụng nội bộ.',
                'salary_range' => ['min' => 500, 'max' => 900, 'currency' => 'USD'],
                'deadline_days' => 19,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-2.png',
            ],
            [
                'title' => 'Talent Acquisition Specialist',
                'slug' => 'talent-acquisition-specialist',
                'description' => 'Tìm kiếm ứng viên, xây dựng pipeline tuyển dụng và phối hợp với các phòng ban để tuyển đúng người.',
                'salary_range' => ['min' => 800, 'max' => 1400, 'currency' => 'USD'],
                'deadline_days' => 27,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-3.png',
            ],
            [
                'title' => 'Project Manager',
                'slug' => 'project-manager',
                'description' => 'Quản lý tiến độ dự án, phối hợp liên phòng ban và đảm bảo chất lượng đầu ra đúng cam kết.',
                'salary_range' => ['min' => 1600, 'max' => 2800, 'currency' => 'USD'],
                'deadline_days' => 26,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-1.png',
            ],
            [
                'title' => 'Product Owner',
                'slug' => 'product-owner',
                'description' => 'Định hướng roadmap sản phẩm, ưu tiên backlog và làm việc với team phát triển để tối đa giá trị sản phẩm.',
                'salary_range' => ['min' => 1700, 'max' => 2900, 'currency' => 'USD'],
                'deadline_days' => 29,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-4.png',
            ],
            [
                'title' => 'Chuyên viên Chăm sóc khách hàng',
                'slug' => 'chuyen-vien-cham-soc-khach-hang',
                'description' => 'Tiếp nhận phản hồi, hỗ trợ khách hàng đa kênh và duy trì mức độ hài lòng cao cho người dùng.',
                'salary_range' => ['min' => 450, 'max' => 850, 'currency' => 'USD'],
                'deadline_days' => 14,
                'positions_count' => 3,
                'thumbnail' => 'assets/img/company-logo-2.png',
            ],
            [
                'title' => 'Nhân viên Kinh doanh B2B',
                'slug' => 'nhan-vien-kinh-doanh-b2b',
                'description' => 'Tìm kiếm khách hàng doanh nghiệp, tư vấn giải pháp và chốt hợp đồng theo mục tiêu doanh số.',
                'salary_range' => ['min' => 700, 'max' => 1500, 'currency' => 'USD'],
                'deadline_days' => 17,
                'positions_count' => 4,
                'thumbnail' => 'assets/img/company-logo-3.png',
            ],
            [
                'title' => 'Kỹ sư Hệ thống',
                'slug' => 'ky-su-he-thong',
                'description' => 'Quản trị máy chủ, hệ thống mạng nội bộ và đảm bảo tính ổn định cho hạ tầng công nghệ thông tin.',
                'salary_range' => ['min' => 1100, 'max' => 1900, 'currency' => 'USD'],
                'deadline_days' => 23,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-1.png',
            ],
            [
                'title' => 'Mobile Developer Flutter',
                'slug' => 'mobile-developer-flutter',
                'description' => 'Phát triển ứng dụng di động đa nền tảng với Flutter, tối ưu hiệu năng và trải nghiệm người dùng.',
                'salary_range' => ['min' => 1200, 'max' => 2100, 'currency' => 'USD'],
                'deadline_days' => 31,
                'positions_count' => 2,
                'thumbnail' => 'assets/img/company-logo-4.png',
            ],
            [
                'title' => 'Kỹ sư An toàn thông tin',
                'slug' => 'ky-su-an-toan-thong-tin',
                'description' => 'Giám sát bảo mật hệ thống, đánh giá lỗ hổng và xây dựng các biện pháp phòng chống rủi ro an ninh mạng.',
                'salary_range' => ['min' => 1500, 'max' => 2700, 'currency' => 'USD'],
                'deadline_days' => 34,
                'positions_count' => 1,
                'thumbnail' => 'assets/img/company-logo-2.png',
            ],
        ];

        foreach ($jobs as $index => $job) {
            $department = $departments[$index % $departments->count()];
            $workplace = $workplaces[$index % $workplaces->count()];

            RecruitmentJob::query()->updateOrCreate(
                ['slug' => $job['slug']],
                [
                    'title' => $job['title'],
                    'description' => $job['description'],
                    'status' => 'published',
                    'salary_range' => $job['salary_range'],
                    'deadline' => now()->addDays($job['deadline_days'])->toDateString(),
                    'positions_count' => $job['positions_count'],
                    'public_url' => '/jobs/' . $job['slug'],
                    'thumbnail' => $job['thumbnail'],
                    'department_id' => $department->id,
                    'branch_id' => $department->branch_id,
                    'workplace_id' => $workplace->id,
                    'created_by' => $creator->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
