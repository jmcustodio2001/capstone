<table class="table table-bordered">
  <thead class="table-secondary">
    <tr>
      <th>Employee</th>
      <th>Competency</th>
      <th>Required Level</th>
      <th>Current Level</th>
      <th>Gap</th>
      <th>Recommended Training</th>
    </tr>
  </thead>
  <tbody>
    @forelse($gaps as $gap)
    <tr>
      <td>{{ $gap->employee->first_name }} {{ $gap->employee->last_name }} (EMP-{{ $gap->employee->employee_id }})</td>
      <td>{{ $gap->competency->competency_name }}</td>
      <td>{{ $gap->required_level }}</td>
      <td>{{ $gap->current_level }}</td>
      <td class="text-danger fw-bold">{{ $gap->gap }}</td>
      <td>
        @if($gap->recommended_training && isset($gap->recommended_training->course_title))
          {{ $gap->recommended_training->course_title }}
        @else
          <span class="text-muted">No training assigned</span>
        @endif
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="6" class="empty-state text-center">All employees meet required skills.</td>
    </tr>
    @endforelse
  </tbody>
</table>
