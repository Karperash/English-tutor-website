<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Lesson;
use App\Models\TeacherNote;
use App\Models\User;
use PDOException;
use App\Models\RecurringLesson;


final class StudentManagementController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('teacher');
        $students = User::students();
        foreach ($students as &$student) {
            $student['upcoming'] = Lesson::upcomingForStudent((int)$student['id'], 1)[0] ?? null;
        }
        unset($student);
        $this->view('teacher/students/index', ['students' => $students]);
    }

    public function createForm(): void
    {
        Auth::requireRole('teacher');
        $this->view('teacher/students/form', ['student' => null, 'mode' => 'create']);
    }

    public function create(): void
    {
        Auth::requireRole('teacher');
        Csrf::guard();
        $data = $this->studentData(true);

        if ($error = $this->validateStudent($data, true)) {
            Flash::set('error', $error);
            $this->redirect('/teacher/students/create');
        }

        try {
            $id = User::createStudent($data);
        } catch (PDOException $e) {
            Flash::set('error', 'Не удалось создать ученика. Возможно, такой логин уже существует.');
            $this->redirect('/teacher/students/create');
        }

        Flash::set('success', 'Ученик создан.');
        $this->redirect('/teacher/students/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireRole('teacher');
        $student = User::findById((int)$id);
        if (!$student || $student['role'] !== 'student' || $student['deleted_at']) {
            http_response_code(404);
            exit('Ученик не найден.');
        }
        $this->view('teacher/students/show', [
            'student' => $student,
            'lessons' => Lesson::forStudent((int)$student['id']),
            'notes' => TeacherNote::forStudent((int)$student['id']),
            'schedule' => RecurringLesson::forStudent((int)$student['id'])
        ]);
    }

    public function editForm(string $id): void
    {
        Auth::requireRole('teacher');
        $student = User::findById((int)$id);
        if (!$student || $student['role'] !== 'student' || $student['deleted_at']) {
            http_response_code(404);
            exit('Ученик не найден.');
        }
        $this->view('teacher/students/form', ['student' => $student, 'mode' => 'edit']);
    }

    public function update(string $id): void
    {
        Auth::requireRole('teacher');
        Csrf::guard();
        $studentId = (int)$id;
        $data = $this->studentData(false);

        if ($error = $this->validateStudent($data, false, $studentId)) {
            Flash::set('error', $error);
            $this->redirect('/teacher/students/' . $studentId . '/edit');
        }

        try {
            User::updateStudent($studentId, $data);
        } catch (PDOException $e) {
            Flash::set('error', 'Не удалось сохранить данные. Проверьте уникальность логина.');
            $this->redirect('/teacher/students/' . $studentId . '/edit');
        }

        Flash::set('success', 'Данные ученика сохранены.');
        $this->redirect('/teacher/students/' . $studentId);
    }

    public function delete(string $id): void
    {
        Auth::requireRole('teacher');
        Csrf::guard();
        User::softDeleteStudent((int)$id);
        Flash::set('success', 'Ученик удалён. История занятий сохранена.');
        $this->redirect('/teacher/students');
    }

    public function addNote(string $id): void
    {
        Auth::requireRole('teacher');
        Csrf::guard();
        $content = $this->input('content');
        $lessonId = (int)($_POST['lesson_id'] ?? 0) ?: null;
        if ($lessonId !== null) {
            $lesson = Lesson::find($lessonId);
            if (!$lesson || (int)$lesson['student_id'] !== (int)$id) {
                Flash::set('error', 'Выбранное занятие не принадлежит этому ученику.');
                $this->redirect('/teacher/students/' . (int)$id);
            }
        }
        if ($content === '') {
            Flash::set('error', 'Введите текст заметки.');
        } else {
            TeacherNote::create((int)$id, $lessonId, $content);
            Flash::set('success', 'Заметка добавлена.');
        }
        $this->redirect('/teacher/students/' . (int)$id);
    }

    private function studentData(bool $requirePassword): array
    {
        return [
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'login' => $this->input('login'),
            'password' => (string)($_POST['password'] ?? ''),
            'phone' => $this->input('phone'),
            'parent_name' => $this->input('parent_name'),
            'parent_phone' => $this->input('parent_phone'),
            '_require_password' => $requirePassword,
        ];
    }
    public function addSchedule(string $id): void
{
    Auth::requireRole('teacher');
    Csrf::guard();

    $studentId = (int)$id;
    $teacher = Auth::user();

    $weekday = (int)($_POST['weekday'] ?? 0);
    $time = trim((string)($_POST['time'] ?? ''));


    if (
        $weekday < 1 ||
        $weekday > 7 ||
        $time === ''
    ) {

        Flash::set(
            'error',
            'Выберите день и время.'
        );

        $this->redirect(
            '/teacher/students/' . $studentId
        );
    }


    RecurringLesson::create(
        (int)$teacher['id'],
        $studentId,
        $weekday,
        $time
    );


    Flash::set(
        'success',
        'Постоянное расписание добавлено.'
    );


    $this->redirect(
        '/teacher/students/' . $studentId
    );
}
public function deleteSchedule(string $id): void
{
    Auth::requireRole('teacher');
    Csrf::guard();

    $studentId = (int)$id;

    $scheduleId = (int)($_POST['schedule_id'] ?? 0);

    if ($scheduleId) {
        RecurringLesson::delete(
            $scheduleId,
            $studentId
        );

        Flash::set(
            'success',
            'Занятие из постоянного расписания удалено.'
        );
    }

    $this->redirect(
        '/teacher/students/' . $studentId
    );
}

    private function validateStudent(array $data, bool $requirePassword, ?int $exceptId = null): ?string
    {
        foreach (['first_name', 'last_name', 'login', 'phone', 'parent_name', 'parent_phone'] as $field) {
            if ($data[$field] === '') {
                return 'Заполните все обязательные поля.';
            }
        }
        if ($requirePassword && strlen($data['password']) < 6) {
            return 'Пароль должен содержать минимум 6 символов.';
        }
        if (!$requirePassword && $data['password'] !== '' && strlen($data['password']) < 6) {
            return 'Новый пароль должен содержать минимум 6 символов.';
        }
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $data['login'])) {
            return 'Логин: 3–50 символов, латинские буквы, цифры, точка, _ или -.';
        }
        if (User::loginExists($data['login'], $exceptId)) {
            return 'Этот логин уже используется.';
        }
        return null;
    }
}
