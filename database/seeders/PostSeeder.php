<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            [
                'title'   => 'Nữ giảng viên xinh đẹp đứng sau những đồ án để đời',
                'excerpt' => 'Giảng viên trẻ Hoàng Mai Anh, Trường Đại học FPT, được biết đến với một triết lý giảng dạy riêng...',
                'content' => '<p>Giảng viên trẻ <strong>Hoàng Mai Anh</strong>, Trường Đại học FPT, được biết đến với một triết lý giảng dạy rất riêng khi khuyến khích sinh viên tạo ra những dự án thật sự có ý nghĩa, những “đồ án để đời” mà nhiều năm sau nhìn lại vẫn có thể tự hào vì đã dốc toàn bộ tâm huyết để thực hiện.</p>' .
                    '<p>Trước khi đến với giảng đường, Mai Anh từng có thời gian làm việc và trải nghiệm trong nhiều môi trường truyền thông khác nhau. Những năm tháng đó giúp cô nàng tích lũy nhiều bài học thực tế về cách xây dựng ý tưởng, triển khai dự án và tạo ra những sản phẩm truyền thông có giá trị cho cộng đồng.</p>' .
                    '<p>Là một giảng viên trẻ, Mai Anh cho rằng lợi thế lớn nhất là sự gần gũi với sinh viên và khả năng cập nhật nhanh những xu hướng mới của ngành. Vì vậy, khi hướng dẫn sinh viên thực hiện đồ án, cô thường đặt ra ba tiêu chí quan trọng: <strong>tính tiên phong, sự khác biệt và giá trị đóng góp cho cộng đồng.</strong></p>',
                'image'   => 'assets/img/posts/baiviet1.png',
            ],
            [
                'title'   => 'Lộ trình học PHP cho người mới bắt đầu năm 2026',
                'excerpt' => 'PHP vẫn là ngôn ngữ mạnh mẽ cho backend. Dưới đây là lộ trình từ Zero đến Hero...',
                'content' => '<p><strong>PHP</strong> vẫn luôn là một trong những ngôn ngữ lập trình kịch bản phía máy chủ (backend) phổ biến nhất thế giới. Với sự ra đời của các phiên bản mới như PHP 8.x, ngôn ngữ này ngày càng trở nên mạnh mẽ và tối ưu hơn về hiệu suất.</p>
                               <h3>1. Tại sao nên học PHP và Laravel?</h3>
                               <p>Laravel là một framework PHP mạnh mẽ, giúp các nhà phát triển xây dựng ứng dụng web nhanh chóng nhờ cú pháp thanh thoát (elegant syntax) và các tính năng tích hợp sẵn như: Eloquent ORM, Routing, Authentication, và Middleware.</p>
                               <h3>2. Lộ trình học tập cơ bản</h3>
                              <p>Để trở thành một Backend Developer thực thụ, bạn nên tuân theo lộ trình sau:</p>
                             <ul>
                              <li><strong>Nắm vững PHP thuần:</strong> Hiểu về biến, mảng, vòng lặp, hàm và đặc biệt là lập trình hướng đối tượng (OOP).</li>
                             <li><strong>Cấu trúc dữ liệu và giải thuật:</strong> Giúp bạn viết mã tối ưu và giải quyết các bài toán logic phức tạp.</li>
                             <li><strong>Làm quen với Database:</strong> Học cách sử dụng MySQL, viết các câu lệnh Query và thiết kế cơ sở dữ liệu chuẩn hóa.</li>
                            <li><strong>Học Laravel Framework:</strong> Tìm hiểu về mô hình MVC, Migration, Seeder và cách xây dựng các API chuyên nghiệp.</li>
                          </ul>
                         <h3>3. Kinh nghiệm thực tế</h3>
                       <p>Trong quá trình học tại <strong>FPT Polytechnic</strong>, việc tham gia vào các dự án thực tế hay làm đồ án môn học là cơ hội tốt nhất để bạn rèn luyện kỹ năng. Hãy bắt đầu từ những ứng dụng đơn giản như quản lý sinh viên, website bán hàng cho đến những hệ thống phức tạp hơn.</p>
                     <p>Đừng quên cập nhật những xu hướng công nghệ mới và tham gia vào cộng đồng lập trình để học hỏi từ những người đi trước. Chúc các bạn thành công trên con đường trở thành một lập trình viên chuyên nghiệp!</p>',
                'image'   => 'assets/img/posts/baiviet2.jpg',
            ],

            [
                'title'   => 'Công bố logo kỉ niệm 20 năm thành lập Trường Đại học FPT',
                'excerpt' => 'Trường Đại học FPT chính thức công bố logo kỉ niệm 20 năm thành lập (2006 - 2026), đánh dấu cột mốc hai thập kỉ hình thành và phát triển. Logo được thiết kế trên nền tảng nhận diện đặc trưng của FPT, thể hiện tinh thần đổi mới, sáng tạo và khát vọng “Vươn mình cùng đất nước - Rising together with the nation”.',
                'content' => '<p><strong>PHP</strong> vẫn luôn là một trong những ngôn ngữ lập trình kịch bản phía máy chủ (backend) phổ biến nhất thế giới. Với sự ra đời của các phiên bản mới như PHP 8.x, ngôn ngữ này ngày càng trở nên mạnh mẽ và tối ưu hơn về hiệu suất.</p>
                               <h3>1. Tại sao nên học PHP và Laravel?</h3>
                               <p>Laravel là một framework PHP mạnh mẽ, giúp các nhà phát triển xây dựng ứng dụng web nhanh chóng nhờ cú pháp thanh thoát (elegant syntax) và các tính năng tích hợp sẵn như: Eloquent ORM, Routing, Authentication, và Middleware.</p>
                               <h3>2. Lộ trình học tập cơ bản</h3>
                              <p>Để trở thành một Backend Developer thực thụ, bạn nên tuân theo lộ trình sau:</p>
                             <ul>
                              <li><strong>Nắm vững PHP thuần:</strong> Hiểu về biến, mảng, vòng lặp, hàm và đặc biệt là lập trình hướng đối tượng (OOP).</li>
                             <li><strong>Cấu trúc dữ liệu và giải thuật:</strong> Giúp bạn viết mã tối ưu và giải quyết các bài toán logic phức tạp.</li>
                             <li><strong>Làm quen với Database:</strong> Học cách sử dụng MySQL, viết các câu lệnh Query và thiết kế cơ sở dữ liệu chuẩn hóa.</li>
                            <li><strong>Học Laravel Framework:</strong> Tìm hiểu về mô hình MVC, Migration, Seeder và cách xây dựng các API chuyên nghiệp.</li>
                          </ul>
                         <h3>3. Kinh nghiệm thực tế</h3>
                       <p>Trong quá trình học tại <strong>FPT Polytechnic</strong>, việc tham gia vào các dự án thực tế hay làm đồ án môn học là cơ hội tốt nhất để bạn rèn luyện kỹ năng. Hãy bắt đầu từ những ứng dụng đơn giản như quản lý sinh viên, website bán hàng cho đến những hệ thống phức tạp hơn.</p>
                     <p>Đừng quên cập nhật những xu hướng công nghệ mới và tham gia vào cộng đồng lập trình để học hỏi từ những người đi trước. Chúc các bạn thành công trên con đường trở thành một lập trình viên chuyên nghiệp!</p>',
                'image'   => 'assets/img/posts/baiviet3.png',
            ],
        ];
        if (Post::query()->count() > 0) {
            return;
        }

        // 3. Vòng lặp tạo dữ liệu
        foreach ($posts as $item) {
            Post::create([
                'title'          => $item['title'],
                'slug'           => Str::slug($item['title']),
                'excerpt'        => $item['excerpt'],
                'content'        => $item['content'],
                'image'          => $item['image'],
                'views'          => rand(10, 500),
                'comments_count' => 0,
                'status'         => 1,
                'published_at'   => Carbon::now(),
            ]);
        }
    }
}
