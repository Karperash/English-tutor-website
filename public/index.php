<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Controllers\HomeworkController;
use App\Controllers\LessonController;
use App\Controllers\NotificationController;
use App\Controllers\PublicController;
use App\Controllers\ScheduleController;
use App\Controllers\StudentController;
use App\Controllers\StudentManagementController;
use App\Controllers\TeacherController;
use App\Core\Env;
use App\Core\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

Env::load(dirname(__DIR__) . '/.env');
date_default_timezone_set((string)Env::get('APP_TIMEZONE', 'Europe/Berlin'));

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_start();

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$router = new Router();

$router->get('/', [PublicController::class, 'home']);
$router->get('/login', [AuthController::class, 'form']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/teacher', [TeacherController::class, 'dashboard']);
$router->get('/teacher/students', [StudentManagementController::class, 'index']);
$router->get('/teacher/students/create', [StudentManagementController::class, 'createForm']);
$router->post('/teacher/students/create', [StudentManagementController::class, 'create']);
$router->get('/teacher/students/{id}', [StudentManagementController::class, 'show']);
$router->get('/teacher/students/{id}/edit', [StudentManagementController::class, 'editForm']);
$router->post('/teacher/students/{id}/edit', [StudentManagementController::class, 'update']);
$router->post('/teacher/students/{id}/delete', [StudentManagementController::class, 'delete']);
$router->post('/teacher/students/{id}/notes', [StudentManagementController::class, 'addNote']);
$router->get('/teacher/schedule', [ScheduleController::class, 'teacher']);

$router->post(
    '/teacher/schedule/lessons',
    [ScheduleController::class, 'createLesson']
);

$router->post(
    '/teacher/students/{id}/schedule',
    [StudentManagementController::class, 'addSchedule']
);

$router->post(
    '/teacher/students/{id}/schedule/delete',
    [StudentManagementController::class, 'deleteSchedule']
);
$router->get('/teacher/lessons/{id}', [LessonController::class, 'teacherShow']);
$router->post('/teacher/lessons/{id}/update', [LessonController::class, 'teacherUpdate']);
$router->post('/teacher/lessons/{id}/cancel', [LessonController::class, 'teacherCancel']);
$router->post('/teacher/lessons/{id}/move', [LessonController::class, 'teacherMove']);

$router->get('/teacher/homework', [HomeworkController::class, 'teacherIndex']);
$router->post('/teacher/lessons/{lessonId}/homework', [HomeworkController::class, 'teacherCreate']);
$router->get('/teacher/homework/{id}', [HomeworkController::class, 'teacherShow']);
$router->post('/teacher/homework/{id}/check', [HomeworkController::class, 'teacherCheck']);

$router->get('/student', [StudentController::class, 'dashboard']);
$router->get('/student/lessons', [LessonController::class, 'studentIndex']);
$router->get('/student/lessons/{id}', [LessonController::class, 'studentShow']);
$router->post('/student/lessons/{id}/cancel', [LessonController::class, 'studentCancel']);
$router->get('/student/homework', [HomeworkController::class, 'studentIndex']);
$router->get('/student/homework/{id}', [HomeworkController::class, 'studentShow']);
$router->post('/student/homework/{id}/submit', [HomeworkController::class, 'studentSubmit']);

$router->get('/notifications', [NotificationController::class, 'index']);
$router->post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
$router->get('/files/{id}', [FileController::class, 'download']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
