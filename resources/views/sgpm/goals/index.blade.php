@php
    $userType = $userType ?? Session::get('user_type');
@endphp

@if($userType == 'Admin')
    @include('includes.Admin_nav')
@elseif($userType == 'Teacher')
    @include('includes.teacher_nav')
@else
    @include('includes.staff_nav')
@endif

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0" style="color: #940000; font-weight: 700;">Strategic Goals (Board Level)</h4>
                            <p class="text-muted small mb-0">Define high-level institutional goals and KPIs</p>
                        </div>
                        <button class="btn btn-primary-custom" data-toggle="modal" data-target="#addGoalModal">
                            <i class="fa fa-plus"></i> Create Strategic Goal
                        </button>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="row">
                            @forelse($goals as $goal)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid {{ $goal->status == 'Draft' ? '#ffc107' : ($goal->status == 'Published' ? '#0dcaf0' : '#198754') }} !important;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="fw-bold mb-0">{{ $goal->title }}</h5>
                                                <span class="badge {{ $goal->status == 'Draft' ? 'bg-warning' : ($goal->status == 'Published' ? 'bg-info' : 'bg-success') }}">
                                                    {{ $goal->status }}
                                                </span>
                                            </div>
                                            <p class="text-muted small mb-3">{{ Str::limit($goal->description, 100) }}</p>
                                            
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <div class="small fw-bold text-muted">KPI</div>
                                                    <div class="small">{{ $goal->kpi }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="small fw-bold text-muted">Target</div>
                                                    <div class="small">{{ $goal->target_value }}</div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                                <div class="text-muted small">
                                                    <i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($goal->timeline_date)->format('M d, Y') }}
                                                </div>
                                                <div class="btn-group">
                                                    @if($goal->status == 'Draft')
                                                        <form action="{{ route('sgpm.goals.publish', $goal->strategic_goalID) }}" method="POST" class="d-inline ajax-form">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-info">Publish</button>
                                                        </form>
                                                        <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#editGoalModal{{ $goal->strategic_goalID }}">Edit</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Edit Goal Modal -->
                                    <div class="modal fade" id="editGoalModal{{ $goal->strategic_goalID }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content border-0" style="border-radius: 15px;">
                                                <form action="{{ route('sgpm.goals.update', $goal->strategic_goalID) }}" method="POST" enctype="multipart/form-data" class="ajax-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Strategic Goal</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12 mb-3">
                                                                <label class="form-label">Goal Title</label>
                                                                <input type="text" name="title" class="form-control" value="{{ $goal->title }}" required>
                                                            </div>
                                                            <div class="col-md-12 mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea name="description" class="form-control" rows="3">{{ $goal->description }}</textarea>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Measurable KPI</label>
                                                                <input type="text" name="kpi" class="form-control" value="{{ $goal->kpi }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Target Value</label>
                                                                <input type="text" name="target_value" class="form-control" value="{{ $goal->target_value }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Timeline Date</label>
                                                                <input type="date" name="timeline_date" class="form-control" value="{{ $goal->timeline_date }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Supporting Doc (Optional)</label>
                                                                <input type="file" name="supporting_document" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary-custom">Update Goal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa fa-flag-checkered fa-3x text-light mb-3"></i>
                                    <p class="text-muted">No strategic goals defined yet. Start defining your vision.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <form action="{{ route('sgpm.goals.store') }}" method="POST" enctype="multipart/form-data" class="ajax-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Strategic Goal</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Goal Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Achieve 100% Student Digital Literacy" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Measurable KPI</label>
                            <input type="text" name="kpi" class="form-control" placeholder="e.g., % of students with certificates" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Value</label>
                            <input type="text" name="target_value" class="form-control" placeholder="e.g., 95%" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Timeline / Deadline</label>
                            <input type="date" name="timeline_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supporting Document (Optional)</label>
                            <input type="file" name="supporting_document" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')
