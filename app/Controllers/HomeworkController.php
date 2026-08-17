<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Homework;
use App\Models\Lesson;
use App\Services\NotificationService;
use App\Services\UploadService;

final class HomeworkController extends Controller
{
    public function teacherIndex(): void
    {
        $teacher = Auth::requireRole('teacher');
        $this->view('teacher/homework/index', [
            'homeworkList' => Homework::forTeacher((int)$teacher['id']),
        ]);
    }

    public function teacherCreate(string $lessonId): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        $lesson = Lesson::find((int)$lessonId);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }

        $title = $this->input('title');
        $description = $this->input('description');
        if ($title === '' || $description === '') {
            Flash::set('error', 'Укажите название и текст домашнего задания.');
            $this->redirect('/teacher/lessons/' . (int)$lessonId);
        }

        $homeworkId = Homework::create((int)$lessonId, (int)$lesson['student_id'], $title, $description);
        $uploadErrors = [];
        foreach (UploadService::normalizeMultiple($_FILES['files'] ?? []) as $file) {
            try {
                $saved = UploadService::save($file);
                Homework::addFile($homeworkId, 'teacher', $saved);
            } catch (\Throwable $e) {
                $uploadErrors[] = $e->getMessage();
            }
        }

        NotificationService::notifyStudent(
            (int)$lesson['student_id'],
            'homework_assigned',
            'Добавлено новое домашнее задание: ' . $title . '.',
            '/student/homework/' . $homeworkId
        );

        Flash::set('success', $uploadErrors ? 'Задание создано, но часть файлов не загрузилась: ' . implode(' ', $uploadErrors) : 'Домашнее задание создано.');
        $this->redirect('/teacher/homework/' . $homeworkId);
    }

    public function teacherShow(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        $homework = Homework::find((int)$id);
        if (!$homework) {
            http_response_code(404);
            exit('Домашнее задание не найдено.');
        }
        $lesson = Lesson::find((int)$homework['lesson_id']);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(403);
            exit('Доступ запрещён.');
        }

        $this->view('teacher/homework/show', [
            'homework' => $homework,
            'lesson' => $lesson,
            'files' => Homework::files((int)$id),
        ]);
    }

    public function teacherCheck(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        $homework = Homework::find((int)$id);
        if (!$homework) {
            http_response_code(404);
            exit('Домашнее задание не найдено.');
        }
        $lesson = Lesson::find((int)$homework['lesson_id']);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(403);
            exit('Доступ запрещён.');
        }

        Homework::check((int)$id, $this->input('teacher_feedback'));
        NotificationService::notifyStudent(
            (int)$homework['student_id'],
            'homework_checked',
            'Домашнее задание «' . $homework['title'] . '» проверено.',
            '/student/homework/' . (int)$id
        );
        Flash::set('success', 'Домашняя работа отмечена как проверенная.');
        $this->redirect('/teacher/homework/' . (int)$id);
    }

    public function studentIndex(): void
    {
        $student = Auth::requireRole('student');
        $this->view('student/homework/index', [
            'homeworkList' => Homework::forStudent((int)$student['id']),
        ]);
    }

    public function studentShow(string $id): void
    {
        $student = Auth::requireRole('student');
        $homework = Homework::find((int)$id);
        if (!$homework || (int)$homework['student_id'] !== (int)$student['id']) {
            http_response_code(404);
            exit('Домашнее задание не найдено.');
        }
        $this->view('student/homework/show', [
            'homework' => $homework,
            'files' => Homework::files((int)$id),
        ]);
    }

    public function studentSubmit(string $id): void
    {
        $student = Auth::requireRole('student');
        Csrf::guard();
        $homework = Homework::find((int)$id);
        if (!$homework || (int)$homework['student_id'] !== (int)$student['id']) {
            http_response_code(404);
            exit('Домашнее задание не найдено.');
        }

        $comment = $this->input('student_comment');
        $files = UploadService::normalizeMultiple($_FILES['files'] ?? []);
        if ($comment === '' && !$files) {
            Flash::set('error', 'Добавьте комментарий или хотя бы один файл.');
            $this->redirect('/student/homework/' . (int)$id);
        }

        $uploadErrors = [];
        foreach ($files as $file) {
            try {
                $saved = UploadService::save($file);
                Homework::addFile((int)$id, 'student', $saved);
            } catch (\Throwable $e) {
                $uploadErrors[] = $e->getMessage();
            }
        }
        Homework::submit((int)$id, (int)$student['id'], $comment);

        NotificationService::notifyTeacher(
            'homework_submitted',
            $student['first_name'] . ' ' . $student['last_name'] . ' отправил(а) домашнее задание «' . $homework['title'] . '».',
            '/teacher/homework/' . (int)$id
        );

        Flash::set('success', $uploadErrors ? 'Работа отправлена, но часть файлов не загрузилась: ' . implode(' ', $uploadErrors) : 'Домашняя работа отправлена преподавателю.');
        $this->redirect('/student/homework/' . (int)$id);
    }
}
