@props(['title', 'value', 'icon', 'color' => 'primary', 'trend' => null, 'trendValue' => null])

<div class="col-12 col-sm-6 col-lg-3 mb-4">
  <div class="dashboard-card h-100">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h6 class="card-subtitle mb-2 text-muted">{{ $title }}</h6>
          <h3 class="card-title mb-0 fw-bold text-{{ $color }}">{{ $value }}</h3>
          @if($trend && $trendValue)
            <small class="text-{{ $trend === 'up' ? 'success' : 'danger' }}">
              <i class="bi bi-arrow-{{ $trend }} me-1"></i>{{ $trendValue }}
            </small>
          @endif
        </div>
        <div class="dashboard-icon">
          <i class="bi bi-{{ $icon }} text-{{ $color }}" style="font-size: 2rem; opacity: 0.7;"></i>
        </div>
      </div>
    </div>
  </div>
</div>
