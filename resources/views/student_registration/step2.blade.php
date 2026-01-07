@include('includes.teacher_nav')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Progress Steps -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">1</div>
                                <div class="step-title">Student Particulars</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator active">
                                <div class="step-number">2</div>
                                <div class="step-title">Guardian Info</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">3</div>
                                <div class="step-title">Health Info</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">4</div>
                                <div class="step-title">Emergency Contact</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator">
                                <div class="step-number">5</div>
                                <div class="step-title">Declaration</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION B: Parent/Guardian Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> SECTION B: Parent/Guardian/Next of Kin Information</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6 class="text-primary-custom"><i class="bi bi-search"></i> Search for Existing Parent</h6>
                        <p class="text-muted small">Enter phone number to check if parent/guardian already exists in system</p>

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="phoneSearch" placeholder="Enter parent phone number"
                                value="{{ old('parent_phone') }}">
                            <button class="btn btn-primary-custom" type="button" id="searchBtn">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>

                        <div id="searchResult" style="display: none;">
                            <div id="existingParentInfo" class="alert alert-info">
                                <!-- Parent info will be displayed here -->
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('student.registration.store-step2') }}" id="parentForm">
                        @csrf
                        <input type="hidden" name="parent_id" id="parent_id">

                        <div id="newParentForm">
                            <h6 class="text-primary-custom mb-3"><i class="bi bi-plus-circle"></i> Enter New Parent/Guardian Details</h6>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label"><strong>First Name *</strong></label>
                                    <input type="text" name="parent_first_name" class="form-control @error('parent_first_name') is-invalid @enderror"
                                        value="{{ old('parent_first_name') }}" required>
                                    @error('parent_first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><strong>Middle Name</strong></label>
                                    <input type="text" name="parent_middle_name" class="form-control"
                                        value="{{ old('parent_middle_name') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><strong>Last Name *</strong></label>
                                    <input type="text" name="parent_last_name" class="form-control @error('parent_last_name') is-invalid @enderror"
                                        value="{{ old('parent_last_name') }}" required>
                                    @error('parent_last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Phone Number *</strong></label>
                                    <input type="text" name="parent_phone" class="form-control @error('parent_phone') is-invalid @enderror"
                                        value="{{ old('parent_phone') }}" required>
                                    @error('parent_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Relationship to Student *</strong></label>
                                    <select name="parent_relationship" class="form-select @error('parent_relationship') is-invalid @enderror" required>
                                        <option value="">Select Relationship</option>
                                        <option value="Parent" {{ old('parent_relationship') == 'Parent' ? 'selected' : '' }}>Parent</option>
                                        <option value="Guardian" {{ old('parent_relationship') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="Next of Kin" {{ old('parent_relationship') == 'Next of Kin' ? 'selected' : '' }}>Next of Kin</option>
                                        <option value="Other" {{ old('parent_relationship') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('parent_relationship')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Occupation</strong></label>
                                    <input type="text" name="parent_occupation" class="form-control"
                                        value="{{ old('parent_occupation') }}" placeholder="e.g., Engineer, Teacher">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Email</strong></label>
                                    <input type="email" name="parent_email" class="form-control"
                                        value="{{ old('parent_email') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label"><strong>Residential Address</strong></label>
                                    <textarea name="parent_address" class="form-control" rows="3"
                                        placeholder="Enter full residential address">{{ old('parent_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-arrow-right"></i> Next: Health Information
                                </button>
                                <a href="{{ route('student.registration.step1') }}" class="btn btn-secondary">
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

    .text-primary-custom {
        color: #940000 !important;
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
        transition: all 0.3s ease;
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
document.getElementById('searchBtn').addEventListener('click', function() {
    const phone = document.getElementById('phoneSearch').value;

    if (!phone) {
        alert('Please enter a phone number');
        return;
    }

    fetch('{{ route("student.registration.search-parent") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Parent found - show their details
            const parent = data.parent;
            let html = `
                <div class="alert alert-success">
                    <h6><i class="bi bi-check-circle"></i> Parent/Guardian Found!</h6>
                    <p><strong>Name:</strong> ${parent.first_name} ${parent.middle_name || ''} ${parent.last_name}</p>
                    <p><strong>Phone:</strong> ${parent.phone}</p>
                    <p><strong>Occupation:</strong> ${parent.occupation || 'N/A'}</p>
                    <p><strong>Address:</strong> ${parent.address || 'N/A'}</p>
                    <button type="button" class="btn btn-sm btn-success" onclick="useExistingParent(${parent.parentID})">
                        <i class="bi bi-check"></i> Use This Parent
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showNewParentForm()">
                        <i class="bi bi-plus"></i> Add New Parent
                    </button>
                </div>
            `;
            document.getElementById('existingParentInfo').innerHTML = html;
            document.getElementById('searchResult').style.display = 'block';
            document.getElementById('newParentForm').style.display = 'none';
        } else {
            // Parent not found
            document.getElementById('searchResult').style.display = 'block';
            document.getElementById('existingParentInfo').innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> ${data.message}
                </div>
            `;
            document.getElementById('newParentForm').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while searching');
    });
});

function useExistingParent(parentID) {
    document.getElementById('parent_id').value = parentID;
    document.getElementById('newParentForm').style.display = 'none';
    document.getElementById('parentForm').submit();
}

function showNewParentForm() {
    document.getElementById('parent_id').value = '';
    document.getElementById('newParentForm').style.display = 'block';
}
</script>

@include('includes.footer')
