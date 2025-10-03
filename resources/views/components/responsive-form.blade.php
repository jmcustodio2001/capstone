@props(['title', 'action', 'method' => 'POST'])

<div class="card dashboard-card">
  <div class="card-header">
    <h5 class="mb-0 fw-bold">{{ $title }}</h5>
  </div>
  <div class="card-body">
    <form action="{{ $action }}" method="{{ $method }}" class="responsive-form">
      @csrf
      @if($method !== 'GET' && $method !== 'POST')
        @method($method)
      @endif
      
      <div class="row g-3">
        {{ $slot }}
      </div>
      
      <div class="row mt-4">
        <div class="col-12">
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
              <i class="bi bi-arrow-left me-1"></i>
              <span class="d-none d-sm-inline">Cancel</span>
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>
              <span class="d-none d-sm-inline">Submit</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
.responsive-form .form-floating {
  margin-bottom: 1rem;
}

.responsive-form .form-control,
.responsive-form .form-select {
  font-size: 16px; /* Prevents zoom on iOS */
  padding: 0.75rem;
}

@media (max-width: 767px) {
  .responsive-form .row > [class*="col-"] {
    margin-bottom: 1rem;
  }
  
  .responsive-form .btn {
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
  }
}
</style>
