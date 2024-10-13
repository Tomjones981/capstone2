<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\EmploymentController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PayrollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
 
Route::middleware('auth:sanctum')->group(function() {
    Route::post('logout', [AuthController::class,'logout']);
    Route::get('/user', [UserController::class, 'user']);
    Route::apiResource('/users', UserController::class);
    Route::get('/user-profile', [UserController::class,'getUserProfile']);
});
    Route::post('/login', [AuthController::class,'login']);

    //department
    Route::post('/create_department', [DepartmentController::class, 'createDepartment']);
    Route::get('/get_department', [DepartmentController::class, 'fetchDepartment']);
    Route::get('/get_faculty_department', [DepartmentController::class, 'fetchFacultyByDepartment']);
    Route::get('/get_faculty_by_department/{departmentId}', [DepartmentController::class, 'ViewFacultyByDepartment']);
    Route::get('/departments', [DepartmentController::class, 'getDepartments']);
    Route::put('/update_department/{id}', [DepartmentController::class, 'update']);
    Route::delete('/delete_department/{id}', [DepartmentController::class, 'destroy']);


    //faculty 
    Route::get('/faculty', [FacultyController::class, 'fetchFacultyInfo']);
    Route::post('/create-faculty', [FacultyController::class, 'createFacultyInfo']);
    Route::get('/faculty/{id}', [FacultyController::class, 'showFacByID']);
    Route::get('/faculties/{id}', [FacultyController::class, 'editFac']);
    Route::post('/faculties/{id}', [FacultyController::class, 'updateFaculty']);
    Route::get('/faculty_list', [FacultyController::class, 'getFacultyList']);


    //import Faculty
    Route::post('/faculty/import', [ImportController::class, 'importFac']);
    //export faculty
    Route::get('/export/faculty/data', [ExportController::class, 'exportFacultyData']);

    //rates
    Route::get('/faculty-with-rate-type', [RateController::class, 'getFacultyWithRateType']);
 
    //employment
    Route::get('/fetch/employment', [EmploymentController::class, 'fetchFacultyEmployment']);

    //schedyle
    Route::post('/schedule/import', [ScheduleController::class, 'import']);
    Route::post('/create_schedule', [ScheduleController::class, 'createSchedule']);
    Route::post('/schedule_update/{id}', [ScheduleController::class, 'updateSched']); 
    Route::get('/getFacultySched', [ScheduleController::class, 'getFacultySchedule']);
    Route::get('/schedule/{id}', [ScheduleController::class, 'showByFaculty']);
    Route::get('/schedule_edit/{id}', [ScheduleController::class, 'edit']);
    Route::delete('/schedule/{id}', [ScheduleController::class, 'destroy']);

    // attendance
    Route::get('/attendance', [AttendanceController::class, 'getFacultyAttendance']);
    Route::post('/get-attendance', [AttendanceController::class, 'getAttendance']);
    Route::post('/import/attendance', [ImportController::class, 'importAttendance']);


    //payroll
    Route::get('/attendance-salary', [PayrollController::class, 'getFullTimeFacultyPayroll']);
    Route::get('/part-time-faculty-data', [PayrollController::class, 'getPartTimeFacultyPayroll']);
    Route::get('/full_time/extraload', [PayrollController::class, 'getFacultyExtraLoad']);
    //parttime payroll attendance
    Route::get('/faculty-attendance/{facultyId}', [PayrollController::class, 'getFacultyAttendanceDetails']);

    //attendance import 
    Route::post('/import/faculty/attendance', [AttendanceController::class, 'importFacultyAttendance']);



    //admin dashboard 
    Route::get('/get_total/faculties', [AdminDashboardController::class, 'getTotalFaculties']);
    Route::get('/get_total/faculties/active', [AdminDashboardController::class, 'getTotalActiveFaculties']);
    Route::get('/get_total/faculties/inactive', [AdminDashboardController::class, 'getTotalInActiveFaculties']);
    Route::get('/get_total/faculties/full_time', [AdminDashboardController::class, 'getTotalFullTimeFaculties']);
    Route::get('/get_total/faculties/part_time', [AdminDashboardController::class, 'getTotalPartTimeFaculties']);


    Route::middleware('auth:sanctum')->post('/change-password', [PasswordController::class, 'changePassword']);




    
 
 

 
  
 