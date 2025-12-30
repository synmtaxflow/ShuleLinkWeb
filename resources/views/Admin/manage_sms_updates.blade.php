@if($user_type == 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    :root {
        --primary-color: #940000;
        --primary-hover: #b30000;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .sms-container {
        padding: 30px;
    }

    .sms-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .sms-header h2 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .sms-header h2 i {
        font-size: 2rem;
    }

    .sms-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .sms-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .card-title i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    .recipient-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .recipient-option:hover {
        border-color: var(--primary-color);
        background: #fff;
        transform: translateX(5px);
    }

    .recipient-option.active {
        border-color: var(--primary-color);
        background: #fff5f5;
    }

    .recipient-option input[type="radio"] {
        margin-right: 15px;
        transform: scale(1.3);
        accent-color: var(--primary-color);
    }

    .recipient-option-label {
        display: flex;
        align-items: center;
        gap: 15px;
        font-weight: 500;
        color: #495057;
        margin-bottom: 10px;
    }

    .recipient-option-label i {
        font-size: 1.5rem;
        color: var(--primary-color);
    }

    .recipient-option-desc {
        color: #6c757d;
        font-size: 0.9rem;
        margin-left: 45px;
        margin-top: 5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .char-count {
        text-align: right;
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .btn-send {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(148, 0, 0, 0.3);
    }

    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(148, 0, 0, 0.4);
    }

    .btn-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stats-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stats-card.failed {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    }

    .stats-card.total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }

    .stats-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .recipient-preview {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
        border-left: 4px solid var(--primary-color);
    }

    .recipient-preview strong {
        color: var(--primary-color);
    }

    .student-search-results {
        max-height: 300px;
        overflow-y: auto;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-top: 10px;
        background: white;
        display: none;
    }

    .student-result-item {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .student-result-item:hover {
        background: #f8f9fa;
    }

    .student-result-item:last-child {
        border-bottom: none;
    }

    .student-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .student-name {
        font-weight: 500;
        color: #212529;
    }

    .student-details {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        max-width: 400px;
    }

    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .results-table {
        margin-top: 20px;
    }

    .badge-success-custom {
        background-color: var(--success-color);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .badge-danger-custom {
        background-color: var(--danger-color);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
    }

    .hidden {
        display: none !important;
    }

    #composeMessageSection.hidden {
        display: none !important;
    }

    #composeMessageSection:not(.hidden) {
        display: block !important;
    }

    .search-filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        font-weight: 500;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
        color: white;
    }
</style>

<div class="container-fluid" style="padding: 20px;">
    <!-- Header -->
    <div class="sms-header">
        <h2>
            <i class="fa fa-comments"></i>
            SMS Notification Management System
        </h2>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Send notifications to parents and teachers efficiently</p>
    </div>

    <!-- Statistics Widgets (Like Attendance Overview) -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success">
                <div class="card-body text-center" style="color: #ffffff;">
                    <h4 style="color: #ffffff; font-size: 2.5rem; font-weight: 700; margin: 10px 0;" id="totalRecipientsWidget">0</h4>
                    <p class="mb-0" style="color: #ffffff; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Total Recipients</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info">
                <div class="card-body text-center" style="color: #ffffff;">
                    <h4 style="color: #ffffff; font-size: 2.5rem; font-weight: 700; margin: 10px 0;" id="availableSmsWidget">
                        <i class="fa fa-spinner fa-spin"></i>
                    </h4>
                    <p class="mb-0" style="color: #ffffff; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Available SMS</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body text-center" style="color: #ffffff;">
                    <h4 style="color: #ffffff; font-size: 2.5rem; font-weight: 700; margin: 10px 0;" id="successCountWidget">0</h4>
                    <p class="mb-0" style="color: #ffffff; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Successfully Sent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger">
                <div class="card-body text-center" style="color: #ffffff;">
                    <h4 style="color: #ffffff; font-size: 2.5rem; font-weight: 700; margin: 10px 0;" id="failedCountWidget">0</h4>
                    <p class="mb-0" style="color: #ffffff; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Failed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section (Horizontal like manage_library) -->
    <div class="search-filter-section">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label"><i class="fa fa-filter"></i> Recipient Type</label>
                <select class="form-control" id="quickRecipientType" onchange="handleQuickRecipientChange()">
                    <option value="">-- Select Type --</option>
                    <option value="all_parents">All Parents</option>
                    <option value="class_parents">Class Parents</option>
                    <option value="all_parents_teachers">All Parents & Teachers</option>
                    <option value="specific_parent">Specific Parent</option>
                </select>
            </div>
            <div class="col-md-3" id="quickClassGroup" style="display: none;">
                <label class="form-label"><i class="fa fa-graduation-cap"></i> Select Class</label>
                <select class="form-control" id="quickClassID">
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4" id="quickStudentSearchGroup" style="display: none;">
                <label class="form-label"><i class="fa fa-search"></i> Search Student</label>
                <input type="text" class="form-control" id="quickStudentSearch" placeholder="Search by name or admission number...">
                <div class="student-search-results" id="quickStudentResults"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-primary-custom w-100" onclick="loadRecipientCount()">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Compose Message Section (Hidden by default, shown after selecting recipient type) -->
    <div class="row hidden" id="composeMessageSection">
        <div class="col-lg-12">
            <div class="sms-card">
                <div class="card-title">
                    <i class="fa fa-paper-plane"></i>
                    Compose Message
                </div>

                <form id="smsForm">
                    <!-- Hidden recipient type (synced from search) -->
                    <input type="hidden" id="recipient_type" name="recipient_type">
                    <input type="hidden" id="classID" name="classID">
                    <input type="hidden" id="studentID" name="studentID">

                    <!-- Selected Recipient Info -->
                    <div class="recipient-preview" id="selectedRecipientInfo">
                        <strong>Selected Recipients:</strong> <span id="selectedRecipientText">-</span>
                        <br>
                        <strong>Total Recipients:</strong> <span id="recipientCount">0</span>
                    </div>

                    <!-- Message -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label class="form-label" for="message">
                            <i class="fa fa-comment"></i>
                            Message
                        </label>
                        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message here... (School name will be automatically added at the beginning)"></textarea>
                        <div class="char-count">
                            <span id="charCount">0</span> characters
                        </div>
                    </div>

                    <!-- Send Button -->
                    <div class="form-group">
                        <button type="button" class="btn btn-send" id="sendBtn" onclick="sendSMS()">
                            <i class="fa fa-paper-plane"></i> Send SMS
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Section -->
            <div class="sms-card hidden" id="resultsCard">
                <div class="card-title">
                    <i class="fa fa-chart-bar"></i>
                    Sending Results
                </div>
                <div id="resultsContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <h4>Sending SMS...</h4>
        <p id="loadingText">Please wait while we send your messages. This may take a few minutes for large batches.</p>
        <div id="progressText" style="margin-top: 15px; font-weight: 600;"></div>
    </div>
</div>

@include('includes.footer')

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let selectedStudentID = null;
    let searchTimeout = null;

    // Wait for jQuery
    (function() {
        function initSMS() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initSMS, 100);
                return;
            }

            var $ = jQuery;

            $(document).ready(function() {
                // Load SMS balance on page load
                loadSmsBalance();
                
                // Load recipient count on page load
                loadRecipientCount();
                
                // Auto-refresh SMS balance every 5 minutes
                setInterval(loadSmsBalance, 300000); // 5 minutes

                // Character counter
                $('#message').on('input', function() {
                    const length = $(this).val().length;
                    $('#charCount').text(length);
                });

    // Removed - using quick search only

                // Handle recipient type change - realtime
                $('#quickRecipientType').on('change', function() {
                    handleQuickRecipientChange();
                });

                // Quick student search
                $('#quickStudentSearch').on('input', function() {
                    const search = $(this).val().trim();
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }
                    if (search.length >= 2) {
                        searchTimeout = setTimeout(() => searchQuickStudents(search), 300);
                    } else {
                        $('#quickStudentResults').hide().empty();
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSMS);
        } else {
            initSMS();
        }
    })();

    // Make handleQuickRecipientChange available globally
    window.handleQuickRecipientChange = function() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        var $ = jQuery;
        const recipientType = $('#quickRecipientType').val();
        
        console.log('Recipient type changed to:', recipientType);
        
        // Show/hide quick search fields
        $('#quickClassGroup').hide();
        $('#quickStudentSearchGroup').hide();

        if (!recipientType) {
            $('#composeMessageSection').addClass('hidden');
            return;
        }

        // Set hidden recipient type
        $('#recipient_type').val(recipientType);

        // Realtime fetch and display based on recipient type
        if (recipientType === 'class_parents') {
            $('#quickClassGroup').show();
            // Show compose form immediately
            showComposeForm(recipientType);
            
            // Handle class change - update count in realtime
            $('#quickClassID').off('change').on('change', function() {
                const classID = $(this).val();
                if (classID) {
                    $('#classID').val(classID);
                    loadRecipientCount();
                    // Update recipient text
                    const className = $('#quickClassID option:selected').text();
                    $('#selectedRecipientText').text(`Parents of ${className}`);
                } else {
                    $('#selectedRecipientText').text('Parents of [Select Class]');
                    $('#recipientCount').text('0');
                }
            });
        } else if (recipientType === 'specific_parent') {
            $('#quickStudentSearchGroup').show();
            // Clear previous student selection
            $('#studentID').val('');
            $('#quickStudentSearch').val('');
            $('#quickStudentResults').hide().empty();
            selectedStudentID = null;
            // Show compose form immediately with placeholder
            showComposeForm(recipientType);
            // Set recipient count to 0 until student is selected
            $('#totalRecipientsWidget').text('0');
            $('#recipientCount').text('0');
            // Student selection will update the form
        } else {
            // For all_parents or all_parents_teachers, show form immediately and fetch count
            showComposeForm(recipientType);
            loadRecipientCount();
        }
    };

    // Removed - no longer needed since we use search section only

    // Removed - using quick search and loadRecipientCount instead

    function sendSMS() {
        if (typeof jQuery === 'undefined') {
            alert('jQuery is not loaded. Please refresh the page.');
            return;
        }

        var $ = jQuery;

        // Get recipient type
        const recipientType = $('#recipient_type').val();
        
        // Ensure compose form is visible
        const composeSection = $('#composeMessageSection');
        if (composeSection.hasClass('hidden') || composeSection.css('display') === 'none') {
            composeSection.removeClass('hidden');
            composeSection.css('display', 'block');
            composeSection.show();
        }
        
        // Get message - use visible textarea only (inside compose section)
        let message = '';
        
        // First, try to get from visible compose section
        const messageInCompose = composeSection.find('#message');
        if (messageInCompose.length > 0) {
            // Get native element
            const messageNative = messageInCompose[0];
            if (messageNative) {
                message = messageNative.value || '';
                message = message.trim();
            }
            
            // If empty, try jQuery
            if (!message) {
                message = messageInCompose.val() || '';
                message = message.trim();
            }
        }
        
        // If still empty, try global selector (fallback)
        if (!message) {
            const messageTextarea = document.getElementById('message');
            if (messageTextarea) {
                message = messageTextarea.value || '';
                message = message.trim();
            } else {
                const $globalMsg = $('#message');
                if ($globalMsg.length > 0) {
                    message = $globalMsg.val() || '';
                    message = message.trim();
                }
            }
        }
        
        console.log('=== SMS Send Validation ===');
        console.log('Compose Section Visible:', !composeSection.hasClass('hidden'));
        console.log('Compose Section Display:', composeSection.css('display'));
        console.log('Recipient Type:', recipientType);
        console.log('Message in Compose Section:', messageInCompose.length);
        console.log('All #message elements:', $('#message').length);
        console.log('Message Value (Native):', messageInCompose.length > 0 ? messageInCompose[0].value : 'N/A');
        console.log('Message Value (jQuery):', messageInCompose.val());
        console.log('Message Value (Final):', message);
        console.log('Message Length:', message.length);

        // Validate recipient type
        if (!recipientType) {
            Swal.fire({
                icon: 'warning',
                title: 'Selection Required',
                text: 'Please select a recipient type from the search section above'
            });
            return;
        }

        // Validate message - check if it's really empty
        if (!message || message.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Message Required',
                html: 'Please enter a message to send.<br><br>The message field appears to be empty. Please type your message in the textarea above.'
            });
            // Focus on message field in compose section
            if (messageInCompose.length > 0) {
                messageInCompose.focus();
            } else {
                $('#message').focus();
            }
            return;
        }

        if (recipientType === 'class_parents' && !$('#classID').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Class Required',
                text: 'Please select a class'
            });
            return;
        }

        if (recipientType === 'specific_parent' && !$('#studentID').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Student Required',
                text: 'Please select a student'
            });
            return;
        }

        // Confirm before sending
        Swal.fire({
            title: 'Confirm Send',
            text: 'Are you sure you want to send this SMS?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, send it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performSend();
            }
        });
    }

    function performSend() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        // Get message value - use visible textarea in compose section
        const composeSection = $('#composeMessageSection');
        let messageValue = '';
        
        // Try to get from compose section first
        const messageInCompose = composeSection.find('#message')[0];
        if (messageInCompose) {
            messageValue = messageInCompose.value || '';
            messageValue = messageValue.trim();
        }
        
        // If empty, try global selector
        if (!messageValue) {
            const messageTextarea = document.getElementById('message');
            if (messageTextarea) {
                messageValue = messageTextarea.value || '';
                messageValue = messageValue.trim();
            }
        }
        
        // Last resort - jQuery
        if (!messageValue) {
            const $msg = composeSection.find('#message');
            if ($msg.length > 0) {
                messageValue = $msg.val() || '';
                messageValue = messageValue.trim();
            } else {
                messageValue = $('#message').val() || '';
                messageValue = messageValue.trim();
            }
        }
        
        console.log('=== Perform Send ===');
        console.log('Compose Section:', composeSection.length);
        console.log('Message in Compose:', composeSection.find('#message').length);
        console.log('All Messages:', $('#message').length);
        console.log('Message Value (Native from Compose):', messageInCompose ? messageInCompose.value : 'N/A');
        console.log('Message Value (jQuery from Compose):', composeSection.find('#message').val());
        console.log('Message Value (Final):', messageValue);
        console.log('Message Length:', messageValue.length);
        
        // Final validation - should not happen if sendSMS validation worked
        if (!messageValue || messageValue.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Message Error',
                html: 'Message is empty. Please enter a message before sending.<br><br>Please check the message textarea and make sure you have typed your message.'
            });
            if (messageTextarea) {
                messageTextarea.focus();
                messageTextarea.select();
            } else {
                $('#message').focus();
            }
            return;
        }
        
        // Get all form data
        const formData = {
            recipient_type: $('#recipient_type').val(),
            message: messageValue,
            classID: $('#classID').val() || null,
            studentID: $('#studentID').val() || null
        };
        
        console.log('=== Sending SMS ===');
        console.log('Form Data:', formData);
        console.log('Message being sent:', formData.message);
        console.log('Message length:', formData.message.length);

        // Show loading
        document.getElementById('loadingOverlay').style.display = 'flex';
        document.getElementById('sendBtn').disabled = true;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '{{ route("send_sms") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                document.getElementById('loadingOverlay').style.display = 'none';
                document.getElementById('sendBtn').disabled = false;

                if (response.success) {
                    const results = response.results;
                    
                    // Update widget statistics
                    updateWidgetStats(results);
                    
                    // Refresh SMS balance
                    loadSmsBalance();

                    // Show results
                    showResults(results);

                    Swal.fire({
                        icon: 'success',
                        title: 'SMS Sent!',
                        html: `
                            <p><strong>Total:</strong> ${results.total}</p>
                            <p><strong>Success:</strong> ${results.success}</p>
                            <p><strong>Failed:</strong> ${results.failed}</p>
                        `,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to send SMS'
                    });
                }
            },
            error: function(xhr) {
                document.getElementById('loadingOverlay').style.display = 'none';
                document.getElementById('sendBtn').disabled = false;
                
                const error = xhr.responseJSON?.message || 'An error occurred';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error
                });
            }
        });
    }

    function showResults(results) {
        const resultsCard = document.getElementById('resultsCard');
        const resultsContent = document.getElementById('resultsContent');
        
        let html = `
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="stats-card total" style="margin: 0;">
                        <div class="stats-number">${results.total}</div>
                        <div class="stats-label">Total</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card success" style="margin: 0;">
                        <div class="stats-number">${results.success}</div>
                        <div class="stats-label">Success</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card failed" style="margin: 0;">
                        <div class="stats-number">${results.failed}</div>
                        <div class="stats-label">Failed</div>
                    </div>
                </div>
            </div>
        `;

        if (results.details && results.details.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            results.details.forEach(detail => {
                const statusClass = detail.status === 'success' ? 'badge-success-custom' : 'badge-danger-custom';
                html += `
                    <tr>
                        <td>${detail.name}</td>
                        <td>${detail.phone}</td>
                        <td><span class="${statusClass}">${detail.status}</span></td>
                        <td>${detail.message}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        resultsContent.innerHTML = html;
        resultsCard.classList.remove('hidden');
    }

    // Load SMS Balance
    function loadSmsBalance() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        $.ajax({
            url: '{{ route("get_sms_balance") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const balance = parseFloat(response.balance || 0).toLocaleString();
                    $('#availableSmsWidget').html(balance);
                } else {
                    $('#availableSmsWidget').html('N/A');
                }
            },
            error: function() {
                $('#availableSmsWidget').html('Error');
            }
        });
    }

    // Load Recipient Count (Realtime)
    function loadRecipientCount() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        const recipientType = $('#recipient_type').val() || $('#quickRecipientType').val();
        
        if (!recipientType) {
            $('#totalRecipientsWidget').text('0');
            $('#recipientCount').text('0');
            return;
        }

        let countPromise;

        switch(recipientType) {
            case 'all_parents':
                countPromise = $.ajax({
                    url: '{{ route("get_all_parents_sms") }}',
                    type: 'GET'
                }).then(res => res.success ? res.count : 0);
                break;

            case 'class_parents':
                const classID = $('#quickClassID').val() || $('#classID').val();
                if (!classID) {
                    $('#totalRecipientsWidget').text('0');
                    return;
                }
                countPromise = $.ajax({
                    url: '{{ route("get_parents_by_class_sms") }}',
                    type: 'GET',
                    data: { classID: classID }
                }).then(res => res.success ? res.count : 0);
                break;

            case 'all_parents_teachers':
                countPromise = Promise.all([
                    $.ajax({ url: '{{ route("get_all_parents_sms") }}', type: 'GET' }),
                    $.ajax({ url: '{{ route("get_all_teachers_sms") }}', type: 'GET' })
                ]).then(results => {
                    const parentsCount = results[0].success ? results[0].count : 0;
                    const teachersCount = results[1].success ? results[1].count : 0;
                    return parentsCount + teachersCount;
                });
                break;

            case 'specific_parent':
                $('#totalRecipientsWidget').text('1');
                return;

            default:
                $('#totalRecipientsWidget').text('0');
                return;
        }

        countPromise.then(count => {
            const finalCount = count || 0;
            $('#totalRecipientsWidget').text(finalCount);
            $('#recipientCount').text(finalCount);
        }).catch(() => {
            $('#totalRecipientsWidget').text('0');
            $('#recipientCount').text('0');
        });
    }

    // Removed duplicate - using window.handleQuickRecipientChange instead

    // Show Compose Form (Realtime) - Make available globally
    window.showComposeForm = function(recipientType) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        var $ = jQuery;
        let recipientText = '';
        
        console.log('Showing compose form for:', recipientType);
        
        switch(recipientType) {
            case 'all_parents':
                recipientText = 'All Parents';
                break;
            case 'class_parents':
                const classID = $('#quickClassID').val();
                if (classID) {
                    const className = $('#quickClassID option:selected').text();
                    recipientText = `Parents of ${className}`;
                } else {
                    recipientText = 'Parents of [Select Class]';
                }
                break;
            case 'all_parents_teachers':
                recipientText = 'All Parents & All Teachers';
                break;
            case 'specific_parent':
                const studentName = $('#quickStudentSearch').val();
                if (studentName) {
                    recipientText = `Parent of ${studentName}`;
                } else {
                    recipientText = 'Parent of [Search Student]';
                }
                break;
        }

        $('#selectedRecipientText').text(recipientText);
        
        // Remove hidden class and show the form
        const composeSection = $('#composeMessageSection');
        console.log('Compose section found:', composeSection.length > 0);
        console.log('Has hidden class:', composeSection.hasClass('hidden'));
        
        composeSection.removeClass('hidden');
        
        // Force show with display style
        composeSection.css('display', 'block');
        
        console.log('Compose form should be visible now');
        
        // Scroll to compose form smoothly
        setTimeout(() => {
            const offset = composeSection.offset();
            if (offset) {
                $('html, body').animate({
                    scrollTop: offset.top - 100
                }, 500);
            }
        }, 200);
    };

    // Search Quick Students
    function searchQuickStudents(search) {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        $.ajax({
            url: '{{ route("search_students_sms") }}',
            type: 'GET',
            data: { search: search },
            success: function(response) {
                if (response.success && response.students.length > 0) {
                    let html = '';
                    response.students.forEach(student => {
                        const fullName = `${student.first_name} ${student.middle_name || ''} ${student.last_name}`.trim();
                        const className = student.subclass && student.subclass.class ? student.subclass.class.class_name : 'N/A';
                        // Escape single quotes in name to prevent JavaScript errors
                        const escapedName = fullName.replace(/'/g, "\\'");
                        const escapedAdmission = (student.admission_number || 'N/A').replace(/'/g, "\\'");
                        const escapedClassName = className.replace(/'/g, "\\'");
                        html += `
                            <div class="student-result-item" onclick="selectQuickStudent(${student.studentID}, '${escapedName}', '${escapedAdmission}', '${escapedClassName}')">
                                <div class="student-info">
                                    <div>
                                        <div class="student-name">${fullName}</div>
                                        <div class="student-details">Admission: ${student.admission_number || 'N/A'} | Class: ${className}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#quickStudentResults').html(html).show();
                } else {
                    $('#quickStudentResults').html('<div class="student-result-item">No students found</div>').show();
                }
            },
            error: function() {
                $('#quickStudentResults').html('<div class="student-result-item">Error searching students</div>').show();
            }
        });
    }

    // Select Quick Student - Make available globally
    window.selectQuickStudent = function(studentID, name, admissionNo, className) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        var $ = jQuery;
        
        console.log('Selecting student:', studentID, name);
        
        // Set the selected student ID
        selectedStudentID = studentID;
        $('#studentID').val(studentID);
        $('#quickStudentSearch').val(name);
        $('#quickStudentResults').hide();
        
        // Set recipient type to specific_parent
        $('#recipient_type').val('specific_parent');
        $('#quickRecipientType').val('specific_parent');
        
        // Update recipient count and form
        $('#totalRecipientsWidget').text('1');
        $('#recipientCount').text('1');
        $('#selectedRecipientText').text(`Parent of ${name}`);
        
        // Ensure compose form is visible and properly displayed
        const composeSection = $('#composeMessageSection');
        composeSection.removeClass('hidden');
        composeSection.css('display', 'block');
        composeSection.show();
        
        console.log('Student selected successfully. StudentID:', studentID, 'Recipient Type:', $('#recipient_type').val());
        
        // Scroll to compose form
        setTimeout(() => {
            const offset = composeSection.offset();
            if (offset) {
                $('html, body').animate({
                    scrollTop: offset.top - 100
                }, 500);
            }
        }, 200);
    };

    // Update widget statistics after sending
    function updateWidgetStats(results) {
        $('#totalRecipientsWidget').text(results.total || 0);
        $('#successCountWidget').text(results.success || 0);
        $('#failedCountWidget').text(results.failed || 0);
    }
</script>
