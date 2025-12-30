@include('includes.teacher_nav')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="bi bi-diagram-3"></i> Approval Chain Status - {{ $examination->exam_name ?? 'N/A' }}</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('teachersDashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <div class="approval-chain-container">
                @foreach($chain as $index => $step)
                    <div class="approval-step mb-4">
                        <div class="card {{ $step['is_teacher_step'] ? 'border-primary' : '' }}">
                            <div class="card-header {{ $step['is_teacher_step'] ? 'bg-primary text-white' : ($step['status'] === 'approved' ? 'bg-success text-white' : ($step['status'] === 'rejected' ? 'bg-danger text-white' : 'bg-secondary text-white')) }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            Step {{ $step['approval_order'] }}: {{ $step['role_name'] }}
                                            @if($step['is_teacher_step'])
                                                <span class="badge badge-light ml-2">Your Step</span>
                                            @endif
                                        </h5>
                                    </div>
                                    <div>
                                        @if($step['status'] === 'approved')
                                            <span class="badge badge-light"><i class="bi bi-check-circle"></i> Approved</span>
                                        @elseif($step['status'] === 'rejected')
                                            <span class="badge badge-light"><i class="bi bi-x-circle"></i> Rejected</span>
                                        @else
                                            <span class="badge badge-light"><i class="bi bi-clock"></i> Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($step['status'] === 'approved' && $step['approver'])
                                    <p><strong>Approved by:</strong> {{ $step['approver']['name'] }}</p>
                                    @if($step['approved_at'])
                                        <p><strong>Approved at:</strong> {{ \Carbon\Carbon::parse($step['approved_at'])->format('M d, Y H:i') }}</p>
                                    @endif
                                @elseif($step['status'] === 'rejected')
                                    @if($step['rejection_reason'])
                                        <div class="alert alert-danger">
                                            <strong>Rejection Reason:</strong> {{ $step['rejection_reason'] }}
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted">Waiting for approval...</p>
                                @endif
                                
                                @if($step['is_teacher_step'] && $step['status'] === 'pending')
                                    <a href="{{ route('approve_result', $examination->examID) }}" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Go to Approve
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        @if($index < count($chain) - 1)
                            <div class="text-center my-2">
                                <i class="bi bi-arrow-down text-muted" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@include('includes.footer')

<style>
.approval-chain-container {
    max-width: 800px;
    margin: 0 auto;
}
.approval-step {
    position: relative;
}
</style>







