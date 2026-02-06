@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .card, .alert, .btn, div, .form-control, .form-select {
        border-radius: 0 !important;
    }
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
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
    .performance-tabs .nav-link {
        cursor: pointer;
        color: #940000;
        border-radius: 0;
    }
    .performance-tabs .nav-link.active {
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
        border-color: #940000;
    }
    .performance-menu .list-group-item {
        cursor: pointer;
        border-left: 4px solid transparent;
    }
    .performance-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 600;
    }
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
    }
    .performance-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .form-loading {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(148, 0, 0, 0.25);
        background: rgba(148, 0, 0, 0.05);
        margin-bottom: 12px;
    }
    .form-progress {
        position: relative;
        flex: 1;
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .form-progress::after {
        content: "";
        position: absolute;
        left: -40%;
        width: 40%;
        height: 100%;
        background: #940000;
        animation: progressSlide 1.1s linear infinite;
    }
    @keyframes progressSlide {
        0% { left: -40%; }
        100% { left: 100%; }
    }
    .chart-wrapper {
        height: 280px;
    }
</style>

<div class="breadcrumbs">
    <div class="col-sm-6">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Performance</h1>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="card">
        <div class="card-header bg-primary-custom text-white">
            <strong>Performance Dashboard</strong>
        </div>
        <div class="card-body performance-body">
            <ul class="nav nav-tabs performance-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link active" data-tab="teacher">Teacher Performance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-tab="student">Student Performance</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-sm-4">
                    <div class="list-group performance-menu">
                        <a class="list-group-item active" data-section="term">
                            <i class="fa fa-calendar"></i> View Term Performance
                        </a>
                        <a class="list-group-item" data-section="exam">
                            <i class="fa fa-file-text-o"></i> View Exam Performance
                        </a>
                        <a class="list-group-item" data-section="year">
                            <i class="fa fa-bar-chart"></i> View Year Performance
                        </a>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-loading" id="performanceLoading">
                        <span><i class="fa fa-spinner fa-spin text-primary-custom"></i> Loading...</span>
                        <div class="form-progress"></div>
                    </div>

                    <div class="performance-section" data-tab="teacher" data-section="term">
                        <div class="section-title">Teacher Term Performance</div>
                        <form id="teacherTermForm">
                            <div class="form-group mb-3">
                                <label>Teacher</label>
                                <select class="form-select teacher-select" name="teacher_id" data-subject-target="teacherTermSubject" required>
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subject</label>
                                <select class="form-select teacher-subject-select" id="teacherTermSubject" name="subject_id" required disabled>
                                    <option value="">Select teacher first</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="col-sm-6 mb-3">
                                <label>Term</label>
                                    <select class="form-select" name="term" required>
                                        <option value="">-- Select Term --</option>
                                        <option value="first_term">First Term</option>
                                        <option value="second_term">Second Term</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label>Year</label>
                                    <select class="form-select" name="year" required>
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                        <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                        <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="chart-wrapper">
                                <canvas id="termPerformanceChart"></canvas>
                            </div>
                            <div class="mt-3" id="termPerformanceSummary"></div>
                        </div>
                    </div>

                    <div class="performance-section d-none" data-tab="teacher" data-section="exam">
                        <div class="section-title">Teacher Exam Performance</div>
                        <form id="teacherExamForm">
                            <div class="form-group mb-3">
                                <label>Teacher</label>
                                <select class="form-select teacher-select" name="teacher_id" data-subject-target="teacherExamSubject" required>
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subject</label>
                                <select class="form-select teacher-subject-select" id="teacherExamSubject" name="subject_id" required disabled>
                                    <option value="">Select teacher first</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Exam</label>
                                <select class="form-select" name="exam_id" required>
                                    <option value="">-- Select Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->examID }}">{{ $exam->exam_name }} ({{ ucfirst(str_replace('_',' ', $exam->term)) }} {{ $exam->year }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="chart-wrapper">
                                <canvas id="examPerformanceChart"></canvas>
                            </div>
                            <div class="mt-3" id="examPerformanceSummary"></div>
                        </div>
                    </div>

                    <div class="performance-section d-none" data-tab="teacher" data-section="year">
                        <div class="section-title">Teacher Year Performance</div>
                        <form id="teacherYearForm">
                            <div class="form-group mb-3">
                                <label>Teacher</label>
                                <select class="form-select teacher-select" name="teacher_id" data-subject-target="teacherYearSubject" required>
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subject</label>
                                <select class="form-select teacher-subject-select" id="teacherYearSubject" name="subject_id" required disabled>
                                    <option value="">Select teacher first</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Year</label>
                                <select class="form-select" name="year" required>
                                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                    <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                                </select>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="chart-wrapper">
                                <canvas id="yearPerformanceChart"></canvas>
                            </div>
                            <div class="mt-3" id="yearPerformanceSummary"></div>
                        </div>
                    </div>

                    <div class="performance-section d-none" data-tab="student" data-section="term">
                        <div class="section-title">Student Term Performance</div>
                        <form id="studentTermForm">
                            <div class="form-group mb-3">
                                <label>Class</label>
                                <select class="form-select class-select" name="class_id" data-subclass-target="studentTermSubclass" data-subject-target="studentTermSubject" data-student-target="studentTermStudent" required>
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subclass (optional)</label>
                                <select class="form-select class-subclass-select" id="studentTermSubclass" name="subclass_id" disabled>
                                    <option value="">All Subclasses</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Student (optional)</label>
                                <select class="form-select class-student-select" id="studentTermStudent" name="student_id" disabled>
                                    <option value="">All Students</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subject</label>
                                <select class="form-select class-subject-select" id="studentTermSubject" name="subject_id" required disabled>
                                    <option value="">Select class first</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="col-sm-6 mb-3">
                                    <label>Term</label>
                                    <select class="form-select" name="term" required>
                                        <option value="">-- Select Term --</option>
                                        <option value="first_term">First Term</option>
                                        <option value="second_term">Second Term</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label>Year</label>
                                    <select class="form-select" name="year" required>
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                        <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                        <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="chart-wrapper">
                                <canvas id="studentTermPerformanceChart"></canvas>
                            </div>
                            <div class="mt-3" id="studentTermPerformanceSummary"></div>
                        </div>
                    </div>
                    <div class="performance-section d-none" data-tab="student" data-section="exam">
                        <div class="section-title">Student Exam Performance</div>
                        <form id="studentExamForm">
                            <div class="form-group mb-3">
                                <label>Class</label>
                                <select class="form-select class-select" name="class_id" data-subclass-target="studentExamSubclass" data-subject-target="studentExamSubject" data-student-target="studentExamStudent" required>
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subclass (optional)</label>
                                <select class="form-select class-subclass-select" id="studentExamSubclass" name="subclass_id" disabled>
                                    <option value="">All Subclasses</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Student (optional)</label>
                                <select class="form-select class-student-select" id="studentExamStudent" name="student_id" disabled>
                                    <option value="">All Students</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subject</label>
                                <select class="form-select class-subject-select" id="studentExamSubject" name="subject_id" required disabled>
                                    <option value="">Select class first</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Exam</label>
                                <select class="form-select" name="exam_id" required>
                                    <option value="">-- Select Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->examID }}">{{ $exam->exam_name }} ({{ ucfirst(str_replace('_',' ', $exam->term)) }} {{ $exam->year }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="chart-wrapper">
                                <canvas id="studentExamPerformanceChart"></canvas>
                            </div>
                            <div class="mt-3" id="studentExamPerformanceSummary"></div>
                        </div>
                    </div>
                    <div class="performance-section d-none" data-tab="student" data-section="year">
                        <div class="section-title">Student Year Performance</div>
                        <form id="studentYearForm">
                            <div class="form-group mb-3">
                                <label>Class</label>
                                <select class="form-select class-select" name="class_id" data-subclass-target="studentYearSubclass" data-subject-target="studentYearSubject" data-student-target="studentYearStudent" required>
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subclass (optional)</label>
                                <select class="form-select class-subclass-select" id="studentYearSubclass" name="subclass_id" disabled>
                                    <option value="">All Subclasses</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Student (optional)</label>
                                <select class="form-select class-student-select" id="studentYearStudent" name="student_id" disabled>
                                    <option value="">All Students</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Subject</label>
                                <select class="form-select class-subject-select" id="studentYearSubject" name="subject_id" required disabled>
                                    <option value="">Select class first</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label>Year</label>
                                <select class="form-select" name="year" required>
                                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                    <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                                    <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                                </select>
                            </div>
                            <button class="btn btn-primary-custom" type="submit">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="chart-wrapper">
                                <canvas id="studentYearPerformanceChart"></canvas>
                            </div>
                            <div class="mt-3" id="studentYearPerformanceSummary"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script>
    (function() {
        const tabs = document.querySelectorAll('.performance-tabs .nav-link');
        const menuItems = document.querySelectorAll('.performance-menu .list-group-item');
        const sections = document.querySelectorAll('.performance-section');
        const loadingBar = document.getElementById('performanceLoading');
        const teacherSelects = document.querySelectorAll('.teacher-select');
        const subjectsEndpoint = "{{ route('admin.performance.teacher.subjects') }}";
        const classSelects = document.querySelectorAll('.class-select');
        const classSubjectsEndpoint = "{{ route('admin.performance.class.subjects') }}";
        const classSubclassesEndpoint = "{{ route('admin.performance.class.subclasses') }}";
        const classStudentsEndpoint = "{{ route('admin.performance.class.students') }}";

        let activeTab = 'teacher';
        let activeSection = 'term';

        function showSections() {
            sections.forEach(section => {
                const sectionName = section.getAttribute('data-section');
                const tabName = section.getAttribute('data-tab');
                if (sectionName === activeSection && tabName === activeTab) {
                    section.classList.remove('d-none');
                } else {
                    section.classList.add('d-none');
                }
            });
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                activeTab = this.getAttribute('data-tab');
                showSections();
            });
        });

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                activeSection = this.getAttribute('data-section');
                showSections();
            });
        });

        let termChart, examChart, yearChart;
        let studentTermChart, studentExamChart, studentYearChart;

        function renderChart(ctxId, data) {
            const ctx = document.getElementById(ctxId);
            if (!ctx) return null;
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Pass', 'Fail'],
                    datasets: [{
                        data: [data.pass_count, data.fail_count],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function setSummary(elementId, data) {
            const el = document.getElementById(elementId);
            if (!el) return;
            if (!data || data.total === 0) {
                el.innerHTML = '<div class="alert alert-info">No results found for selected filters.</div>';
                return;
            }
            const comment = data.comment || '';
            el.innerHTML = `
                <div class="alert alert-success">
                    <strong>Pass:</strong> ${data.pass_rate}% (${data.pass_count}/${data.total}) |
                    <strong>Fail:</strong> ${data.fail_rate}% (${data.fail_count}/${data.total})
                    <div>${comment}</div>
                </div>
            `;
        }

        function handleSubmit(formId, url, chartRef, chartSetter, summaryId, chartId) {
            const form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (form.dataset.loading === '1') {
                    return;
                }
                form.dataset.loading = '1';
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) submitButton.disabled = true;
                if (loadingBar) loadingBar.style.display = 'flex';
                const formData = new FormData(form);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(res => {
                    const contentType = res.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        return Promise.resolve({ success: false, data: { total: 0 } });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        if (chartRef && chartRef.destroy) chartRef.destroy();
                        const newChart = renderChart(chartId, data.data);
                        chartSetter(newChart);
                        setSummary(summaryId, data.data);
                    } else {
                        setSummary(summaryId, { total: 0 });
                    }
                })
                .catch(() => {
                    setSummary(summaryId, { total: 0 });
                })
                .finally(() => {
                    if (loadingBar) loadingBar.style.display = 'none';
                    form.dataset.loading = '0';
                    if (submitButton) submitButton.disabled = false;
                });
            });
        }

        handleSubmit('teacherTermForm', '{{ route('admin.performance.teacher.term') }}', termChart, chart => termChart = chart, 'termPerformanceSummary', 'termPerformanceChart');
        handleSubmit('teacherExamForm', '{{ route('admin.performance.teacher.exam') }}', examChart, chart => examChart = chart, 'examPerformanceSummary', 'examPerformanceChart');
        handleSubmit('teacherYearForm', '{{ route('admin.performance.teacher.year') }}', yearChart, chart => yearChart = chart, 'yearPerformanceSummary', 'yearPerformanceChart');
        handleSubmit('studentTermForm', '{{ route('admin.performance.student.term') }}', studentTermChart, chart => studentTermChart = chart, 'studentTermPerformanceSummary', 'studentTermPerformanceChart');
        handleSubmit('studentExamForm', '{{ route('admin.performance.student.exam') }}', studentExamChart, chart => studentExamChart = chart, 'studentExamPerformanceSummary', 'studentExamPerformanceChart');
        handleSubmit('studentYearForm', '{{ route('admin.performance.student.year') }}', studentYearChart, chart => studentYearChart = chart, 'studentYearPerformanceSummary', 'studentYearPerformanceChart');

        function updateSubjectSelect(selectId, subjects) {
            const select = document.getElementById(selectId);
            if (!select) return;
            select.innerHTML = '';
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'All Subjects';
            select.appendChild(allOption);
            subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                select.appendChild(option);
            });
            select.disabled = false;
        }

        function loadSubjectsForTeacher(teacherId, targetSelectId) {
            const targetSelect = document.getElementById(targetSelectId);
            if (!targetSelect) return;
            targetSelect.disabled = true;
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            const url = new URL(subjectsEndpoint, window.location.origin);
            url.searchParams.set('teacher_id', teacherId);
            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    updateSubjectSelect(targetSelectId, data.data || []);
                } else {
                    targetSelect.innerHTML = '<option value="">No subjects found</option>';
                }
            })
            .catch(() => {
                targetSelect.innerHTML = '<option value="">No subjects found</option>';
            });
        }

        teacherSelects.forEach(select => {
            select.addEventListener('change', function() {
                const teacherId = this.value;
                const targetSelectId = this.getAttribute('data-subject-target');
                if (!teacherId || !targetSelectId) {
                    return;
                }
                loadSubjectsForTeacher(teacherId, targetSelectId);
            });
        });

        function updateSelectOptions(selectId, items, allLabel) {
            const select = document.getElementById(selectId);
            if (!select) return;
            select.innerHTML = '';
            if (allLabel) {
                const allOption = document.createElement('option');
                allOption.value = 'all';
                allOption.textContent = allLabel;
                select.appendChild(allOption);
            } else {
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'All Subclasses';
                select.appendChild(emptyOption);
            }
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });
            select.disabled = false;
        }

        function loadSubjectsForClass(classId, targetSelectId) {
            const targetSelect = document.getElementById(targetSelectId);
            if (!targetSelect) return;
            targetSelect.disabled = true;
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            const url = new URL(classSubjectsEndpoint, window.location.origin);
            url.searchParams.set('class_id', classId);
            fetch(url.toString(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    updateSelectOptions(targetSelectId, data.data || [], 'All Subjects');
                } else {
                    targetSelect.innerHTML = '<option value="">No subjects found</option>';
                }
            })
            .catch(() => {
                targetSelect.innerHTML = '<option value="">No subjects found</option>';
            });
        }

        function loadSubclassesForClass(classId, targetSelectId) {
            const targetSelect = document.getElementById(targetSelectId);
            if (!targetSelect) return;
            targetSelect.disabled = true;
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            const url = new URL(classSubclassesEndpoint, window.location.origin);
            url.searchParams.set('class_id', classId);
            fetch(url.toString(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    updateSelectOptions(targetSelectId, data.data || [], null);
                } else {
                    targetSelect.innerHTML = '<option value="">All Subclasses</option>';
                    targetSelect.disabled = false;
                }
            })
            .catch(() => {
                targetSelect.innerHTML = '<option value="">All Subclasses</option>';
                targetSelect.disabled = false;
            });
        }

        function loadStudentsForClass(classId, subclassId, targetSelectId) {
            const targetSelect = document.getElementById(targetSelectId);
            if (!targetSelect) return;
            targetSelect.disabled = true;
            targetSelect.innerHTML = '<option value="">Loading...</option>';
            const url = new URL(classStudentsEndpoint, window.location.origin);
            url.searchParams.set('class_id', classId);
            if (subclassId) {
                url.searchParams.set('subclass_id', subclassId);
            }
            fetch(url.toString(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    updateSelectOptions(targetSelectId, data.data || [], 'All Students');
                } else {
                    targetSelect.innerHTML = '<option value="">All Students</option>';
                    targetSelect.disabled = false;
                }
            })
            .catch(() => {
                targetSelect.innerHTML = '<option value="">All Students</option>';
                targetSelect.disabled = false;
            });
        }

        classSelects.forEach(select => {
            select.addEventListener('change', function() {
                const classId = this.value;
                const subjectTarget = this.getAttribute('data-subject-target');
                const subclassTarget = this.getAttribute('data-subclass-target');
                const studentTarget = this.getAttribute('data-student-target');
                if (!classId) {
                    return;
                }
                if (subjectTarget) {
                    loadSubjectsForClass(classId, subjectTarget);
                }
                if (subclassTarget) {
                    loadSubclassesForClass(classId, subclassTarget);
                }
                if (studentTarget) {
                    loadStudentsForClass(classId, null, studentTarget);
                }
            });
        });

        const subclassSelects = document.querySelectorAll('.class-subclass-select');
        subclassSelects.forEach(select => {
            select.addEventListener('change', function() {
                const classSelect = this.closest('form')?.querySelector('.class-select');
                const studentSelect = this.closest('form')?.querySelector('.class-student-select');
                if (!classSelect || !studentSelect) return;
                const classId = classSelect.value;
                const subclassId = this.value || null;
                if (!classId) return;
                loadStudentsForClass(classId, subclassId, studentSelect.id);
            });
        });
    })();
</script>
