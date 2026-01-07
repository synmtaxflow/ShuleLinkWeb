<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Scheme of Work</title>
    
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
            border: 1px solid #ccc;
            padding: 5px;
            font-size: 0.85rem;
            font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
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
        
        .row-number {
            width: 40px;
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .objectives-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .info-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        
        .remark-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .action-buttons {
            white-space: nowrap;
        }
        
        .btn-remove-row {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .btn-remove-row:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0">
                        <i class="fa fa-cog"></i> <span data-translate="manageSchemeOfWork">Manage Scheme of Work</span>
                    </h3>
                    <p class="mb-0 mt-2">
                        Subject: <strong>{{ $scheme->classSubject->subject->subject_name ?? 'N/A' }}</strong>
                        @if($scheme->classSubject->subclass && $scheme->classSubject->subclass->class)
                            | Class: <strong>{{ $scheme->classSubject->subclass->class->class_name }} {{ $scheme->classSubject->subclass->subclass_name }}</strong>
                        @elseif($scheme->classSubject->class)
                            | Class: <strong>{{ $scheme->classSubject->class->class_name }}</strong>
                        @endif
                        | Year: <strong>{{ $scheme->year }}</strong>
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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
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
                    @if($scheme->learningObjectives && $scheme->learningObjectives->count() > 0)
                        @foreach($scheme->learningObjectives as $index => $objective)
                            <div class="objective-item mb-2 d-flex align-items-center" data-objective-index="{{ $index }}">
                                <input type="text" class="form-control mr-2" name="learning_objectives[]" value="{{ $objective->objective_text }}" placeholder="Enter learning objective">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeObjective(this)">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @endif
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
                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="deleteScheme()">
                        <i class="fa fa-trash"></i> Delete Scheme
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
                                <th data-translate="remarks">Remarks</th>
                                <th style="width: 80px;" data-translate="action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="schemeTableBody">
                            @php
                                $rowCount = 1;
                                $itemsByMonth = $scheme->items->groupBy('month');
                                
                                // Define month order
                                $monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 
                                             'July', 'August', 'September', 'October', 'November', 'December'];
                                
                                // Get all months (including empty ones)
                                $allMonths = $monthOrder;
                            @endphp
                            
                            @foreach($allMonths as $month)
                                @php
                                    $monthItems = $itemsByMonth->has($month) ? $itemsByMonth->get($month) : collect();
                                    $monthRowspan = $monthItems->count() > 0 ? $monthItems->count() : 1;
                                    $isFirstRow = true;
                                    
                                    // Check for holidays in this month
                                    $monthHolidays = $allHolidays->filter(function($holiday) use ($month) {
                                        return $holiday['start_month'] === $month || $holiday['end_month'] === $month;
                                    });
                                @endphp
                                
                                @if($monthItems->count() > 0)
                                    @foreach($monthItems as $item)
                                        <tr data-item-id="{{ $item->itemID }}" data-month="{{ $month }}">
                                            <td class="row-number">{{ $rowCount }}</td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][main_competence]" rows="2">{{ $item->main_competence ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][specific_competences]" rows="2">{{ $item->specific_competences ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][learning_activities]" rows="2">{{ $item->learning_activities ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][specific_activities]" rows="2">{{ $item->specific_activities ?? '' }}</textarea>
                                            </td>
                                            @if($isFirstRow)
                                                <td class="month-cell" rowspan="{{ $monthRowspan }}">{{ $month }}</td>
                                                @php $isFirstRow = false; @endphp
                                            @endif
                                            <td>
                                                <input type="text" name="items[{{ $item->itemID }}][week]" value="{{ $item->week ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $item->itemID }}][number_of_periods]" value="{{ $item->number_of_periods ?? '' }}" min="0">
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][teaching_methods]" rows="2">{{ $item->teaching_methods ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][teaching_resources]" rows="2">{{ $item->teaching_resources ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][assessment_tools]" rows="2">{{ $item->assessment_tools ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $item->itemID }}][references]" rows="2">{{ $item->references ?? '' }}</textarea>
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="checkbox" class="remark-checkbox" data-item-id="{{ $item->itemID }}" {{ strtolower(trim($item->remarks ?? '')) === 'done' ? 'checked' : '' }} onchange="updateRemarkStatus(this, {{ $item->itemID }})" title="Mark as Done">
                                            </td>
                                            <td class="action-buttons">
                                                <button type="button" class="btn btn-remove-row btn-sm" onclick="removeRow(this)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @php $rowCount++; @endphp
                                    @endforeach
                                @else
                                    {{-- Empty month - show one row with month cell --}}
                                    <tr data-month="{{ $month }}">
                                        <td class="row-number">{{ $rowCount }}</td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][main_competence]" rows="2"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][specific_competences]" rows="2"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][learning_activities]" rows="2"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][specific_activities]" rows="2"></textarea>
                                        </td>
                                        <td class="month-cell" rowspan="1">{{ $month }}</td>
                                        <td>
                                            <input type="text" name="items[new_{{ $month }}_0][week]">
                                        </td>
                                        <td>
                                            <input type="number" name="items[new_{{ $month }}_0][number_of_periods]" min="0">
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][teaching_methods]" rows="2"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][teaching_resources]" rows="2"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][assessment_tools]" rows="2"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[new_{{ $month }}_0][references]" rows="2"></textarea>
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="checkbox" class="remark-checkbox" data-item-id="new_{{ $month }}_0" onchange="updateRemarkStatus(this, 'new_{{ $month }}_0')" title="Mark as Done" disabled>
                                        </td>
                                        <td class="action-buttons">
                                            <button type="button" class="btn btn-remove-row btn-sm" onclick="removeRow(this)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @php $rowCount++; @endphp
                                @endif
                                
                                {{-- Add holidays for this month --}}
                                @foreach($monthHolidays as $holiday)
                                    <tr class="holiday-row">
                                        <td colspan="14" class="holiday-cell">
                                            {{ $holiday['name'] }} 
                                            @if($holiday['start_date'] === $holiday['end_date'])
                                                ({{ \Carbon\Carbon::parse($holiday['start_date'])->format('j.n.Y') }})
                                            @else
                                                ({{ \Carbon\Carbon::parse($holiday['start_date'])->format('j.n.Y') }} - {{ \Carbon\Carbon::parse($holiday['end_date'])->format('j.n.Y') }})
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                
                                {{-- Add separator row after each month (except last) --}}
                                @if(!$loop->last)
                                    <tr class="month-separator">
                                        <td colspan="14" style="background-color: #e0e0e0; height: 2px; padding: 0; border: none;"></td>
                                    </tr>
                                @endif
                                
                                {{-- Add Row button for each month --}}
                                <tr class="add-row-control" data-control-month="{{ $month }}">
                                    <td colspan="13" style="padding: 10px; background-color: #f9f9f9; text-align: center;">
                                        <button type="button" class="btn btn-sm btn-primary-custom" onclick="addRowToMonth('{{ $month }}')">
                                            <i class="fa fa-plus"></i> <span data-translate="addRow">Add Row</span> to {{ $month }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
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
        let rowCounter = {{ $rowCount }};
        let objectiveCounter = {{ $scheme->learningObjectives ? $scheme->learningObjectives->count() : 0 }};
        
        // Language translations
        const translations = {
            en: {
                manageSchemeOfWork: 'Manage Scheme of Work',
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
                remarks: 'Remarks',
                addRow: 'Add Row',
                action: 'Action'
            },
            sw: {
                manageSchemeOfWork: 'Simamia Ratiba ya Kazi',
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
                remarks: 'Maelezo',
                addRow: 'Ongeza Safu',
                action: 'Kitendo'
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
        
        // Add objective
        function addObjective() {
            const container = document.getElementById('objectivesContainer');
            const div = document.createElement('div');
            div.className = 'objective-item mb-2 d-flex align-items-center';
            div.setAttribute('data-objective-index', objectiveCounter);
            div.innerHTML = `
                <input type="text" class="form-control mr-2" name="learning_objectives[]" placeholder="Enter learning objective">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeObjective(this)">
                    <i class="fa fa-times"></i>
                </button>
            `;
            container.appendChild(div);
            objectiveCounter++;
        }
        
        // Remove objective
        function removeObjective(button) {
            button.closest('.objective-item').remove();
        }
        
        // Update remark status immediately via AJAX
        function updateRemarkStatus(checkbox, itemId) {
            // Check if item is new (not saved yet)
            if (typeof itemId === 'string' && itemId.startsWith('new_')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Save First!',
                    text: 'Please save the scheme first before updating remarks',
                    confirmButtonColor: '#940000',
                    confirmButtonText: 'OK'
                });
                checkbox.checked = false;
                return;
            }
            
            const isChecked = checkbox.checked;
            const remark = isChecked ? 'done' : '';
            
            // Show loading on checkbox
            checkbox.disabled = true;
            
            // Prepare data
            const data = {
                item_id: itemId,
                remark: remark,
                _token: '{{ csrf_token() }}'
            };
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("teacher.updateSchemeOfWork", $scheme->scheme_of_workID) }}/remark',
                method: 'POST',
                data: data,
                success: function(response) {
                    checkbox.disabled = false;
                    if (response.success) {
                        if (isChecked) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Done!',
                                text: 'Remarks updated successfully',
                                confirmButtonColor: '#940000',
                                confirmButtonText: 'OK',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Undo!',
                                text: 'Remarks updated successfully',
                                confirmButtonColor: '#940000',
                                confirmButtonText: 'OK',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        // Revert checkbox state on error
                        checkbox.checked = !isChecked;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to update remarks',
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    checkbox.disabled = false;
                    // Revert checkbox state on error
                    checkbox.checked = !isChecked;
                    const errorMsg = xhr.responseJSON?.message || 'Failed to update remarks';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg,
                        confirmButtonColor: '#940000',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
        
        // Add row to month - matching create page structure
        function addRowToMonth(month) {
            const tbody = document.getElementById('schemeTableBody');
            const monthRows = Array.from(tbody.querySelectorAll(`tr[data-month="${month}"]`)).filter(row => !row.classList.contains('holiday-row') && !row.classList.contains('month-separator') && !row.classList.contains('add-row-control'));
            
            if (monthRows.length === 0) {
                alert('No existing rows found for this month. Please refresh the page.');
                return;
            }
            
            const lastRow = monthRows[monthRows.length - 1];
            const newRowId = `new_${month}_${Date.now()}`;
            
            // Calculate new rowspan for month cell - update the first row's month cell
            const firstRow = monthRows[0];
            const monthCell = firstRow ? firstRow.querySelector('.month-cell') : null;
            if (monthCell) {
                const currentRowspan = parseInt(monthCell.getAttribute('rowspan')) || 1;
                monthCell.setAttribute('rowspan', currentRowspan + 1);
            } else {
                // If no month cell exists (empty month), we need to add it
                console.error('Month cell not found for month:', month);
                return;
            }
            
            // Get row number from last row and increment
            const lastRowNumber = parseInt(lastRow.querySelector('.row-number')?.textContent || '0');
            const newRowNumber = lastRowNumber + 1;
            
            // Create new row - structure must match existing rows (without month cell since it uses rowspan)
            // Structure: row-number, main_competence, specific_competences, learning_activities, specific_activities, week, number_of_periods, teaching_methods, teaching_resources, assessment_tools, references, remarks, action
            // Note: Month cell is NOT included in new row because it uses rowspan from first row
            // Total: 13 columns (matching existing subsequent rows)
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-month', month);
            newRow.innerHTML = `
                <td class="row-number">${newRowNumber}</td>
                <td><textarea name="items[${newRowId}][main_competence]" rows="2"></textarea></td>
                <td><textarea name="items[${newRowId}][specific_competences]" rows="2"></textarea></td>
                <td><textarea name="items[${newRowId}][learning_activities]" rows="2"></textarea></td>
                <td><textarea name="items[${newRowId}][specific_activities]" rows="2"></textarea></td>
                <td><input type="text" name="items[${newRowId}][week]" value=""></td>
                <td><input type="number" name="items[${newRowId}][number_of_periods]" min="0" value=""></td>
                <td><textarea name="items[${newRowId}][teaching_methods]" rows="2"></textarea></td>
                <td><textarea name="items[${newRowId}][teaching_resources]" rows="2"></textarea></td>
                <td><textarea name="items[${newRowId}][assessment_tools]" rows="2"></textarea></td>
                <td><textarea name="items[${newRowId}][references]" rows="2"></textarea></td>
                <td style="text-align: center;">
                    <input type="checkbox" class="remark-checkbox" data-item-id="${newRowId}" onchange="updateRemarkStatus(this, '${newRowId}')" title="Mark as Done" disabled>
                </td>
                <td class="action-buttons">
                    <button type="button" class="btn btn-remove-row btn-sm" onclick="removeRow(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;
            
            // Find add button row for this month and insert before it
            const addButtonRow = tbody.querySelector(`tr.add-row-control[data-control-month="${month}"]`);
            if (addButtonRow) {
                addButtonRow.before(newRow);
            } else {
                // If no add button row, insert after last row of this month
                lastRow.after(newRow);
            }
            
            // Update row counter
            rowCounter = Math.max(rowCounter, newRowNumber);
        }
        
        // Remove row
        function removeRow(button) {
            const row = button.closest('tr');
            const month = row.getAttribute('data-month');
            const monthRows = Array.from(document.querySelectorAll(`tr[data-month="${month}"]`)).filter(r => !r.classList.contains('holiday-row') && !r.classList.contains('month-separator') && !r.classList.contains('add-row-control'));
            
            // Don't allow removing if it's the only row for the month
            if (monthRows.length <= 1) {
                alert('Cannot remove the last row for this month');
                return;
            }
            
            // Update month cell rowspan
            const monthCell = monthRows[0] ? monthRows[0].querySelector('.month-cell') : null;
            if (monthCell) {
                const currentRowspan = parseInt(monthCell.getAttribute('rowspan')) || 1;
                if (currentRowspan > 1) {
                    monthCell.setAttribute('rowspan', currentRowspan - 1);
                }
            }
            
            row.remove();
        }
        
        // Save scheme
        function saveScheme() {
            console.log('Save scheme clicked');
            
            // Show loading state
            const saveBtn = document.querySelector('button[onclick="saveScheme()"]');
            const originalBtnContent = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span data-translate="saving">Saving...</span>';
            
            const formData = {
                learning_objectives: [],
                items: []
            };
            
            // Collect learning objectives
            document.querySelectorAll('input[name="learning_objectives[]"]').forEach(function(input) {
                if (input.value.trim()) {
                    formData.learning_objectives.push(input.value.trim());
                }
            });
            
            // Collect items
            document.querySelectorAll('tr[data-month]').forEach(function(row) {
                if (row.classList.contains('holiday-row') || row.classList.contains('month-separator') || row.classList.contains('add-row-control')) {
                    return;
                }
                
                const itemId = row.getAttribute('data-item-id');
                const month = row.getAttribute('data-month');
                
                // Get current remark value from database (not from checkbox since it updates via AJAX)
                // We don't need to collect checkbox state here anymore since remarks update immediately
                
                const itemData = {
                    month: month,
                    main_competence: row.querySelector('textarea[name*="[main_competence]"]')?.value || '',
                    specific_competences: row.querySelector('textarea[name*="[specific_competences]"]')?.value || '',
                    learning_activities: row.querySelector('textarea[name*="[learning_activities]"]')?.value || '',
                    specific_activities: row.querySelector('textarea[name*="[specific_activities]"]')?.value || '',
                    week: row.querySelector('input[name*="[week]"]')?.value || '',
                    number_of_periods: row.querySelector('input[name*="[number_of_periods]"]')?.value || '',
                    teaching_methods: row.querySelector('textarea[name*="[teaching_methods]"]')?.value || '',
                    teaching_resources: row.querySelector('textarea[name*="[teaching_resources]"]')?.value || '',
                    assessment_tools: row.querySelector('textarea[name*="[assessment_tools]"]')?.value || '',
                    references: row.querySelector('textarea[name*="[references]"]')?.value || ''
                    // Remarks are updated separately via AJAX, not included in save changes
                };
                
                // Add item ID if it's an existing item
                if (itemId) {
                    itemData.itemID = parseInt(itemId);
                }
                
                formData.items.push(itemData);
            });
            
            console.log('Form data:', formData);
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("teacher.updateSchemeOfWork", $scheme->scheme_of_workID) }}',
                method: 'POST',
                data: {
                    learning_objectives: formData.learning_objectives,
                    items: formData.items,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Response:', response);
                    // Restore button
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnContent;
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Scheme of work updated successfully!',
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to update scheme of work',
                            confirmButtonColor: '#940000',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    // Restore button
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnContent;
                    
                    const errorMsg = xhr.responseJSON?.message || 'Failed to update scheme of work';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg,
                        confirmButtonColor: '#940000',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
        
        // Delete scheme
        function deleteScheme() {
            Swal.fire({
                title: 'Delete Scheme?',
                text: 'This will permanently delete this scheme of work. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete It',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route("teacher.deleteSchemeOfWork", $scheme->scheme_of_workID) }}',
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
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
                                    text: response.message,
                                    confirmButtonColor: '#940000',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.message || 'Failed to delete scheme';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMsg,
                                confirmButtonColor: '#940000',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>

