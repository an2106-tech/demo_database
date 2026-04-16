const xlsx = require('xlsx');
const path = require('path');

const inputPath  = path.resolve('d:\\demo_database\\Bản sao của Workshop4.xlsx');
const outputPath = path.resolve('d:\\demo_database\\Ws4_DailyMeeting_Updated.xlsx');

const wb = xlsx.readFile(inputPath);

// ── helpers ──────────────────────────────────────────────────────────────────
function cell(v, style) {
  return { v, t: typeof v === 'number' ? 'n' : 's', s: style };
}

const DAYS = [
  'Monday (2)', 'Tuesday (3)', 'Wednesday (4)', 'Thursday (5)',
  'Friday (6)', 'Saturday (7)', 'Sunday (8)', 'Monday (9)', 'Tuesday (10)'
];

// ── member data (from JSON export) ──────────────────────────────────────────
const members = [
  {
    name: 'Mai Anh Tú',
    yesterday: [
      'Cập nhật cấu trúc bảng dữ liệu mới.',
      'Đã hoàn thành bộ lọc (Filter) trang việc làm.',
      'Đã xong các phương thức xử lý logic CRUD Admin.',
      'Đã xử lý xong các lỗi PSR-4 và chuẩn PSR-3.',
      'Đã hoàn thành test chức năng duyệt hồ sơ.',
      'Đã fix lỗi xung đột (Conflict) Footer hệ thống.',
      'Đã soát lỗi logic trên toàn bộ trang Quản trị.',
      'Đã hoàn tất việc đóng gói mã nguồn tuần cũ.',
      'Đã xong các phương thức xử lý Offer (mẫu mời làm việc).',
    ],
    today: [
      'Thực hiện xử lý logic gửi Mail cho ứng viên.',
      'Xử lý logic cho phần duyệt tin tuyển dụng.',
      'Test chức năng và kiểm tra lại validate Form.',
      'Tối ưu các phương thức xử lý dữ liệu bảng.',
      'Test chức năng phân quyền (Filament Shield).',
      'Kiểm tra tính bảo mật của các phương thức xử lý.',
      'Kiểm tra lại lần cuối và đóng gói mã nguồn.',
      'Thực hiện xử lý logic tạo file PDF Offer.',
      'Test chức năng gửi mail tự động cho ứng viên trúng tuyển.',
    ],
    obstacles: [
      'Lỗi khi cấu hình Mail Server (Gmail Pass).',
      'Logic hiển thị trạng thái duyệt tin còn bị sai.',
      'Test chức năng phát hiện lỗi khi bỏ trống ô.',
      'Các lỗi PSR-4 gây gián đoạn khi chạy hệ thống.',
      'Chia role bằng Shield đôi khi bị mất quyền Admin.',
      'Phát sinh lỗi khi nhiều người cùng thao tác.',
      'Áp lực thời gian để tổng hợp báo cáo.',
      'Lỗi định dạng khi xuất file PDF từ dữ liệu hệ thống.',
      'Email gửi đi đôi khi bị rơi vào hòm thư rác (Spam).',
    ],
  },
  {
    name: 'Lê Quang Đồng',
    yesterday: [
      'Cập nhật lại màu sắc chuẩn FPT Poly.',
      'Đã xong khung code giao diện Employer mới.',
      'Đã tích hợp hình ảnh vào các Category.',
      'Đã thành công xử lý logic vào giao diện.',
      'Đã sửa các lỗi vỡ khung trong code giao diện.',
      'Đã hoàn thiện các hiệu ứng hover cho nút.',
      'Đã kiểm tra hiển thị trên thiết bị di động.',
      'Đã sửa xong các lỗi vỡ khung trang chủ.',
      'Đã hoàn thiện giao diện các nút chức năng mới.',
    ],
    today: [
      'Code giao diện chi tiết trang Nhà tuyển dụng.',
      'Cắt giao diện và chỉnh sửa các trang con.',
      'Thực hiện phương thức xử lý hiển thị dữ liệu.',
      'Test chức năng giao diện trang danh sách việc.',
      'Chỉnh sửa code giao diện (Sửa lỗi Header nhảy).',
      'Test chức năng hiển thị Category ngoài Client.',
      'Hoàn thiện toàn bộ các trang của Employer.',
      'Code giao diện bảng điều khiển (Dashboard) cho HR.',
      'Test chức năng lọc địa chỉ làm việc ngay trên giao diện.',
    ],
    obstacles: [
      'Code giao diện chưa khớp với các giao diện khác.',
      'Tìm hình ảnh minh họa cho các danh mục khó.',
      'Xử lý logic hiển thị dữ liệu (Render) bị lỗi.',
      'Test chức năng thấy trang chủ load chậm.',
      'Một số phần code giao diện bị lệch trên Mobile.',
      'Lỗi hiển thị hình ảnh.',
      'Các nút bấm đôi khi không phản hồi.',
      'Code giao diện phần biểu đồ hiển thị chưa cân đối.',
      'Lỗi dữ liệu không thay đổi khi chọn bộ lọc địa chỉ.',
    ],
  },
  {
    name: 'Đặng Thị Kim Anh',
    yesterday: [
      'Cập nhật các ô nhập liệu (Input) mới.',
      'Đã xong các form nhập liệu cho ứng viên.',
      'Đã hoàn thành các phương thức xử lý và hiển thị thông báo lỗi trên giao diện người dùng.',
      'Đã hoàn thiện giao diện cho các trang Admin.',
      'Đã hoàn thành test chức năng đăng ký/đăng nhập.',
      'Đã chỉnh sửa giao diện theo kết quả test.',
      'Đã Việt hóa toàn bộ các trang phía Client.',
      'Hoàn thành user icon cho giao diện Client.',
      'Đã xong giao diện Modal đăng ký cho ứng viên và nhà tuyển dụng.',
    ],
    today: [
      'Code giao diện modal đăng ký/đăng nhập.',
      'Cắt giao diện và làm trang danh sách ứng viên.',
      'Thực hiện phương thức xử lý để hiển thị modal trên giao diện người dùng.',
      'Test chức năng nộp hồ sơ của ứng viên.',
      'Xây dựng giao diện chức năng thay đổi mật khẩu (trường nhập mật khẩu hiện tại, mới và xác nhận).',
      'Test chức năng và kiểm tra các liên kết (Link).',
      'Tạo user icon thông tin tài khoản giao diện Client.',
      'Code giao diện Modal đăng ký cho ứng viên và doanh nghiệp.',
      'Test chức năng đăng ký tài khoản qua các (Modal).',
    ],
    obstacles: [
      'Chia role hiển thị menu bị chồng chéo.',
      'Code giao diện bộ lọc tìm kiếm nhìn bị rối.',
      'Phương thức xử lý chưa chặn được ký tự lạ.',
      'Test chức năng thấy thông báo hiện sai chỗ.',
      'Code giao diện trang báo lỗi nhìn còn đơn giản.',
      'Báo lỗi khi chuyển Link và 1 số không chuyển được.',
      'CSS giao diện còn gặp lỗi cấu trúc.',
      'Code giao diện Modal bị lỗi không tự đóng sau khi lưu.',
      'Lỗi hiển thị thông báo lỗi ngay trên Modal đăng ký.',
    ],
  },
  {
    name: 'Nguyễn Trọng An',
    yesterday: [
      'Cập nhật Database cho các chi nhánh (Branch).',
      'Đã thực hiện xử lý logic phân quyền người dùng.',
      'Đã xong các phương thức xử lý login/logout.',
      'Đã xong các xử lý logic thống kê Dashboard.',
      'Đã hoàn thành test chức năng phân quyền.',
      'Đã tối ưu các câu lệnh xử lý logic database.',
      'Đã sao lưu dữ liệu (Upload CSDL lên Git).',
      'Đã cập nhật xong dữ liệu các chi nhánh mới.',
      'Đã xử lý xong phân quyền cho cấp bậc quản lý mới.',
    ],
    today: [
      'Thực hiện xử lý logic phân quyền người dùng.',
      'Thực hiện phương thức xử lý login/logout.',
      'Code các phương thức xử lý logic cho Admin.',
      'Test chức năng hiển thị biểu đồ thống kê.',
      'Thực hiện logic phân quyền bằng Shield.',
      'Test chức năng chịu tải khi dữ liệu lớn.',
      'Kiểm tra tính chính xác của dữ liệu cuối tuần.',
      'Thực hiện phương thức xử lý phân quyền cho từng chi nhánh.',
      'Test chức năng lọc ứng viên theo từng chi nhánh cụ thể.',
    ],
    obstacles: [
      'Phát sinh xung đột khi đồng bộ mã nguồn (khác biệt local vs máy chung).',
      'Xử lý phương thức login/logout chưa lấy được dữ liệu người dùng.',
      'Các phương thức xử lý báo cáo bị sai số.',
      'Test chức năng biểu đồ không nhận dữ liệu.',
      'Việc chia role gặp lỗi xung đột Middleware.',
      'Phát hiện lỗ hổng bảo mật khi chạy test.',
      'Lỗi khi chuyển dữ liệu báo cáo sang Excel.',
      'Xử lý logic phân quyền chi nhánh bị xung đột dữ liệu.',
      'Truy vấn dữ liệu từ nhiều chi nhánh tốn nhiều bộ nhớ.',
    ],
  },
  {
    name: 'Lâm Chí Hào',
    yesterday: [
      'Chỉnh sửa giao diện chi tiết bài viết.',
      'Đã xong giao diện chi tiết công việc.',
      'Đã xong giao diện và hoàn thiện trang cá nhân.',
      'Đã xong phần hiển thị hồ sơ cá nhân (Profile).',
      'Đã hoàn thành test chức năng chỉnh sửa hồ sơ.',
      'Đã sửa xong lỗi giao diện Header và Footer.',
      'Đã kiểm tra lại toàn bộ trải nghiệm người dùng.',
      'Đã sửa xong các lỗi Icon hiển thị sai.',
      'Đã hoàn thiện giao diện trang thông tin công ty.',
    ],
    today: [
      'Code giao diện trang tin chi tiết công việc.',
      'Cắt giao diện và hoàn thiện trang cá nhân.',
      'Thực hiện phương thức xử lý cập nhật Avatar.',
      'Test chức năng và sửa lỗi link các trang con.',
      'Tối ưu hóa các phương thức xử lý chuyển trang.',
      'Test chức năng xem lịch phỏng vấn Admin.',
      'Kiểm tra lại toàn bộ code giao diện lần cuối.',
      'Code giao diện phần thêm chi tiết ứng viên cho HR.',
      'Test chức năng hiển thị ảnh đại diện (Avatar) của ứng viên.',
    ],
    obstacles: [
      'Code giao diện bị lỗi khi hiển thị bài viết dài.',
      'Tìm ảnh minh họa phù hợp cho About và Home còn khó khăn.',
      'Xử lý logic tải file bị treo khi file quá lớn.',
      'Test chức năng phát hiện link layout bị sai.',
      'Code giao diện bị tràn khi xem trên máy tính bảng.',
      'Các phương thức xử lý đôi khi phản hồi chậm.',
      'Một vài icon bị lỗi hiển thị khi chạy thực tế.',
      'Code giao diện phần thêm thông tin bị tràn màn hình.',
      'Ảnh đại diện của người dùng không hiển thị đúng kích thước.',
    ],
  },
];

// ── build worksheet data (2-D array) ─────────────────────────────────────────
const rows = [];

// Header row
rows.push(['Name / Task', 'Nội dung', ...DAYS]);

// Member rows
for (const m of members) {
  rows.push([m.name, 'What did you do yesterday?', ...m.yesterday]);
  rows.push([null,   'What will you work on today?', ...m.today]);
  rows.push([null,   'Do you have any obstacles?', ...m.obstacles]);
  rows.push([]); // blank separator
}

// Build worksheet
const ws = xlsx.utils.aoa_to_sheet(rows);

// Column widths
ws['!cols'] = [
  { wch: 20 },  // Name
  { wch: 32 },  // Task label
  ...DAYS.map(() => ({ wch: 55 })),
];

// Row heights – make content rows taller
ws['!rows'] = rows.map((_, i) => i === 0 ? { hpt: 22 } : { hpt: 60 });

// ── replace the sheet in the workbook ────────────────────────────────────────
wb.Sheets['Ws4_DailyMeeting'] = ws;

xlsx.writeFile(wb, outputPath);
console.log('Done! Saved to:', outputPath);
