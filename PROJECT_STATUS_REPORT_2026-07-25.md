# Báo cáo kiểm thử và tiến độ dự án

**Thời điểm báo cáo:** 25/07/2026  
**Nhánh kiểm tra:** `haolc`  
**Commit nền:** `aa2e62b`  
**Nền tảng:** Laravel 13.1.1, PHP 8.3.30, Livewire, Filament, Vite

## 1. Kết luận điều hành

Dự án hiện ở giai đoạn **UAT / Release Candidate**: các luồng nghiệp vụ tuyển dụng cốt lõi đã hoạt động và có độ phủ kiểm thử tự động tốt. Hệ thống chưa nên được xác nhận production ngay cho đến khi hoàn tất kiểm thử trình duyệt end-to-end và quy trình CI.

**Mức đánh giá hiện tại**

| Hạng mục | Trạng thái | Nhận định |
|---|---|---|
| Nghiệp vụ tuyển dụng cốt lõi | Đạt | Auth, hồ sơ, JD, apply, pipeline, interview, offer và notification đã có test |
| Phân quyền và giới hạn chi nhánh | Đạt | Candidate, HR, PM, Director, Admin được kiểm tra theo vai trò |
| Snapshot hồ sơ khi ứng tuyển | Đạt | Dữ liệu hồ sơ/CV tại thời điểm apply không bị ghi đè ngoài lựa chọn đồng bộ rõ ràng |
| Chatbot AI | Đạt | Intent nội bộ, phân quyền và lỗi provider đã có test; hội thoại không được ghi xuống DB |
| Frontend build | Đạt | Vite build production thành công |
| Database migration | Đạt sau điều chỉnh | Migration tạo bảng chatbot đã được loại bỏ |
| E2E, CI và vận hành | Chưa hoàn tất | Chưa có browser E2E và workflow CI trong repository |

## 2. Kết quả kiểm thử ngày 25/07/2026

### Kiểm thử tự động

- Lệnh: `php artisan test`
- Kết quả cuối: **165 test passed**
- Assertions: **686**
- Thời gian: **69.41 giây**
- Không còn test failed sau khi sửa lỗi truy vấn AI.

### Kiểm tra build và mã nguồn

- `php artisan view:cache`: thành công.
- `npm run build` tương đương Vite production build: thành công, 57 modules được xử lý.
- `vendor/bin/pint --dirty`: đạt.
- `git diff --check`: đạt, không có lỗi whitespace.
- `php artisan migrate --pretend`: chạy được và hiển thị đúng SQL migration pending.

### Lỗi phát hiện và đã sửa

Hai test context AI cho Employer và Director từng thất bại trên SQLite vì truy vấn dùng `HAVING applications_count` trên non-aggregate query. Truy vấn đã được chuyển sang điều kiện quan hệ Eloquent `has('applications', '<=', 1)`, vẫn giữ `withCount` để sắp xếp và hiển thị số hồ sơ.

File sửa: `app/Services/AiChatContextService.php`

## 3. Phạm vi chức năng đã xác nhận

### Ứng viên

- Đăng ký, đăng nhập, quên và đặt lại mật khẩu.
- Kích hoạt tài khoản ứng viên cho người dùng đa vai trò.
- Cập nhật hồ sơ, avatar, thông tin liên hệ và CV.
- Tìm kiếm việc làm theo địa điểm, phòng ban và mức phù hợp.
- Ứng tuyển với CV; guest apply được liên kết theo email.
- Không apply trùng cùng công việc.
- Snapshot hồ sơ ứng tuyển được giữ độc lập với thay đổi hồ sơ sau này.
- Chỉ đồng bộ dữ liệu apply về hồ sơ khi ứng viên chủ động chọn.
- Xem, quản lý và rút hồ sơ thuộc quyền sở hữu.
- Nhận và phản hồi offer bằng signed link.
- Chatbot hỗ trợ trạng thái hồ sơ, lịch phỏng vấn, offer và gợi ý việc làm.

### HR, PM và Director

- Tạo, sửa và quản lý tin tuyển dụng theo chi nhánh.
- Tạo nháp JD, đánh giá chất lượng và cải thiện JD bằng AI.
- Lưu JD theo cấu trúc nội dung.
- Xem ứng viên đúng phạm vi chi nhánh.
- Chuyển trạng thái pipeline theo đúng bước hợp lệ.
- Lên lịch phỏng vấn, đánh giá, tạo offer hoặc từ chối.
- Director nhận briefing KPI, điểm nghẽn, workload và hạng mục chờ duyệt.
- Chatbot chỉ đọc dữ liệu trong phạm vi vai trò/chi nhánh.

### Admin và hệ thống

- Phân quyền tài nguyên theo vai trò tuyển dụng.
- Quản lý notification theo đúng người nhận.
- Ghi lịch sử thay đổi trạng thái application.
- Không gửi email dư thừa cho các thay đổi trạng thái nội bộ.
- Dashboard và danh sách sử dụng snapshot ứng viên đúng thời điểm apply.

## 4. Tình trạng dữ liệu hồ sơ và CV

Luồng apply hiện bảo vệ snapshot:

1. Application lưu thông tin ứng viên tại thời điểm nộp.
2. Giao diện pipeline và trang review ưu tiên snapshot/attachment của application.
3. Thay đổi hồ sơ ứng viên sau đó không ghi đè application đã nộp.
4. Apply trùng không được phép ghi đè snapshot cũ.
5. Dữ liệu apply chỉ cập nhật ngược về hồ sơ khi ứng viên chọn đồng bộ.

Các hành vi này đã được xác nhận bởi `ApplyJobFlowTest` và `ApplicationSnapshotDisplayTest`.

## 5. Điểm cần quyết định trước production

### Đã xử lý — Chính sách lưu lịch sử chatbot

Migration tạo `ai_chat_sessions` và `ai_chat_messages` đã được loại bỏ sau khi MySQL báo ổ đĩa đầy. Chatbox hiện giữ tối đa **30 message trong Livewire state**, không tạo bảng riêng và không làm database tăng theo lịch sử trò chuyện. Khi tải lại trang hoặc tạo component mới, lịch sử chat cũ không được khôi phục.

Sự cố migration không tạo được bảng nào và không được ghi vào bảng `migrations`. Nguyên nhân vận hành được xác nhận là ổ `C:` hết dung lượng, trong khi ổ `D:` vẫn còn dung lượng trống. Cần tiếp tục giải phóng ổ `C:` trước khi chạy MySQL lâu dài.

### P1 — Kiểm thử AI provider thực tế

Automated test đang mock Gemini để ổn định và tránh phát sinh chi phí. Cần smoke test riêng trên staging với `GEMINI_API_KEY`, kiểm tra timeout, quota, payload lỗi và nội dung tiếng Việt.

### P2 — Thiếu browser E2E

Chưa có test trình duyệt tự động cho các luồng nhiều bước:

- Candidate đăng nhập → cập nhật CV → tìm việc → apply.
- HR nhận hồ sơ → screening → interview → offer.
- Director xem KPI → duyệt → kiểm tra giới hạn chi nhánh.
- Chatbox trên desktop/mobile, đóng/mở và cuộn tin nhắn.

### P2 — Thiếu CI

Repository chưa có workflow trong `.github/workflows`. Mỗi pull request chưa tự động chạy test, formatter và frontend build.

### P2 — Tài liệu dự án chưa đồng bộ

`README.md` chủ yếu còn nội dung mặc định Laravel và `TODO.md` chỉ ghi một lỗi Blade cũ đã hoàn tất. Cần cập nhật setup, vai trò, biến môi trường, quy trình release và sơ đồ nghiệp vụ.

## 6. Checklist UAT thủ công cho nhóm

### Candidate

- [ ] Đăng ký tài khoản mới và đăng nhập.
- [ ] Tải PDF CV, cập nhật hồ sơ và kiểm tra phần trăm hoàn thiện.
- [ ] Tìm việc theo địa điểm/phòng ban.
- [ ] Apply một job và xác nhận không apply trùng.
- [ ] Sửa hồ sơ sau apply và xác nhận snapshot application không đổi.
- [ ] Xem lịch phỏng vấn, offer và phản hồi signed link.
- [ ] Hỏi chatbot về trạng thái hồ sơ và công việc phù hợp.

### HR

- [ ] Tạo JD thường và JD bằng AI.
- [ ] Xác nhận không xem/thao tác hồ sơ ngoài chi nhánh.
- [ ] Chuyển hồ sơ qua screening, interview và offer.
- [ ] Kiểm tra notification và email từ chối.
- [ ] Hỏi chatbot về hồ sơ tồn, lịch phỏng vấn và tin ít hồ sơ.

### Director/Admin

- [ ] Kiểm tra dashboard KPI và workload.
- [ ] Kiểm tra hạng mục chờ duyệt và offer.
- [ ] Xác nhận Director chỉ thấy chi nhánh được phân công.
- [ ] Kiểm tra quyền Admin trên application và tài nguyên quản trị.

### Giao diện và vận hành

- [ ] Kiểm tra Chrome/Edge ở desktop.
- [ ] Kiểm tra mobile 375px và tablet 768px.
- [ ] Kiểm tra upload CV dung lượng lớn và file sai định dạng.
- [ ] Kiểm tra AI khi thiếu key, timeout và hết quota.
- [ ] Chạy backup/restore thử trên bản sao database trước migration production.

## 7. Kế hoạch đề xuất

| Giai đoạn | Công việc | Tiêu chí hoàn thành |
|---|---|---|
| UAT hardening | Test provider staging và kiểm tra fallback | AI lỗi có fallback và không ghi lịch sử xuống DB |
| Release automation | Thêm CI cho test, Pint và Vite build | PR không thể merge khi kiểm tra thất bại |
| Browser verification | Bổ sung E2E cho Candidate, HR và Director | Các happy path chạy tự động trên trình duyệt |
| Documentation | Viết lại README và runbook release | Thành viên mới setup và deploy theo tài liệu |
| Production readiness | Backup rehearsal, migrate staging, smoke test | Có sign-off của nghiệp vụ và kỹ thuật |

## 8. Lệnh kiểm tra chuẩn trước khi bàn giao

```bash
php artisan test
vendor/bin/pint --test
php artisan view:cache
php artisan migrate:status
php artisan migrate --pretend
npm run build
git diff --check
```

## 9. Kết luận

Core tuyển dụng đã đủ ổn định để nhóm bắt đầu UAT có kiểm soát. Điểm chặn production lớn nhất không nằm ở luồng apply/pipeline hay persistence chatbot mà nằm ở thiếu E2E, thiếu CI và chưa smoke test AI provider trên staging. Sau khi hoàn tất các hạng mục này, dự án có thể chuyển sang bước staging sign-off và chuẩn bị phát hành production.
