<!-- Student Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 95vw;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: white; border-bottom: 1px solid #e9ecef; color: #212529;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-check"></i> Student Registration Form
                </h5>
                <button type="button" class="btn-close btn-close-white" id="registrationModalCloseBtn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Selected Class Display -->
                <div class="alert alert-info mb-3">
                    <strong>Selected Class:</strong> <span id="selectedSubclassName" class="fw-bold">Loading...</span>
                </div>

                <!-- Progress Indicator -->
                <div class="mb-4">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 20%; background-color: #940000;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="mt-2 text-center text-muted small">
                        <span id="stepIndicator">Step 1 of 5</span>
                    </div>
                </div>

                <!-- Form Container -->
                <form id="registrationForm" method="POST">
                    @csrf
                    <!-- Hidden field to store selected subclass ID -->
                    <input type="hidden" id="selectedSubclassID" name="subclassID" value="">
                    <!-- Hidden field to store selected parent ID when found -->
                    <input type="hidden" id="parentIdField" name="parent_id" value="">

                    <!-- Step 1: Student Particulars -->
                    <div id="step1" class="step-content">
                        <h6 class="mb-3 fw-bold" style="color: #212529;">
                            <i class="bi bi-person-lines-fill"></i> Student Particulars
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Admission Number (Auto)</label>
                                <input type="text" class="form-control" id="admissionNumber" name="admission_number" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" id="reg_first_name" required>
                                <small class="text-danger d-none error-first_name"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" id="reg_middle_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" id="reg_last_name" required>
                                <small class="text-danger d-none error-last_name"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" name="gender" id="reg_gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <small class="text-danger d-none error-gender"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                                <small class="text-danger d-none error-date_of_birth"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Age (Auto-calculated)</label>
                                <input type="text" class="form-control" id="ageDisplay" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Birth Certificate No.</label>
                                <input type="text" class="form-control" name="birth_certificate_number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Religion</label>
                                <input type="text" class="form-control" name="religion" placeholder="e.g., Christian, Muslim">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nationality</label>
                                <input type="text" class="form-control" name="nationality" placeholder="e.g., Kenyan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Student Photo</label>
                                <input type="file" class="form-control" name="student_photo" accept="image/*">
                                <small class="text-muted">Max 2MB (JPEG, PNG)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Parent/Guardian -->
                    <div id="step2" class="step-content d-none">
                        <h6 class="mb-3 fw-bold" style="color: #212529;">
                            <i class="bi bi-people-fill"></i> Parent/Guardian Information
                        </h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parent Phone <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">255</span>
                                <input type="text" class="form-control" id="parentPhone" name="parent_phone" placeholder="7XXXXXXXX" pattern="[0-9]{9}" required>
                                <button class="btn btn-outline-secondary" type="button" id="searchParentBtn">Search</button>
                            </div>
                            <small class="text-muted d-block mt-1">Format: 7XXXXXXXX (9 digits after 255)</small>
                            <small class="text-danger d-none error-parent_phone"></small>
                            <div id="phoneExistsError" class="d-none mt-2">
                                <!-- Message will be populated dynamically -->
                            </div>
                        </div>

                        <!-- Parent Not Found Warning -->
                        <div id="parentNotFoundWarning" class="alert alert-warning d-none mb-3">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Parent Not Found!</strong> No parent found with this phone number. Please continue to register a new parent below.
                        </div>

                        <!-- Found Parent Display -->
                        <div id="foundParentDiv" class="d-none mb-3 p-3 border rounded" style="background-color: #f5f5f5; border-color: #e9ecef;">
                            <h6 class="mb-2" style="color: #212529;">Found Parent:</h6>
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <img id="foundParentImage" src="" alt="Parent Photo" style="width: 100%; max-width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                                </div>
                                <div class="col-md-9">
                                    <p class="mb-1"><strong>Name:</strong> <span id="foundParentName"></span></p>
                                    <p class="mb-1"><strong>Phone:</strong> <span id="foundParentPhone"></span></p>
                                    <p class="mb-1"><strong>Relationship:</strong> <span id="foundParentRelationship"></span></p>
                                    <p class="mb-2"><strong>Email:</strong> <span id="foundParentEmail"></span></p>
                                    <button type="button" class="btn btn-sm btn-success" id="useFoundParentBtn">Use This Parent</button>
                                    <button type="button" class="btn btn-sm btn-secondary" id="cancelFoundParentBtn">Enter Different Parent</button>
                                </div>
                            </div>
                        </div>

                        <!-- New Parent Form -->
                        <div id="newParentDiv" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="parent_first_name">
                                <small class="text-danger d-none error-parent_first_name"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Middle Name</label>
                                <input type="text" class="form-control" name="parent_middle_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="parent_last_name">
                                <small class="text-danger d-none error-parent_last_name"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="parent_relationship" placeholder="e.g., Father, Mother">
                                <small class="text-danger d-none error-parent_relationship"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" class="form-control" name="parent_occupation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="parent_email">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <textarea class="form-control" name="parent_address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Parent Photo</label>
                                <input type="file" class="form-control" name="parent_photo" accept="image/*">
                                <small class="text-muted">Max 2MB (JPEG, PNG)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Health Information -->
                    <div id="step3" class="step-content d-none">
                        <h6 class="mb-3 fw-bold" style="color: #212529;">
                            <i class="bi bi-heart-pulse"></i> Health Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">General Health Condition</label>
                                <textarea class="form-control" name="general_health_condition" rows="2" placeholder="Describe any special health conditions"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_disability" id="hasDisability">
                                    <label class="form-check-label fw-bold" for="hasDisability">
                                        Student has disability
                                    </label>
                                </div>
                                <div id="disabilityDetailsDiv" class="d-none mt-2">
                                    <textarea class="form-control" name="disability_details" rows="2" placeholder="Describe the disability"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_chronic_illness" id="hasChronicIllness">
                                    <label class="form-check-label fw-bold" for="hasChronicIllness">
                                        Student has chronic illness
                                    </label>
                                </div>
                                <div id="chronicDetailsDiv" class="d-none mt-2">
                                    <textarea class="form-control" name="chronic_illness_details" rows="2" placeholder="Describe the chronic illness"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Immunization Details</label>
                                <textarea class="form-control" name="immunization_details" rows="2" placeholder="e.g., Fully vaccinated, pending, allergies to vaccines"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Emergency Contact -->
                    <div id="step4" class="step-content d-none">
                        <h6 class="mb-3 fw-bold" style="color: #212529;">
                            <i class="bi bi-exclamation-triangle"></i> Emergency Contact
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="emergency_contact_name" required>
                                <small class="text-danger d-none error-emergency_contact_name"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="emergency_contact_relationship" placeholder="e.g., Uncle, Aunt, Friend" required>
                                <small class="text-danger d-none error-emergency_contact_relationship"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">255</span>
                                    <input type="tel" class="form-control" name="emergency_contact_phone" placeholder="7XXXXXXXX" pattern="[0-9]{9}" required>
                                </div>
                                <small class="text-danger d-none error-emergency_contact_phone"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review & Declaration -->
                    <div id="step5" class="step-content d-none">
                        <h6 class="mb-3 fw-bold" style="color: #212529;">
                            <i class="bi bi-file-earmark-check"></i> Review & Declaration
                        </h6>

                        <!-- Review Section - Display All Entered Information -->
                        <div id="reviewSection" class="mb-4" style="max-height: 500px; overflow-y: auto;">
                            <!-- Student Particulars Review -->
                            <div class="mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                                <h6 class="fw-bold mb-2" style="color: #940000;">
                                    <i class="bi bi-person-check"></i> Student Particulars
                                </h6>
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <img id="reviewStudentPhoto" src="" alt="Student Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef;" onerror="this.onerror=null; this.src=this.dataset.placeholder;">
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Class:</strong> <span id="reviewClass">-</span></p>
                                                <p class="mb-1"><strong>Admission Number:</strong> <span id="reviewAdmissionNumber">-</span></p>
                                                <p class="mb-1"><strong>Name:</strong> <span id="reviewStudentName">-</span></p>
                                                <p class="mb-1"><strong>Gender:</strong> <span id="reviewGender">-</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Date of Birth:</strong> <span id="reviewDOB">-</span></p>
                                                <p class="mb-1"><strong>Birth Certificate:</strong> <span id="reviewBirthCert">-</span></p>
                                                <p class="mb-1"><strong>Religion:</strong> <span id="reviewReligion">-</span></p>
                                                <p class="mb-1"><strong>Nationality:</strong> <span id="reviewNationality">-</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Parent/Guardian Information Review -->
                            <div class="mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                                <h6 class="fw-bold mb-2" style="color: #940000;">
                                    <i class="bi bi-people-fill"></i> Parent/Guardian Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-3 text-center mb-3">
                                        <img id="reviewParentPhoto" src="" alt="Parent Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef;" onerror="this.onerror=null; this.src=this.dataset.placeholder;">
                                    </div>
                                    <div class="col-md-9">
                                        <div id="reviewParentInfo">
                                            <!-- Will be populated dynamically -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Health Information Review -->
                            <div class="mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                                <h6 class="fw-bold mb-2" style="color: #940000;">
                                    <i class="bi bi-heart-pulse"></i> Health Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>General Health:</strong> <span id="reviewGeneralHealth">-</span></p>
                                        <p class="mb-1"><strong>Has Disability:</strong> <span id="reviewDisability">-</span></p>
                                        <p class="mb-1" id="reviewDisabilityDetails" style="display: none;"><strong>Disability Details:</strong> <span id="reviewDisabilityDetailsText">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Has Chronic Illness:</strong> <span id="reviewChronicIllness">-</span></p>
                                        <p class="mb-1" id="reviewChronicDetails" style="display: none;"><strong>Chronic Illness Details:</strong> <span id="reviewChronicDetailsText">-</span></p>
                                        <p class="mb-1"><strong>Immunization:</strong> <span id="reviewImmunization">-</span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Review -->
                            <div class="mb-3 p-3 border rounded" style="background-color: #f8f9fa;">
                                <h6 class="fw-bold mb-2" style="color: #940000;">
                                    <i class="bi bi-telephone-fill"></i> Emergency Contact
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Name:</strong> <span id="reviewEmergencyName">-</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Relationship:</strong> <span id="reviewEmergencyRelationship">-</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Phone:</strong> <span id="reviewEmergencyPhone">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Declaration Section -->
                        <div class="mb-4 p-3 border rounded" style="background-color: #f5f5f5; border-color: #e9ecef;">
                            <h6 class="fw-bold mb-2" style="color: #212529;">
                                <i class="bi bi-file-check"></i> Parent/Guardian Declaration
                            </h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="parent_declaration" id="parentDeclaration" required>
                                <label class="form-check-label" for="parentDeclaration">
                                    I declare that all information provided in this registration form is true and accurate to the best of my knowledge.
                                </label>
                                <small class="text-danger d-none error-parent_declaration"></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="declaration_date" required>
                                <small class="text-danger d-none error-declaration_date"></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Additional Notes (Optional)</label>
                                <textarea class="form-control" name="parent_declaration_notes" rows="2" placeholder="Any additional information..."></textarea>
                            </div>
                        </div>

                        <!-- Official Use Only -->
                        <div class="p-3 border rounded" style="background-color: #f5f5f5; border-color: #e9ecef;">
                            <h6 class="fw-bold mb-2" style="color: #212529;">Official Use Only</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Registering Officer Name</label>
                                    <input type="text" class="form-control" name="registering_officer_name" placeholder="Staff name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Title</label>
                                    <input type="text" class="form-control" name="registering_officer_title" placeholder="e.g., Registrar, Deputy Principal">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div id="errorAlert" class="alert alert-danger mt-3 d-none" role="alert"></div>

                    <!-- Summary for Review -->
                    <div id="summaryDiv" class="d-none mt-3 p-3 border rounded" style="background-color: #f5f5f5; border-color: #e9ecef;">
                        <h6 class="fw-bold mb-2" style="color: #212529;">Registration Summary</h6>
                        <div id="summaryContent"></div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer with Navigation -->
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">
                    <i class="bi bi-chevron-left"></i> Previous
                </button>
                <button type="button" class="btn btn-secondary" id="registrationModalCancelBtn" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>
                <button type="button" class="btn" id="nextBtn" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef;">
                    Next <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn" id="submitBtn" style="background-color: #f5f5f5; color: #212529; border: 1px solid #e9ecef; display:none;">
                    <i class="bi bi-check-circle"></i> Submit Registration
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 5;
    let selectedParentId = null;
    let schoolNumber = '{{ session("schoolID") ?? "SCH" }}';
    let registrationNumber = '{{ optional(\App\Models\School::find(session("schoolID")))->registration_number ?? session("schoolID") ?? "REG" }}';

    const stepContents = {
        1: document.getElementById('step1'),
        2: document.getElementById('step2'),
        3: document.getElementById('step3'),
        4: document.getElementById('step4'),
        5: document.getElementById('step5')
    };

    // Function to populate review section with all entered data
    function populateReviewSection() {
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        const data = {};
        
        // Collect all form values
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Student Particulars
        const subclassName = document.getElementById('selectedSubclassName').textContent;
        document.getElementById('reviewClass').textContent = subclassName || 'Not selected';
        document.getElementById('reviewAdmissionNumber').textContent = data.admission_number || 'Auto-generated';
        document.getElementById('reviewStudentName').textContent = 
            (data.first_name || '') + ' ' + (data.middle_name || '') + ' ' + (data.last_name || '');
        document.getElementById('reviewGender').textContent = data.gender || 'N/A';
        document.getElementById('reviewDOB').textContent = data.date_of_birth || 'N/A';
        document.getElementById('reviewBirthCert').textContent = data.birth_certificate_number || 'N/A';
        document.getElementById('reviewReligion').textContent = data.religion || 'N/A';
        document.getElementById('reviewNationality').textContent = data.nationality || 'N/A';
        
        // Student Photo - get from file input or use placeholder based on gender
        const studentPhotoInput = document.querySelector('input[name="student_photo"]');
        const studentPhotoImg = document.getElementById('reviewStudentPhoto');
        const studentGender = data.gender || 'Male';
        const studentPlaceholder = studentGender === 'Female' 
            ? '{{ asset("images/female.png") }}' 
            : '{{ asset("images/male.png") }}';
        
        if (studentPhotoInput && studentPhotoInput.files && studentPhotoInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                studentPhotoImg.src = e.target.result;
            };
            reader.readAsDataURL(studentPhotoInput.files[0]);
        } else {
            studentPhotoImg.src = studentPlaceholder;
        }
        studentPhotoImg.dataset.placeholder = studentPlaceholder;
        
        // Parent/Guardian Information
        const parentId = data.parent_id;
        const reviewParentInfo = document.getElementById('reviewParentInfo');
        const parentPhotoImg = document.getElementById('reviewParentPhoto');
        
        if (parentId) {
            // Using existing parent
            const foundParentName = document.getElementById('foundParentName').textContent;
            const foundParentPhone = document.getElementById('foundParentPhone').textContent;
            const foundParentRelationship = document.getElementById('foundParentRelationship').textContent;
            const foundParentEmail = document.getElementById('foundParentEmail').textContent;
            
            // Get parent image from found parent div
            const foundParentImage = document.getElementById('foundParentImage');
            if (foundParentImage && foundParentImage.src && !foundParentImage.src.includes('male.png') && !foundParentImage.src.includes('female.png')) {
                parentPhotoImg.src = foundParentImage.src;
            } else {
                // Use placeholder based on parent gender (get from search result or default to male)
                // Try to get gender from the search result data stored
                const parentGender = foundParentImage ? (foundParentImage.dataset.gender || null) : null;
                const parentPlaceholder = parentGender === 'Female' 
                    ? '{{ asset("images/female.png") }}' 
                    : '{{ asset("images/male.png") }}';
                parentPhotoImg.src = parentPlaceholder;
            }
            parentPhotoImg.dataset.placeholder = parentPhotoImg.src.includes('male.png') || parentPhotoImg.src.includes('female.png') 
                ? parentPhotoImg.src 
                : (parentPhotoImg.src.includes('userImages') ? '{{ asset("images/male.png") }}' : '{{ asset("images/male.png") }}');
            
            reviewParentInfo.innerHTML = `
                <p class="mb-1"><strong>Using Existing Parent:</strong></p>
                <p class="mb-1"><strong>Name:</strong> ${foundParentName}</p>
                <p class="mb-1"><strong>Phone:</strong> ${foundParentPhone}</p>
                <p class="mb-1"><strong>Relationship:</strong> ${foundParentRelationship}</p>
                <p class="mb-1"><strong>Email:</strong> ${foundParentEmail}</p>
            `;
        } else {
            // New parent - check if photo was uploaded
            const parentPhotoInput = document.querySelector('input[name="parent_photo"]');
            const parentGender = data.parent_gender || 'Male';
            const parentPlaceholder = parentGender === 'Female' 
                ? '{{ asset("images/female.png") }}' 
                : '{{ asset("images/male.png") }}';
            
            if (parentPhotoInput && parentPhotoInput.files && parentPhotoInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    parentPhotoImg.src = e.target.result;
                };
                reader.readAsDataURL(parentPhotoInput.files[0]);
            } else {
                parentPhotoImg.src = parentPlaceholder;
            }
            parentPhotoImg.dataset.placeholder = parentPlaceholder;
            
            reviewParentInfo.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Name:</strong> ${(data.parent_first_name || '') + ' ' + (data.parent_middle_name || '') + ' ' + (data.parent_last_name || '')}</p>
                        <p class="mb-1"><strong>Phone:</strong> ${data.parent_phone || 'N/A'}</p>
                        <p class="mb-1"><strong>Relationship:</strong> ${data.parent_relationship || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Occupation:</strong> ${data.parent_occupation || 'N/A'}</p>
                        <p class="mb-1"><strong>Email:</strong> ${data.parent_email || 'N/A'}</p>
                        <p class="mb-1"><strong>Address:</strong> ${data.parent_address || 'N/A'}</p>
                    </div>
                </div>
            `;
        }
        
        // Health Information
        document.getElementById('reviewGeneralHealth').textContent = data.general_health_condition || 'N/A';
        
        const hasDisability = document.querySelector('input[name="has_disability"]:checked');
        if (hasDisability) {
            document.getElementById('reviewDisability').textContent = 'Yes';
            document.getElementById('reviewDisabilityDetails').style.display = 'block';
            document.getElementById('reviewDisabilityDetailsText').textContent = data.disability_details || 'N/A';
        } else {
            document.getElementById('reviewDisability').textContent = 'No';
            document.getElementById('reviewDisabilityDetails').style.display = 'none';
        }
        
        const hasChronicIllness = document.querySelector('input[name="has_chronic_illness"]:checked');
        if (hasChronicIllness) {
            document.getElementById('reviewChronicIllness').textContent = 'Yes';
            document.getElementById('reviewChronicDetails').style.display = 'block';
            document.getElementById('reviewChronicDetailsText').textContent = data.chronic_illness_details || 'N/A';
        } else {
            document.getElementById('reviewChronicIllness').textContent = 'No';
            document.getElementById('reviewChronicDetails').style.display = 'none';
        }
        
        document.getElementById('reviewImmunization').textContent = data.immunization_details || 'N/A';
        
        // Emergency Contact
        document.getElementById('reviewEmergencyName').textContent = data.emergency_contact_name || 'N/A';
        document.getElementById('reviewEmergencyRelationship').textContent = data.emergency_contact_relationship || 'N/A';
        document.getElementById('reviewEmergencyPhone').textContent = data.emergency_contact_phone || 'N/A';
    }

    const showStep = (step) => {
        // Hide all steps
        Object.values(stepContents).forEach(el => el.classList.add('d-none'));
        // Show current step
        stepContents[step].classList.remove('d-none');

        // If showing step 5, populate review section
        if (step === 5) {
            populateReviewSection();
        }

        // Update progress bar
        const progress = (step / totalSteps) * 100;
        const progressBar = document.getElementById('progressBar');
        progressBar.style.width = progress + '%';
        progressBar.style.backgroundColor = '#940000';
        document.getElementById('stepIndicator').textContent = `Step ${step} of ${totalSteps}`;

        // Update buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        prevBtn.style.display = step === 1 ? 'none' : 'inline-block';
        nextBtn.style.display = step === totalSteps ? 'none' : 'inline-block';
        submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
        // Label preview when moving to the last step
        nextBtn.innerHTML = step === totalSteps - 1
            ? 'Preview <i class="bi bi-eye"></i>'
            : 'Next <i class="bi bi-chevron-right"></i>';

        currentStep = step;
    };

    // If on preview (step 5), close acts like "Previous"
    const closeBtn = document.getElementById('registrationModalCloseBtn');
    const cancelBtn = document.getElementById('registrationModalCancelBtn');
    [closeBtn, cancelBtn].forEach(btn => {
        if (!btn) return;
        btn.addEventListener('click', (e) => {
            if (currentStep === totalSteps) {
                e.preventDefault();
                e.stopPropagation();
                showStep(currentStep - 1);
            }
        }, true);
    });

    // Generate admission number on form load
    function generateAdmissionNumber() {
        const year = new Date().getFullYear();
        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        const admNo = registrationNumber + '/' + random + '/' + year;
        document.getElementById('admissionNumber').value = admNo;
    }

    // Real-time age calculator
    document.getElementById('dateOfBirth').addEventListener('input', function() {
        if (!this.value) {
            document.getElementById('ageDisplay').value = '';
            return;
        }
        const dob = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        document.getElementById('ageDisplay').value = isNaN(age) ? '' : (age + ' years');
    });

    // Generate admission number on init
    generateAdmissionNumber();

    // Parent phone validation - force 255 prefix and validate format
    // Also check if phone number already exists
    let phoneCheckTimeout;
    
    // Parent phone input - user enters only 9 digits (255 prefix is shown in input-group-text)
    document.getElementById('parentPhone').addEventListener('input', function() {
        let val = this.value.trim();
        const phoneExistsError = document.getElementById('phoneExistsError');
        const errorSmall = document.querySelector('.error-parent_phone');
        
        // Clear previous timeout
        clearTimeout(phoneCheckTimeout);
        
        // Hide error messages initially
        phoneExistsError.classList.add('d-none');
        if (errorSmall) errorSmall.classList.add('d-none');
        this.classList.remove('is-invalid', 'is-warning');
        
        // User should only enter 9 digits (without 255 prefix since it's shown in input-group-text)
        // Remove any non-numeric characters and limit to 9 digits
        const digits = val.replace(/[^0-9]/g, '');
        if (digits.length <= 9) {
            this.value = digits;
        } else {
            this.value = digits.substring(0, 9);
        }
        val = this.value;
        
        // Validate format - should be 9 digits
        if (val.length > 0 && val.length !== 9) {
            // Still typing, don't show error yet
        } else if (val.length === 9) {
            // Valid format - check if phone exists
            phoneCheckTimeout = setTimeout(() => {
                const fullPhone = '255' + val;
                checkPhoneExists(fullPhone, this);
            }, 500);
        }
    });
    
    // Also check on blur
    document.getElementById('parentPhone').addEventListener('blur', function() {
        let val = this.value.trim();
        // User should only enter 9 digits (without 255 prefix)
        const digits = val.replace(/[^0-9]/g, '');
        if (digits.length <= 9) {
            this.value = digits;
        } else {
            this.value = digits.substring(0, 9);
        }
        val = this.value;
        if (val.length === 9) {
            const fullPhone = '255' + val;
            checkPhoneExists(fullPhone, this);
        }
    });
    
    // Emergency contact phone - user enters only 9 digits (255 prefix is shown in input-group-text)
    const emergencyPhoneInput = document.querySelector('input[name="emergency_contact_phone"]');
    
    if (emergencyPhoneInput) {
        emergencyPhoneInput.addEventListener('input', function() {
            let val = this.value.trim();
            // User should only enter 9 digits (without 255 prefix since it's shown in input-group-text)
            // Remove any non-numeric characters and limit to 9 digits
            const digits = val.replace(/[^0-9]/g, '');
            if (digits.length <= 9) {
                this.value = digits;
            } else {
                this.value = digits.substring(0, 9);
            }
        });
        
        emergencyPhoneInput.addEventListener('blur', function() {
            let val = this.value.trim();
            // User should only enter 9 digits (without 255 prefix)
            const digits = val.replace(/[^0-9]/g, '');
            if (digits.length <= 9) {
                this.value = digits;
            } else {
                this.value = digits.substring(0, 9);
            }
        });
    }
    
    // Global variable to track if phone exists in another school
    let phoneExistsInOtherSchool = false;
    
    // Function to check if phone number exists
    function checkPhoneExists(phone, inputElement) {
        // Don't check if parent is already selected
        if (selectedParentId) {
            phoneExistsInOtherSchool = false;
            return;
        }
        
        fetch('{{ route("student.registration.search-parent") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ phone: phone })
        })
        .then(async response => {
            const text = await response.text();
            try {
                return text ? JSON.parse(text) : {};
            } catch (err) {
                return { success: false };
            }
        })
        .then(data => {
            const phoneExistsError = document.getElementById('phoneExistsError');
            if (data.success && data.parent && data.in_current_school) {
                // Parent exists in current school - show danger alert as requested
                phoneExistsInOtherSchool = false;
                phoneExistsError.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Parent Already Exists in This School!</strong><br>Click "Search" to use this parent, or use a different phone number.';
                phoneExistsError.className = 'alert alert-danger mt-2';
                phoneExistsError.classList.remove('d-none');
                inputElement.classList.add('is-invalid');
                inputElement.classList.remove('is-warning');
            } else if (data.in_other_school) {
                // Phone exists in another school - show error and block form
                phoneExistsInOtherSchool = true;
                phoneExistsError.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> This phone number already exists in another school. Please try another number.';
                phoneExistsError.className = 'alert alert-danger d-none mt-2';
                phoneExistsError.classList.remove('d-none');
                inputElement.classList.add('is-invalid');
                inputElement.classList.remove('is-warning');
                // Set custom validity to block form submission
                inputElement.setCustomValidity('This phone number already exists in another school. Please try another number.');
            } else {
                // Parent doesn't exist - hide warning
                phoneExistsInOtherSchool = false;
                phoneExistsError.classList.add('d-none');
                inputElement.classList.remove('is-warning', 'is-invalid');
                inputElement.setCustomValidity('');
            }
        })
        .catch(err => {
            console.error('Error checking phone:', err);
            phoneExistsInOtherSchool = false;
        });
    }

    // Parent search functionality
    document.getElementById('searchParentBtn').addEventListener('click', function() {
        let phone = document.getElementById('parentPhone').value.trim();
        // Extract 9 digits (user enters only digits, 255 prefix is shown separately)
        phone = phone.replace(/[^0-9]/g, '');
        
        if (!phone || !/^[0-9]{9}$/.test(phone)) {
            alert('Please enter a valid phone number (9 digits after 255)');
            return;
        }
        const fullPhone = '255' + phone;

        fetch('{{ route("student.registration.search-parent") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ phone: fullPhone })
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = text ? JSON.parse(text) : {};
                if (!response.ok) {
                    console.error('Search parent HTTP error', response.status, text);
                    
                    // Show error in the phoneExistsError div instead of alert
                    const phoneExistsError = document.getElementById('phoneExistsError');
                    const phoneInput = document.getElementById('parentPhone');
                    let errorMessage = data.message || ('Server error: ' + response.status);
                    
                    if (phoneExistsError) {
                        phoneExistsError.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> ' + errorMessage;
                        phoneExistsError.className = 'alert alert-danger mt-2';
                        phoneExistsError.classList.remove('d-none');
                    }
                    if (phoneInput) {
                        phoneInput.classList.add('is-invalid');
                    }
                    
                    return { success: false, message: errorMessage };
                }
                return data;
            } catch (err) {
                console.error('Failed parsing JSON from search-parent:', err, text);
                
                // Show error in the phoneExistsError div instead of alert
                const phoneExistsError = document.getElementById('phoneExistsError');
                const phoneInput = document.getElementById('parentPhone');
                let errorMessage = 'Error searching for parent. Please try again.';
                
                // Try to extract error message from HTML response if JSON parsing failed
                if (text && text.includes('message')) {
                    try {
                        // Try to find error message in HTML
                        const match = text.match(/"message":"([^"]+)"/);
                        if (match && match[1]) {
                            errorMessage = match[1];
                        }
                    } catch (e) {
                        // Ignore
                    }
                }
                
                if (phoneExistsError) {
                    phoneExistsError.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> ' + errorMessage;
                    phoneExistsError.className = 'alert alert-danger mt-2';
                    phoneExistsError.classList.remove('d-none');
                }
                if (phoneInput) {
                    phoneInput.classList.add('is-invalid');
                }
                
                return { success: false, message: errorMessage };
            }
        })
        .then(data => {
            const phoneExistsError = document.getElementById('phoneExistsError');
            const parentNotFoundWarning = document.getElementById('parentNotFoundWarning');
            const phoneInput = document.getElementById('parentPhone');
            
            if (data.success && data.parent) {
                // Parent found - load the parent data properly
                selectedParentId = data.parent.parentID;
                document.getElementById('parentIdField').value = selectedParentId;
                
                // Set parent name, phone, relationship, email
                const fullName = (data.parent.first_name || '') + ' ' + (data.parent.middle_name || '') + ' ' + (data.parent.last_name || '');
                document.getElementById('foundParentName').textContent = fullName.trim();
                document.getElementById('foundParentPhone').textContent = data.parent.phone || fullPhone;
                document.getElementById('foundParentRelationship').textContent = data.parent.relationship_to_student || 'Parent/Guardian';
                document.getElementById('foundParentEmail').textContent = data.parent.email || '(not provided)';
                
                // Set parent image or placeholder based on stored image or gender
                const imgEl = document.getElementById('foundParentImage');
                const parentGender = data.parent.gender || null;
                
                // Store gender in dataset for later use in review section
                imgEl.dataset.gender = parentGender || 'Male';
                
                // Set image with error fallback
                if (data.parent.image && data.parent.image.length > 0) {
                    imgEl.src = data.parent.image;
                    // Add error handler for failed image loads
                    imgEl.onerror = function() {
                        // Use parent gender if available, otherwise default to male
                        const placeholder = (parentGender === 'Female') ? '/images/female.png' : '/images/male.png';
                        imgEl.src = placeholder;
                    };
                } else {
                    // Use parent gender if available, otherwise default to male
                    const placeholder = (parentGender === 'Female') ? '/images/female.png' : '/images/male.png';
                    imgEl.src = placeholder;
                }
                
                // Hide warnings/errors and show found parent
                phoneExistsError.classList.add('d-none');
                phoneExistsError.innerHTML = ''; // Clear error message
                parentNotFoundWarning.classList.add('d-none');
                phoneInput.classList.remove('is-warning', 'is-invalid');
                phoneInput.setCustomValidity('');
                document.getElementById('foundParentDiv').classList.remove('d-none');
                document.getElementById('newParentDiv').classList.add('d-none');
                
                // Reset phone validation flag
                phoneExistsInOtherSchool = false;
            } else if (data.in_other_school) {
                // Phone exists in another school - show error and block form
                phoneExistsInOtherSchool = true;
                parentNotFoundWarning.classList.add('d-none');
                phoneExistsError.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> This phone number already exists in another school. Please try another number.';
                phoneExistsError.className = 'alert alert-danger mt-2';
                phoneExistsError.classList.remove('d-none');
                phoneInput.classList.add('is-invalid');
                phoneInput.classList.remove('is-warning');
                phoneInput.setCustomValidity('This phone number already exists in another school. Please try another number.');
                document.getElementById('foundParentDiv').classList.add('d-none');
                document.getElementById('newParentDiv').classList.add('d-none');
                selectedParentId = null;
            } else {
                // Parent not found - show warning div instead of alert
                parentNotFoundWarning.classList.remove('d-none');
                phoneExistsError.classList.add('d-none');
                phoneInput.classList.remove('is-warning', 'is-invalid');
                document.getElementById('foundParentDiv').classList.add('d-none');
                document.getElementById('newParentDiv').classList.remove('d-none');
                selectedParentId = null;
            }
        })
        .catch(err => {
            console.error('Error searching parent:', err);
            
            // Show error in the phoneExistsError div instead of alert
            const phoneExistsError = document.getElementById('phoneExistsError');
            const phoneInput = document.getElementById('parentPhone');
            
            if (phoneExistsError) {
                phoneExistsError.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> Failed to search parent. Please check your connection and try again.';
                phoneExistsError.className = 'alert alert-danger mt-2';
                phoneExistsError.classList.remove('d-none');
            }
            if (phoneInput) {
                phoneInput.classList.add('is-invalid');
            }
        });
    });

    document.getElementById('useFoundParentBtn').addEventListener('click', function() {
        // Set the parent ID in hidden field
        if (selectedParentId) {
            document.getElementById('parentIdField').value = selectedParentId;
            console.log('Parent ID set to:', selectedParentId);
            
            // Reset phone validation flag
            phoneExistsInOtherSchool = false;
            
            // Hide warnings and errors
            document.getElementById('phoneExistsError').classList.add('d-none');
            document.getElementById('parentNotFoundWarning').classList.add('d-none');
            const phoneInput = document.getElementById('parentPhone');
            phoneInput.classList.remove('is-warning', 'is-invalid');
            phoneInput.setCustomValidity('');
            
            // Hide the new parent form fields
        document.getElementById('newParentDiv').classList.add('d-none');
            
            // Clear any validation errors on new parent fields
            document.querySelectorAll('#newParentDiv input, #newParentDiv textarea').forEach(field => {
                field.classList.remove('is-invalid');
                field.removeAttribute('required');
            });
            
            // Show success message
            const foundParentDiv = document.getElementById('foundParentDiv');
            if (foundParentDiv) {
                foundParentDiv.style.borderColor = '#28a745';
                foundParentDiv.style.backgroundColor = '#d4edda';
            }
        } else {
            console.error('No parent ID selected');
            Swal.fire({
                title: 'Error!',
                text: 'Parent ID not found. Please search again.',
                icon: 'error',
                confirmButtonColor: '#f5f5f5'
            });
            return;
        }
    });

    document.getElementById('cancelFoundParentBtn').addEventListener('click', function() {
        document.getElementById('foundParentDiv').classList.add('d-none');
        document.getElementById('newParentDiv').classList.remove('d-none');
        document.getElementById('parentNotFoundWarning').classList.add('d-none');
        document.getElementById('parentIdField').value = '';
        selectedParentId = null;
        
        // Re-check phone if it has a value
        const phoneInput = document.getElementById('parentPhone');
        const phone = phoneInput.value.trim();
        if (phone && /^[0-9]{9}$/.test(phone)) {
            const fullPhone = '255' + phone;
            checkPhoneExists(fullPhone, phoneInput);
        } else {
            // Clear warnings if phone is empty
            document.getElementById('phoneExistsError').classList.add('d-none');
            phoneInput.classList.remove('is-warning');
        }
    });

    // Health checkbox toggles
    document.getElementById('hasDisability').addEventListener('change', function() {
        document.getElementById('disabilityDetailsDiv').classList.toggle('d-none', !this.checked);
    });

    document.getElementById('hasChronicIllness').addEventListener('change', function() {
        document.getElementById('chronicDetailsDiv').classList.toggle('d-none', !this.checked);
    });

    // Navigation
    document.getElementById('prevBtn').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (currentStep > 1) showStep(currentStep - 1);
    });

    document.getElementById('nextBtn').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('Next button clicked, current step:', currentStep);
        
        // Always validate before proceeding - block if ANY error exists
        const isValid = validateCurrentStep();
        
        if (!isValid) {
            console.log('Validation failed for step:', currentStep, '- Blocking navigation');
            // Error message is already shown in validateCurrentStep via SweetAlert
            return false; // Block navigation completely
        }
        
            console.log('Validation passed, moving to step:', currentStep + 1);
        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        }
    });

    document.getElementById('submitBtn').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        submitForm();
    });

    function validateCurrentStep() {
        const form = document.getElementById('registrationForm');
        const currentStepEl = stepContents[currentStep];
        
        console.log('Validating Step', currentStep);
        
        if (!currentStepEl) {
            console.warn('No step element found');
            return true;
        }

        let isValid = true;
        let failedFields = [];
        let errorMessages = [];

        // Special validation for Step 1 - check if subclassID is selected
        if (currentStep === 1) {
            const subclassID = document.getElementById('selectedSubclassID').value;
            if (!subclassID || subclassID.trim() === '') {
                isValid = false;
                failedFields.push('subclassID');
                // Show error in the selected class display area
                const selectedClassDisplay = document.getElementById('selectedSubclassName');
                if (selectedClassDisplay) {
                    selectedClassDisplay.parentElement.classList.add('alert-danger');
                    selectedClassDisplay.parentElement.classList.remove('alert-info');
                    selectedClassDisplay.textContent = 'Please select a class from the class selector above.';
                }
            } else {
                // Clear error if class is selected
                const selectedClassDisplay = document.getElementById('selectedSubclassName');
                if (selectedClassDisplay) {
                    selectedClassDisplay.parentElement.classList.remove('alert-danger');
                    selectedClassDisplay.parentElement.classList.add('alert-info');
                }
            }
        }

        // Special validation for Step 2 (Parent/Guardian) - check if phone exists in another school
        if (currentStep === 2) {
            const parentPhoneInput = document.getElementById('parentPhone');
            if (parentPhoneInput && phoneExistsInOtherSchool) {
                isValid = false;
                parentPhoneInput.classList.add('is-invalid');
                failedFields.push('parent_phone');
                errorMessages.push('This phone number already exists in another school. Please try another number.');
            }
        }

        // Check for any visible error messages/alerts in current step (skip .is-invalid as it's just a class, not an error message element)
        // This check is now less important since we're displaying errors directly below fields
        // We'll skip this to avoid duplicate error messages

        // Validate only required fields in the current visible step
        const requiredFields = currentStepEl.querySelectorAll('[required]');
        console.log('Found', requiredFields.length, 'required fields');

        requiredFields.forEach(field => {
            // Skip hidden fields
            if (field.type === 'hidden') {
                return;
            }
            
            // For select fields, check if value is empty string or not selected
            let fieldValue = '';
            if (field.tagName === 'SELECT') {
                fieldValue = field.value || '';
            } else {
                fieldValue = field.value ? field.value.trim() : '';
            }
            
            console.log('Field', field.name, 'type:', field.type || field.tagName, 'has value:', fieldValue, 'length:', fieldValue.length);
            
            // Check if field is empty (for select, empty string means not selected)
            if (!fieldValue || fieldValue === '' || (field.tagName === 'SELECT' && field.selectedIndex === 0 && field.options[0].value === '')) {
                field.classList.add('is-invalid');
                if (!failedFields.includes(field.name)) {
                failedFields.push(field.name);
                }
                isValid = false;
                
                // Get field label for error message
                let label = currentStepEl.querySelector(`label[for="${field.id}"]`);
                if (!label) {
                    // Try to find label in parent containers
                    const parentContainer = field.closest('.col-md-6, .col-md-4, .col-md-12, .form-group');
                    if (parentContainer) {
                        label = parentContainer.querySelector('label');
                    }
                }
                
                let fieldLabel = field.name;
                if (label && label.textContent) {
                    // Extract label text, remove asterisk and colon
                    fieldLabel = label.textContent.replace(/\*/g, '').replace(/:/g, '').trim();
                    // If label is empty after cleaning, use field name
                    if (!fieldLabel || fieldLabel === '') {
                        fieldLabel = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    }
                } else {
                    // Fallback to formatted field name
                    fieldLabel = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                }
                
                const errorMsg = `${fieldLabel} is required.`;
                console.log('Field error:', field.name, 'Label:', fieldLabel, 'Error message:', errorMsg);
                
                // Display error message below the field
                let errorElement = currentStepEl.querySelector(`.error-${field.name}`);
                if (!errorElement) {
                    // Try to find existing error element
                    errorElement = field.parentElement.querySelector(`small.error-${field.name}`);
                }
                if (!errorElement) {
                    // Create new error element
                    errorElement = document.createElement('small');
                    errorElement.className = `text-danger error-${field.name}`;
                    errorElement.style.display = 'block';
                    errorElement.style.marginTop = '4px';
                    // Insert after the field
                    field.parentNode.insertBefore(errorElement, field.nextSibling);
                }
                if (errorElement) {
                    errorElement.textContent = errorMsg;
                    errorElement.classList.remove('d-none');
                    errorElement.style.display = 'block';
                }
            } else {
                // Check if field has custom validity error
                if (field.validity && field.validity.customError && field.validationMessage) {
                    isValid = false;
                    if (!failedFields.includes(field.name)) {
                        failedFields.push(field.name);
                    }
                    // Display error message below the field
                    let errorElement = currentStepEl.querySelector(`.error-${field.name}`);
                    if (!errorElement) {
                        errorElement = field.parentElement.querySelector(`small.error-${field.name}`);
                        if (!errorElement) {
                            errorElement = document.createElement('small');
                            errorElement.className = `text-danger error-${field.name}`;
                            field.parentElement.appendChild(errorElement);
                        }
                    }
                    if (errorElement) {
                        errorElement.textContent = field.validationMessage;
                        errorElement.classList.remove('d-none');
                    }
            } else {
                field.classList.remove('is-invalid');
                    // Hide error message if field is valid
                    let errorElement = currentStepEl.querySelector(`.error-${field.name}`);
                    if (errorElement) {
                        errorElement.classList.add('d-none');
                        errorElement.textContent = '';
                    }
                }
            }
        });

        // Also check HTML5 validation (but skip if already validated above)
        const fieldsToValidate = currentStepEl.querySelectorAll('input[required], select[required], textarea[required]');
        fieldsToValidate.forEach(field => {
            if (field.type === 'hidden') return;
            
            // Skip if already marked as invalid
            if (failedFields.includes(field.name)) {
                return;
            }
            
            // For select fields, check if value is selected
            if (field.tagName === 'SELECT') {
                if (!field.value || field.value === '' || (field.selectedIndex === 0 && field.options[0] && field.options[0].value === '')) {
                field.classList.add('is-invalid');
                    if (!failedFields.includes(field.name)) {
                failedFields.push(field.name);
                    }
                isValid = false;
                    
                    const label = currentStepEl.querySelector(`label[for="${field.id}"]`) || 
                                 field.closest('.form-group')?.querySelector('label') ||
                                 field.closest('.col-md-6, .col-md-4, .col-md-12')?.querySelector('label');
                    let fieldLabel = field.name;
                    if (label && label.textContent) {
                        fieldLabel = label.textContent.replace(/\*/g, '').replace(/:/g, '').trim();
                        if (!fieldLabel || fieldLabel === '') {
                            fieldLabel = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        }
                    } else {
                        fieldLabel = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    }
                    const errorMsg = `${fieldLabel} is required.`;
                    
                    // Display error message below the field
                    let errorElement = currentStepEl.querySelector(`.error-${field.name}`);
                    if (!errorElement) {
                        errorElement = field.parentElement.querySelector(`small.error-${field.name}`);
                        if (!errorElement) {
                            errorElement = document.createElement('small');
                            errorElement.className = `text-danger error-${field.name}`;
                            field.parentElement.appendChild(errorElement);
                        }
                    }
                    if (errorElement) {
                        errorElement.textContent = errorMsg;
                        errorElement.classList.remove('d-none');
                    }
                }
            } else if (!field.checkValidity()) {
                field.classList.add('is-invalid');
                if (!failedFields.includes(field.name)) {
                    failedFields.push(field.name);
                }
                isValid = false;
                
                // Get validation message and display below field
                let errorMsg = '';
                if (field.validationMessage) {
                    errorMsg = field.validationMessage;
                } else {
                    const label = currentStepEl.querySelector(`label[for="${field.id}"]`) || 
                                 field.closest('.form-group')?.querySelector('label') ||
                                 field.closest('.col-md-6, .col-md-4, .col-md-12')?.querySelector('label');
                    let fieldLabel = field.name;
                    if (label && label.textContent) {
                        fieldLabel = label.textContent.replace(/\*/g, '').replace(/:/g, '').trim();
                        if (!fieldLabel || fieldLabel === '') {
                            fieldLabel = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        }
                    } else {
                        fieldLabel = field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    }
                    errorMsg = `${fieldLabel} is required.`;
                }
                
                // Display error message below the field
                let errorElement = currentStepEl.querySelector(`.error-${field.name}`);
                if (!errorElement) {
                    errorElement = field.parentElement.querySelector(`small.error-${field.name}`);
                    if (!errorElement) {
                        errorElement = document.createElement('small');
                        errorElement.className = `text-danger error-${field.name}`;
                        field.parentElement.appendChild(errorElement);
                    }
                }
                if (errorElement && errorMsg && errorMsg.trim() !== '') {
                    errorElement.textContent = errorMsg;
                    errorElement.classList.remove('d-none');
                }
            }
        });

        // Check for any error messages in the step (skip this to avoid duplicates since we're displaying errors directly)
        // Error messages are now displayed directly below fields, so we don't need to collect them here

        console.log('Validation passed', isValid);
        console.log('Failed fields:', failedFields);
        console.log('Error messages:', errorMessages);
        
        if (!isValid) {
            // Scroll to first invalid field
            const firstInvalidField = currentStepEl.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    firstInvalidField.focus();
                }, 100);
            }
        } else {
            // Clear all error messages when validation passes
            currentStepEl.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
            currentStepEl.querySelectorAll('small.text-danger.error-').forEach(errorEl => {
                errorEl.classList.add('d-none');
                errorEl.textContent = '';
            });
        }
        
        return isValid;
    }

    // Function to collect all form data for confirmation
    function collectFormData() {
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        const data = {};
        
        // Collect all form values
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Get selected subclass name
        const subclassName = document.getElementById('selectedSubclassName').textContent;
        
        // Build confirmation summary
        let summary = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';
        summary += '<h6 style="color: #212529; margin-bottom: 15px;"><i class="bi bi-person-check"></i> Student Information</h6>';
        summary += '<p><strong>Class:</strong> ' + subclassName + '</p>';
        summary += '<p><strong>Name:</strong> ' + (data.first_name || '') + ' ' + (data.middle_name || '') + ' ' + (data.last_name || '') + '</p>';
        summary += '<p><strong>Gender:</strong> ' + (data.gender || 'N/A') + '</p>';
        summary += '<p><strong>Date of Birth:</strong> ' + (data.date_of_birth || 'N/A') + '</p>';
        summary += '<p><strong>Birth Certificate:</strong> ' + (data.birth_certificate_number || 'N/A') + '</p>';
        summary += '<p><strong>Religion:</strong> ' + (data.religion || 'N/A') + '</p>';
        summary += '<p><strong>Nationality:</strong> ' + (data.nationality || 'N/A') + '</p>';
        
        summary += '<hr style="margin: 15px 0;">';
        summary += '<h6 style="color: #212529; margin-bottom: 15px;"><i class="bi bi-people-fill"></i> Parent/Guardian Information</h6>';
        
        // Check if using found parent or new parent
        const parentId = data.parent_id;
        if (parentId) {
            const foundParentName = document.getElementById('foundParentName').textContent;
            const foundParentPhone = document.getElementById('foundParentPhone').textContent;
            summary += '<p><strong>Using Existing Parent:</strong></p>';
            summary += '<p><strong>Name:</strong> ' + foundParentName + '</p>';
            summary += '<p><strong>Phone:</strong> ' + foundParentPhone + '</p>';
        } else {
            summary += '<p><strong>Name:</strong> ' + (data.parent_first_name || '') + ' ' + (data.parent_middle_name || '') + ' ' + (data.parent_last_name || '') + '</p>';
            summary += '<p><strong>Phone:</strong> ' + (data.parent_phone || 'N/A') + '</p>';
            summary += '<p><strong>Relationship:</strong> ' + (data.parent_relationship || 'N/A') + '</p>';
            summary += '<p><strong>Occupation:</strong> ' + (data.parent_occupation || 'N/A') + '</p>';
            summary += '<p><strong>Email:</strong> ' + (data.parent_email || 'N/A') + '</p>';
            summary += '<p><strong>Address:</strong> ' + (data.parent_address || 'N/A') + '</p>';
        }
        
        summary += '<hr style="margin: 15px 0;">';
        summary += '<h6 style="color: #212529; margin-bottom: 15px;"><i class="bi bi-heart-pulse"></i> Health Information</h6>';
        summary += '<p><strong>General Health:</strong> ' + (data.general_health_condition || 'N/A') + '</p>';
        
        // Check for disability checkbox
        const hasDisability = document.querySelector('input[name="has_disability"]:checked') || 
                             (data.has_disability === 'on' || data.has_disability === '1' || data.has_disability === true);
        summary += '<p><strong>Has Disability:</strong> ' + (hasDisability ? 'Yes' : 'No') + '</p>';
        if (hasDisability && data.disability_details) {
            summary += '<p><strong>Disability Details:</strong> ' + data.disability_details + '</p>';
        }
        
        // Check for chronic illness checkbox
        const hasChronicIllness = document.querySelector('input[name="has_chronic_illness"]:checked') || 
                                 (data.has_chronic_illness === 'on' || data.has_chronic_illness === '1' || data.has_chronic_illness === true);
        summary += '<p><strong>Has Chronic Illness:</strong> ' + (hasChronicIllness ? 'Yes' : 'No') + '</p>';
        if (hasChronicIllness && data.chronic_illness_details) {
            summary += '<p><strong>Chronic Illness Details:</strong> ' + data.chronic_illness_details + '</p>';
        }
        summary += '<p><strong>Immunization Details:</strong> ' + (data.immunization_details || 'N/A') + '</p>';
        
        summary += '<hr style="margin: 15px 0;">';
        summary += '<h6 style="color: #212529; margin-bottom: 15px;"><i class="bi bi-telephone-fill"></i> Emergency Contact</h6>';
        summary += '<p><strong>Name:</strong> ' + (data.emergency_contact_name || 'N/A') + '</p>';
        summary += '<p><strong>Relationship:</strong> ' + (data.emergency_contact_relationship || 'N/A') + '</p>';
        summary += '<p><strong>Phone:</strong> ' + (data.emergency_contact_phone || 'N/A') + '</p>';
        
        summary += '<hr style="margin: 15px 0;">';
        summary += '<h6 style="color: #212529; margin-bottom: 15px;"><i class="bi bi-file-check"></i> Declaration</h6>';
        // Check for parent declaration checkbox
        const parentDeclaration = document.querySelector('input[name="parent_declaration"]:checked') || 
                                 (data.parent_declaration === 'on' || data.parent_declaration === '1' || data.parent_declaration === true);
        summary += '<p><strong>Parent Declaration:</strong> ' + (parentDeclaration ? '<span style="color: green;">✓ Confirmed</span>' : '<span style="color: red;">✗ Not Confirmed</span>') + '</p>';
        summary += '<p><strong>Declaration Date:</strong> ' + (data.declaration_date || 'N/A') + '</p>';
        
        summary += '</div>';
        
        return summary;
    }

    function submitForm() {
        // First, collect and show confirmation
        const summary = collectFormData();
        
        Swal.fire({
            title: 'Confirm Registration',
            html: summary,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle"></i> Yes, Submit Registration',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
            width: '700px',
            customClass: {
                popup: 'text-start'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we register the student',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);

                // Ensure subclassID is set
                const subclassID = document.getElementById('selectedSubclassID').value;
                if (!subclassID || subclassID.trim() === '') {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a class before submitting the registration.',
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                    return;
                }
                formData.set('subclassID', subclassID);

                // Format parent phone to start with 255 (not +255)
                const parentPhone = formData.get('parent_phone');
                if (parentPhone) {
                    let phone = parentPhone.trim().replace(/[^0-9]/g, ''); // Remove any non-numeric characters
                    // Remove leading 255 if user entered it (we'll add it back)
                    if (phone.startsWith('255')) {
                        phone = phone.substring(3);
                    }
                    // Add 255 prefix (without +)
                    phone = '255' + phone;
                    formData.set('parent_phone', phone);
                }

                // Format emergency contact phone to start with 255 (not +255)
                const emergencyPhone = formData.get('emergency_contact_phone');
                if (emergencyPhone) {
                    let phone = emergencyPhone.trim().replace(/[^0-9]/g, ''); // Remove any non-numeric characters
                    // User enters only 9 digits, so add 255 prefix
                    phone = '255' + phone;
                    formData.set('emergency_contact_phone', phone);
                }

        fetch('{{ route("student.registration.store-complete") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
                .then(async response => {
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: '<div style="text-align: center;"><i class="bi bi-check-circle-fill" style="font-size: 60px; color: #28a745;"></i><br><br><strong>Student Registered Successfully!</strong><br><br>Admission Number: <span style="color: #212529; font-size: 1.2em; font-weight: bold;">' + data.admission_number + '</span></div>',
                            icon: 'success',
                            confirmButtonColor: '#f5f5f5',
                            confirmButtonText: 'OK'
                        }).then(() => {
                document.getElementById('registrationModal').querySelector('.btn-close').click();
                location.reload();
                        });
            } else {
                        // Handle validation errors
                        let errorMessage = data.message || 'Registration failed. Please try again.';
                        let fieldErrors = {};
                        
                        if (data.errors) {
                            // Convert Laravel validation errors to object
                            fieldErrors = data.errors;
                            const errorList = Object.entries(data.errors).map(([field, messages]) => {
                                return `<strong>${getFieldLabel(field)}:</strong> ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                            }).join('<br>');
                            errorMessage = errorList || errorMessage;
                        }
                        
                        // Display error in SweetAlert
                        Swal.fire({
                            title: 'Validation Error!',
                            html: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#f5f5f5',
                            width: '700px',
                            customClass: {
                                htmlContainer: 'text-start'
                            }
                        }).then(() => {
                            // Highlight fields with errors and navigate to the step containing the first error
                            highlightErrors(fieldErrors);
                        });
            }
        })
        .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'An error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#f5f5f5'
                    });
                });
            }
        });
    }

    function showError(message) {
        const errorDiv = document.getElementById('errorAlert');
        errorDiv.textContent = message;
        errorDiv.classList.remove('d-none');
    }

    // Function to get field label from field name
    function getFieldLabel(fieldName) {
        const fieldLabels = {
            'first_name': 'First Name',
            'last_name': 'Last Name',
            'gender': 'Gender',
            'date_of_birth': 'Date of Birth',
            'subclassID': 'Class/Subclass',
            'parent_phone': 'Parent Phone Number',
            'parent_first_name': 'Parent First Name',
            'parent_last_name': 'Parent Last Name',
            'parent_relationship': 'Parent Relationship',
            'emergency_contact_name': 'Emergency Contact Name',
            'emergency_contact_relationship': 'Emergency Contact Relationship',
            'emergency_contact_phone': 'Emergency Contact Phone',
            'parent_declaration': 'Parent Declaration',
            'declaration_date': 'Declaration Date',
            'student_photo': 'Student Photo',
            'parent_photo': 'Parent Photo'
        };
        return fieldLabels[fieldName] || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Function to highlight fields with errors and navigate to the step containing the first error
    function highlightErrors(fieldErrors) {
        if (!fieldErrors || Object.keys(fieldErrors).length === 0) {
            return;
        }

        // Map field names to their step numbers
        const fieldStepMap = {
            'first_name': 1, 'last_name': 1, 'gender': 1, 'date_of_birth': 1, 'subclassID': 1,
            'student_photo': 1, 'birth_certificate_number': 1, 'religion': 1, 'nationality': 1,
            'parent_phone': 2, 'parent_first_name': 2, 'parent_last_name': 2, 'parent_relationship': 2,
            'parent_photo': 2, 'parent_id': 2,
            'general_health_condition': 3, 'has_disability': 3, 'disability_details': 3,
            'has_chronic_illness': 3, 'chronic_illness_details': 3, 'immunization_details': 3,
            'emergency_contact_name': 4, 'emergency_contact_relationship': 4, 'emergency_contact_phone': 4,
            'parent_declaration': 5, 'declaration_date': 5
        };

        // Find the first error field and its step
        const firstErrorField = Object.keys(fieldErrors)[0];
        const stepNumber = fieldStepMap[firstErrorField] || 1;

        // Navigate to the step containing the first error
        if (stepNumber !== currentStep) {
            showStep(stepNumber);
        }

        // Clear previous error highlights
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.error-message, .text-danger.error-message').forEach(el => {
            if (el.classList.contains('error-message') || el.classList.contains('text-danger')) {
                el.classList.add('d-none');
            }
        });

        // Highlight fields with errors
        Object.keys(fieldErrors).forEach(fieldName => {
            // Try to find the input field
            let field = document.querySelector(`[name="${fieldName}"]`);
            
            if (!field) {
                // Try alternative selectors
                field = document.getElementById(fieldName);
            }
            
            if (!field) {
                // Try with underscore variations
                field = document.querySelector(`[name="${fieldName.replace(/_/g, '-')}"]`);
            }

            if (field) {
                // Add error class
                field.classList.add('is-invalid');
                
                // Show error message
                let errorElement = field.parentElement.querySelector(`.error-${fieldName}`);
                if (!errorElement) {
                    errorElement = document.createElement('small');
                    errorElement.className = `text-danger error-${fieldName}`;
                    field.parentElement.appendChild(errorElement);
                }
                errorElement.classList.remove('d-none');
                errorElement.textContent = Array.isArray(fieldErrors[fieldName]) 
                    ? fieldErrors[fieldName][0] 
                    : fieldErrors[fieldName];
                
                // Scroll to the first error field
                if (fieldName === firstErrorField) {
                    setTimeout(() => {
                        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        field.focus();
                    }, 300);
                }
            }
        });
    }

    // Real-time validation for step 1 fields
    function validateFieldRealtime(field) {
        const fieldValue = field.value.trim();
        const fieldName = field.name;
        const errorElement = document.querySelector(`.error-${fieldName}`);
        const isRequired = field.hasAttribute('required');
        
        // Clear previous error
        field.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.classList.add('d-none');
            errorElement.textContent = '';
        }
        
        // Validate if required and empty
        if (isRequired && !fieldValue) {
            field.classList.add('is-invalid');
            if (errorElement) {
                const fieldLabel = field.closest('.col-md-6, .col-md-4')?.querySelector('label')?.textContent?.replace('*', '').replace(':', '').trim() || fieldName.replace(/_/g, ' ');
                errorElement.textContent = `${fieldLabel} is required.`;
                errorElement.classList.remove('d-none');
            }
            return false;
        }
        
        // Additional validations
        if (fieldName === 'date_of_birth' && fieldValue) {
            const dob = new Date(fieldValue);
            const today = new Date();
            if (dob > today) {
                field.classList.add('is-invalid');
                if (errorElement) {
                    errorElement.textContent = 'Date of birth cannot be in the future.';
                    errorElement.classList.remove('d-none');
                }
                return false;
            }
        }
        
        return true;
    }

    // Add real-time validation listeners for step 1
    setTimeout(function() {
        const firstNameField = document.querySelector('input[name="first_name"]');
        const lastNameField = document.querySelector('input[name="last_name"]');
        const genderField = document.querySelector('select[name="gender"]');
        const dobField = document.querySelector('input[name="date_of_birth"]');
        
        if (firstNameField) {
            firstNameField.addEventListener('blur', function() {
                validateFieldRealtime(this);
            });
            let firstNameTimeout;
            firstNameField.addEventListener('input', function() {
                clearTimeout(firstNameTimeout);
                firstNameTimeout = setTimeout(() => {
                    if (this.value.trim().length > 0) {
                        validateFieldRealtime(this);
                    } else if (this.hasAttribute('required')) {
                        validateFieldRealtime(this);
                    }
                }, 500);
            });
        }
        
        if (lastNameField) {
            lastNameField.addEventListener('blur', function() {
                validateFieldRealtime(this);
            });
            let lastNameTimeout;
            lastNameField.addEventListener('input', function() {
                clearTimeout(lastNameTimeout);
                lastNameTimeout = setTimeout(() => {
                    if (this.value.trim().length > 0) {
                        validateFieldRealtime(this);
                    } else if (this.hasAttribute('required')) {
                        validateFieldRealtime(this);
                    }
                }, 500);
            });
        }
        
        if (genderField) {
            genderField.addEventListener('change', function() {
                validateFieldRealtime(this);
            });
        }
        
        if (dobField) {
            dobField.addEventListener('blur', function() {
                validateFieldRealtime(this);
            });
        }
    }, 100);

    // Initialize - show step 1
    showStep(1);
});
</script>
