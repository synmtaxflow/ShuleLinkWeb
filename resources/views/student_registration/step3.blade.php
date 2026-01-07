@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Progress Steps -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col"><div class="step-indicator"><div class="step-number">1</div><div class="step-title">Student Particulars</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">2</div><div class="step-title">Guardian Info</div></div></div>
                        <div class="col"><div class="step-indicator active"><div class="step-number">3</div><div class="step-title">Health Info</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">4</div><div class="step-title">Emergency Contact</div></div></div>
                        <div class="col"><div class="step-indicator"><div class="step-number">5</div><div class="step-title">Declaration</div></div></div>
                    </div>
                </div>
            </div>

            <!-- SECTION C: Health Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-heart-pulse"></i> SECTION C: Health Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('student.registration.store-step3') }}">
                        @csrf

                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle"></i> Please provide accurate health information about the student.
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><strong>General Health Condition</strong></label>
                            <textarea name="general_health_condition" class="form-control" rows="3"
                                placeholder="Describe the student's general health condition (e.g., excellent, good, fair)">{{ old('general_health_condition') }}</textarea>
                            <small class="text-muted">e.g., Generally healthy, prone to allergies, etc.</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="has_disability" id="has_disability" class="form-check-input"
                                    value="1" {{ old('has_disability') ? 'checked' : '' }} onchange="toggleDisability()">
                                <label class="form-check-label" for="has_disability">
                                    <strong>Does the student have any disability?</strong>
                                </label>
                            </div>
                        </div>

                        <div id="disabilityDetails" style="display: {{ old('has_disability') ? 'block' : 'none' }}; margin-bottom: 20px;">
                            <label class="form-label"><strong>Disability Details *</strong></label>
                            <textarea name="disability_details" class="form-control" rows="3"
                                placeholder="Describe the disability and any special accommodations needed">{{ old('disability_details') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="has_chronic_illness" id="has_chronic_illness" class="form-check-input"
                                    value="1" {{ old('has_chronic_illness') ? 'checked' : '' }} onchange="toggleChronicIllness()">
                                <label class="form-check-label" for="has_chronic_illness">
                                    <strong>Does the student have any chronic illness?</strong>
                                </label>
                            </div>
                        </div>

                        <div id="chronicIllnessDetails" style="display: {{ old('has_chronic_illness') ? 'block' : 'none' }}; margin-bottom: 20px;">
                            <label class="form-label"><strong>Chronic Illness Details *</strong></label>
                            <textarea name="chronic_illness_details" class="form-control" rows="3"
                                placeholder="Describe the chronic illness, medications, and any triggers to avoid">{{ old('chronic_illness_details') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><strong>Immunization/Vaccination Details</strong></label>
                            <textarea name="immunization_details" class="form-control" rows="3"
                                placeholder="List vaccinations received and any allergies to vaccines">{{ old('immunization_details') }}</textarea>
                            <small class="text-muted">e.g., DPT completed, Polio series, etc.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-arrow-right"></i> Next: Emergency Contact
                                </button>
                                <a href="{{ route('student.registration.step2') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                                <a href="{{ route('student.registration.cancel') }}" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-custom {
        background-color: #940000 !important;
    }

    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }

    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
    }

    .step-indicator {
        padding: 15px;
        border-radius: 8px;
        background-color: #f0f0f0;
    }

    .step-indicator.active {
        background-color: #940000;
        color: white;
    }

    .step-number {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .step-title {
        font-size: 12px;
        margin-top: 5px;
    }
</style>

<script>
function toggleDisability() {
    const checkbox = document.getElementById('has_disability');
    document.getElementById('disabilityDetails').style.display = checkbox.checked ? 'block' : 'none';
}

function toggleChronicIllness() {
    const checkbox = document.getElementById('has_chronic_illness');
    document.getElementById('chronicIllnessDetails').style.display = checkbox.checked ? 'block' : 'none';
}
</script>

@include('includes.footer')
