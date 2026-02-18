@php
    $userType = Session::get('user_type');
    $isAdmin = ($userType == 'Admin');
    // Get list of existing member IDs to exclude them from dropdowns
    $existingTeacherIds = $dept->members->pluck('teacherID')->filter()->toArray();
    $existingStaffIds = $dept->members->pluck('staffID')->filter()->toArray();
    $existingPrefixedIds = array_merge(
        array_map(fn($id) => 't_'.$id, $existingTeacherIds),
        array_map(fn($id) => 's_'.$id, $existingStaffIds)
    );
@endphp

<div class="modal-header">
    <h5 class="modal-title">Members: {{ $dept->department_name }}</h5>
    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
</div>
<div class="modal-body">
    <div class="row">
        <!-- Current Members -->
        <div class="{{ $isAdmin ? 'col-md-7' : 'col-md-12' }}">
            <h6 class="mb-3">Current Members</h6>
            <div class="table-responsive" style="max-height: 400px;">
                <table class="table table-sm table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 30px;" class="text-center">
                                <input type="checkbox" id="checkAllMembers" checked style="cursor: pointer;">
                            </th>
                            <th>Name</th>
                            <th>Role</th>
                            @if($isAdmin)
                            <th class="text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dept->members as $member)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="member-checkbox" 
                                           id="mem_{{ $member->id }}" 
                                           value="{{ $member->id }}" checked
                                           style="cursor: pointer;">
                                </td>
                                <td>
                                    @if($member->teacher)
                                        {{ $member->teacher->first_name }} {{ $member->teacher->last_name }}
                                    @else
                                        {{ $member->staff->first_name }} {{ $member->staff->last_name }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $member->teacher ? 'bg-info' : 'bg-secondary' }}">
                                        {{ $member->teacher ? 'Teacher' : 'Staff' }}
                                    </span>
                                </td>
                                @if($isAdmin)
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger remove-member" data-id="{{ $member->id }}" title="Remove from department">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 4 : 3 }}" class="text-center text-muted py-3">No members yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @php
                $curT = Session::get('teacherID');
                $curS = Session::get('staffID');
                $isHOD = ($dept->head_teacherID == $curT && $curT) || ($dept->head_staffID == $curS && $curS);
            @endphp

            @if($isAdmin || $isHOD)
            <div class="mt-4 p-3 border rounded bg-light">
                <h6><i class="fa fa-envelope"></i> Send SMS to Selected</h6>
                <form id="groupSmsForm">
                    @csrf
                    <div class="mb-2">
                        <textarea class="form-control" name="sms_message" id="sms_message" rows="2" placeholder="Andika ujumbe wako hapa..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100" id="sendSmsBtn">
                        <i class="fa fa-paper-plane"></i> Send SMS
                    </button>
                </form>
            </div>
            @endif
        </div>

        @if($isAdmin)
        <!-- Add New Members (Dynamic Repeater) -->
        <div class="col-md-5 border-left">
            <h6 class="mb-3">Assign New Members</h6>
            <form id="addMemberForm">
                @csrf
                <div id="member_rows_container">
                    <!-- First Row -->
                    <div class="member-row mb-2 d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                            <select class="form-control select2 member-select" name="members[]" required style="width: 100%;">
                                <option value="">-- Select Member --</option>
                                <optgroup label="Teachers">
                                    @foreach($teachers as $t)
                                        @if(!in_array('t_'.$t->id, $existingPrefixedIds))
                                            <option value="t_{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                                        @endif
                                    @endforeach
                                </optgroup>
                                <optgroup label="Staff">
                                    @foreach($staff as $s)
                                        @if(!in_array('s_'.$s->id, $existingPrefixedIds))
                                            <option value="s_{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-row" style="display: none; height: 38px;"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                
                <div class="mt-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addMoreBtn">
                        <i class="fa fa-plus-circle"></i> Add More
                    </button>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="fa fa-save"></i> Assign Selected Members
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
    (function() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        $(document).ready(function() {
            // Initialize Select2 on the first row
            initSelect2($('.member-select'));

            // Load existing selections to disable them in future clones
            const existingIds = @json($existingPrefixedIds);

            $('#addMoreBtn').on('click', function() {
                const $container = $('#member_rows_container');
                /* Clone only the first row div, carefully */
                const $firstRow = $('.member-row').first();
                const $newRow = $firstRow.clone();
                
                // Clean up the new row
                $newRow.find('.select2-container').remove(); // remove select2 span
                const $select = $newRow.find('select');
                
                // Reset select
                $select.removeClass('select2-hidden-accessible');
                $select.removeAttr('data-select2-id');
                $select.removeAttr('tabindex');
                $select.removeAttr('aria-hidden');
                $select.val('');
                $select.show(); // Ensure it's visible before re-initializing
                
                // Show remove button
                $newRow.find('.remove-row').show();
                
                $container.append($newRow);
                
                // Re-init select2
                initSelect2($select);
                
                // Update disabled options
                updateDisabledOptions();
            });

            $(document).on('click', '.member-row .remove-row', function() {
                $(this).closest('.member-row').remove();
                updateDisabledOptions();
            });

            $(document).on('change', '.member-select', function() {
                updateDisabledOptions();
            });

            function initSelect2($element) {
                if ($element && $element.length && typeof $element.select2 === 'function') {
                    $element.select2({
                        dropdownParent: $('#membersModal'),
                        placeholder: "Search member...",
                        width: '100%'
                    });
                }
            }

            function updateDisabledOptions() {
                const selectedValues = [];
                $('.member-select').each(function() {
                    const val = $(this).val();
                    if (val) selectedValues.push(val);
                });

                $('.member-select').each(function() {
                    const $thisSelect = $(this);
                    const currentVal = $thisSelect.val();
                    
                    $thisSelect.find('option').each(function() {
                        const optVal = $(this).val();
                        // Disable if selected elsewhere (but not if it's the current value of this select)
                        if (optVal && optVal !== currentVal && selectedValues.includes(optVal)) {
                            $(this).prop('disabled', true);
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });
                    
                    // Refresh Select2 to reflect disabled state
                    if ($thisSelect.hasClass('select2-hidden-accessible')) {
                        $thisSelect.trigger('change.select2');
                    }
                });
            }

            // Check All Members Toggle
            $(document).on('change', '#checkAllMembers', function() {
                $('.member-checkbox').prop('checked', $(this).prop('checked'));
            });
        });
    })();
</script>

<style>
    .gap-2 { gap: 0.5rem; }
    .member-row .select2-container--default .select2-selection--single {
        height: 38px;
        border-radius: 5px;
        line-height: 38px;
    }
</style>
