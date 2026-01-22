@if($permissions->count() === 0)
    <div class="alert alert-info">No permission requests found.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-primary-custom text-white">
                <tr>
                    <th>Requested At</th>
                    <th>Mode</th>
                    <th>Period</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                    <tr>
                        <td>{{ $permission->created_at ? $permission->created_at->format('d M Y H:i') : 'N/A' }}</td>
                        <td>{{ ucfirst($permission->time_mode) }}</td>
                        <td>
                            @if($permission->time_mode === 'days')
                                {{ $permission->start_date }} - {{ $permission->end_date }} ({{ $permission->days_count }} days)
                            @else
                                {{ $permission->start_time }} - {{ $permission->end_time }}
                            @endif
                        </td>
                        <td>{{ ucfirst($permission->reason_type) }}</td>
                        <td>
                            @if($permission->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($permission->status === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
