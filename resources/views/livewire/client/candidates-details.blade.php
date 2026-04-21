<div>
      <section class="single-candidate-page section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-6">
                  <div class="single-candidate-box">
                     <div class="single-candidate-img">
                        @if($candidate->user?->avatar && file_exists(public_path('storage/' . $candidate->user->avatar)))
                             <img src="{{ asset('storage/' . $candidate->user->avatar) }}" alt="{{ $candidate->name }}" />
                        @else
                             <img src="{{ asset('assets/img/candidate-default.png') }}" alt="{{ $candidate->name }}" />
                        @endif
                     </div>
                     <div class="single-candidate-box-right">
                        <h4>{{ $candidate->name }}</h4>
                        <p>{{ $candidate->resume?->profile_title ?: 'Ứng viên' }}</p>
                        
                        @if($latestSubmission && $latestSubmission->ai_matching_score)
                            <div class="ai-score-badge mt-2" style="background: {{ $latestSubmission->ai_matching_score >= 80 ? '#ecfdf5' : ($latestSubmission->ai_matching_score >= 50 ? '#fffbeb' : '#fef2f2') }}; padding: 8px 15px; border-radius: 10px; display: inline-block; border: 1px solid {{ $latestSubmission->ai_matching_score >= 80 ? '#10b981' : ($latestSubmission->ai_matching_score >= 50 ? '#f59e0b' : '#ef4444') }};">
                                <span style="font-weight: 700; color: {{ $latestSubmission->ai_matching_score >= 80 ? '#065f46' : ($latestSubmission->ai_matching_score >= 50 ? '#92400e' : '#991b1b') }};">
                                    <i class="fa fa-magic"></i> Độ phù hợp AI: {{ $latestSubmission->ai_matching_score }}%
                                </span>
                            </div>
                        @endif
                     </div>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="single-candidate-action">
                     <a href="#" class="bookmarks"><i class="fa fa-star"></i>Lưu hồ sơ</a>
                     <a href="mailto:{{ $candidate->email }}" class="candidate-contact"><i class="fa fa-envelope"></i>Liên hệ ngay</a>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <div class="single-candidate-bottom-area section_70">
         <div class="container">
            <div class="row">
               <div class="col-md-10 col-lg-9 mx-auto">
                  <div class="single-candidate-bottom-left">
                     <div class="single-candidate-widget">
                        <h3>Mục tiêu nghề nghiệp</h3>
                        <p>{!! nl2br(e($candidate->resume?->career_objective ?: 'Chưa có thông tin giới thiệu.')) !!}</p>
                     </div>

                     @if($latestSubmission && is_array($latestSubmission->ai_analysis))
                        <div class="single-candidate-widget" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px;">
                            <h3 style="color: var(--fpt-orange); display: flex; align-items: center; gap: 10px;">
                                <i class="fa fa-bolt"></i> Chi tiết phân tích AI
                            </h3>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h5 style="font-size: 1rem; font-weight: 700; color: #059669;">
                                        <i class="fa fa-check-circle"></i> Điểm mạnh & Phù hợp
                                    </h5>
                                    <ul class="mt-2" style="list-style: none; padding-left: 0;">
                                        @foreach($latestSubmission->ai_analysis['match_reasons'] ?? [] as $reason)
                                            <li class="mb-2" style="font-size: 0.9rem; color: #475569;">• {{ $reason }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5 style="font-size: 1rem; font-weight: 700; color: #dc2626;">
                                        <i class="fa fa-warning"></i> Điểm cần lưu ý
                                    </h5>
                                    <ul class="mt-2" style="list-style: none; padding-left: 0;">
                                        @foreach($latestSubmission->ai_analysis['missing_skills'] ?? [] as $missing)
                                            <li class="mb-2" style="font-size: 0.9rem; color: #475569;">• {{ $missing }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                     @endif

                     <div class="single-candidate-widget">
                        <h3>Kỹ năng</h3>
                        @if($candidate->resume && is_array($candidate->resume->skills))
                            <ul>
                               @foreach($candidate->resume->skills as $skill)
                                  <li><a href="#">{{ $skill }}</a></li>
                               @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Chưa cập nhật kỹ năng.</p>
                        @endif
                     </div>

                     <div class="single-candidate-widget">
                        <h3>Lịch sử làm việc</h3>
                        @if($candidate->resume && is_array($candidate->resume->experiences))
                            @foreach($candidate->resume->experiences as $exp)
                                <div class="single-work-history">
                                   <div class="single-candidate-list">
                                      <div class="main-comment">
                                         <div class="candidate-text" style="padding-left: 0;">
                                            <div class="candidate-info">
                                               <div class="candidate-title">
                                                  <h3><a href="#">{{ $exp['position'] ?? 'Chức vụ' }}</a> at {{ $exp['company'] ?? 'Công ty' }}</h3>
                                               </div>
                                               <p><i class="fa fa-calendar-check-o"></i> {{ $exp['period'] ?? '' }}</p>
                                            </div>
                                            <div class="candidate-text-inner">
                                               <p>{{ $exp['description'] ?? '' }}</p>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">Chưa cập nhật kinh nghiệm.</p>
                        @endif
                     </div>
                  </div>
               </div>
               <div class="col-md-10 col-lg-3 mx-auto">
                  <div class="single-candidate-bottom-right">
                     <div class="single-candidate-widget-2">
                        <ul>
                           <li><i class="fa fa-envelope"></i> {{ $candidate->email }}</li>
                           <li><i class="fa fa-phone"></i> {{ $candidate->phone ?: 'Chưa có SĐT' }}</li>
                           @if($candidate->resume && isset($candidate->resume->personal_info['website']))
                            <li><i class="fa fa-globe"></i> {{ $candidate->resume->personal_info['website'] }}</li>
                           @endif
                        </ul>
                     </div>
                     <div class="single-candidate-widget-2">
                        <h3>Tin tuyển dụng đã nộp</h3>
                        <div class="applied-jobs mt-3">
                            @foreach($candidate->applications as $app)
                                <div class="mb-3 p-3 bg-white rounded-3 border shadow-sm">
                                    <div style="font-weight: 700; font-size: 0.9rem;">{{ $app->job->title }}</div>
                                    <div class="mt-1">
                                        <span class="badge" style="background: {{ $app->status->getColor() }}; color: #fff;">
                                            {{ $app->status->getLabel() }}
                                        </span>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                        {{ $app->applied_at->format('d/m/Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      </div>
  </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      </div>