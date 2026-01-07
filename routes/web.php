<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ManageClassessController;
use App\Http\Controllers\ManageExaminationController;
use App\Http\Controllers\ManageParentsController;
use App\Http\Controllers\ResultManagementController;
use App\Http\Controllers\ManageStudentController;
use App\Http\Controllers\ManageSubjectController;
use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\ManageTeachersController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\SMS_InformationController;
use App\Http\Controllers\AccomodationController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ManageAttendanceController;
use App\Http\Controllers\ParentsContoller;
use App\Http\Controllers\ZKTecoPushController;
use App\Http\Controllers\ZKTecoController;
use App\Http\Controllers\GradeDefinitionController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\OnlineApplicationController;
use App\Http\Controllers\StudentRegistrationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('home');
});

// Pricing Route
Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

// Online Application Routes
Route::get('online-application', [OnlineApplicationController::class, 'index'])->name('online_application');
Route::get('online-application/school/{schoolID}', [OnlineApplicationController::class, 'getSchoolDetails'])->name('online_application.school_details');
Route::get('online-application/apply', [OnlineApplicationController::class, 'showApplicationForm'])->name('online_application.apply');
Route::post('online-application/apply', [OnlineApplicationController::class, 'storeApplication'])->name('online_application.store');

// school management route
Route::get('school', [SchoolController::class, 'school'])->name('school');
Route::post('update_school', [SchoolController::class, 'updateSchool'])->name('update_school');
Route::get('get_school_details', [SchoolController::class, 'get_school_details'])->name('get_school_details');
// Configuration Routes
Route::get('3345', [ConfigurationController::class, 'index'])->name('configuration.index');
Route::post('schools', [ConfigurationController::class, 'storeSchool'])->name('save_school');

// login Routes
Route::get('login', [Auth::class, 'login'])->name('login');
Route::post('auth', [Auth::class, 'auth'])->name('auth');

// logut route
Route::get('logout', [Auth::class, 'logout'])->name('logout');

// Admin Routes
Route::get('AdminDashboard', [AdminController::class, 'AdminDashboard'])->name('AdminDashboard');
Route::get('admin/scheme-of-work', [AdminController::class, 'adminSchemeOfWork'])->name('admin.schemeOfWork');
Route::get('task-management', [AdminController::class, 'taskManagement'])->name('taskManagement');
Route::get('admin/get-teacher-tasks', [AdminController::class, 'getTeacherTasks'])->name('admin.get_teacher_tasks');
Route::post('approve-task/{taskID}', [AdminController::class, 'approveTask'])->name('approve_task');
Route::post('reject-task/{taskID}', [AdminController::class, 'rejectTask'])->name('reject_task');

// Parents Routes
Route::get('parentDashboard', [ParentsContoller::class, 'parentDashboard'])->name('parentDashboard');
Route::get('parentResults', [ParentsContoller::class, 'parentResults'])->name('parentResults');
Route::get('parentAttendance', [ParentsContoller::class, 'parentAttendance'])->name('parentAttendance');
Route::get('parentPayments', [ParentsContoller::class, 'parentPayments'])->name('parentPayments');
Route::get('parentFeesSummary', [ParentsContoller::class, 'parentFeesSummary'])->name('parentFeesSummary');
Route::get('parentSubjects', [ParentsContoller::class, 'parentSubjects'])->name('parentSubjects');
Route::get('get_student_subjects/{studentID}', [ParentsContoller::class, 'getStudentSubjects'])->name('get_student_subjects');
Route::post('elect_subject', [ParentsContoller::class, 'electSubject'])->name('elect_subject');
Route::post('deselect_subject', [ParentsContoller::class, 'deselectSubject'])->name('deselect_subject');
Route::post('change-language', [ParentsContoller::class, 'changeLanguage'])->name('change_language');
Route::get('get_parent_payments_ajax', [ParentsContoller::class, 'get_parent_payments_ajax'])->name('get_parent_payments_ajax');
Route::post('request_control_number/{studentID}', [ParentsContoller::class, 'request_control_number'])->name('request_control_number');
Route::get('get_fees_summary_ajax', [ParentsContoller::class, 'get_fees_summary_ajax'])->name('get_fees_summary_ajax');

// Parents API Routes
Route::get('api/parent/results', [ParentsContoller::class, 'apiGetParentResults'])->name('api.parent.results');
Route::post('api/parent/results', [ParentsContoller::class, 'apiGetParentResults'])->name('api.parent.results.post');
Route::get('api/parent/attendance', [ParentsContoller::class, 'apiGetParentAttendance'])->name('api.parent.attendance');
Route::post('api/parent/attendance', [ParentsContoller::class, 'apiGetParentAttendance'])->name('api.parent.attendance.post');

// teachers Management Routes
Route::get('manageTeachers', [ManageTeachersController::class, 'manageTeachers'])->name('manageTeachers');
Route::post('save_teachers', [ManageTeachersController::class, 'save_teachers'])->name('save_teachers');
Route::get('get_teacher/{id}', [ManageTeachersController::class, 'get_teacher'])->name('get_teacher');
Route::post('update_teacher', [ManageTeachersController::class, 'update_teacher'])->name('update_teacher');
Route::post('send_teacher_to_fingerprint', [ManageTeachersController::class, 'sendTeacherToFingerprint'])->name('send_teacher_to_fingerprint');
Route::post('send_student_to_fingerprint', [ManageClassessController::class, 'sendStudentToFingerprint'])->name('send_student_to_fingerprint');

// Export teacher attendance (web routes to access session)
Route::get('attendance/export-teachers-excel', [AttendanceApiController::class, 'exportTeacherAttendanceExcel'])->name('attendance.export_teachers_excel');
Route::get('attendance/export-teachers-pdf', [AttendanceApiController::class, 'exportTeacherAttendancePdf'])->name('attendance.export_teachers_pdf');

// Classess Routes
Route::get('manageClasses', [ManageClassessController::class, 'manageClasses'])->name('manageClasses');
Route::get('get_class/{classID}', [ManageClassessController::class, 'get_class'])->name('get_class');
Route::post('save_class', [ManageClassessController::class, 'save_class'])->name('save_class');
Route::post('update_class', [ManageClassessController::class, 'update_class'])->name('update_class');
Route::post('activate_class/{classID}', [ManageClassessController::class, 'activate_class'])->name('activate_class');
Route::delete('delete_class/{classID}', [ManageClassessController::class, 'delete_class'])->name('delete_class');
Route::post('save_sub_lass', [ManageClassessController::class, 'save_sub_lass'])->name('save_sub_lass');
Route::post('save_combie', [ManageClassessController::class, 'save_combie'])->name('save_combie');
Route::post('update_combie', [ManageClassessController::class, 'update_combie'])->name('update_combie');
Route::delete('delete_combie/{combieID}', [ManageClassessController::class, 'delete_combie'])->name('delete_combie');
Route::get('get_classes', [ManageClassessController::class, 'get_classes'])->name('get_classes');
Route::get('admin/get-classes', [ManageClassessController::class, 'get_classes'])->name('admin.get_classes');
Route::get('admin/get-terms-for-year', [ManageAttendanceController::class, 'getTermsForYear'])->name('admin.get_terms_for_year');
Route::get('admin/get-exams-for-year-term', [ManageAttendanceController::class, 'getExamsForYearTerm'])->name('admin.get_exams_for_year_term');
Route::get('admin/get-subclasses-for-class', [ManageAttendanceController::class, 'getSubclassesForClass'])->name('admin.get_subclasses_for_class');
Route::get('admin/get-subjects-for-class', [ManageAttendanceController::class, 'getSubjectsForClass'])->name('admin.get_subjects_for_class');
Route::get('admin/get-exam-attendance-data', [ManageAttendanceController::class, 'getExamAttendanceData'])->name('admin.get_exam_attendance_data');
Route::get('admin/get-student-missed-subjects', [ManageAttendanceController::class, 'getStudentMissedSubjects'])->name('admin.get_student_missed_subjects');
Route::get('get_class_subclasses/{classID}', [ManageClassessController::class, 'get_class_subclasses'])->name('get_class_subclasses');
Route::get('get_subclass/{subclassID}', [ManageClassessController::class, 'get_subclass'])->name('get_subclass');
Route::post('update_subclass', [ManageClassessController::class, 'update_subclass'])->name('update_subclass');
Route::post('activate_subclass/{subclassID}', [ManageClassessController::class, 'activate_subclass'])->name('activate_subclass');
Route::get('get_subclass_students/{subclassID}', [ManageClassessController::class, 'get_subclass_students'])->name('get_subclass_students');
Route::get('get_subclass_subjects/{subclassID}', [ManageClassessController::class, 'get_subclass_subjects'])->name('get_subclass_subjects');
Route::get('get_class_grading', [ManageClassessController::class, 'get_class_grading'])->name('get_class_grading');
Route::post('update_grade_range', [ManageClassessController::class, 'update_grade_range'])->name('update_grade_range');
Route::delete('delete_subclass/{subclassID}', [ManageClassessController::class, 'delete_subclass'])->name('delete_subclass');

// Subjects Routes
Route::get('manageSubjects', [ManageSubjectController::class, 'manageSubjects'])->name('manageSubjects');
Route::post('save_school_subject', [ManageSubjectController::class, 'save_school_subject'])->name('save_school_subject');
Route::post('update_school_subject', [ManageSubjectController::class, 'update_school_subject'])->name('update_school_subject');
Route::delete('delete_school_subject/{subjectID}', [ManageSubjectController::class, 'delete_school_subject'])->name('delete_school_subject');
Route::post('activate_subject/{subjectID}', [ManageSubjectController::class, 'activate_subject'])->name('activate_subject');
Route::get('get_class_subjects_by_subclass/{subclassID}', [ManageSubjectController::class, 'get_class_subjects_by_subclass'])->name('get_class_subjects_by_subclass');
Route::post('save_class_subject', [ManageSubjectController::class, 'save_class_subject'])->name('save_class_subject');
Route::post('activate_class_subject/{classSubjectID}', [ManageSubjectController::class, 'activate_class_subject'])->name('activate_class_subject');
Route::get('get_class_subjects', [ManageSubjectController::class, 'get_class_subjects'])->name('get_class_subjects');
Route::post('update_class_subject', [ManageSubjectController::class, 'update_class_subject'])->name('update_class_subject');
Route::delete('delete_class_subject/{classSubjectID}', [ManageSubjectController::class, 'delete_class_subject'])->name('delete_class_subject');
// Subject Election Routes
Route::get('get_subject_electors/{classSubjectID}', [ManageSubjectController::class, 'get_subject_electors'])->name('get_subject_electors');
Route::post('save_subject_election', [ManageSubjectController::class, 'save_subject_election'])->name('save_subject_election');
Route::post('deselect_student', [ManageSubjectController::class, 'deselect_student'])->name('deselect_student');

// Teachers Routes
Route::get('teachersDashboard', [TeachersController::class, 'teachersDashboard'])->name('teachersDashboard');
Route::get('teacher/my-sessions', [TeachersController::class, 'mySessions'])->name('teacher.mySessions');
Route::get('teacher/my-tasks', [TeachersController::class, 'myTasks'])->name('teacher.myTasks');
Route::get('teacher/get-session-students', [TeachersController::class, 'getSessionStudents'])->name('teacher.get_session_students');
Route::post('teacher/collect-session-attendance', [TeachersController::class, 'collectSessionAttendance'])->name('teacher.collect_session_attendance');
Route::post('teacher/assign-session-task', [TeachersController::class, 'assignSessionTask'])->name('teacher.assign_session_task');
Route::post('teacher/update-session-task/{taskID}', [TeachersController::class, 'updateSessionTask'])->name('teacher.update_session_task');
Route::get('teacher/session-attendance/{classSubjectID}', [TeachersController::class, 'sessionAttendance'])->name('teacher.session_attendance');
Route::get('teacher/get-session-attendance-data', [TeachersController::class, 'getSessionAttendanceData'])->name('teacher.get_session_attendance_data');
Route::get('teacher/update-attendance', [TeachersController::class, 'updateAttendance'])->name('teacher.update_attendance');
Route::get('teacher/get-collected-attendance', [TeachersController::class, 'getCollectedAttendance'])->name('teacher.get_collected_attendance');
Route::get('teacher/get-session-attendance-for-update', [TeachersController::class, 'getSessionAttendanceForUpdate'])->name('teacher.get_session_attendance_for_update');
Route::get('teacherSubjects', [TeachersController::class, 'teacherSubjects'])->name('teacherSubjects');
Route::get('teacher/scheme-of-work', [TeachersController::class, 'schemeOfWork'])->name('teacher.schemeOfWork');
Route::get('teacher/scheme-of-work/create/{classSubjectID}', [TeachersController::class, 'createNewScheme'])->name('teacher.createSchemeOfWork');
Route::post('teacher/scheme-of-work/store', [TeachersController::class, 'storeNewScheme'])->name('teacher.storeSchemeOfWork');
Route::post('teacher/scheme-of-work/check-existing', [TeachersController::class, 'checkExistingScheme'])->name('teacher.checkExistingScheme');
Route::get('teacher/scheme-of-work/view/{schemeOfWorkID}', [TeachersController::class, 'viewSchemeOfWork'])->name('teacher.viewSchemeOfWork');
Route::get('teacher/scheme-of-work/manage/{schemeOfWorkID}', [TeachersController::class, 'manageSchemeOfWork'])->name('teacher.manageSchemeOfWork');
Route::post('teacher/scheme-of-work/update/{schemeOfWorkID}', [TeachersController::class, 'updateSchemeOfWork'])->name('teacher.updateSchemeOfWork');
Route::post('teacher/scheme-of-work/update/{schemeOfWorkID}/remark', [TeachersController::class, 'updateSchemeOfWorkRemark'])->name('teacher.updateSchemeOfWorkRemark');
Route::get('teacher/scheme-of-work/use-existing/{classSubjectID}', [TeachersController::class, 'useExistingSchemes'])->name('teacher.useExistingSchemes');
Route::post('teacher/scheme-of-work/use-this/{schemeOfWorkID}', [TeachersController::class, 'useThisScheme'])->name('teacher.useThisScheme');
Route::delete('teacher/scheme-of-work/delete/{schemeOfWorkID}', [TeachersController::class, 'deleteSchemeOfWork'])->name('teacher.deleteSchemeOfWork');
Route::get('teacher/scheme-of-work/export-pdf/{schemeOfWorkID}', [TeachersController::class, 'exportSchemeOfWorkPDF'])->name('teacher.exportSchemeOfWorkPDF');
Route::get('teacher/scheme-of-work/export-excel/{schemeOfWorkID}', [TeachersController::class, 'exportSchemeOfWorkExcel'])->name('teacher.exportSchemeOfWorkExcel');
Route::get('teacher/lesson-plans', [TeachersController::class, 'lessonPlans'])->name('teacher.lessonPlans');
Route::get('teacher/get-session-attendance-stats', [TeachersController::class, 'getSessionAttendanceStats'])->name('teacher.get_session_attendance_stats');
Route::post('teacher/lesson-plan/store', [TeachersController::class, 'storeLessonPlan'])->name('teacher.store_lesson_plan');
Route::get('teacher/lesson-plan/get', [TeachersController::class, 'getLessonPlan'])->name('teacher.get_lesson_plan');
Route::post('teacher/lesson-plan/update/{lessonPlanID}', [TeachersController::class, 'updateLessonPlan'])->name('teacher.update_lesson_plan');
Route::get('teacher/exam-attendance/{classSubjectID}', [TeachersController::class, 'examAttendance'])->name('teacher.exam_attendance');
Route::get('teacher/get-terms-for-year', [TeachersController::class, 'getTermsForYear'])->name('teacher.get_terms_for_year');
Route::get('teacher/get-exams-for-year-term', [TeachersController::class, 'getExamsForYearTerm'])->name('teacher.get_exams_for_year_term');
Route::get('teacher/get-exam-attendance-data', [TeachersController::class, 'getExamAttendanceData'])->name('teacher.get_exam_attendance_data');
Route::get('get_subject_students/{classSubjectID}', [TeachersController::class, 'getSubjectStudents'])->name('get_subject_students');
Route::get('get_subject_results/{classSubjectID}', [TeachersController::class, 'getSubjectResults'])->name('get_subject_results');
Route::get('get_subject_results/{classSubjectID}/{examID}', [TeachersController::class, 'getSubjectResults'])->name('get_subject_results_by_exam');
Route::get('get_examinations_for_subject/{classSubjectID}', [TeachersController::class, 'getExaminationsForSubject'])->name('get_examinations_for_subject');
Route::get('check_exam_paper_status/{examID}/{classSubjectID}', [TeachersController::class, 'checkExamPaperStatus'])->name('check_exam_paper_status');
Route::post('dismiss_exam_rejection_notification', [TeachersController::class, 'dismissExamRejectionNotification'])->name('dismiss_exam_rejection_notification');
Route::post('save_subject_results', [TeachersController::class, 'saveSubjectResults'])->name('save_subject_results');
Route::get('approve_result/{examID}', [TeachersController::class, 'approveResult'])->name('approve_result');
Route::get('get_filtered_results_for_approval/{examID}', [TeachersController::class, 'getFilteredResultsForApproval'])->name('get_filtered_results_for_approval');
Route::post('submit_result_approval/{examID}', [TeachersController::class, 'submitResultApproval'])->name('submit_result_approval');
Route::get('view_approval_chain/{examID}', [TeachersController::class, 'viewApprovalChain'])->name('view_approval_chain');
Route::get('get_class_teacher_approvals/{examID}', [TeachersController::class, 'getClassTeacherApprovals'])->name('get_class_teacher_approvals');
Route::get('get_coordinator_approvals/{examID}', [TeachersController::class, 'getCoordinatorApprovals'])->name('get_coordinator_approvals');
Route::post('get_teachers_for_subject', [TeachersController::class, 'getTeachersForSubject'])->name('get_teachers_for_subject');
Route::post('send_message_to_teachers', [TeachersController::class, 'sendMessageToTeachers'])->name('send_message_to_teachers');
Route::get('download_excel_template/{classSubjectID}/{examID}', [TeachersController::class, 'downloadExcelTemplate'])->name('download_excel_template');
Route::post('upload_excel_results', [TeachersController::class, 'uploadExcelResults'])->name('upload_excel_results');

// Users Roles Routes
Route::post('save_teacher_role', [ManageTeachersController::class, 'save_teacher_role'])->name('save_teacher_role');
Route::post('change_teacher_role', [ManageTeachersController::class, 'change_teacher_role'])->name('change_teacher_role');
Route::delete('remove_teacher_role/{id}', [ManageTeachersController::class, 'remove_teacher_role'])->name('remove_teacher_role');

// Roles and Permissions Routes (Spatie)
Route::post('create_role', [ManageTeachersController::class, 'create_role'])->name('create_role');
Route::post('update_role', [ManageTeachersController::class, 'update_role'])->name('update_role');
Route::post('create_permission', [ManageTeachersController::class, 'create_permission'])->name('create_permission');
Route::post('create_bulk_permissions', [ManageTeachersController::class, 'create_bulk_permissions'])->name('create_bulk_permissions');
Route::post('update_role_permissions', [ManageTeachersController::class, 'update_role_permissions'])->name('update_role_permissions');
Route::delete('delete_role/{id}', [ManageTeachersController::class, 'delete_role'])->name('delete_role');
Route::get('get_permissions', [ManageTeachersController::class, 'get_permissions'])->name('get_permissions');
Route::get('get_role_with_permissions/{id}', [ManageTeachersController::class, 'get_role_with_permissions'])->name('get_role_with_permissions');

// class teacher routes
Route::get('AdmitedClasses', [ManageClassessController::class, 'AdmitedClasses'])->name('AdmitedClasses');
Route::get('ClassMangement/{subclassID?}', [ManageClassessController::class, 'ClassMangement'])->name('ClassMangement');
Route::get('get_examinations_for_subclass/{subclassID}', [ManageClassessController::class, 'getExaminationsForSubclass'])->name('get_examinations_for_subclass');
Route::get('get_subclass_results/{subclassID}', [ManageClassessController::class, 'getSubclassResults'])->name('get_subclass_results');
Route::get('get_subclass_results/{subclassID}/{examID}', [ManageClassessController::class, 'getSubclassResults'])->name('get_subclass_results_by_exam');
Route::get('get_student_detailed_results/{studentID}', [ManageClassessController::class, 'getStudentDetailedResults'])->name('get_student_detailed_results');
Route::get('get_student_detailed_results/{studentID}/{examID}', [ManageClassessController::class, 'getStudentDetailedResults'])->name('get_student_detailed_results_by_exam');
Route::get('download_student_results_pdf/{studentID}/{examID}', [ManageClassessController::class, 'downloadStudentResultsPDF'])->name('download_student_results_pdf');

// Grade Definitions Routes
Route::get('manage_grade_definitions', [GradeDefinitionController::class, 'index'])->name('grade_definitions.index');
Route::post('grade_definitions', [GradeDefinitionController::class, 'store'])->name('grade_definitions.store');
Route::get('grade_definitions/{id}', [GradeDefinitionController::class, 'show'])->name('grade_definitions.show');
Route::put('grade_definitions/{id}', [GradeDefinitionController::class, 'update'])->name('grade_definitions.update');
Route::delete('grade_definitions/{id}', [GradeDefinitionController::class, 'destroy'])->name('grade_definitions.destroy');

// Student Routes
// Student Management Routes
Route::get('manage_student', [ManageStudentController::class, 'manage_student'])->name('manage_student');
Route::get('get_students_list', [ManageStudentController::class, 'get_students'])->name('get_students_list');
Route::get('get_student_statistics', [ManageStudentController::class, 'get_student_statistics'])->name('get_student_statistics');
Route::get('export_students_pdf', [ManageStudentController::class, 'export_students_pdf'])->name('export_students_pdf');
Route::get('export_students_excel', [ManageStudentController::class, 'export_students_excel'])->name('export_students_excel');
Route::get('get_subclasses_with_stats', [ManageStudentController::class, 'getSubclassesWithStats'])->name('get_subclasses_with_stats');
// Student Registration Routes
Route::get('student/registration/step1', [StudentRegistrationController::class, 'showStep1'])->name('student.registration.step1');
Route::post('student/registration/store-step1', [StudentRegistrationController::class, 'storeStep1'])->name('student.registration.store-step1');
Route::get('student/registration/step2', [StudentRegistrationController::class, 'showStep2'])->name('student.registration.step2');
Route::post('student/registration/store-step2', [StudentRegistrationController::class, 'storeStep2'])->name('student.registration.store-step2');
Route::get('student/registration/step3', [StudentRegistrationController::class, 'showStep3'])->name('student.registration.step3');
Route::post('student/registration/store-step3', [StudentRegistrationController::class, 'storeStep3'])->name('student.registration.store-step3');
Route::get('student/registration/step4', [StudentRegistrationController::class, 'showStep4'])->name('student.registration.step4');
Route::post('student/registration/store-step4', [StudentRegistrationController::class, 'storeStep4'])->name('student.registration.store-step4');
Route::get('student/registration/step5', [StudentRegistrationController::class, 'showStep5'])->name('student.registration.step5');
Route::post('student/registration/store-step5', [StudentRegistrationController::class, 'storeStep5'])->name('student.registration.store-step5');
Route::get('student/registration/success/{studentID}', [StudentRegistrationController::class, 'showSuccess'])->name('student.registration.success');
Route::get('student/registration/cancel', [StudentRegistrationController::class, 'cancelRegistration'])->name('student.registration.cancel');
Route::post('student/registration/search-parent', [StudentRegistrationController::class, 'searchParentByPhone'])->name('student.registration.search-parent');
Route::post('student/registration/store-complete', [StudentRegistrationController::class, 'storeComplete'])->name('student.registration.store-complete');
Route::get('get_student_details/{studentID}', [ManageStudentController::class, 'get_student_details'])->name('get_student_details');
Route::post('save_student', [ManageStudentController::class, 'save_student'])->name('save_student');
Route::post('test_device_connection', [ManageStudentController::class, 'test_device_connection'])->name('test_device_connection');
Route::post('retrieve_users_from_device', [ManageStudentController::class, 'retrieve_users_from_device'])->name('retrieve_users_from_device');
Route::post('check_fingerprint_progress', [ManageStudentController::class, 'check_fingerprint_progress'])->name('check_fingerprint_progress');
Route::get('get_student/{studentID}', [ManageStudentController::class, 'get_student'])->name('get_student');
Route::post('update_student', [ManageStudentController::class, 'update_student'])->name('update_student');
Route::post('transfer_student', [ManageStudentController::class, 'transfer_student'])->name('transfer_student');
Route::delete('delete_student/{studentID}', [ManageStudentController::class, 'delete_student'])->name('delete_student');
Route::post('activate_student/{studentID}', [ManageStudentController::class, 'activate_student'])->name('activate_student');
Route::post('revert_transfer/{studentID}', [ManageStudentController::class, 'revert_transfer'])->name('revert_transfer');
Route::get('download_students_pdf/{subclassID}', [ManageStudentController::class, 'download_students_pdf'])->name('download_students_pdf');
Route::get('get_subclasses_for_school', [ManageClassessController::class, 'get_subclasses_for_school'])->name('get_subclasses_for_school');
Route::get('get_eligible_subclasses_for_transfer/{studentID}', [ManageClassessController::class, 'get_eligible_subclasses_for_transfer'])->name('get_eligible_subclasses_for_transfer');

// Parent Routes
Route::get('manage_parents', [ManageParentsController::class, 'manage_parents'])->name('manage_parents');
Route::post('save_parent', [ManageParentsController::class, 'save_parent'])->name('save_parent');
Route::get('get_parents', [ManageParentsController::class, 'get_parents'])->name('get_parents');
Route::get('get_parent/{parentID}', [ManageParentsController::class, 'get_parent'])->name('get_parent');
Route::post('update_parent', [ManageParentsController::class, 'update_parent'])->name('update_parent');
Route::delete('delete_parent/{parentID}', [ManageParentsController::class, 'delete_parent'])->name('delete_parent');
Route::get('get_parents_for_pdf/{subclassID}', [ManageParentsController::class, 'get_parents_for_pdf'])->name('get_parents_for_pdf');
Route::get('get_parent_details/{parentID}', [ManageParentsController::class, 'get_parent_details'])->name('get_parent_details');

// Examination Routes
Route::get('exam_paper', [ManageExaminationController::class, 'exam_paper'])->name('exam_paper');
Route::get('manageExamination', [ManageExaminationController::class, 'manageExamination'])->name('manageExamination');
Route::post('search_examinations', [ManageExaminationController::class, 'searchExaminations'])->name('search_examinations');
Route::post('store_examination', [ManageExaminationController::class, 'store'])->name('store_examination');
Route::get('get_exam/{examID}', [ManageExaminationController::class, 'getExam'])->name('get_exam');
Route::get('get_exam_details/{examID}', [ManageExaminationController::class, 'getExamDetails'])->name('get_exam_details');
Route::post('update_examination/{examID}', [ManageExaminationController::class, 'update'])->name('update_examination');
Route::post('change_exam_status/{examID}', [ManageExaminationController::class, 'changeStatus'])->name('change_exam_status');
Route::post('approve_exam/{examID}', [ManageExaminationController::class, 'approveExam'])->name('approve_exam');
Route::delete('delete_examination/{examID}', [ManageExaminationController::class, 'destroy'])->name('delete_examination');
Route::get('get_subclasses_for_exam', [ManageExaminationController::class, 'getSubclasses'])->name('get_subclasses_for_exam');
Route::get('get_class_subjects_for_exam', [ManageExaminationController::class, 'getClassSubjects'])->name('get_class_subjects_for_exam');
Route::post('get_class_subjects_by_subclass', [ManageExaminationController::class, 'getClassSubjects'])->name('get_class_subjects_by_subclass');
Route::post('update_results_status/{examID}', [ManageExaminationController::class, 'updateResultsStatus'])->name('update_results_status');
Route::post('toggle_enter_result/{examID}', [ManageExaminationController::class, 'toggleEnterResult'])->name('toggle_enter_result');
Route::post('toggle_publish_result/{examID}', [ManageExaminationController::class, 'togglePublishResult'])->name('toggle_publish_result');
Route::post('toggle_upload_paper/{examID}', [ManageExaminationController::class, 'toggleUploadPaper'])->name('toggle_upload_paper');
Route::post('auto_shift_students/{examID}', [ManageExaminationController::class, 'autoShiftStudents'])->name('auto_shift_students');
Route::post('unshift_students/{examID}', [ManageExaminationController::class, 'unshiftStudents'])->name('unshift_students');
Route::post('update_exam_attendance/{examID}', [ManageExaminationController::class, 'updateExamAttendance'])->name('update_exam_attendance');
Route::get('get_class_student_counts/{classID}', [ManageExaminationController::class, 'getClassStudentCounts'])->name('get_class_student_counts');
Route::get('my_supervise_exams', [ManageExaminationController::class, 'getMySuperviseExams'])->name('my_supervise_exams');
Route::get('hall_students/{examHallID}', [ManageExaminationController::class, 'getHallStudents'])->name('get_hall_students');
Route::get('admin/get-exam-halls/{examID}', [ManageExaminationController::class, 'getExamHalls'])->name('admin.get_exam_halls');
Route::post('hall_attendance/{examHallID}', [ManageExaminationController::class, 'updateHallAttendance'])->name('update_hall_attendance');
Route::post('move_student_hall/{examHallID}', [ManageExaminationController::class, 'moveStudentHall'])->name('move_student_hall');

// Result Management Routes
Route::get('manageResults', [ResultManagementController::class, 'index'])->name('manageResults');

// Exam Papers Routes
Route::post('store_exam_paper', [ManageExaminationController::class, 'storeExamPaper'])->name('store_exam_paper');
Route::post('update_exam_paper/{examPaperID}', [ManageExaminationController::class, 'updateExamPaper'])->name('update_exam_paper');
Route::get('get_exam_papers/{examID}', [ManageExaminationController::class, 'getExamPapers'])->name('get_exam_papers');
Route::post('approve_reject_exam_paper/{examPaperID}', [ManageExaminationController::class, 'approveRejectExamPaper'])->name('approve_reject_exam_paper');
Route::get('get_my_exam_papers', [ManageExaminationController::class, 'getMyExamPapers'])->name('get_my_exam_papers');
Route::get('download_exam_paper/{examPaperID}', [ManageExaminationController::class, 'downloadExamPaper'])->name('download_exam_paper');
Route::delete('delete_exam_paper/{examPaperID}', [ManageExaminationController::class, 'deleteExamPaper'])->name('delete_exam_paper');
Route::get('supervise_exams', [ManageExaminationController::class, 'supervise_exams'])->name('supervise_exams');
// Attendance Routes
Route::post('save_attendance', [ManageClassessController::class, 'saveAttendance'])->name('save_attendance');
Route::get('get_attendance', [ManageClassessController::class, 'getAttendance'])->name('get_attendance');
Route::get('get_attendance/{attendanceID}', [ManageClassessController::class, 'getAttendanceById'])->name('get_attendance_by_id');
Route::post('update_attendance', [ManageClassessController::class, 'updateAttendance'])->name('update_attendance');
Route::delete('delete_attendance/{attendanceID}', [ManageClassessController::class, 'deleteAttendance'])->name('delete_attendance');
Route::get('get_attendance_overview', [ManageClassessController::class, 'getAttendanceOverview'])->name('get_attendance_overview');

// Time table routes
Route::get('timeTable', [TimeTableController::class, 'timeTable'])->name('timeTable');
Route::get('teacher_time_table', [TimeTableController::class, 'teacher_time_table'])->name('teacher_time_table');
Route::get('supervise_exam_time_table', [TimeTableController::class, 'supervise_exam_time_table'])->name('supervise_exam_time_table');

// Exam Timetable Routes
Route::post('store_exam_timetable', [TimeTableController::class, 'storeExamTimetable'])->name('store_exam_timetable');
Route::get('get_exam_timetables', [TimeTableController::class, 'getExamTimetables'])->name('get_exam_timetables');
Route::get('get_exam_details_timetable', [TimeTableController::class, 'getExamDetails'])->name('get_exam_details_timetable');
Route::get('get_exam_supervisors', [TimeTableController::class, 'getExamSupervisors'])->name('get_exam_supervisors');
Route::post('update_supervise_teacher', [TimeTableController::class, 'updateSuperviseTeacher'])->name('update_supervise_teacher');
Route::get('get_subclass_subjects_timetable', [TimeTableController::class, 'getSubclassSubjects'])->name('get_subclass_subjects_timetable');
Route::get('get_school_subjects_timetable', [TimeTableController::class, 'getSchoolSubjects'])->name('get_school_subjects_timetable');
Route::delete('delete_exam_timetable/{examTimetableID}', [TimeTableController::class, 'deleteExamTimetable'])->name('delete_exam_timetable');
Route::delete('delete_all_exam_timetable/{examID}', [TimeTableController::class, 'deleteAllExamTimetable'])->name('delete_all_exam_timetable');
Route::get('get_subject_hall_supervisors', [TimeTableController::class, 'getSubjectHallSupervisors'])->name('get_subject_hall_supervisors');
Route::put('update_exam_timetable_time/{examTimetableID}', [TimeTableController::class, 'updateExamTimetableTime'])->name('update_exam_timetable_time');
Route::get('get_supervise_teachers', [TimeTableController::class, 'getSuperviseTeachers'])->name('get_supervise_teachers');
Route::put('update_hall_supervisor/{supervisorID}', [TimeTableController::class, 'updateHallSupervisor'])->name('update_hall_supervisor');
Route::post('shuffle_exam_timetable/{examID}', [TimeTableController::class, 'shuffleExamTimetable'])->name('shuffle_exam_timetable');
Route::post('swap_exam_subjects', [TimeTableController::class, 'swapExamSubjects'])->name('swap_exam_subjects');

// Session Timetable Routes
Route::get('admin/get-session-timetable-definition', [TimeTableController::class, 'getSessionTimetableDefinition'])->name('get_session_timetable_definition');
Route::post('admin/save-session-timetable-definition', [TimeTableController::class, 'saveSessionTimetableDefinition'])->name('save_session_timetable_definition');
Route::get('admin/get-session-types', [TimeTableController::class, 'getSessionTypes'])->name('get_session_types');
Route::get('admin/get-selected-subjects-for-subclass', [TimeTableController::class, 'getSelectedSubjectsForSubclass'])->name('get_selected_subjects_for_subclass');
Route::get('admin/check-subclass-has-timetable', [TimeTableController::class, 'checkSubclassHasTimetable'])->name('check_subclass_has_timetable');
Route::get('admin/get-all-subclasses-with-timetables', [TimeTableController::class, 'getAllSubclassesWithTimetables'])->name('get_all_subclasses_with_timetables');
Route::get('admin/get-session-timetable', [TimeTableController::class, 'getSessionTimetable'])->name('get_session_timetable');
Route::post('admin/save-class-session-timetables', [TimeTableController::class, 'saveClassSessionTimetables'])->name('save_class_session_timetables');
Route::post('admin/delete-class-session-timetable', [TimeTableController::class, 'deleteClassSessionTimetable'])->name('delete_class_session_timetable');
Route::post('admin/shuffle-session-timetable', [TimeTableController::class, 'shuffleSessionTimetable'])->name('shuffle_session_timetable');
Route::post('admin/swap-session-timetable', [TimeTableController::class, 'swapSessionTimetable'])->name('swap_session_timetable');

// Calendar routes
Route::get('admin/calendar', [CalendarController::class, 'adminCalendar'])->name('admin.calendar');
Route::get('teacher/calendar', [CalendarController::class, 'teacherCalendar'])->name('teacher.calendar');
Route::get('holidays/{holidayID}', [CalendarController::class, 'getHoliday'])->name('holidays.show');
Route::post('holidays', [CalendarController::class, 'storeHoliday'])->name('holidays.store');
Route::post('holidays/bulk', [CalendarController::class, 'storeBulkHolidays'])->name('holidays.bulk');
Route::put('holidays/{holidayID}', [CalendarController::class, 'updateHoliday'])->name('holidays.update');
Route::delete('holidays/{holidayID}', [CalendarController::class, 'deleteHoliday'])->name('holidays.delete');
Route::get('events/{eventID}', [CalendarController::class, 'getEvent'])->name('events.show');
Route::post('events', [CalendarController::class, 'storeEvent'])->name('events.store');
Route::put('events/{eventID}', [CalendarController::class, 'updateEvent'])->name('events.update');
Route::delete('events/{eventID}', [CalendarController::class, 'deleteEvent'])->name('events.delete');
Route::get('calendar-data', [CalendarController::class, 'getCalendarData'])->name('calendar.data');

//manage Attendance routes
Route::get('manageAttendance', [ManageAttendanceController::class, 'manageAttendance'])->name('manageAttendance');
Route::post('search_attendance', [ManageAttendanceController::class, 'searchAttendance'])->name('search_attendance');
Route::post('search_fingerprint_attendance', [ManageAttendanceController::class, 'searchFingerprintAttendance'])->name('search_fingerprint_attendance');
Route::get('student_attendance_details/{studentID}', [ManageAttendanceController::class, 'getStudentAttendanceDetails'])->name('student_attendance_details');
Route::get('student_fingerprint_attendance_details/{studentID}', [ManageAttendanceController::class, 'getStudentFingerprintAttendanceDetails'])->name('student_fingerprint_attendance_details');


//library routes
Route::get('manage_library', [LibraryController::class, 'manage_library'])->name('manage_library');
Route::get('get_books', [LibraryController::class, 'get_books'])->name('get_books');
Route::get('get_subjects_by_class', [LibraryController::class, 'get_subjects_by_class'])->name('get_subjects_by_class');
Route::post('store_book', [LibraryController::class, 'store_book'])->name('store_book');
Route::post('update_book/{bookID}', [LibraryController::class, 'update_book'])->name('update_book');
Route::delete('delete_book/{bookID}', [LibraryController::class, 'delete_book'])->name('delete_book');
Route::post('borrow_book', [LibraryController::class, 'borrow_book'])->name('borrow_book');
Route::post('return_book/{borrowID}', [LibraryController::class, 'return_book'])->name('return_book');
Route::get('get_book_borrows', [LibraryController::class, 'get_book_borrows'])->name('get_book_borrows');
Route::get('get_book_statistics', [LibraryController::class, 'get_book_statistics'])->name('get_book_statistics');
Route::get('get_students', [LibraryController::class, 'get_students'])->name('get_students');

//fees routes
Route::get('manage_fees', [FeesController::class, 'manage_fees'])->name('manage_fees');
Route::post('store_fee', [FeesController::class, 'store_fee'])->name('store_fee');
Route::post('update_fee/{feeID}', [FeesController::class, 'update_fee'])->name('update_fee');
Route::delete('delete_fee/{feeID}', [FeesController::class, 'delete_fee'])->name('delete_fee');
Route::post('toggle_fee_status/{feeID}', [FeesController::class, 'toggle_fee_status'])->name('toggle_fee_status');
Route::get('get_fee/{feeID}', [FeesController::class, 'get_fee'])->name('get_fee');

//payments routes
Route::get('view_payments', [FeesController::class, 'view_payments'])->name('view_payments');
Route::get('get_payments_ajax', [FeesController::class, 'get_payments_ajax'])->name('get_payments_ajax');
Route::post('generate_control_numbers', [FeesController::class, 'generate_control_numbers'])->name('generate_control_numbers');
Route::post('send_control_numbers_sms', [FeesController::class, 'send_control_numbers_sms'])->name('send_control_numbers_sms');
Route::post('resend_control_number/{paymentID}', [FeesController::class, 'resend_control_number'])->name('resend_control_number');
Route::post('update_payment_status/{paymentID}', [FeesController::class, 'update_payment_status'])->name('update_payment_status');
Route::get('export_payment_invoice_pdf/{studentID}', [FeesController::class, 'exportPaymentInvoicePDF'])->name('export_payment_invoice_pdf');
Route::post('record_payment', [FeesController::class, 'record_payment'])->name('record_payment');
Route::get('get_payment_records', [FeesController::class, 'get_payment_records'])->name('get_payment_records');

//sms notification
// SMS Notification Routes
Route::get('sms_notification', [SMS_InformationController::class, 'sms_notification'])->name('sms_notification');
Route::get('get_all_parents_sms', [SMS_InformationController::class, 'get_all_parents'])->name('get_all_parents_sms');
Route::get('get_parents_by_class_sms', [SMS_InformationController::class, 'get_parents_by_class'])->name('get_parents_by_class_sms');
Route::get('get_all_teachers_sms', [SMS_InformationController::class, 'get_all_teachers'])->name('get_all_teachers_sms');
Route::get('get_parent_by_student_sms', [SMS_InformationController::class, 'get_parent_by_student'])->name('get_parent_by_student_sms');
Route::get('search_students_sms', [SMS_InformationController::class, 'search_students'])->name('search_students_sms');
Route::get('get_sms_balance', [SMS_InformationController::class, 'get_sms_balance'])->name('get_sms_balance');
Route::post('send_sms', [SMS_InformationController::class, 'send_sms'])->name('send_sms');

//Accomodation routes
Route::get('manage_accomodation', [AccomodationController::class, 'manage_accomodation'])->name('manage_accomodation');

// ZKTeco Push SDK Routes (No authentication required - called by device)
// These routes must be accessible from the device network
Route::get('iclock/getrequest', [ZKTecoPushController::class, 'getRequest'])->name('zkteco.push.getrequest');
Route::post('iclock/cdata', [ZKTecoPushController::class, 'cdata'])->name('zkteco.push.cdata');

// Fingerprint Device Settings Routes
Route::get('fingerprint_device_settings', [ZKTecoController::class, 'index'])->name('fingerprint_device_settings');
Route::post('zkteco/test-connection', [ZKTecoController::class, 'testConnection'])->name('zkteco.test_connection');
Route::post('zkteco/device-info', [ZKTecoController::class, 'getDeviceInfo'])->name('zkteco.device_info');
Route::post('zkteco/attendance', [ZKTecoController::class, 'getAttendance'])->name('zkteco.attendance');

// Push SDK Setup Wizard Routes
Route::get('zkteco/setup/server-info', [ZKTecoController::class, 'getServerInfo'])->name('zkteco.setup.server-info');
Route::post('zkteco/setup/test-connection', [ZKTecoController::class, 'testDeviceConnection'])->name('zkteco.setup.test-connection');
Route::post('zkteco/setup/import-users', [ZKTecoController::class, 'importUsersFromDevice'])->name('zkteco.setup.import-users');
Route::get('zkteco/setup/check-activity', [ZKTecoController::class, 'checkRecentActivity'])->name('zkteco.setup.check-activity');

// Attendance Routes (from device - raw format)
Route::post('zkteco/attendance/today', [ZKTecoController::class, 'getTodayAttendance'])->name('zkteco.attendance.today');
Route::post('zkteco/attendance/by-date', [ZKTecoController::class, 'getAttendanceByDate'])->name('zkteco.attendance.by-date');
Route::get('zkteco/attendance/for-class', [ZKTecoController::class, 'getFingerprintAttendanceForClass'])->name('zkteco.attendance.for-class');
Route::get('zkteco/attendance/from-db', [ZKTecoController::class, 'getFingerprintAttendanceFromDB'])->name('zkteco.attendance.from-db');
Route::post('zkteco/attendance/sync-all', [ZKTecoController::class, 'syncAllAttendanceFromDevice'])->name('zkteco.attendance.sync-all');

// Live attendance (today) sync + fetch for class (used by teacher view)
Route::get('zkteco/attendance/live-today', [ZKTecoController::class, 'syncLiveAttendanceToday'])->name('zkteco.attendance.live-today');

// User Management Routes
Route::post('zkteco/user/check-device-status', [ZKTecoController::class, 'checkDeviceStatus'])->name('zkteco.user.check-device-status');
Route::post('zkteco/user/register', [ZKTecoController::class, 'registerUserToDevice'])->name('zkteco.user.register');
Route::post('zkteco/user/list-device-users', [ZKTecoController::class, 'listDeviceUsers'])->name('zkteco.user.list-device-users');
Route::post('zkteco/user/delete', [ZKTecoController::class, 'deleteUserFromDevice'])->name('zkteco.user.delete');
Route::post('zkteco/attendance/today', [ZKTecoController::class, 'getTodayAttendance'])->name('zkteco.attendance.today');
Route::post('zkteco/attendance/by-date', [ZKTecoController::class, 'getAttendanceByDate'])->name('zkteco.attendance.by-date');

// Student Device Registration Routes (similar to sample project)
Route::post('students/{id}/register-device', [ZKTecoController::class, 'registerStudentToDevice'])->name('students.register-device');

// Temporary route to populate results (remove after use)
Route::get('populate-results', function () {
    $output = [];
    $output[] = "Starting to populate results...<br>";
    
    // Check how many records need updating
    $count = DB::table('results')
        ->whereNull('marks')
        ->where('status', 'not_allowed')
        ->count();
    
    $output[] = "Records to update: $count<br>";
    
    if ($count > 0) {
        $output[] = "Updating marks...<br>";
        
        // Update marks in batches
        $updated = 0;
        DB::table('results')
            ->whereNull('marks')
            ->where('status', 'not_allowed')
            ->orderBy('resultID')
            ->chunk(500, function ($results) use (&$updated, &$output) {
                foreach ($results as $result) {
                    $rand = mt_rand(1, 100);
                    if ($rand <= 30) {
                        $marks = mt_rand(0, 29);
                    } elseif ($rand <= 70) {
                        $marks = mt_rand(30, 64);
                    } else {
                        $marks = mt_rand(65, 100);
                    }
                    
                    DB::table('results')
                        ->where('resultID', $result->resultID)
                        ->update(['marks' => $marks]);
                    $updated++;
                }
            });
        
        $output[] = "Updated $updated marks.<br>";
        $output[] = "Updating grades...<br>";
        
        // Update grades
        DB::statement("
            UPDATE results r
            INNER JOIN examinations e ON r.examID = e.examID
            INNER JOIN subclasses s ON r.subclassID = s.subclassID
            INNER JOIN classes c ON s.classID = c.classID
            INNER JOIN schools sch ON c.schoolID = sch.schoolID
            SET r.grade = CASE 
                WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4') THEN
                    CASE 
                        WHEN r.marks >= 75 THEN 'A'
                        WHEN r.marks >= 65 THEN 'B'
                        WHEN r.marks >= 45 THEN 'C'
                        WHEN r.marks >= 30 THEN 'D'
                        WHEN r.marks >= 20 THEN 'E'
                        ELSE 'F'
                    END
                WHEN sch.school_type = 'Primary' THEN
                    CASE 
                        WHEN r.marks >= 75 THEN 'A'
                        WHEN r.marks >= 65 THEN 'B'
                        WHEN r.marks >= 45 THEN 'C'
                        WHEN r.marks >= 30 THEN 'D'
                        ELSE 'F'
                    END
                ELSE 
                    CASE 
                        WHEN r.marks >= 75 THEN 'A'
                        WHEN r.marks >= 65 THEN 'B'
                        WHEN r.marks >= 45 THEN 'C'
                        WHEN r.marks >= 30 THEN 'D'
                        ELSE 'F'
                    END
            END
            WHERE r.marks IS NOT NULL AND r.grade IS NULL
        ");
        
        $output[] = "Grades updated.<br>";
        $output[] = "Updating remarks...<br>";
        
        // Update remarks
        DB::table('results')
            ->whereNotNull('marks')
            ->whereNull('remark')
            ->update([
                'remark' => DB::raw("CASE WHEN marks >= 30 THEN 'Pass' ELSE 'Fail' END")
            ]);
        
        $output[] = "Remarks updated.<br>";
        $output[] = "Updating status...<br>";
        
        // Update status
        DB::table('results')
            ->whereNotNull('marks')
            ->where('status', 'not_allowed')
            ->update(['status' => 'allowed']);
        
        $output[] = "Status updated.<br>";
    }
    
    // Show summary
    $summary = DB::table('results')
        ->selectRaw('
            COUNT(*) as total,
            COUNT(marks) as with_marks,
            COUNT(grade) as with_grades,
            COUNT(CASE WHEN marks >= 30 THEN 1 END) as passed,
            COUNT(CASE WHEN marks < 30 THEN 1 END) as failed
        ')
        ->first();
    
    $output[] = "<br><strong>Summary:</strong><br>";
    $output[] = "Total records: {$summary->total}<br>";
    $output[] = "With marks: {$summary->with_marks}<br>";
    $output[] = "With grades: {$summary->with_grades}<br>";
    $output[] = "Passed (>=30): {$summary->passed}<br>";
    $output[] = "Failed (<30): {$summary->failed}<br>";
    
    return implode('', $output);
})->name('populate.results');
