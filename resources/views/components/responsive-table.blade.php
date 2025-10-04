@props(['headers', 'data', 'mobileCardTitle' => 'Record'])

<div class="responsive-table-container">
  <!-- Desktop Table View -->
  <div class="table-responsive-mobile">
    <table class="table table-bordered table-hover">
      <thead class="table-primary">
        <tr>
          @foreach($headers as $header)
            <th class="fw-bold">{{ $header['label'] }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($data as $row)
          <tr>
            @foreach($headers as $key => $header)
              <td>{{ $row[$key] ?? '' }}</td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Mobile Card View -->
  <div class="table-card-mobile">
    @foreach($data as $row)
      <div class="card mb-3">
        <div class="card-header">
          <h6 class="mb-0 fw-bold">{{ $row[$headers[0]['key']] ?? $mobileCardTitle }}</h6>
        </div>
        <div class="card-body">
          @foreach($headers as $key => $header)
            @if($key > 0) <!-- Skip first column as it's used in header -->
              <div class="row mb-2">
                <div class="col-5 fw-bold text-muted">{{ $header['label'] }}:</div>
                <div class="col-7">{!! $row[$key] ?? '' !!}</div>
              </div>
            @endif
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
</div>
