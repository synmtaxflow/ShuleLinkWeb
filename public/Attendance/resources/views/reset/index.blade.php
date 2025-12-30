@extends('layouts.vali')

@section('title', 'Reset Database - Fresh Start')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <div>
                    <h3 class="tile-title">Fresh Start - Reset Database</h3>
                    <p class="text-muted mb-0">Delete all records and start with a clean database</p>
                </div>
                <div class="d-flex flex-wrap">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary mb-2 mr-2">← Back to Dashboard</a>
                    <a href="{{ route('users.index') }}" class="btn btn-primary mb-2 mr-2">👥 Manage Users</a>
                    <a href="{{ route('attendances.index') }}" class="btn btn-primary mb-2">📋 View Attendance</a>
                </div>
            </div>

            {{-- Flash messages (use global layout styling) --}}
            @if(session('success'))
                <div class="alert alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    ✗ {{ session('error') }}
                </div>
            @endif

            {{-- Current statistics as Vali widgets --}}
            <div class="row mb-3">
                <div class="col-md-6 col-sm-12">
                    <div class="widget-small primary coloured-icon">
                        <i class="icon fa fa-users fa-3x"></i>
                        <div class="info">
                            <h4>Total Users</h4>
                            <p>
                                <b>{{ $stats['total_users'] }}</b><br>
                                <small>{{ $stats['registered_users'] }} registered on device</small>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="widget-small info coloured-icon">
                        <i class="icon fa fa-clock-o fa-3x"></i>
                        <div class="info">
                            <h4>Total Attendance Records</h4>
                            <p>
                                <b>{{ $stats['total_attendances'] }}</b><br>
                                <small>{{ $stats['today_attendances'] }} today</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Warning panel --}}
            <div class="alert alert-warning" role="alert">
                <h4 class="alert-heading">⚠️ Warning: This Action Cannot Be Undone!</h4>
                <p>
                    Deleting records will permanently remove all data from the database.
                    Make sure you have backed up any important data before proceeding.
                </p>
            </div>

            {{-- Reset options in Bootstrap columns --}}
            <div class="row">
                {{-- Delete Attendance Only --}}
                <div class="col-md-4">
                    <div class="tile">
                        <h4 class="tile-title">🗑️ Delete Attendance Records Only</h4>
                        <p class="text-muted">
                            This will delete all attendance records but keep all users in the database.
                        </p>
                        <div class="alert alert-info">
                            <strong>Will delete:</strong> {{ $stats['total_attendances'] }} attendance record(s)<br>
                            <strong>Will keep:</strong> {{ $stats['total_users'] }} user(s)
                        </div>
                        <form method="POST" action="{{ route('reset.delete-attendances') }}" onsubmit="return confirmDelete('{{ $stats['total_attendances'] }} attendance records')">
                            @csrf
                            <input type="hidden" name="confirm" value="DELETE_ALL">
                            <button type="submit" class="btn btn-danger btn-block">
                                🗑️ Delete All Attendance Records
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Delete Users Only --}}
                <div class="col-md-4">
                    <div class="tile">
                        <h4 class="tile-title">👥 Delete Users Only</h4>
                        <p class="text-muted">
                            This will delete all users. Attendance records will also be deleted
                            (due to foreign key constraints).
                        </p>
                        <div class="alert alert-info">
                            <strong>Will delete:</strong> {{ $stats['total_users'] }} user(s) + {{ $stats['total_attendances'] }} attendance record(s)
                        </div>
                        <form method="POST" action="{{ route('reset.delete-users') }}" onsubmit="return confirmDelete('{{ $stats['total_users'] }} users and all their attendance records')">
                            @csrf
                            <input type="hidden" name="confirm" value="DELETE_ALL">
                            <button type="submit" class="btn btn-danger btn-block">
                                👥 Delete All Users
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Delete Everything --}}
                <div class="col-md-4">
                    <div class="tile">
                        <h4 class="tile-title text-danger">🔥 Complete Reset (Delete Everything)</h4>
                        <p class="text-muted">
                            This will delete ALL users and ALL attendance records. Complete fresh start.
                        </p>
                        <div class="alert alert-danger">
                            <strong>⚠️ Will delete:</strong><br>
                            • {{ $stats['total_users'] }} user(s)<br>
                            • {{ $stats['total_attendances'] }} attendance record(s)<br>
                            <strong>Everything will be gone!</strong>
                        </div>
                        <form method="POST" action="{{ route('reset.delete-all') }}" onsubmit="return confirmDeleteEverything()">
                            @csrf
                            <input type="hidden" name="confirm" value="DELETE_EVERYTHING">
                            <button type="submit" class="btn btn-danger btn-block font-weight-bold">
                                🔥 DELETE EVERYTHING - FRESH START
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function confirmDelete(item) {
    return confirm(`⚠️ Are you sure you want to delete ${item}?\n\nThis action CANNOT be undone!\n\nType "DELETE" to confirm.`) && 
           prompt('Type "DELETE" to confirm:') === 'DELETE';
}

function confirmDeleteEverything() {
    const confirm1 = confirm('⚠️ WARNING: This will delete EVERYTHING!\n\n• All users\n• All attendance records\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?');
    if (!confirm1) return false;
    
    const confirm2 = prompt('Type "DELETE EVERYTHING" (exactly) to confirm:');
    if (confirm2 !== 'DELETE EVERYTHING') {
        alert('Confirmation text did not match. Operation cancelled.');
        return false;
    }
    
    return true;
}
</script>
@endsection





