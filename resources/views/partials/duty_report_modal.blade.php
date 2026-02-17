<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<style>
    #signature-pad {
        background-color: #fff;
        border: 1px solid #eee;
    }
    @media (min-width: 1200px) {
        #dutyReportModal .modal-xl {
            max-width: 95% !important;
        }
    }
    .signature-box input::placeholder {
        color: #ddd;
        font-family: Arial, sans-serif;
        font-weight: normal;
        font-size: 0.9rem;
    }
</style>
<div class="modal fade" id="dutyReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-book mr-2"></i> Daily Duty Report</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background-color: #f4f7f6;">
                <form id="dailyDutyForm">
                    @csrf
                    <input type="hidden" name="report_date" id="report_date">
                    <input type="hidden" name="attendance_data" id="attendance_data">
                    <input type="hidden" name="reportID" id="reportID">

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="alert alert-info py-2 px-3 border-0 shadow-sm mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-0"><strong>Teacher on Duty:</strong> <u id="display_teacher_name">---</u></h6>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h6 class="mb-0"><strong>Day:</strong> <span id="display_day" class="text-uppercase text-primary"></span></h6>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <h6 class="mb-0"><strong>Date:</strong> <span id="display_date" class="font-weight-bold"></span></h6>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center" id="attendance_table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th rowspan="2" class="align-middle">CLASS</th>
                                            <th colspan="3">REGISTERED</th>
                                            <th colspan="3">PRESENT</th>
                                            <th colspan="3">SHIFTED</th>
                                            <th colspan="3">NEW COMERS</th>
                                            <th colspan="3">ABSENT</th>
                                            <th colspan="3">PERMISSION</th>
                                            <th colspan="3">SICK</th>
                                        </tr>
                                        <tr style="font-size: 0.7rem;">
                                            @for($i=0; $i<7; $i++)
                                                <th>B</th><th>G</th><th>T</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $cls)
                                        <tr class="class-row" data-class-id="{{ $cls->classID }}">
                                            <td class="bg-light font-weight-bold">{{ $cls->class_name }}</td>
                                            <!-- REGISTERED -->
                                            <td><input type="number" class="form-control form-control-sm reg-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm reg-t" readonly value="0"></td>
                                            <!-- PRESENT -->
                                            <td><input type="number" class="form-control form-control-sm pres-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm pres-t" readonly value="0"></td>
                                            <!-- SHIFTED -->
                                            <td><input type="number" class="form-control form-control-sm shift-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm shift-t" readonly value="0"></td>
                                            <!-- NEW COMERS -->
                                            <td><input type="number" class="form-control form-control-sm new-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm new-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm new-t" readonly value="0"></td>
                                            <!-- ABSENT -->
                                            <td><input type="number" class="form-control form-control-sm abs-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm abs-t" readonly value="0"></td>
                                            <!-- PERMISSION -->
                                            <td><input type="number" class="form-control form-control-sm perm-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm perm-t" readonly value="0"></td>
                                            <!-- SICK -->
                                            <td><input type="number" class="form-control form-control-sm sick-b" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-g" value="0"></td>
                                            <td><input type="number" class="form-control form-control-sm sick-t" readonly value="0"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td>TOTAL</td>
                                            @for($i=0; $i<21; $i++)
                                                <td id="total-{{ $i }}">0</td>
                                            @endfor
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold"><i class="fa fa-calculator mr-1"></i> 1. Attendance report in percentage (%):</label>
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="number" step="0.01" name="attendance_percentage" id="attendance_percentage" class="form-control font-weight-bold" style="background-color: #e8f5e9; color: #2e7d32;">
                                            <div class="input-group-append"><span class="input-group-text bg-success text-white">%</span></div>
                                        </div>
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> Auto-calculated based on system attendance data.</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>2. School environment:</label>
                                        <input type="text" name="school_environment" class="form-control form-control-sm" placeholder="e.g. Well cleaned">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>3. Pupil's cleanliness:</label>
                                        <input type="text" name="pupils_cleanliness" class="form-control form-control-sm" placeholder="e.g. They were smart">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>4. Teacher's attendance:</label>
                                        <textarea name="teachers_attendance" class="form-control form-control-sm" rows="2" placeholder="i) Name... ii) Name..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>5. Time table:</label>
                                        <input type="text" name="timetable_status" class="form-control form-control-sm" placeholder="e.g. Well followed">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>6. Outside activities:</label>
                                        <input type="text" name="outside_activities" class="form-control form-control-sm" placeholder="e.g. Well conducted">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>7. Special events:</label>
                                        <input type="text" name="special_events" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>8. Teachers' on duty comments:</label>
                                        <textarea name="teacher_comments" class="form-control form-control-sm" rows="2" placeholder="Sum up the day..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Feedback Section (Hidden by default) -->
                    <div class="card shadow-sm border-0 border-top border-warning overflow-hidden" id="adminFeedbackSection" style="display: none; border-top-width: 5px !important;">
                        <div class="card-header bg-white py-3">
                            <h6 class="text-warning mb-0 font-weight-bold">
                                <i class="fa fa-gavel mr-2"></i> HEADMASTER / ADMINISTRATOR REVIEW & SIGNATURE
                            </h6>
                        </div>
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold text-muted small uppercase">Admin / Headmaster General Comments:</label>
                                        <textarea name="admin_comments" id="admin_comments" class="form-control border-0 shadow-sm" rows="6" placeholder="Enter feedback, instructions or observations here..." style="resize: none;"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="signature-container">
                                        <label class="font-weight-bold text-muted small uppercase mb-2 d-block">Digital Signature & Name:</label>
                                        <div class="bg-white border rounded shadow-sm p-2 mb-2" style="position: relative;">
                                            <canvas id="signature-pad" class="signature-pad" style="width: 100%; height: 150px; cursor: crosshair;"></canvas>
                                            <button type="button" id="clear-signature" class="btn btn-xs btn-link text-danger" style="position: absolute; bottom: 5px; right: 5px;">
                                                <i class="fa fa-eraser"></i> Clear
                                            </button>
                                            <div id="view-only-signature" style="display: none; width: 100%; height: 150px; text-align: center;">
                                                <img id="signature-image-preview" src="" style="max-height: 100%; max-width: 100%;">
                                            </div>
                                        </div>
                                        <input type="hidden" name="signature_image" id="signature_image_input">
                                        <div class="form-group mb-0">
                                            <input type="text" name="signed_by" id="signed_by" class="form-control font-weight-bold border-0 shadow-sm" placeholder="Type Your Full Name Here" style="background: #fff; color: #000080; border-top: 1px solid #eee !important;">
                                        </div>
                                        <div id="signedAtDisplay" class="text-success small mt-2 font-weight-bold" style="display: none;">
                                            <i class="fa fa-check-circle"></i> SIGNED ON <span id="signedAtDate"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="syncFromAttendance" style="display: none;">
                        <i class="fa fa-refresh"></i> Sync From Attendance
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-danger" id="downloadReportPdf" style="display: none;">
                        <i class="fa fa-file-pdf-o"></i> Download PDF
                    </button>
                    <!-- Teacher Buttons -->
                    <button type="button" class="btn btn-info" id="saveDraft">Save as Draft</button>
                    <button type="button" class="btn btn-primary" id="saveAndSend">Send to Admin</button>
                    
                    <!-- Admin Buttons -->
                    <button type="button" class="btn btn-success" id="btnApproveReport" style="display: none;">
                        <i class="fa fa-check-square-o"></i> Approve & Sign Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
