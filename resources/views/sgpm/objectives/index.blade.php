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
                            <h4 class="card-title mb-0" style="color: #940000; font-weight: 700;">Departmental Objectives</h4>
                            <p class="text-muted small mb-0">Break down strategic goals into departmental execution targets</p>
                        </div>
                        @if($userType == 'Admin')
                        <button class="btn btn-primary-custom" data-toggle="modal" data-target="#addObjModal">
                            <i class="fa fa-plus"></i> Assign Objective
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Strategic Goal</th>
                                        <th>Department</th>
                                        <th>KPI & Target</th>
                                        <th>Budget</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($objectives as $obj)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $obj->strategicGoal->title }}</div>
                                                <div class="small text-muted">Goal Timeline: {{ $obj->strategicGoal->timeline_date }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $obj->department->department_name }}</span>
                                            </td>
                                            <td>
                                                <div class="small fw-bold">{{ $obj->kpi }}</div>
                                                <div class="badge bg-success">{{ $obj->target_value }}</div>
                                            </td>
                                            <td>{{ $obj->budget ? number_format($obj->budget, 2) : 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $obj->status == 'Not Started' ? 'bg-secondary' : ($obj->status == 'In Progress' ? 'bg-primary' : 'bg-success') }}">
                                                    {{ $obj->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addActionPlanModal{{ $obj->objectiveID }}" title="Add Action Plan">
                                                    <i class="fa fa-list-tasks"></i> Plan
                                                </button>
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No objectives assigned to departments.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Objective Modal -->
<div class="modal fade" id="addObjModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <form action="{{ route('sgpm.objectives.store') }}" method="POST" class="ajax-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Objective to Department</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Strategic Goal</label>
                        <select name="strategic_goalID" class="form-select" required>
                            <option value="">-- Select Goal --</option>
                            @foreach($goals as $goal)
                                <option value="{{ $goal->strategic_goalID }}">{{ $goal->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="departmentID" class="form-select" required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->departmentID }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">KPI for this Department</label>
                        <input type="text" name="kpi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Value</label>
                        <input type="text" name="target_value" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allocated Budget (Optional)</label>
                        <input type="number" name="budget" class="form-control" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Assign Objective</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($objectives as $obj)
<!-- Add Action Plan Modal -->
<div class="modal fade" id="addActionPlanModal{{ $obj->objectiveID }}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="border-radius: 15px;">
            <form action="{{ route('sgpm.objectives.storeActionPlan') }}" method="POST" class="ajax-form">
                @csrf
                <input type="hidden" name="objectiveID" value="{{ $obj->objectiveID }}">
                <div class="modal-header">
                    <h5 class="modal-title">Create Action Plan for {{ $obj->department->department_name }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action Plan Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Milestones</label>
                        <textarea name="milestones" class="form-control" rows="3" placeholder="Step 1, Step 2..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" name="deadline" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@include('includes.footer')
