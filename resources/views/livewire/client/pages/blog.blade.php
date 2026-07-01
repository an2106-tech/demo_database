<div>
    <div class="blog-index-page">
        <style>
            .blog-index-page {
                --blog-primary: #d45a18;
                --blog-primary-soft: rgba(212, 90, 24, .1);
                --blog-ink: #111827;
                --blog-muted: #64748b;
                --blog-line: rgba(203, 213, 225, .72);
                --blog-soft: #f8fafc;
            }

            .blog-index-page .blog-hero {
                background: linear-gradient(180deg, #f8fafc 0%, #fff 72%);
                border-bottom: 1px solid var(--blog-line);
                padding: 112px 0 44px;
            }

            .blog-index-page .blog-shell {
                max-width: 1180px;
                margin: 0 auto;
            }

            .blog-index-page .blog-hero-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 340px;
                gap: 40px;
                align-items: end;
            }

            .blog-index-page .blog-kicker {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 999px;
                background: var(--blog-primary-soft);
                color: var(--blog-primary);
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .blog-index-page .blog-hero h1 {
                margin: 18px 0 12px;
                color: var(--blog-ink);
                font-size: 42px;
                line-height: 1.08;
                font-weight: 900;
                letter-spacing: -.04em;
            }

            .blog-index-page .blog-hero p {
                max-width: 680px;
                margin: 0;
                color: var(--blog-muted);
                font-size: 16px;
                line-height: 1.8;
            }

            .blog-index-page .blog-hero-panel {
                padding: 22px;
                border: 1px solid var(--blog-line);
                border-radius: 22px;
                background: #fff;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .05);
            }

            .blog-index-page .blog-hero-panel span {
                display: block;
                color: var(--blog-muted);
                font-size: 12px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .blog-index-page .blog-hero-panel strong {
                display: block;
                margin-top: 8px;
                color: var(--blog-ink);
                font-size: 30px;
                line-height: 1;
                font-weight: 900;
            }

            .blog-index-page .blog-hero-panel p {
                margin-top: 10px;
                font-size: 13px;
                line-height: 1.6;
            }

            .blog-index-page .blog-breadcrumb {
                margin-top: 24px;
                color: var(--blog-muted);
                font-size: 13px;
                font-weight: 700;
            }

            .blog-index-page .blog-breadcrumb a {
                color: var(--blog-muted);
            }

            .blog-index-page .blog-breadcrumb span {
                color: var(--blog-primary);
            }

            .blog-index-page .blog-content-section {
                padding: 44px 0 72px;
                background: var(--blog-soft);
            }

            .blog-index-page .blog-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 330px;
                gap: 28px;
                align-items: start;
            }

            .blog-index-page .blog-list {
                display: grid;
                gap: 18px;
            }

            .blog-index-page .post-card {
                display: grid;
                grid-template-columns: 260px minmax(0, 1fr);
                gap: 22px;
                padding: 18px;
                border: 1px solid var(--blog-line);
                border-radius: 24px;
                background: #fff;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .045);
                transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
            }

            .blog-index-page .post-card:hover {
                transform: translateY(-2px);
                border-color: rgba(212, 90, 24, .24);
                box-shadow: 0 24px 52px rgba(15, 23, 42, .07);
            }

            .blog-index-page .post-card.is-featured {
                grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
                padding: 0;
                overflow: hidden;
            }

            .blog-index-page .post-media {
                position: relative;
                min-height: 190px;
                border-radius: 18px;
                overflow: hidden;
                background: #e2e8f0;
            }

            .blog-index-page .post-card.is-featured .post-media {
                min-height: 340px;
                border-radius: 0;
            }

            .blog-index-page .post-media img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform .35s ease;
            }

            .blog-index-page .post-card:hover .post-media img {
                transform: scale(1.035);
            }

            .blog-index-page .post-date {
                position: absolute;
                top: 14px;
                left: 14px;
                min-width: 62px;
                padding: 9px 10px;
                border-radius: 14px;
                background: var(--blog-primary);
                color: #fff;
                text-align: center;
                box-shadow: 0 14px 26px rgba(212, 90, 24, .22);
            }

            .blog-index-page .post-date strong {
                display: block;
                font-size: 18px;
                line-height: 1;
                font-weight: 900;
            }

            .blog-index-page .post-date span {
                display: block;
                margin-top: 3px;
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
            }

            .blog-index-page .post-body {
                min-width: 0;
                padding: 4px 0;
            }

            .blog-index-page .post-card.is-featured .post-body {
                padding: 30px 28px 28px 0;
                align-self: center;
            }

            .blog-index-page .post-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 12px;
            }

            .blog-index-page .post-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 10px;
                border-radius: 999px;
                background: var(--blog-primary-soft);
                color: var(--blog-primary);
                font-size: 12px;
                font-weight: 800;
            }

            .blog-index-page .post-card h2,
            .blog-index-page .post-card h3 {
                margin: 0;
                color: var(--blog-ink);
                font-weight: 900;
                letter-spacing: -.025em;
            }

            .blog-index-page .post-card h2 {
                font-size: 30px;
                line-height: 1.16;
            }

            .blog-index-page .post-card h3 {
                font-size: 21px;
                line-height: 1.3;
            }

            .blog-index-page .post-card a {
                color: inherit;
            }

            .blog-index-page .post-excerpt {
                margin: 12px 0 0;
                color: var(--blog-muted);
                font-size: 14px;
                line-height: 1.72;
            }

            .blog-index-page .post-action {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-top: 18px;
                min-height: 42px;
                padding: 0 16px;
                border-radius: 12px;
                background: var(--blog-primary);
                color: #fff !important;
                font-size: 13px;
                font-weight: 900;
                box-shadow: 0 14px 26px rgba(212, 90, 24, .18);
            }

            .blog-index-page .post-action:active {
                transform: translateY(1px);
            }

            .blog-index-page .blog-sidebar {
                position: sticky;
                top: 96px;
                display: grid;
                gap: 16px;
            }

            .blog-index-page .sidebar-card {
                padding: 18px;
                border: 1px solid var(--blog-line);
                border-radius: 22px;
                background: #fff;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .045);
            }

            .blog-index-page .sidebar-card h3 {
                margin: 0 0 14px;
                color: var(--blog-ink);
                font-size: 16px;
                font-weight: 900;
            }

            .blog-index-page .blog-search {
                display: flex;
                align-items: stretch;
            }

            .blog-index-page .blog-search input {
                width: 100%;
                height: 46px;
                padding: 0 14px;
                border: 1px solid rgba(148, 163, 184, .3);
                border-right: 0;
                border-radius: 12px 0 0 12px;
                background: var(--blog-soft);
                color: var(--blog-ink);
                font-size: 14px;
            }

            .blog-index-page .blog-search button {
                width: 52px;
                border: 1px solid var(--blog-primary);
                border-radius: 0 12px 12px 0;
                background: var(--blog-primary);
                color: #fff;
            }

            .blog-index-page .blog-search input:focus {
                outline: none;
                background: #fff;
                border-color: var(--blog-primary);
                box-shadow: 0 0 0 4px var(--blog-primary-soft);
            }

            .blog-index-page .topic-list {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .blog-index-page .topic-list a {
                display: inline-flex;
                padding: 8px 11px;
                border-radius: 999px;
                background: var(--blog-soft);
                color: #334155;
                font-size: 12px;
                font-weight: 800;
            }

            .blog-index-page .latest-list {
                display: grid;
                gap: 12px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .blog-index-page .latest-item {
                display: grid;
                grid-template-columns: 72px minmax(0, 1fr);
                gap: 12px;
                align-items: center;
            }

            .blog-index-page .latest-item img {
                width: 72px;
                height: 58px;
                object-fit: cover;
                border-radius: 12px;
                background: #e2e8f0;
            }

            .blog-index-page .latest-item h4 {
                margin: 0;
                color: var(--blog-ink);
                font-size: 13px;
                font-weight: 900;
                line-height: 1.45;
            }

            .blog-index-page .latest-item p {
                margin: 4px 0 0;
                color: var(--blog-muted);
                font-size: 12px;
                font-weight: 700;
            }

            .blog-index-page .empty-state {
                padding: 46px 22px;
                border: 1px solid var(--blog-line);
                border-radius: 24px;
                background: #fff;
                text-align: center;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .045);
            }

            .blog-index-page .empty-state h3 {
                margin: 0 0 8px;
                color: var(--blog-ink);
                font-size: 22px;
                font-weight: 900;
            }

            .blog-index-page .empty-state p {
                margin: 0;
                color: var(--blog-muted);
                font-size: 14px;
            }

            .blog-index-page .blog-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                flex-wrap: wrap;
                margin-top: 10px;
                padding: 18px;
                border: 1px solid var(--blog-line);
                border-radius: 20px;
                background: #fff;
            }

            .blog-index-page .blog-pagination p {
                margin: 0;
                color: var(--blog-muted);
                font-size: 13px;
                font-weight: 700;
            }

            .blog-index-page .pager-controls {
                display: inline-flex;
                gap: 6px;
                padding: 6px;
                border: 1px solid var(--blog-line);
                border-radius: 16px;
                background: #fff;
            }

            .blog-index-page .pager-btn {
                min-width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                border: 1px solid var(--blog-line);
                background: #fff;
                color: var(--blog-ink);
                font-size: 13px;
                font-weight: 900;
            }

            .blog-index-page .pager-btn.is-active {
                background: var(--blog-primary);
                border-color: var(--blog-primary);
                color: #fff;
            }

            .blog-index-page .pager-btn:disabled {
                background: var(--blog-soft);
                color: #94a3b8;
                cursor: not-allowed;
            }

            @media (max-width: 991px) {
                .blog-index-page .blog-hero-grid,
                .blog-index-page .blog-layout,
                .blog-index-page .post-card,
                .blog-index-page .post-card.is-featured {
                    grid-template-columns: 1fr;
                }

                .blog-index-page .blog-sidebar {
                    position: static;
                }

                .blog-index-page .post-card.is-featured .post-body {
                    padding: 22px;
                }

                .blog-index-page .post-card.is-featured .post-media {
                    min-height: 260px;
                }
            }

            @media (max-width: 767px) {
                .blog-index-page .blog-hero {
                    padding: 76px 0 30px;
                }

                .blog-index-page .blog-hero h1 {
                    font-size: 32px;
                }

                .blog-index-page .blog-content-section {
                    padding: 30px 0 52px;
                }

                .blog-index-page .blog-pagination {
                    justify-content: center;
                    text-align: center;
                }
            }
        </style>

        <section class="blog-hero">
            <div class="container blog-shell">
                <div class="blog-hero-grid">
                    <div>
                        <span class="blog-kicker">FPT Careers Journal</span>
                        <h1>Bài viết & Career Tips</h1>
                        <p>Kỹ năng phỏng vấn, định hướng nghề nghiệp và những câu chuyện từ môi trường học tập, tuyển dụng tại FPT.</p>
                        <div class="blog-breadcrumb">
                            <a href="{{ route('home') }}">Trang chủ</a>
                            <span>/</span>
                            <span>Bài viết</span>
                        </div>
                    </div>

                    <div class="blog-hero-panel">
                        <span>Thư viện nội dung</span>
                        <strong>{{ $posts->total() }}</strong>
                        <p>Bài viết đang có trên hệ thống, được sắp xếp theo thời gian cập nhật mới nhất.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="blog-content-section">
            <div class="container blog-shell">
                <div class="blog-layout">
                    <div class="blog-list">
                        @forelse ($posts as $post)
                            @php
                                $publishedAt = $post->published_at ? \Carbon\Carbon::parse($post->published_at) : $post->created_at;
                                $imagePath = $post->image ?: 'assets/img/blog-1.jpeg';
                                $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 180);
                                $isFeatured = $loop->first && $posts->currentPage() === 1;
                            @endphp

                            <article class="post-card {{ $isFeatured ? 'is-featured' : '' }}" wire:key="post-{{ $post->id }}">
                                <a class="post-media" href="{{ route('pages.single', ['post' => $post->slug ?: $post->id]) }}" aria-label="Đọc bài {{ $post->title }}">
                                    <img src="{{ asset($imagePath) }}" alt="{{ $post->title }}">
                                    <span class="post-date">
                                        <strong>{{ $publishedAt?->format('d') }}</strong>
                                        <span>{{ $publishedAt?->format('m/Y') }}</span>
                                    </span>
                                </a>

                                <div class="post-body">
                                    <div class="post-meta">
                                        <span class="post-pill"><i class="fa fa-bookmark-o"></i> Career Tips</span>
                                        <span class="post-pill"><i class="fa fa-clock-o"></i> {{ $publishedAt?->format('d/m/Y') }}</span>
                                    </div>

                                    @if ($isFeatured)
                                        <h2><a href="{{ route('pages.single', ['post' => $post->slug ?: $post->id]) }}">{{ $post->title }}</a></h2>
                                    @else
                                        <h3><a href="{{ route('pages.single', ['post' => $post->slug ?: $post->id]) }}">{{ $post->title }}</a></h3>
                                    @endif

                                    <p class="post-excerpt">{{ $excerpt }}</p>
                                    <a class="post-action" href="{{ route('pages.single', ['post' => $post->slug ?: $post->id]) }}">Đọc bài viết</a>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state">
                                <h3>Chưa có bài viết phù hợp</h3>
                                <p>Hãy thử đổi từ khóa tìm kiếm hoặc quay lại sau khi có nội dung mới.</p>
                            </div>
                        @endforelse

                        @if ($posts->hasPages())
                            @php
                                $currentPage = $posts->currentPage();
                                $lastPage = $posts->lastPage();
                            @endphp

                            <nav class="blog-pagination" aria-label="Phân trang bài viết">
                                <p>Hiển thị {{ $posts->firstItem() }}-{{ $posts->lastItem() }} trong {{ $posts->total() }} bài viết</p>
                                <div class="pager-controls">
                                    <button type="button" class="pager-btn" wire:click="previousPage"
                                        wire:loading.attr="disabled" @disabled($posts->onFirstPage()) aria-label="Trang trước">
                                        Trước
                                    </button>

                                    @for ($page = 1; $page <= $lastPage; $page++)
                                        <button type="button" class="pager-btn {{ $page === $currentPage ? 'is-active' : '' }}"
                                            wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                                            @disabled($page === $currentPage) @if ($page === $currentPage) aria-current="page" @endif
                                            aria-label="Đến trang {{ $page }}">
                                            {{ $page }}
                                        </button>
                                    @endfor

                                    <button type="button" class="pager-btn" wire:click="nextPage"
                                        wire:loading.attr="disabled" @disabled(! $posts->hasMorePages()) aria-label="Trang sau">
                                        Sau
                                    </button>
                                </div>
                            </nav>
                        @endif
                    </div>

                    <aside class="blog-sidebar">
                        <div class="sidebar-card">
                            <h3>Tìm bài viết</h3>
                            <form class="blog-search" wire:submit.prevent>
                                <input type="search" wire:model.live.debounce.400ms="search"
                                    placeholder="Nhập kỹ năng, ngành nghề...">
                                <button type="submit" aria-label="Tìm kiếm"><i class="fa fa-search"></i></button>
                            </form>
                        </div>

                        <div class="sidebar-card">
                            <h3>Chủ đề nổi bật</h3>
                            <ul class="topic-list">
                                <li><a href="#">Phỏng vấn</a></li>
                                <li><a href="#">Định hướng nghề</a></li>
                                <li><a href="#">Công nghệ</a></li>
                                <li><a href="#">Kỹ năng mềm</a></li>
                            </ul>
                        </div>

                        <div class="sidebar-card">
                            <h3>Bài viết mới</h3>
                            <ul class="latest-list">
                                @forelse ($latestPosts as $latestPost)
                                    @php
                                        $latestPublishedAt = $latestPost->published_at ? \Carbon\Carbon::parse($latestPost->published_at) : $latestPost->created_at;
                                        $latestImagePath = $latestPost->image ?: 'assets/img/blog-2.jpeg';
                                    @endphp
                                    <li class="latest-item" wire:key="latest-post-{{ $latestPost->id }}">
                                        <a href="{{ route('pages.single', ['post' => $latestPost->slug ?: $latestPost->id]) }}">
                                            <img src="{{ asset($latestImagePath) }}" alt="{{ $latestPost->title }}">
                                        </a>
                                        <div>
                                            <h4><a href="{{ route('pages.single', ['post' => $latestPost->slug ?: $latestPost->id]) }}">{{ \Illuminate\Support\Str::limit($latestPost->title, 58) }}</a></h4>
                                            <p>{{ $latestPublishedAt?->format('d/m/Y') }}</p>
                                        </div>
                                    </li>
                                @empty
                                    <li class="latest-item">
                                        <div>
                                            <h4>Chưa có bài viết mới</h4>
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
    </div>
</div>
