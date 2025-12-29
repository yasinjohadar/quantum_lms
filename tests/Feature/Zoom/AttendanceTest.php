<?php

use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use App\Services\Zoom\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'teacher']);
    Role::firstOrCreate(['name' => 'student']);
});

test('join and leave events are logged', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $attendanceService = app(AttendanceService::class);
    $request = request();

    // Log join
    $attendanceLog = $attendanceService->logJoin($student, $liveSession, $request);

    $this->assertDatabaseHas('attendance_logs', [
        'user_id' => $student->id,
        'live_session_id' => $liveSession->id,
        'joined_at' => $attendanceLog->joined_at,
    ]);

    // Log leave
    $attendanceService->logLeave($attendanceLog, $request);

    $attendanceLog->refresh();
    $this->assertNotNull($attendanceLog->left_at);
    $this->assertNotNull($attendanceLog->duration_seconds);
});

test('duration is calculated correctly', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $attendanceLog = AttendanceLog::factory()->create([
        'user_id' => $student->id,
        'live_session_id' => $liveSession->id,
        'joined_at' => now()->subHour(),
        'left_at' => now(),
    ]);

    $duration = $attendanceLog->calculateDuration();
    $this->assertGreaterThan(3500, $duration); // Approximately 1 hour in seconds
    $this->assertLessThan(3700, $duration);
});

test('attendance statistics are calculated correctly', function () {
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    // Create enrolled students
    $students = User::factory()->count(10)->create();
    foreach ($students as $student) {
        $student->assignRole('student');
        Enrollment::factory()->create([
            'user_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);
    }

    // Create attendance for 7 students
    foreach ($students->take(7) as $student) {
        AttendanceLog::factory()->create([
            'user_id' => $student->id,
            'live_session_id' => $liveSession->id,
            'joined_at' => now()->subHour(),
            'left_at' => now(),
        ]);
    }

    $attendanceService = app(AttendanceService::class);
    $stats = $attendanceService->getSessionAttendanceStats($liveSession);

    $this->assertEquals(10, $stats['total_enrolled']);
    $this->assertEquals(7, $stats['attended_count']);
    $this->assertEquals(3, $stats['absent_count']);
    $this->assertEquals(70.0, $stats['attendance_percentage']);
});

test('teachers can view attendance lists', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $response = $this->actingAs($teacher)
        ->get(route('admin.live-sessions.attendance.index', $liveSession->id));

    $response->assertStatus(200);
    $response->assertViewIs('admin.live-sessions.attendance.index');
});

test('students can view their own attendance', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    
    $subject = Subject::factory()->create();
    Enrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    AttendanceLog::factory()->create([
        'user_id' => $student->id,
        'live_session_id' => $liveSession->id,
    ]);

    $response = $this->actingAs($student)
        ->get(route('student.attendance.index'));

    $response->assertStatus(200);
    $response->assertViewIs('student.attendance.index');
});

test('export functionality works', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');
    
    $subject = Subject::factory()->create();
    $liveSession = LiveSession::factory()->create([
        'sessionable_type' => Subject::class,
        'sessionable_id' => $subject->id,
    ]);

    $student = User::factory()->create();
    AttendanceLog::factory()->create([
        'user_id' => $student->id,
        'live_session_id' => $liveSession->id,
    ]);

    $response = $this->actingAs($teacher)
        ->get(route('admin.live-sessions.attendance.export', [$liveSession->id, 'excel']));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
