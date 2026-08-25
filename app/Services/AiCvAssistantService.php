<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCvAssistantService
{
    protected ?string $apiKey;
    protected string $apiUrl;
    protected ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $this->apiUrl = config('services.gemini.url', 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent');
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Bóc tách toàn bộ nội dung text của CV cũ thành JSON có cấu trúc để điền vào CV Builder
     */
    public function parseCvToStructuredJson(string $rawText, ?string $pdfPath = null): ?array
    {
        $this->lastError = null;

        if (blank($this->apiKey)) {
            $this->lastError = 'Chưa cấu hình GEMINI_API_KEY trong hệ thống.';
            return null;
        }

        if (blank($rawText) && blank($pdfPath)) {
            $this->lastError = 'Không có dữ liệu text hoặc file PDF để trích xuất.';
            return null;
        }

        $prompt = <<<PROMPT
Bạn là chuyên gia bóc tách dữ liệu CV / Resume thông minh và chi tiết của hệ thống FPT Career.
Nhiệm vụ của bạn là phân tích toàn bộ nội dung CV được cung cấp (văn bản thô và/hoặc file PDF đính kèm) và trích xuất thành dữ liệu JSON chuẩn xác, đầy đủ 100% tất cả các mục.

HƯỚNG DẪN BÓC TÁCH CHI TIẾT TỪNG MỤC:
1. name: Họ và tên ứng viên (ví dụ: "Nguyễn Trọng An").
2. email: Email liên hệ (ví dụ: "trongan456nc@gmail.com").
3. phone: Số điện thoại (ví dụ: "0862420611").
4. profile_title: Chức danh nghề nghiệp / Vị trí chuyên môn (ví dụ: "Lập trình web backend" hoặc "FullStack Developer").
5. date_of_birth: Ngày sinh (ví dụ: "2006-06-21" hoặc "21/06/2006").
6. gender: Giới tính ("Nam", "Nữ", hoặc "Khác").
7. city: Tỉnh / Thành phố (ví dụ: "Cần Thơ").
8. address: Địa chỉ cụ thể (ví dụ: "Ninh Kiều, Cần Thơ").
9. career_objective: Toàn bộ đoạn văn mục tiêu nghề nghiệp hoặc tóm tắt bản thân.
10. experiences: MỤC ĐẶC BIỆT QUAN TRỌNG! Trích xuất TẤT CẢ các kinh nghiệm làm việc HOẶC các "Dự án" (Projects) mà ứng viên đã thực hiện:
    - company: Tên công ty HOẶC Tên dự án (ví dụ: "Website thuê đồ", "Website siêu thị", "Phân tích cơ sở dữ liệu cho hệ thống quản lý khoá học trực tuyến").
    - position: Vị trí / Vai trò (ví dụ: "FullStack Developer (Leader)", "Fullstack Developer (Leader)", "Analytic").
    - from: Thời gian bắt đầu (ví dụ: "12/02/2026", "25/11/2025", "05/08/2025", "2024").
    - to: Thời gian kết thúc (ví dụ: "26/02/2026", "12/12/2025", "26/08/2025", "Hiện tại").
    - description: Toàn bộ các gạch đầu dòng mô tả tính năng, công nghệ, cấu trúc MVC/OOP, cơ sở dữ liệu, tối ưu code...
11. educations: Quá trình học vấn:
    - school: Tên trường học / Cao đẳng / Đại học (ví dụ: "Cao Đẳng FPT Polytechnic").
    - degree: Ngành học / Chuyên ngành (ví dụ: "Ngành Công Nghệ Thông Tin").
    - from: Năm bắt đầu (ví dụ: "2024").
    - to: Năm kết thúc (ví dụ: "2026").
    - description: Toàn bộ thông tin xếp loại, GPA, thành tích (ví dụ: "Xếp loại: Giỏi\nSinh viên giỏi ngành lập trình web Fall 2024\nSinh viên xuất sắc ngành lập trình web Summer 2025").
12. skills: Tách riêng từng kỹ năng thành từng mục (Frontend, Backend, Database, Kỹ năng mềm). Ví dụ:
    [{ "name": "HTML/CSS", "level": "Thành thạo" }, { "name": "JavaScript", "level": "Thành thạo" }, { "name": "Bootstrap", "level": "Thành thạo" }, { "name": "PHP", "level": "Thành thạo" }, { "name": "Laravel", "level": "Thành thạo" }, { "name": "MySQL", "level": "Thành thạo" }, { "name": "Giao tiếp & Ứng xử", "level": "Khá" }, { "name": "Làm việc nhóm", "level": "Khá" }, { "name": "Thuyết trình", "level": "Khá" }]
13. languages: Danh sách ngoại ngữ (ví dụ: [{ "name": "Tiếng Anh", "level": "Cơ bản" }]).
14. certifications: Danh sách chứng chỉ (ví dụ: [{ "name": "Responsive Web Design", "issuer": "freeCodeCamp", "date": "2024", "description": "" }, { "name": "PHP Cơ bản", "issuer": "Udemy", "date": "2025", "description": "" }, { "name": "Git & GitHub Basics", "issuer": "Udemy", "date": "2025", "description": "" }]).
15. achievements: Danh hiệu và giải thưởng (ví dụ: [{ "title": "Bằng khen cho đề tài “Phân tích & thiết kế cơ sở dữ liệu hệ thống E-learning”", "date": "2024", "description": "" }]).
16. activities: Danh sách hoạt động (ví dụ: [{ "title": "Tham gia thiện nguyện của kĩ năng làm việc", "from": "05/11/2024", "to": "", "description": "" }, { "title": "Tham dự Talkshow Code Style trong phát triển phần mềm Cao đẳng FPT Polytechnic Cần Thơ", "from": "20/03/2025", "to": "", "description": "" }, { "title": "Tham dự Talkshow ứng dụng AI vào việc học - Cao đẳng FPT Polytechnic Cần Thơ", "from": "22/01/2026", "to": "", "description": "" }]).
17. references: Người tham chiếu (ví dụ: [{ "name": "Phan Văn Tính", "title": "Giảng viên bộ môn Công nghệ thông tin", "email": "tinhpv10.fpl@gmail.com", "phone": "" }]).

Nội dung CV văn bản thô:
{$rawText}
PROMPT;

        $parts = [['text' => $prompt]];

        if (!blank($pdfPath) && file_exists($pdfPath)) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'application/pdf',
                    'data' => base64_encode(file_get_contents($pdfPath))
                ]
            ];
        }

        try {
            $response = Http::timeout(90)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [['parts' => $parts]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'response_mime_type' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                $this->lastError = 'Lỗi gọi AI: ' . $response->status() . ' - ' . $response->body();
                Log::error('AI Parse CV Failed: ' . $response->body());
                return null;
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            $data = json_decode((string) $content, true);

            if (is_array($data)) {
                return $this->normalizeParsedCv($data);
            }

            return null;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error('AI Parse CV Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Chuẩn hóa dữ liệu JSON sau khi AI trích xuất để tương thích 100% với CV Builder
     */
    public function normalizeParsedCv(array $data): array
    {
        $res = [];
        $res['name'] = !empty($data['name']) ? (string)$data['name'] : null;
        $res['email'] = !empty($data['email']) ? (string)$data['email'] : null;
        $res['phone'] = !empty($data['phone']) ? (string)$data['phone'] : null;
        $res['profile_title'] = !empty($data['profile_title']) ? (string)$data['profile_title'] : null;
        $res['date_of_birth'] = !empty($data['date_of_birth']) ? (string)$data['date_of_birth'] : null;
        $res['gender'] = !empty($data['gender']) ? (string)$data['gender'] : null;
        $res['city'] = !empty($data['city']) ? (string)$data['city'] : null;
        $res['address'] = !empty($data['address']) ? (string)$data['address'] : null;
        $res['career_objective'] = !empty($data['career_objective']) ? (string)$data['career_objective'] : null;

        // Experiences / Projects
        $res['experiences'] = [];
        $rawExp = $data['experiences'] ?? ($data['projects'] ?? []);
        if (is_array($rawExp)) {
            foreach ($rawExp as $exp) {
                if (is_string($exp) && trim($exp) !== '') {
                    $res['experiences'][] = ['company' => trim($exp), 'position' => '', 'from' => '', 'to' => '', 'description' => ''];
                } elseif (is_array($exp)) {
                    $company = $exp['company'] ?? ($exp['project_name'] ?? ($exp['title'] ?? ''));
                    $position = $exp['position'] ?? ($exp['role'] ?? '');
                    $from = $exp['from'] ?? '';
                    $to = $exp['to'] ?? '';
                    if (empty($from) && !empty($exp['time'])) {
                        $parts = explode('-', $exp['time']);
                        $from = trim($parts[0] ?? '');
                        $to = trim($parts[1] ?? '');
                    }
                    $desc = $exp['description'] ?? '';
                    if (is_array($desc)) {
                        $desc = implode("\n", array_map(fn($d) => str_starts_with(trim($d), '•') ? trim($d) : '• ' . trim($d), $desc));
                    }
                    if (!empty($company) || !empty($position) || !empty($desc)) {
                        $res['experiences'][] = [
                            'company' => (string)$company,
                            'position' => (string)$position,
                            'from' => (string)$from,
                            'to' => (string)$to,
                            'description' => (string)$desc,
                        ];
                    }
                }
            }
        }

        // Educations
        $res['educations'] = [];
        $rawEdu = $data['educations'] ?? [];
        if (is_array($rawEdu)) {
            foreach ($rawEdu as $edu) {
                if (is_array($edu)) {
                    $school = $edu['school'] ?? ($edu['university'] ?? '');
                    $degree = $edu['degree'] ?? ($edu['major'] ?? '');
                    $from = $edu['from'] ?? '';
                    $to = $edu['to'] ?? '';
                    if (empty($from) && !empty($edu['time'])) {
                        $parts = explode('-', $edu['time']);
                        $from = trim($parts[0] ?? '');
                        $to = trim($parts[1] ?? '');
                    }
                    $desc = $edu['description'] ?? '';
                    if (is_array($desc)) {
                        $desc = implode("\n", array_map(fn($d) => '• ' . trim($d), $desc));
                    }
                    if (!empty($school) || !empty($degree)) {
                        $res['educations'][] = [
                            'school' => (string)$school,
                            'degree' => (string)$degree,
                            'from' => (string)$from,
                            'to' => (string)$to,
                            'description' => (string)$desc,
                        ];
                    }
                }
            }
        }

        // Skills
        $res['skills'] = [];
        $rawSkills = $data['skills'] ?? [];
        if (is_array($rawSkills)) {
            foreach ($rawSkills as $s) {
                if (is_string($s) && trim($s) !== '') {
                    $res['skills'][] = ['name' => trim($s), 'level' => 'Thành thạo'];
                } elseif (is_array($s) && !empty($s['name'])) {
                    $res['skills'][] = [
                        'name' => (string)$s['name'],
                        'level' => (string)($s['level'] ?? 'Thành thạo'),
                    ];
                }
            }
        }

        // Languages
        $res['languages'] = [];
        $rawLang = $data['languages'] ?? [];
        if (is_array($rawLang)) {
            foreach ($rawLang as $l) {
                if (is_string($l) && trim($l) !== '') {
                    $res['languages'][] = ['name' => trim($l), 'level' => 'Cơ bản'];
                } elseif (is_array($l) && !empty($l['name'])) {
                    $res['languages'][] = [
                        'name' => (string)$l['name'],
                        'level' => (string)($l['level'] ?? 'Giao tiếp'),
                    ];
                }
            }
        }

        // Certifications
        $res['certifications'] = [];
        $rawCert = $data['certifications'] ?? [];
        if (is_array($rawCert)) {
            foreach ($rawCert as $c) {
                if (is_string($c) && trim($c) !== '') {
                    $res['certifications'][] = ['name' => trim($c), 'issuer' => '', 'date' => '', 'description' => ''];
                } elseif (is_array($c) && !empty($c['name'])) {
                    $res['certifications'][] = [
                        'name' => (string)$c['name'],
                        'issuer' => (string)($c['issuer'] ?? ''),
                        'date' => (string)($c['date'] ?? ''),
                        'description' => (string)($c['description'] ?? ''),
                    ];
                }
            }
        }

        // Achievements
        $res['achievements'] = [];
        $rawAch = $data['achievements'] ?? [];
        if (is_string($rawAch) && trim($rawAch) !== '') {
            $res['achievements'][] = ['title' => trim($rawAch), 'date' => '', 'description' => ''];
        } elseif (is_array($rawAch)) {
            foreach ($rawAch as $a) {
                if (is_string($a) && trim($a) !== '') {
                    $res['achievements'][] = ['title' => trim($a), 'date' => '', 'description' => ''];
                } elseif (is_array($a) && !empty($a['title'])) {
                    $res['achievements'][] = [
                        'title' => (string)$a['title'],
                        'date' => (string)($a['date'] ?? ''),
                        'description' => (string)($a['description'] ?? ''),
                    ];
                }
            }
        }

        // Activities
        $res['activities'] = [];
        $rawAct = $data['activities'] ?? [];
        if (is_array($rawAct)) {
            foreach ($rawAct as $act) {
                if (is_string($act) && trim($act) !== '') {
                    $res['activities'][] = ['title' => trim($act), 'from' => '', 'to' => '', 'description' => ''];
                } elseif (is_array($act) && !empty($act['title'])) {
                    $res['activities'][] = [
                        'title' => (string)$act['title'],
                        'from' => (string)($act['from'] ?? ''),
                        'to' => (string)($act['to'] ?? ''),
                        'description' => (string)($act['description'] ?? ''),
                    ];
                }
            }
        }

        // References
        $res['references'] = [];
        $rawRef = $data['references'] ?? [];
        if (is_array($rawRef)) {
            foreach ($rawRef as $r) {
                if (is_array($r) && !empty($r['name'])) {
                    $res['references'][] = [
                        'name' => (string)$r['name'],
                        'title' => (string)($r['title'] ?? ''),
                        'email' => (string)($r['email'] ?? ''),
                        'phone' => (string)($r['phone'] ?? ''),
                    ];
                }
            }
        }

        return $res;
    }

    /**
     * AI Co-pilot: Gợi ý mục tiêu nghề nghiệp theo chức danh và kỹ năng
     */
    public function generateObjective(string $position, ?string $level = 'Junior/Mid-level', array $skills = []): ?string
    {
        $this->lastError = null;

        if (blank($this->apiKey)) {
            $this->lastError = 'Chưa cấu hình GEMINI_API_KEY.';
            return null;
        }

        $skillList = implode(', ', array_filter($skills));

        $prompt = <<<PROMPT
Bạn là chuyên gia tư vấn viết CV chuyên nghiệp. Hãy viết 1 đoạn văn mục tiêu nghề nghiệp (Career Objective / Professional Summary) ngắn gọn, súc tích (khoảng 3-4 câu, dưới 100 từ) bằng tiếng Việt cho ứng viên:
- Vị trí mong muốn: {$position}
- Cấp bậc: {$level}
- Kỹ năng thế mạnh: {$skillList}

Đoạn văn cần:
1. Thể hiện năng lực cốt lõi và kinh nghiệm nổi bật.
2. Nêu rõ giá trị sẽ mang lại cho tổ chức/doanh nghiệp.
3. Định hướng phát triển nghề nghiệp rõ ràng.
Chỉ trả về trực tiếp đoạn văn mục tiêu nghề nghiệp, không thêm lời dẫn, không dấu ngoặc kép.
PROMPT;

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.7,
                ],
            ]);

            if ($response->failed()) {
                $this->lastError = 'Lỗi kết nối AI.';
                return null;
            }

            $text = trim((string) $response->json('candidates.0.content.parts.0.text'));
            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * AI Co-pilot: Tối ưu mô tả công việc (chuẩn STAR, định lượng kết quả)
     */
    public function enhanceExperienceDescription(string $draftText, string $position = ''): ?string
    {
        $this->lastError = null;

        if (blank($this->apiKey)) {
            $this->lastError = 'Chưa cấu hình GEMINI_API_KEY.';
            return null;
        }

        if (blank($draftText)) {
            $this->lastError = 'Vui lòng nhập vài gạch đầu dòng mô tả công việc trước khi nhờ AI tối ưu.';
            return null;
        }

        $prompt = <<<PROMPT
Bạn là chuyên gia tối ưu CV chuẩn quốc tế. Hãy viết lại phần mô tả công việc dưới đây thành 3 - 5 gạch đầu dòng (bullet points) cực kỳ ấn tượng, chuyên nghiệp theo mô hình STAR (Situation - Task - Action - Result).
- Vị trí công việc: {$position}
- Nội dung gốc của ứng viên:
{$draftText}

Quy tắc tối ưu:
- Dùng các động từ hành động mạnh (Chủ trì, Phát triển, Triển khai, Tối ưu, Phối hợp, Xây dựng...).
- Tự động bổ sung các số liệu giả định hợp lý hoặc khung đo lường (ví dụ: tăng 20% hiệu suất, xử lý 10.000 request/ngày, giảm 30% thời gian...) nếu nội dung gốc chưa có.
- Trình bày dạng danh sách gạch đầu dòng "-", mỗi dòng 1 ý hoàn chỉnh.
- Chỉ trả về danh sách các gạch đầu dòng đã tối ưu bằng tiếng Việt, không kèm lời dẫn.
PROMPT;

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.6,
                ],
            ]);

            if ($response->failed()) {
                $this->lastError = 'Lỗi kết nối AI.';
                return null;
            }

            $text = trim((string) $response->json('candidates.0.content.parts.0.text'));
            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }
}
