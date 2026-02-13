@include('includes.Admin_nav')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .subject-analysis-wrapper,
    .subject-analysis-wrapper * {
        font-family: "Century Gothic", "Segoe UI", Tahoma, sans-serif;
    }
    .analysis-card {
        border-radius: 12px;
        border: 1px solid #f1d7d7;
        box-shadow: 0 6px 16px rgba(148, 0, 0, 0.08);
    }
    .analysis-title {
        color: #940000;
        font-weight: 600;
    }
    .question-stat {
        background: #fff7f7;
        border: 1px solid #f1d7d7;
        border-radius: 10px;
        padding: 0.75rem;
    }
    .analysis-class-title {
        color: #000000;
    }
</style>

<div class="container-fluid mt-4 subject-analysis-wrapper">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary-custom text-white rounded">
            <h4 class="mb-0"><i class="fa fa-line-chart"></i> Subject Analysis</h4>
        </div>
    </div>

    <div class="card analysis-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.subject_analysis') }}">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label>Year</label>
                        <select class="form-control" name="year" id="analysis_year">
                            <option value="">All</option>
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Term</label>
                        <select class="form-control" name="term" id="analysis_term">
                            <option value="">All</option>
                            <option value="first_term" {{ $term == 'first_term' ? 'selected' : '' }}>First Term</option>
                            <option value="second_term" {{ $term == 'second_term' ? 'selected' : '' }}>Second Term</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Exam</label>
                        <select class="form-control" name="examID" id="analysis_exam" data-selected="{{ $examID }}">
                            <option value="">Select Exam</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Class</label>
                        <select class="form-control" name="classID" id="analysis_class">
                            <option value="">All</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->classID }}" {{ $classID == $class->classID ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Subclass</label>
                        <select class="form-control" name="subclassID" id="analysis_subclass">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Subject</label>
                        <select class="form-control" name="subjectID" id="analysis_subject" data-selected="{{ $subjectID }}">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary-custom w-100" type="submit">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$examID)
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle"></i> Select filters and click Filter to view results.
        </div>
    @else
        @if(empty($analysisData))
            <div class="alert alert-warning text-center">
                <i class="fa fa-exclamation-triangle"></i> No results found. Incomplete.
            </div>
        @else
            @foreach($groupedAnalysis as $classDisplay => $subjects)
                <div class="card analysis-card mb-4">
                    <div class="card-header bg-primary-custom text-white">
                        <h5 class="mb-0 analysis-class-title">
                            <i class="fa fa-users"></i> {{ $classDisplay ?: 'All Classes' }}
                        </h5>
                    </div>
                </div>
                @foreach($subjects as $subjectGroup)
                    <div class="card analysis-card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 analysis-title">
                                {{ $subjectGroup['subject_name'] }}
                            </h5>
                        </div>
                        <div class="card-body">
                        @php
                            $teacher = $subjectGroup['teacher'] ?? null;
                            $teacherName = $teacher
                                ? trim(($teacher->first_name ?? '').' '.($teacher->middle_name ?? '').' '.($teacher->last_name ?? ''))
                                : 'N/A';
                            $teacherPhone = $teacher->phone_number ?? 'N/A';
                            $teacherPhoto = $teacher && $teacher->image
                                ? asset('userImages/'.$teacher->image)
                                : asset('images/male.png');
                            $overall = $subjectGroup['overall_stats'] ?? null;
                        @endphp
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $teacherPhoto }}" alt="Teacher" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                                    <div class="ml-2">
                                        <div class="analysis-title">Teacher: {{ $teacherName }}</div>
                                        <small class="text-muted">Phone: {{ $teacherPhone }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center justify-content-md-end">
                                    <span class="mr-2">Overall:</span>
                                    <span class="badge badge-{{ $overall['remark_class'] ?? 'secondary' }}">
                                        {{ $overall['remark'] ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="text-muted mt-1 text-md-right">
                                    Pass: {{ $overall['pass'] ?? 0 }} ({{ $overall['pass_rate'] ?? 0 }}%) |
                                    Fail: {{ $overall['fail'] ?? 0 }} ({{ $overall['fail_rate'] ?? 0 }}%) |
                                    Answered: {{ $overall['answered'] ?? 0 }}
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body p-3">
                                <label class="mb-2">Send Comment to Teacher</label>
                                <div class="input-group">
                                    <textarea class="form-control subject-comment-text" rows="2" placeholder="Write comment based on subject performance..."></textarea>
                                    <div class="input-group-append">
                                        <button
                                            class="btn btn-primary-custom send-subject-comment"
                                            type="button"
                                            data-teacher-id="{{ $teacher->id ?? '' }}"
                                            data-class-subject-id="{{ $subjectGroup['class_subjectID'] }}"
                                            data-exam-id="{{ $examID }}"
                                        >
                                            Send
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1 comment-status"></small>
                            </div>
                        </div>
                        @if(!empty($subjectGroup['question_stats']))
                            @php
                                $subjectKey = $subjectGroup['class_subjectID'].'-'.preg_replace('/[^a-zA-Z0-9]/', '', strtolower($subjectGroup['subject_name']));
                                $lineChartId = 'line-chart-'.$subjectKey;
                                $barChartId = 'bar-chart-'.$subjectKey;
                                $optionalSelections = [];
                                foreach ($subjectGroup['questions'] as $question) {
                                    if (!empty($question->is_optional)) {
                                        $count = 0;
                                        foreach ($subjectGroup['student_question_marks'] as $studentMarks) {
                                            $mark = $studentMarks[$question->exam_paper_questionID] ?? null;
                                            if ($mark !== null && $mark !== '') {
                                                $count++;
                                            }
                                        }
                                        $optionalSelections[] = [
                                            'label' => 'Qn '.$question->question_number,
                                            'count' => $count,
                                        ];
                                    }
                                }
                                $optionalSelections = collect($optionalSelections)->sortByDesc('count')->values()->all();
                                $totalStudents = max(1, count($subjectGroup['result_rows']));
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="question-stat">
                                        <strong>Best Question:</strong>
                                        {{ $subjectGroup['best_question']['question']->question_description ?? 'N/A' }}
                                        ({{ $subjectGroup['best_question']['percent'] ?? 0 }}%)
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="question-stat">
                                        <strong>Worst Question:</strong>
                                        {{ $subjectGroup['worst_question']['question']->question_description ?? 'N/A' }}
                                        ({{ $subjectGroup['worst_question']['percent'] ?? 0 }}%)
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <h6 class="analysis-title">Question Performance</h6>
                                @foreach($subjectGroup['question_stats'] as $stat)
                                    <div class="d-flex justify-content-between border-bottom py-1">
                                        <span>Qn {{ $stat['question']->question_number }}: {{ $stat['question']->question_description }}</span>
                                        <strong>
                                            {{ $stat['percent'] === null ? 'N/A' : ($stat['percent'] . '%') }}
                                        </strong>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <h6 class="analysis-title">Pass vs Fail Trend</h6>
                                    <canvas id="{{ $lineChartId }}" height="220"></canvas>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="analysis-title">Optional Question Choices</h6>
                                    <canvas id="{{ $barChartId }}" height="220"></canvas>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">No question formats found for this subject.</div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover analysis-students-table">
                                <thead class="bg-primary-custom text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Remark</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectGroup['result_rows'] as $index => $row)
                                        @php
                                            $student = $row['student'];
                                            $photoUrl = $student && $student->photo
                                                ? asset('userImages/'.$student->photo)
                                                : asset('images/male.png');
                                            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                                            $marks = $row['marks'];
                                            $grade = $marks === null ? 'Incomplete' : ($row['grade'] ?? 'N/A');
                                            $remark = $marks === null ? 'Incomplete' : ($row['remark'] ?? 'N/A');
                                            $detailId = 'student-detail-'.$subjectGroup['class_subjectID'].'-'.$student->studentID;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <img src="{{ $photoUrl }}" alt="Student" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                                                {{ $studentName }}
                                            </td>
                                            <td>{{ $subjectGroup['subject_name'] }}</td>
                                            <td>{{ $marks ?? 'Incomplete' }}</td>
                                            <td>{{ $grade }}</td>
                                            <td>{{ $remark }}</td>
                                            <td>
                                                <button
                                                    class="btn btn-sm btn-outline-primary toggle-student-details"
                                                    type="button"
                                                    data-target="#{{ $detailId }}"
                                                >
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="student-detail-row d-none" id="{{ $detailId }}">
                                            <td colspan="7">
                                                @if(!empty($subjectGroup['questions']))
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Question</th>
                                                                    <th>Marks</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($subjectGroup['questions'] as $question)
                                                                    @php
                                                                        $qMarks = $subjectGroup['student_question_marks'][$student->studentID][$question->exam_paper_questionID] ?? '-';
                                                                    @endphp
                                                                    <tr>
                                                                        <td>Qn {{ $question->question_number }}: {{ $question->question_description }}</td>
                                                                        <td>{{ $qMarks }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="text-muted">No question breakdown available.</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endforeach
        @endif
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    $(document).on('click', '.send-subject-comment', function() {
        const $btn = $(this);
        const teacherId = $btn.data('teacher-id');
        const classSubjectId = $btn.data('class-subject-id');
        const examId = $btn.data('exam-id');
        const $wrapper = $btn.closest('.card-body');
        const $text = $wrapper.find('.subject-comment-text');
        const $status = $wrapper.find('.comment-status');
        const message = ($text.val() || '').trim();

        if (!teacherId) {
            $status.text('Teacher not available.').removeClass('text-success').addClass('text-danger');
            return;
        }

        if (!message) {
            $status.text('Please write a comment first.').removeClass('text-success').addClass('text-danger');
            return;
        }

        $status.text('Sending...').removeClass('text-danger text-success').addClass('text-muted');
        $.ajax({
            url: '{{ route("admin.send_subject_analysis_comment") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                teacherID: teacherId,
                class_subjectID: classSubjectId,
                examID: examId,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    $status.text('Comment sent successfully.').removeClass('text-danger text-muted').addClass('text-success');
                    $text.val('');
                } else {
                    $status.text('Failed to send comment.').removeClass('text-success text-muted').addClass('text-danger');
                }
            },
            error: function(xhr) {
                const err = xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message) ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Failed to send comment.';
                $status.text(err).removeClass('text-success text-muted').addClass('text-danger');
            }
        });
    });
    if ($.fn.DataTable) {
        $('.analysis-students-table').each(function() {
            const $table = $(this);
            const detailMap = {};

            $table.find('tr.student-detail-row').each(function() {
                const detailId = $(this).attr('id');
                detailMap[detailId] = $(this).find('td').first().html();
                $(this).remove();
            });

            const dt = $table.DataTable({
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                order: [],
                columnDefs: [{ orderable: false, targets: -1 }]
            });

            $table.data('detailMap', detailMap);
            $table.data('dtInstance', dt);
        });
    }

    $(document).on('click', '.toggle-student-details', function() {
        const $btn = $(this);
        let targetId = $btn.data('target');
        const $row = $btn.closest('tr');
        const $table = $btn.closest('table');
        const dt = $table.data('dtInstance');
        const detailMap = $table.data('detailMap') || {};

        if (typeof targetId === 'string' && targetId.charAt(0) === '#') {
            targetId = targetId.substring(1);
        }

        if (!dt || !targetId || !detailMap[targetId]) {
            return;
        }

        const row = dt.row($row);
        if (row.child.isShown()) {
            row.child.hide();
        } else {
            row.child(detailMap[targetId]).show();
        }
    });
    function loadSubclasses(classID, selectedId) {
        if (!classID) {
            $('#analysis_subclass').html('<option value="">All</option>');
            return;
        }
        $.ajax({
            url: '{{ route("admin.get_subclasses_for_class") }}',
            method: 'GET',
            data: { classID: classID },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">All</option>';
                    response.subclasses.forEach(function(subclass) {
                        const selected = String(subclass.subclassID) === String(selectedId) ? 'selected' : '';
                        options += `<option value="${subclass.subclassID}" ${selected}>${subclass.subclass_name}</option>`;
                    });
                    $('#analysis_subclass').html(options);
                }
            }
        });
    }

    function loadExams(year, term, selectedExam) {
        if (!year || !term) {
            $('#analysis_exam').html('<option value="">Select Exam</option>');
            return;
        }
        $.ajax({
            url: '{{ route("admin.get_exams_for_year_term") }}',
            method: 'GET',
            data: { year: year, term: term },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Select Exam</option>';
                    response.exams.forEach(function(exam) {
                        const selected = String(exam.examID) === String(selectedExam) ? 'selected' : '';
                        options += `<option value="${exam.examID}" ${selected}>${exam.exam_name}</option>`;
                    });
                    $('#analysis_exam').html(options);
                }
            }
        });
    }

    function loadSubjects(classID, subclassID, selectedSubject) {
        if (!classID) {
            $('#analysis_subject').html('<option value="">All</option>');
            return;
        }
        $.ajax({
            url: '{{ route("admin.get_class_subjects_for_analysis") }}',
            method: 'GET',
            data: { classID: classID, subclassID: subclassID },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">All</option>';
                    response.subjects.forEach(function(subject) {
                        const selected = String(subject.subjectID) === String(selectedSubject) ? 'selected' : '';
                        options += `<option value="${subject.subjectID}" ${selected}>${subject.subject_name}</option>`;
                    });
                    $('#analysis_subject').html(options);
                }
            }
        });
    }

    const initialClass = $('#analysis_class').val();
    const initialSubclass = '{{ $subclassID }}';
    const initialYear = $('#analysis_year').val();
    const initialTerm = $('#analysis_term').val();
    const initialExam = $('#analysis_exam').data('selected');
    const initialSubject = $('#analysis_subject').data('selected');
    loadExams(initialYear, initialTerm, initialExam);

    if (initialClass) {
        loadSubclasses(initialClass, initialSubclass);
        if (initialSubclass) {
            loadSubjects(initialClass, initialSubclass, initialSubject);
        }
    }

    $('#analysis_class').on('change', function() {
        const classID = $(this).val();
        loadSubclasses(classID, '');
        $('#analysis_subject').html('<option value="">All</option>');
    });

    $('#analysis_subclass').on('change', function() {
        const classID = $('#analysis_class').val();
        if (classID && $(this).val()) {
            loadSubjects(classID, $(this).val(), '');
        } else {
            $('#analysis_subject').html('<option value="">All</option>');
        }
    });

    $('#analysis_year, #analysis_term').on('change', function() {
        loadExams($('#analysis_year').val(), $('#analysis_term').val(), '');
    });

    @if($examID && !empty($analysisData))
        @foreach($groupedAnalysis as $classDisplay => $subjects)
            @foreach($subjects as $subjectGroup)
                @if(!empty($subjectGroup['question_stats']))
                    @php
                        $subjectKey = $subjectGroup['class_subjectID'].'-'.preg_replace('/[^a-zA-Z0-9]/', '', strtolower($subjectGroup['subject_name']));
                        $lineChartId = 'line-chart-'.$subjectKey;
                        $barChartId = 'bar-chart-'.$subjectKey;
                    $lineLabels = [];
                    $linePass = [];
                    $lineFail = [];
                    foreach ($subjectGroup['question_stats'] as $stat) {
                        if ($stat['percent'] === null) {
                            continue;
                        }
                        $lineLabels[] = 'Qn '.$stat['question']->question_number;
                        $linePass[] = $stat['percent'];
                        $lineFail[] = max(0, 100 - $stat['percent']);
                    }
                        $optionalSelections = [];
                        foreach ($subjectGroup['questions'] as $question) {
                            if (!empty($question->is_optional)) {
                                $count = 0;
                                foreach ($subjectGroup['student_question_marks'] as $studentMarks) {
                                    $mark = $studentMarks[$question->exam_paper_questionID] ?? null;
                                    if ($mark !== null && $mark !== '') {
                                        $count++;
                                    }
                                }
                                $optionalSelections[] = [
                                    'label' => 'Qn '.$question->question_number,
                                    'count' => $count,
                                ];
                            }
                        }
                        $optionalSelections = collect($optionalSelections)->sortByDesc('count')->values()->all();
                        $barLabels = array_map(function ($item) { return $item['label']; }, $optionalSelections);
                        $barCounts = array_map(function ($item) { return $item['count']; }, $optionalSelections);
                        $totalStudents = max(1, count($subjectGroup['result_rows']));
                    @endphp
                    (function() {
                        const lineCtx = document.getElementById(@json($lineChartId));
                        if (lineCtx) {
                            new Chart(lineCtx, {
                                type: 'line',
                                data: {
                                    labels: @json($lineLabels),
                                    datasets: [
                                        {
                                            label: 'Pass %',
                                            data: @json($linePass),
                                            borderColor: '#28a745',
                                            backgroundColor: 'rgba(40, 167, 69, 0.15)',
                                            tension: 0.3,
                                            fill: true
                                        },
                                        {
                                            label: 'Fail %',
                                            data: @json($lineFail),
                                            borderColor: '#dc3545',
                                            backgroundColor: 'rgba(220, 53, 69, 0.15)',
                                            tension: 0.3,
                                            fill: true
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        tooltip: { mode: 'index', intersect: false }
                                    },
                                    scales: {
                                        y: { min: 0, max: 100, ticks: { callback: v => v + '%' } }
                                    }
                                }
                            });
                        }

                        const barCtx = document.getElementById(@json($barChartId));
                        if (barCtx) {
                            new Chart(barCtx, {
                                type: 'bar',
                                data: {
                                    labels: @json($barLabels),
                                    datasets: [{
                                        label: 'Selected',
                                        data: @json($barCounts),
                                        backgroundColor: 'rgba(148, 0, 0, 0.6)'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    const count = context.raw || 0;
                                                    const percent = Math.round((count / @json($totalStudents)) * 100);
                                                    return `Selected by ${count} students (${percent}%)`;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        }
                    })();
                @endif
            @endforeach
        @endforeach
    @endif

});
</script>
