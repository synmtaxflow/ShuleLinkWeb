<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Scheme of Work</title>
    
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        * {
            font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
        }
        
        .fa, .fa:before, i.fa, [class*="fa-"]:before, [class^="fa-"]:before {
            font-family: 'FontAwesome' !important;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .header-section {
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .content-wrapper {
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-primary-custom {
            background-color: #940000;
            border-color: #940000;
            color: #ffffff;
        }
        
        .btn-primary-custom:hover {
            background-color: #b30000;
            border-color: #b30000;
            color: #ffffff;
        }
        
        .scheme-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .scheme-table th,
        .scheme-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .scheme-table th {
            background-color: #940000;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .scheme-table input,
        .scheme-table textarea {
            width: 100%;
            border: none;
            padding: 5px;
            font-size: 0.85rem;
        }
        
        .scheme-table textarea {
            resize: vertical;
            min-height: 40px;
        }
        
        .month-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 30px;
            background-color: #940000;
            color: white;
            font-weight: bold;
        }
        
        .month-cell {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 30px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .holiday-row {
            background-color: #ffe6e6 !important;
        }
        
        .holiday-cell {
            background-color: #ffcccc;
            text-align: center;
            font-weight: bold;
            color: #cc0000;
            padding: 15px !important;
        }
        
        .holiday-row input,
        .holiday-row textarea {
            pointer-events: none;
            background-color: #f5f5f5;
            opacity: 0.6;
        }
        
        .row-number {
            width: 40px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .add-row-btn {
            margin: 10px 0;
        }
        
        .objectives-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .objective-item {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }
        
        .objective-item input {
            flex: 1;
            margin-right: 10px;
        }
        
        
        .remove-row-btn {
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .remove-row-btn:hover {
            background-color: #c82333;
            color: #ffffff;
        }
        
        .remove-objective-btn {
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .remove-objective-btn:hover {
            background-color: #c82333;
            color: #ffffff;
        }
        
        .scheme-table th:last-child,
        .scheme-table td:last-child {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0">
                        <i class="fa fa-file-text-o"></i> <span data-translate="createSchemeOfWork">Create New Scheme of Work</span>
                    </h3>
                    <p class="mb-0 mt-2">
                        Subject: <strong>{{ $classSubject->subject->subject_name ?? 'N/A' }}</strong>
                        @if($classSubject->subclass && $classSubject->subclass->class)
                            | Class: <strong>{{ $classSubject->subclass->class->class_name }} {{ $classSubject->subclass->subclass_name }}</strong>
                        @elseif($classSubject->class)
                            | Class: <strong>{{ $classSubject->class->class_name }}</strong>
                        @endif
                        | Year: <strong>{{ $currentYear }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="d-inline-block mr-2">
                        <select id="languageSelector" class="form-control form-control-sm" style="width: auto; display: inline-block;" onchange="changeLanguage(this.value)">
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                        </select>
                    </div>
                    <a href="{{ route('teacher.schemeOfWork') }}" class="btn btn-light">
                        <i class="fa fa-arrow-left"></i> <span data-translate="goBack">Go Back</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="content-wrapper">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <!-- Learning Objectives Section -->
            <div class="objectives-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fa fa-list"></i> <span data-translate="defineLearningObjectives">Define Learning Objectives</span>
                    </h5>
                    <button type="button" class="btn btn-primary-custom btn-sm" onclick="addObjective()">
                        <i class="fa fa-plus"></i> <span data-translate="addObjective">Add Objective</span>
                    </button>
                </div>
                <div id="objectivesContainer">
                    <!-- Objectives will be added here dynamically -->
                </div>
            </div>

            <!-- Scheme of Work Table Section -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fa fa-table"></i> <span data-translate="schemeOfWorkTable">Scheme of Work Table</span>
                    </h5>
                    <button type="button" class="btn btn-success btn-sm" onclick="saveScheme()">
                        <i class="fa fa-save"></i> <span data-translate="saveChanges">Save Changes</span>
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table class="scheme-table" id="schemeTable">
                        <thead>
                            <tr>
                                <th class="row-number">#</th>
                                <th data-translate="mainCompetence">Main Competence</th>
                                <th data-translate="specificCompetences">Specific Competences</th>
                                <th data-translate="learningActivities">Learning Activities</th>
                                <th data-translate="specificActivities">Specific Activities</th>
                                <th class="month-header" data-translate="month">Month</th>
                                <th data-translate="week">Week</th>
                                <th data-translate="numberOfPeriods">Number of Periods</th>
                                <th data-translate="teachingMethods">Teaching and Learning Methods</th>
                                <th data-translate="teachingResources">Teaching and Learning Resources</th>
                                <th data-translate="assessmentTools">Assessment Tools</th>
                                <th data-translate="references">References</th>
                                <th style="width: 60px;" data-translate="action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="schemeTableBody">
                            <!-- Rows will be added dynamically by month -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        const holidays = @json($holidays ?? []);
        const nonWorkingEvents = @json($nonWorkingEvents ?? []);
        const allHolidays = @json($allHolidays ?? []);
        const classSubjectID = {{ $classSubject->class_subjectID }};
        const year = {{ $currentYear }};
        
        let objectiveCount = 0;
        let rowCount = 0;
        let monthRows = {}; // Track rows per month
        
        // Initialize table with months
        $(document).ready(function() {
            initializeTable();
        });
        
        function initializeTable() {
            const tbody = $('#schemeTableBody');
            tbody.empty();
            monthRows = {};
            rowCount = 0;
            
            months.forEach((month, monthIndex) => {
                // Check for holidays in this month
                const monthHolidays = getHolidaysForMonth(monthIndex + 1);
                
                // Add holiday rows first if they exist
                if (monthHolidays.length > 0) {
                    const holidayRows = createHolidayRow(month, monthHolidays);
                    tbody.append(holidayRows);
                }
                
                // Add first data row for this month (WITH month cell and rowspan=1)
                const lastHolidayRow = $(`.holiday-row[data-holiday-month="${month}"]`).last();
                if (lastHolidayRow.length > 0) {
                    addFirstRowForMonth(month, lastHolidayRow);
                } else {
                    addFirstRowForMonth(month);
                }
            });
        }
        
        // Add first row for month (with month cell)
        function addFirstRowForMonth(month, insertAfterRow = null) {
            if (!monthRows[month]) {
                monthRows[month] = 0;
            }
            monthRows[month]++;
            rowCount++;
            
            const rowOrder = monthRows[month] - 1;
            
            // First row has month cell with rowspan=1 (will be updated when more rows are added)
            const row = $(`
                <tr data-month="${month}" data-row-order="${rowOrder}" data-row-id="${rowCount}">
                    <td class="row-number">${rowCount}</td>
                    <td><input type="text" name="items[${rowCount}][main_competence]" class="form-control" /></td>
                    <td><textarea name="items[${rowCount}][specific_competences]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][learning_activities]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][specific_activities]" class="form-control" rows="2"></textarea></td>
                    <td class="month-cell" rowspan="1">${month}</td>
                    <td><input type="text" name="items[${rowCount}][week]" class="form-control" placeholder="e.g. Week 1, Week 2..." /></td>
                    <td><input type="number" name="items[${rowCount}][number_of_periods]" class="form-control" min="1" /></td>
                    <td><textarea name="items[${rowCount}][teaching_methods]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][teaching_resources]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][assessment_tools]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][references]" class="form-control" rows="2"></textarea></td>
                    <td>
                        <button type="button" class="btn btn-sm remove-row-btn" onclick="removeRow(${rowCount}, '${month}')" title="Remove Row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            
            // Add hidden fields
            row.append(
                `<input type="hidden" name="items[${rowCount}][month]" value="${month}" />` +
                `<input type="hidden" name="items[${rowCount}][row_order]" value="${rowOrder}" />`
            );
            
            // Insert row
            if (insertAfterRow && insertAfterRow.length > 0) {
                insertAfterRow.after(row);
            } else {
                // Find position based on month order
                let insertAfter = null;
                const monthIndex = months.indexOf(month);
                for (let i = monthIndex - 1; i >= 0; i--) {
                    const prevMonthRows = $(`tr[data-month="${months[i]}"]:not(.holiday-row)`);
                    if (prevMonthRows.length > 0) {
                        insertAfter = prevMonthRows.last();
                        break;
                    }
                    const prevMonthHoliday = $(`.holiday-row[data-holiday-month="${months[i]}"]`);
                    if (prevMonthHoliday.length > 0) {
                        insertAfter = prevMonthHoliday;
                        break;
                    }
                }
                if (insertAfter && insertAfter.length > 0) {
                    insertAfter.after(row);
                } else {
                    $('#schemeTableBody').append(row);
                }
            }
            
            // Update row numbers
            updateRowNumbers();
        }
        
        function getHolidaysForMonth(monthIndex) {
            const monthName = months[monthIndex - 1];
            const monthHolidays = [];
            
            // Check all holidays (including non-working events)
            allHolidays.forEach(holiday => {
                const startDate = new Date(holiday.start_date);
                const endDate = new Date(holiday.end_date);
                const startMonth = startDate.getMonth() + 1;
                const endMonth = endDate.getMonth() + 1;
                
                // Check if holiday falls in this month
                if (startMonth === monthIndex || endMonth === monthIndex || 
                    (startMonth < monthIndex && endMonth > monthIndex)) {
                    monthHolidays.push(holiday);
                }
            });
            
            return monthHolidays;
        }
        
        function createHolidayRow(month, monthHolidays) {
            let holidayRows = '';
            
            monthHolidays.forEach(holiday => {
                const startDate = new Date(holiday.start_date);
                const endDate = new Date(holiday.end_date);
                const startDateStr = formatDate(holiday.start_date);
                const endDateStr = formatDate(holiday.end_date);
                
                let dateRange = startDateStr;
                if (startDateStr !== endDateStr) {
                    dateRange = `${startDateStr} - ${endDateStr}`;
                }
                
                holidayRows += `
                    <tr class="holiday-row" data-holiday-month="${month}" data-holiday-start="${holiday.start_date}" data-holiday-end="${holiday.end_date}">
                        <td class="row-number"></td>
                        <td class="holiday-cell" colspan="11">
                            <strong>${holiday.name}</strong> - ${dateRange}
                            <input type="hidden" name="holiday_${holiday.start_date}_${holiday.end_date}" value="1" />
                        </td>
                        <td></td>
                    </tr>
                `;
            });
            
            return holidayRows;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = date.getDate();
            const month = date.getMonth() + 1;
            return `${day}.${month}`;
        }
        
        function addRowForMonth(month, insertAfterRow = null) {
            if (!monthRows[month]) {
                monthRows[month] = 0;
            }
            monthRows[month]++;
            rowCount++;
            
            const rowOrder = monthRows[month] - 1;
            
            // Count existing rows for this month to calculate rowspan (ONLY for month column)
            const existingRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
            const newRowspan = existingRows.length + 1;
            
            // Update ONLY the first row's month cell rowspan (month column only, NOT week)
            const firstRow = existingRows.first();
            if (firstRow.length > 0) {
                const monthCell = firstRow.find('.month-cell');
                if (monthCell.length > 0) {
                    monthCell.attr('rowspan', newRowspan);
                }
            }
            
            // Create new row WITHOUT month cell (since it's already in first row with rowspan)
            const row = $(`
                <tr data-month="${month}" data-row-order="${rowOrder}" data-row-id="${rowCount}">
                    <td class="row-number">${rowCount}</td>
                    <td><input type="text" name="items[${rowCount}][main_competence]" class="form-control" /></td>
                    <td><textarea name="items[${rowCount}][specific_competences]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][learning_activities]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][specific_activities]" class="form-control" rows="2"></textarea></td>
                    <td><input type="text" name="items[${rowCount}][week]" class="form-control" placeholder="e.g. Week 1, Week 2..." /></td>
                    <td><input type="number" name="items[${rowCount}][number_of_periods]" class="form-control" min="1" /></td>
                    <td><textarea name="items[${rowCount}][teaching_methods]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][teaching_resources]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][assessment_tools]" class="form-control" rows="2"></textarea></td>
                    <td><textarea name="items[${rowCount}][references]" class="form-control" rows="2"></textarea></td>
                    <td>
                        <button type="button" class="btn btn-sm remove-row-btn" onclick="removeRow(${rowCount}, '${month}')" title="Remove Row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            
            // Add hidden fields
            row.append(
                `<input type="hidden" name="items[${rowCount}][month]" value="${month}" />` +
                `<input type="hidden" name="items[${rowCount}][row_order]" value="${rowOrder}" />`
            );
            
            // Insert row
            if (insertAfterRow && insertAfterRow.length > 0) {
                insertAfterRow.after(row);
            } else {
                // Find where to insert
                const lastMonthRow = $(`tr[data-month="${month}"]:not(.holiday-row)`).last();
                if (lastMonthRow.length > 0) {
                    lastMonthRow.after(row);
                } else {
                    // Find position based on month order
                    let insertAfter = null;
                    const monthIndex = months.indexOf(month);
                    for (let i = monthIndex - 1; i >= 0; i--) {
                        const prevMonthRows = $(`tr[data-month="${months[i]}"]:not(.holiday-row)`);
                        if (prevMonthRows.length > 0) {
                            insertAfter = prevMonthRows.last();
                            break;
                        }
                        // Check for holiday rows
                        const prevMonthHoliday = $(`.holiday-row[data-holiday-month="${months[i]}"]`);
                        if (prevMonthHoliday.length > 0) {
                            insertAfter = prevMonthHoliday;
                            break;
                        }
                    }
                    if (insertAfter && insertAfter.length > 0) {
                        insertAfter.after(row);
                    } else {
                        $('#schemeTableBody').append(row);
                    }
                }
            }
            
            // Update row numbers
            updateRowNumbers();
        }
        
        function updateRowNumbers() {
            let counter = 1;
            $('#schemeTableBody tr[data-month]:not(.holiday-row)').each(function() {
                $(this).find('.row-number').text(counter);
                counter++;
            });
        }
        
        function addObjective() {
            objectiveCount++;
            const objectiveHtml = `
                <div class="objective-item" data-objective-id="${objectiveCount}">
                    <input type="text" name="learning_objectives[]" class="form-control" placeholder="Enter learning objective..." />
                    <button type="button" class="btn btn-sm remove-objective-btn" onclick="removeObjective(${objectiveCount})">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `;
            $('#objectivesContainer').append(objectiveHtml);
        }
        
        function removeObjective(id) {
            $(`.objective-item[data-objective-id="${id}"]`).remove();
        }
        
        function saveScheme() {
            const formData = {
                class_subjectID: classSubjectID,
                year: year,
                learning_objectives: [],
                items: [],
                _token: '{{ csrf_token() }}'
            };
            
            // Collect learning objectives
            $('input[name="learning_objectives[]"]').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    formData.learning_objectives.push(value);
                }
            });
            
            // Collect table items (skip holiday rows)
            $('tr[data-month]:not(.holiday-row)').each(function() {
                const month = $(this).data('month');
                const rowOrder = $(this).data('row-order');
                const rowId = $(this).data('row-id');
                
                if (!rowId || !month) return; // Skip if no row ID or month
                
                const item = {
                    month: month,
                    row_order: rowOrder,
                    main_competence: $(this).find('input[name*="[main_competence]"]').val() || null,
                    specific_competences: $(this).find('textarea[name*="[specific_competences]"]').val() || null,
                    learning_activities: $(this).find('textarea[name*="[learning_activities]"]').val() || null,
                    specific_activities: $(this).find('textarea[name*="[specific_activities]"]').val() || null,
                    week: $(this).find('input[name*="[week]"]').val() || null,
                    number_of_periods: $(this).find('input[name*="[number_of_periods]"]').val() || null,
                    teaching_methods: $(this).find('textarea[name*="[teaching_methods]"]').val() || null,
                    teaching_resources: $(this).find('textarea[name*="[teaching_resources]"]').val() || null,
                    assessment_tools: $(this).find('textarea[name*="[assessment_tools]"]').val() || null,
                    references: $(this).find('textarea[name*="[references]"]').val() || null
                };
                
                // Include item if at least one field is filled
                if (item.main_competence || item.specific_competences || item.learning_activities || 
                    item.specific_activities || item.week || item.number_of_periods) {
                    formData.items.push(item);
                }
            });
            
            // Validate
            if (formData.items.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Please add at least one row to the scheme of work table.',
                    confirmButtonColor: '#940000',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show loading
            const saveBtn = $('button[onclick="saveScheme()"]');
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <span data-translate="saving">Saving...</span>');
            
            // Submit
            $.ajax({
                url: '{{ route("teacher.storeSchemeOfWork") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Scheme of work created successfully!',
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = '{{ route("teacher.schemeOfWork") }}';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to save scheme of work',
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'OK'
                        });
                        saveBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Failed to save scheme of work';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg,
                        confirmButtonColor: '#940000',
                        confirmButtonText: 'OK'
                    });
                    saveBtn.prop('disabled', false).html(originalText);
                }
            });
        }
        
        // Add row button functionality - will be added per month section
        function addRowToMonth(month) {
            const lastRow = $(`tr[data-month="${month}"]:not(.holiday-row)`).last();
            addRowForMonth(month, lastRow);
            // Refresh add buttons after adding row
            setTimeout(refreshAddButtons, 200);
        }
        
        // Remove row functionality
        function removeRow(rowId, month) {
            const row = $(`tr[data-row-id="${rowId}"]`);
            if (row.length === 0) return;
            
            // Check if this is the last row for this month
            const monthRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
            if (monthRows.length <= 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Cannot remove the last row in a month. Each month must have at least one row.',
                    confirmButtonColor: '#940000',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Remove the row
            row.remove();
            
            // Update rowspan for remaining rows in the same month (ONLY first row has month cell)
            const remainingRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
            if (remainingRows.length > 0) {
                const newRowspan = remainingRows.length;
                const firstRow = remainingRows.first();
                const monthCell = firstRow.find('.month-cell');
                if (monthCell.length > 0) {
                    monthCell.attr('rowspan', newRowspan);
                }
            }
            
            // Update row numbers
            updateRowNumbers();
            
            // Refresh add buttons
            setTimeout(refreshAddButtons, 100);
        }
        
        // Add month sections with add row buttons
        function addMonthSectionControls() {
            months.forEach(month => {
                const monthDataRows = $(`tr[data-month="${month}"]:not(.holiday-row)`);
                if (monthDataRows.length > 0) {
                    const lastRow = monthDataRows.last();
                    // Check if add button already exists
                    if (lastRow.next('.add-row-control').length === 0) {
                        const addBtnRow = $(`
                            <tr class="add-row-control" data-control-month="${month}">
                                <td colspan="12" style="padding: 10px; background-color: #f9f9f9; text-align: center;">
                                    <button type="button" class="btn btn-sm btn-primary-custom" onclick="addRowToMonth('${month}')">
                                        <i class="fa fa-plus"></i> Add Row to ${month}
                                    </button>
                                </td>
                            </tr>
                        `);
                        lastRow.after(addBtnRow);
                    } else {
                        // Move button to after last row
                        const existingBtn = lastRow.next('.add-row-control');
                        if (existingBtn.length > 0 && existingBtn.data('control-month') === month) {
                            // Button is in correct position
                        } else {
                            existingBtn.remove();
                            const addBtnRow = $(`
                                <tr class="add-row-control" data-control-month="${month}">
                                    <td colspan="12" style="padding: 10px; background-color: #f9f9f9; text-align: center;">
                                        <button type="button" class="btn btn-sm btn-primary-custom" onclick="addRowToMonth('${month}')">
                                            <i class="fa fa-plus"></i> Add Row to ${month}
                                        </button>
                                    </td>
                                </tr>
                            `);
                            lastRow.after(addBtnRow);
                        }
                    }
                }
            });
        }
        
        // Re-initialize add buttons after adding rows
        function refreshAddButtons() {
            $('.add-row-control').remove();
            setTimeout(addMonthSectionControls, 100);
        }
        
        // Initialize add buttons after table is ready
        setTimeout(addMonthSectionControls, 500);
        
        // Language translations
        const translations = {
            en: {
                createSchemeOfWork: 'Create New Scheme of Work',
                goBack: 'Go Back',
                defineLearningObjectives: 'Define Learning Objectives',
                addObjective: 'Add Objective',
                schemeOfWorkTable: 'Scheme of Work Table',
                saveChanges: 'Save Changes',
                mainCompetence: 'Main Competence',
                specificCompetences: 'Specific Competences',
                learningActivities: 'Learning Activities',
                specificActivities: 'Specific Activities',
                month: 'Month',
                week: 'Week',
                numberOfPeriods: 'Number of Periods',
                teachingMethods: 'Teaching and Learning Methods',
                teachingResources: 'Teaching and Learning Resources',
                assessmentTools: 'Assessment Tools',
                references: 'References',
                addRow: 'Add Row',
                remove: 'Remove',
                action: 'Action',
                saving: 'Saving...'
            },
            sw: {
                createSchemeOfWork: 'Unda Ratiba Mpya ya Kazi',
                goBack: 'Rudi Nyuma',
                defineLearningObjectives: 'Fafanua Malengo ya Kujifunza',
                addObjective: 'Ongeza Lengo',
                schemeOfWorkTable: 'Jedwali la Ratiba ya Kazi',
                saveChanges: 'Hifadhi Mabadiliko',
                mainCompetence: 'Uwezo Mkuu',
                specificCompetences: 'Uwezo Maalum',
                learningActivities: 'Shughuli za Kujifunza',
                specificActivities: 'Shughuli Maalum',
                month: 'Mwezi',
                week: 'Wiki',
                numberOfPeriods: 'Idadi ya Vipindi',
                teachingMethods: 'Mbinu za Kufundisha na Kujifunza',
                teachingResources: 'Rasilimali za Kufundisha na Kujifunza',
                assessmentTools: 'Zana za Tathmini',
                references: 'Marejeo',
                addRow: 'Ongeza Safu',
                remove: 'Ondoa',
                action: 'Kitendo',
                saving: 'Inahifadhi...'
            }
        };
        
        // Get current language
        function getCurrentLanguage() {
            const selector = document.getElementById('languageSelector');
            return selector ? selector.value : 'en';
        }
        
        // Get translation
        function t(key) {
            const lang = getCurrentLanguage();
            return translations[lang][key] || translations.en[key] || key;
        }
        
        // Change language function
        function changeLanguage(lang) {
            console.log('Changing language to:', lang);
            
            // Update all static text on the page
            document.querySelectorAll('[data-translate]').forEach(function(element) {
                const key = element.getAttribute('data-translate');
                const translation = t(key);
                element.textContent = translation;
            });
        }
    </script>
</body>
</html>

