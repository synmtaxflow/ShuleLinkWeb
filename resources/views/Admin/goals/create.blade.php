@include('includes.Admin_nav')

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header" style="background: linear-gradient(45deg, #940000, #c30000); color: white; padding: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fa fa-bullseye"></i> Create New School Goal</h4>
                            <a href="{{ route('admin.goals.index') }}" class="btn btn-sm btn-light" style="border-radius: 20px; color: #940000;">
                                <i class="fa fa-list"></i> View All Goals
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4" style="background-color: #fcfcfc;">
                        <form action="{{ route('admin.goals.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-4">
                                <label for="goal_name" class="form-label" style="font-weight: 600; color: #444;">Goal Name <span class="text-danger">*</span></label>
                                <input type="text" name="goal_name" id="goal_name" class="form-control" placeholder="e.g. Improve National Exam Performance" required style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                                <small class="text-muted">Enter a descriptive title for this objective.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="target_percentage" class="form-label" style="font-weight: 600; color: #444;">Target Percentage (%) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="target_percentage" id="target_percentage" class="form-control" min="1" max="100" placeholder="e.g. 95" required style="border-radius: 8px 0 0 8px; padding: 12px; border: 1px solid #ddd;">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="background-color: #eee; border-radius: 0 8px 8px 0;">%</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">The weight of this goal in the overall school progress.</small>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="deadline" class="form-label" style="font-weight: 600; color: #444;">Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="deadline" id="deadline" class="form-control" required style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                                    <small class="text-muted">When should this goal be achieved?</small>
                                </div>
                            </div>

                            <div class="text-right pt-3">
                                <button type="reset" class="btn btn-outline-secondary px-4 mr-2" style="border-radius: 25px;">Clear</button>
                                <button type="submit" class="btn btn-primary px-5" style="border-radius: 25px; background-color: #940000; border: none; box-shadow: 0 4px 10px rgba(148,0,0,0.3);">
                                    <i class="fa fa-save"></i> Save Goal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')
