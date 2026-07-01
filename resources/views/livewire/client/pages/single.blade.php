<div>
    <div class="blog-detail-page">
        <style>
            .blog-detail-page {
                --detail-primary: #d45a18;
                --detail-primary-soft: rgba(212, 90, 24, .1);
                --detail-ink: #111827;
                --detail-muted: #64748b;
                --detail-line: rgba(203, 213, 225, .76);
                --detail-soft: #f8fafc;
                background: #fff;
                color: var(--detail-ink);
            }

            .blog-detail-page .detail-shell {
                max-width: 1180px;
                margin: 0 auto;
            }

            .blog-detail-page .detail-hero {
                padding: 112px 0 44px;
                border-bottom: 1px solid var(--detail-line);
                background:
                    radial-gradient(circle at 88% 18%, rgba(212, 90, 24, .12), transparent 30%),
                    linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            }

            .blog-detail-page .detail-hero-grid {
                display: grid;
                grid-template-columns: minmax(0, .95fr) minmax(320px, .72fr);
                gap: 44px;
                align-items: end;
            }

            .blog-detail-page .detail-kicker {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 9px 14px;
                border-radius: 999px;
                background: var(--detail-primary-soft);
                color: var(--detail-primary);
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .blog-detail-page .detail-hero h1 {
                margin: 22px 0 18px;
                color: var(--detail-ink);
                font-size: clamp(34px, 4.3vw, 54px);
                font-weight: 900;
                line-height: 1.03;
                letter-spacing: -2.2px;
            }

            .blog-detail-page .detail-excerpt {
                max-width: 760px;
                margin: 0;
                color: var(--detail-muted);
                font-size: 17px;
                line-height: 1.8;
            }

            .blog-detail-page .detail-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 28px;
            }

            .blog-detail-page .detail-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 13px;
                border: 1px solid var(--detail-line);
                border-radius: 999px;
                background: rgba(255, 255, 255, .78);
                color: #475569;
                font-size: 13px;
                font-weight: 800;
            }

            .blog-detail-page .detail-cover {
                position: relative;
                overflow: hidden;
                min-height: 360px;
                border: 1px solid var(--detail-line);
                border-radius: 30px;
                background: #e2e8f0;
                box-shadow: 0 26px 70px -38px rgba(15, 23, 42, .38);
            }

            .blog-detail-page .detail-cover img {
                width: 100%;
                height: 100%;
                min-height: 360px;
                object-fit: cover;
                display: block;
                transform: scale(1.01);
            }

            .blog-detail-page .detail-date-card {
                position: absolute;
                left: 18px;
                bottom: 18px;
                width: 92px;
                padding: 14px 10px;
                border-radius: 20px;
                background: var(--detail-primary);
                color: #fff;
                text-align: center;
                box-shadow: 0 18px 34px -20px rgba(212, 90, 24, .78);
            }

            .blog-detail-page .detail-date-card strong {
                display: block;
                font-size: 28px;
                line-height: 1;
                font-weight: 900;
            }

            .blog-detail-page .detail-date-card span {
                display: block;
                margin-top: 4px;
                font-size: 12px;
                font-weight: 800;
            }

            .blog-detail-page .detail-breadcrumb {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 28px;
                color: var(--detail-muted);
                font-size: 14px;
                font-weight: 700;
            }

            .blog-detail-page .detail-breadcrumb a {
                color: var(--detail-muted);
            }

            .blog-detail-page .detail-breadcrumb span {
                color: var(--detail-primary);
            }

            .blog-detail-page .detail-body-section {
                padding: 46px 0 74px;
                background: var(--detail-soft);
            }

            .blog-detail-page .detail-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 340px;
                gap: 28px;
                align-items: start;
            }

            .blog-detail-page .article-card {
                overflow: hidden;
                border: 1px solid var(--detail-line);
                border-radius: 30px;
                background: #fff;
                box-shadow: 0 24px 60px -44px rgba(15, 23, 42, .34);
            }

            .blog-detail-page .article-content {
                padding: 42px 46px 36px;
                color: #334155;
                font-size: 16px;
                line-height: 1.85;
            }

            .blog-detail-page .article-content > *:first-child {
                margin-top: 0;
            }

            .blog-detail-page .article-content p {
                margin: 0 0 20px;
            }

            .blog-detail-page .article-content h2,
            .blog-detail-page .article-content h3,
            .blog-detail-page .article-content h4 {
                margin: 34px 0 14px;
                color: var(--detail-ink);
                font-weight: 900;
                letter-spacing: -.6px;
                line-height: 1.22;
            }

            .blog-detail-page .article-content h3 {
                font-size: 24px;
            }

            .blog-detail-page .article-content ul,
            .blog-detail-page .article-content ol {
                margin: 0 0 22px;
                padding-left: 22px;
            }

            .blog-detail-page .article-content li {
                margin-bottom: 10px;
            }

            .blog-detail-page .article-content strong {
                color: var(--detail-ink);
                font-weight: 900;
            }

            .blog-detail-page .article-content blockquote {
                margin: 30px 0;
                padding: 24px 26px;
                border-left: 4px solid var(--detail-primary);
                border-radius: 0 22px 22px 0;
                background: var(--detail-primary-soft);
                color: #374151;
                font-size: 18px;
                font-weight: 700;
                line-height: 1.65;
            }

            .blog-detail-page .article-footer {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                padding: 24px 46px 34px;
                border-top: 1px solid var(--detail-line);
            }

            .blog-detail-page .back-link,
            .blog-detail-page .share-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-height: 44px;
                padding: 0 16px;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 900;
                transition: all .28s cubic-bezier(.16, 1, .3, 1);
            }

            .blog-detail-page .back-link {
                background: var(--detail-primary);
                color: #fff;
                box-shadow: 0 18px 34px -22px rgba(212, 90, 24, .8);
            }

            .blog-detail-page .share-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .blog-detail-page .share-link {
                width: 44px;
                padding: 0;
                border: 1px solid var(--detail-line);
                background: #fff;
                color: #334155;
            }

            .blog-detail-page .back-link:hover,
            .blog-detail-page .share-link:hover {
                transform: translateY(-2px);
            }

            .blog-detail-page .back-link:active,
            .blog-detail-page .share-link:active {
                transform: translateY(0) scale(.98);
            }

            .blog-detail-page .detail-sidebar {
                position: sticky;
                top: 96px;
                display: grid;
                gap: 18px;
            }

            .blog-detail-page .sidebar-card {
                border: 1px solid var(--detail-line);
                border-radius: 24px;
                background: #fff;
                padding: 22px;
                box-shadow: 0 22px 48px -42px rgba(15, 23, 42, .38);
            }

            .blog-detail-page .sidebar-card h3 {
                margin: 0 0 14px;
                color: var(--detail-ink);
                font-size: 18px;
                font-weight: 900;
                letter-spacing: -.4px;
            }

            .blog-detail-page .summary-list {
                display: grid;
                gap: 12px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .blog-detail-page .summary-list li {
                display: flex;
                justify-content: space-between;
                gap: 18px;
                padding-bottom: 12px;
                border-bottom: 1px solid var(--detail-line);
                color: var(--detail-muted);
                font-size: 13px;
                font-weight: 800;
            }

            .blog-detail-page .summary-list li:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .blog-detail-page .summary-list strong {
                color: var(--detail-ink);
                text-align: right;
            }

            .blog-detail-page .related-list {
                display: grid;
                gap: 14px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .blog-detail-page .related-item a {
                display: grid;
                grid-template-columns: 78px 1fr;
                gap: 12px;
                align-items: center;
                color: inherit;
            }

            .blog-detail-page .related-item img {
                width: 78px;
                height: 64px;
                border-radius: 16px;
                object-fit: cover;
                background: #e2e8f0;
            }

            .blog-detail-page .related-item h4 {
                margin: 0;
                color: var(--detail-ink);
                font-size: 14px;
                font-weight: 900;
                line-height: 1.35;
            }

            .blog-detail-page .related-item p {
                margin: 6px 0 0;
                color: var(--detail-muted);
                font-size: 12px;
                font-weight: 800;
            }

            .blog-detail-page .empty-detail {
                padding: 92px 0;
                background: var(--detail-soft);
            }

            .blog-detail-page .empty-detail-card {
                max-width: 680px;
                margin: 0 auto;
                padding: 44px;
                border: 1px solid var(--detail-line);
                border-radius: 30px;
                background: #fff;
                text-align: center;
                box-shadow: 0 24px 60px -44px rgba(15, 23, 42, .34);
            }

            .blog-detail-page .empty-detail-card h1 {
                margin: 0 0 12px;
                color: var(--detail-ink);
                font-size: 32px;
                font-weight: 900;
                letter-spacing: -1px;
            }

            .blog-detail-page .empty-detail-card p {
                margin: 0 0 24px;
                color: var(--detail-muted);
                line-height: 1.7;
            }

            @media (max-width: 991px) {
                .blog-detail-page .detail-hero-grid,
                .blog-detail-page .detail-layout {
                    grid-template-columns: 1fr;
                }

                .blog-detail-page .detail-sidebar {
                    position: static;
                }
            }

            @media (max-width: 575px) {
                .blog-detail-page .detail-hero {
                    padding: 78px 0 32px;
                }

                .blog-detail-page .detail-hero h1 {
                    font-size: 34px;
                    letter-spacing: -1.4px;
                }

                .blog-detail-page .detail-cover,
                .blog-detail-page .detail-cover img {
                    min-height: 260px;
                }

                .blog-detail-page .article-content,
                .blog-detail-page .article-footer {
                    padding-left: 24px;
                    padding-right: 24px;
                }
            }
        </style>

        @if ($post)
            @php
                $publishedAt = $post->published_at ? \Carbon\Carbon::parse($post->published_at) : $post->created_at;
                $imagePath = $post->image ?: 'assets/img/blog-1.jpeg';
                $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 190);
                $readMinutes = max(1, ceil(str_word_count(strip_tags((string) $post->content)) / 220));
            @endphp

            <section class="detail-hero">
                <div class="container detail-shell">
                    <div class="detail-hero-grid">
                        <div>
                            <span class="detail-kicker">FPT Careers Journal</span>
                            <h1>{{ $post->title }}</h1>
                            <p class="detail-excerpt">{{ $excerpt }}</p>
                            <div class="detail-meta">
                                <span class="detail-pill"><i class="fa fa-calendar-o"></i> {{ $publishedAt?->format('d/m/Y') }}</span>
                                <span class="detail-pill"><i class="fa fa-clock-o"></i> {{ $readMinutes }} phút đọc</span>
                                <span class="detail-pill"><i class="fa fa-eye"></i> {{ number_format((int) $post->views) }} lượt xem</span>
                            </div>
                            <div class="detail-breadcrumb">
                                <a href="{{ route('home') }}">Trang chủ</a>
                                <span>/</span>
                                <a href="{{ route('pages.blog') }}">Bài viết</a>
                                <span>/ Chi tiết</span>
                            </div>
                        </div>

                        <figure class="detail-cover">
                            <img src="{{ asset($imagePath) }}" alt="{{ $post->title }}">
                            <figcaption class="detail-date-card">
                                <strong>{{ $publishedAt?->format('d') }}</strong>
                                <span>{{ $publishedAt?->format('m/Y') }}</span>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <section class="detail-body-section">
                <div class="container detail-shell">
                    <div class="detail-layout">
                        <article class="article-card">
                            <div class="article-content">
                                {!! $post->content !!}
                            </div>

                            <footer class="article-footer">
                                <a class="back-link" href="{{ route('pages.blog') }}">
                                    <i class="fa fa-long-arrow-left"></i>
                                    Quay lại danh sách
                                </a>
                                <div class="share-actions" aria-label="Chia sẻ bài viết">
                                    <a class="share-link" href="#" aria-label="Chia sẻ Facebook"><i class="fa fa-facebook"></i></a>
                                    <a class="share-link" href="#" aria-label="Chia sẻ Twitter"><i class="fa fa-twitter"></i></a>
                                    <a class="share-link" href="#" aria-label="Chia sẻ LinkedIn"><i class="fa fa-linkedin"></i></a>
                                </div>
                            </footer>
                        </article>

                        <aside class="detail-sidebar">
                            <div class="sidebar-card">
                                <h3>Tóm tắt bài viết</h3>
                                <ul class="summary-list">
                                    <li><span>Chuyên mục</span><strong>Career Tips</strong></li>
                                    <li><span>Ngày đăng</span><strong>{{ $publishedAt?->format('d/m/Y') }}</strong></li>
                                    <li><span>Thời lượng</span><strong>{{ $readMinutes }} phút</strong></li>
                                    <li><span>Bình luận</span><strong>{{ (int) $post->comments_count }}</strong></li>
                                </ul>
                            </div>

                            <div class="sidebar-card">
                                <h3>Bài viết liên quan</h3>
                                <ul class="related-list">
                                    @forelse ($relatedPosts as $relatedPost)
                                        @php
                                            $relatedPublishedAt = $relatedPost->published_at ? \Carbon\Carbon::parse($relatedPost->published_at) : $relatedPost->created_at;
                                            $relatedImagePath = $relatedPost->image ?: 'assets/img/blog-2.jpeg';
                                        @endphp
                                        <li class="related-item" wire:key="related-post-{{ $relatedPost->id }}">
                                            <a href="{{ route('pages.single', ['post' => $relatedPost->slug ?: $relatedPost->id]) }}">
                                                <img src="{{ asset($relatedImagePath) }}" alt="{{ $relatedPost->title }}">
                                                <div>
                                                    <h4>{{ \Illuminate\Support\Str::limit($relatedPost->title, 58) }}</h4>
                                                    <p>{{ $relatedPublishedAt?->format('d/m/Y') }}</p>
                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="related-item">
                                            <div>
                                                <h4>Chưa có bài viết liên quan</h4>
                                                <p>Vui lòng quay lại sau.</p>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        @else
            <section class="empty-detail">
                <div class="container">
                    <div class="empty-detail-card">
                        <h1>Chưa có bài viết</h1>
                        <p>Hiện chưa có nội dung để hiển thị. Bạn có thể quay lại trang danh sách để xem các cập nhật mới nhất.</p>
                        <a class="back-link" href="{{ route('pages.blog') }}">Quay lại bài viết</a>
                    </div>
                </div>
            </section>
        @endif
    </div>
</div>
