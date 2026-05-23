<!-- AI Agent Widget for Dashboard -->
<div class="card mb-4" style="border: 2px solid #667eea; border-radius: 12px; overflow: hidden;">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1" style="font-weight: 800;">
                    <i class="fas fa-brain me-2"></i>AI Automation Agent
                </h5>
                <small style="opacity: 0.9;">Advanced AI-Powered Automation & Analytics</small>
            </div>
            <div class="text-end">
                <div class="badge bg-white text-primary" style="font-size: 0.9rem; padding: 8px 12px;">
                    <i class="fas fa-circle-check me-1"></i>Online
                </div>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <div class="row g-3 mb-4">
            <!-- AI Requests -->
            <div class="col-md-3">
                <div class="text-center p-3 border rounded" style="border-radius: 10px; background: #f8f9ff;">
                    <i class="fas fa-comments" style="font-size: 1.8rem; color: #667eea; margin-bottom: 8px;"></i>
                    <h6 class="mt-2 mb-1" style="font-weight: 700; color: #1e293b;">AI Requests</h6>
                    <h4 style="color: #667eea; font-weight: 800; margin: 0;">{{ $ai_requests_today ?? 0 }}</h4>
                    <small class="text-muted">Today</small>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="col-md-3">
                <div class="text-center p-3 border rounded" style="border-radius: 10px; background: #f0fdf4;">
                    <i class="fas fa-check-circle" style="font-size: 1.8rem; color: #22c55e; margin-bottom: 8px;"></i>
                    <h6 class="mt-2 mb-1" style="font-weight: 700; color: #1e293b;">Success Rate</h6>
                    <h4 style="color: #22c55e; font-weight: 800; margin: 0;">{{ $ai_success_rate ?? 98 }}%</h4>
                    <small class="text-muted">Last 30 days</small>
                </div>
            </div>

            <!-- Avg Response Time -->
            <div class="col-md-3">
                <div class="text-center p-3 border rounded" style="border-radius: 10px; background: #fef3c7;">
                    <i class="fas fa-tachometer-alt" style="font-size: 1.8rem; color: #f59e0b; margin-bottom: 8px;"></i>
                    <h6 class="mt-2 mb-1" style="font-weight: 700; color: #1e293b;">Avg Response</h6>
                    <h4 style="color: #f59e0b; font-weight: 800; margin: 0;">{{ $ai_avg_response ?? 245 }}ms</h4>
                    <small class="text-muted">Processing time</small>
                </div>
            </div>

            <!-- Active Features -->
            <div class="col-md-3">
                <div class="text-center p-3 border rounded" style="border-radius: 10px; background: #f3e8ff;">
                    <i class="fas fa-star" style="font-size: 1.8rem; color: #a855f7; margin-bottom: 8px;"></i>
                    <h6 class="mt-2 mb-1" style="font-weight: 700; color: #1e293b;">Features</h6>
                    <h4 style="color: #a855f7; font-weight: 800; margin: 0;">8</h4>
                    <small class="text-muted">Active modules</small>
                </div>
            </div>
        </div>

        <!-- AI Features -->
        <div class="row g-2 mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center p-2 border rounded" style="border-radius: 8px;">
                    <i class="fas fa-comments-dollar" style="color: #667eea; font-size: 1.2rem; margin-right: 12px;"></i>
                    <div>
                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.9rem;">Chat Processing</h6>
                        <small class="text-muted">Natural language queries</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center p-2 border rounded" style="border-radius: 8px;">
                    <i class="fas fa-microphone" style="color: #22c55e; font-size: 1.2rem; margin-right: 12px;"></i>
                    <div>
                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.9rem;">Voice Support</h6>
                        <small class="text-muted">Multi-language voice I/O</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center p-2 border rounded" style="border-radius: 8px;">
                    <i class="fas fa-chart-bar" style="color: #f59e0b; font-size: 1.2rem; margin-right: 12px;"></i>
                    <div>
                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.9rem;">Analytics</h6>
                        <small class="text-muted">Real-time business insights</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center p-2 border rounded" style="border-radius: 8px;">
                    <i class="fas fa-bell" style="color: #a855f7; font-size: 1.2rem; margin-right: 12px;"></i>
                    <div>
                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.9rem;">Notifications</h6>
                        <small class="text-muted">Email, SMS, In-app alerts</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Buttons -->
        <div class="row g-2">
            <div class="col-md-3">
                <a href="{{ route('ai-agent.chat.index') }}" class="btn btn-primary w-100" style="border-radius: 8px; font-weight: 700;">
                    <i class="fas fa-comments me-1"></i> Chat Now
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('api.analytics.dashboard') }}" class="btn btn-outline-primary w-100" style="border-radius: 8px; font-weight: 700;">
                    <i class="fas fa-chart-line me-1"></i> Analytics
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('api.notifications.user') }}" class="btn btn-outline-primary w-100" style="border-radius: 8px; font-weight: 700;">
                    <i class="fas fa-bell me-1"></i> Alerts
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('ai-agent.chat.status') }}" class="btn btn-outline-primary w-100" style="border-radius: 8px; font-weight: 700;">
                    <i class="fas fa-info-circle me-1"></i> Status
                </a>
            </div>
        </div>
    </div>
</div>
