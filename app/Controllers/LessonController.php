<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Homework;
use App\Models\Lesson;
use App\Models\ScheduleSlot;
use App\Services\NotificationService;

final class LessonController extends Controller
{
    public function teacherShow(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        $lesson = Lesson::find((int)$id);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }

        $this->view('teacher/lesson', [
            'lesson' => $lesson,
            'history' => Lesson::history((int)$id),
            'freeSlots' => ScheduleSlot::freeForTeacher((int)$teacher['id']),
            'homeworkList' => Homework::forLesson((int)$id),
        ]);
    }

    public function teacherUpdate(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        $lesson = Lesson::find((int)$id);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }

        $payment = $this->input('payment_status');
        $status = $this->input('status');
        if (!in_array($payment, ['unpaid', 'paid'], true) || !in_array($status, ['scheduled', 'completed'], true)) {
            Flash::set('error', 'Некорректный статус занятия.');
            $this->redirect('/teacher/lessons/' . (int)$id);
        }

        $meetingUrl = $this->input('meeting_url');
        if ($meetingUrl !== '' && !filter_var($meetingUrl, FILTER_VALIDATE_URL)) {
            Flash::set('error', 'Укажите корректную ссылку на подключение.');
            $this->redirect('/teacher/lessons/' . (int)$id);
        }

        Lesson::updateTeacherFields((int)$id, (int)$teacher['id'], [
            'meeting_url' => $meetingUrl,
            'meeting_comment' => $this->input('meeting_comment'),
            'payment_status' => $payment,
            'status' => $status,
        ]);

        NotificationService::notifyStudent(
            (int)$lesson['student_id'],
            'lesson_updated',
            'Информация о занятии ' . date('d.m.Y H:i', strtotime((string)$lesson['starts_at'])) . ' обновлена.',
            '/student/lessons/' . (int)$id
        );
        Flash::set('success', 'Занятие обновлено.');
        $this->redirect('/teacher/lessons/' . (int)$id);
    }

    public function teacherCancel(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        $lesson = Lesson::find((int)$id);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }

        try {
            Lesson::cancelByTeacher((int)$id, (int)$teacher['id']);
            NotificationService::notifyStudent(
                (int)$lesson['student_id'],
                'lesson_cancelled',
                'Преподаватель отменил занятие ' . date('d.m.Y H:i', strtotime((string)$lesson['starts_at'])) . '.',
                '/student/lessons'
            );
            Flash::set('success', 'Занятие отменено. Временной слот снова свободен.');
        } catch (\Throwable $e) {
            Flash::set('error', $e->getMessage());
        }
        $this->redirect('/teacher/lessons/' . (int)$id);
    }

    public function teacherMove(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        $newSlotId = (int)($_POST['slot_id'] ?? 0);
        $lesson = Lesson::find((int)$id);
        if (!$lesson || (int)$lesson['teacher_id'] !== (int)$teacher['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }
        if (!$newSlotId) {
            Flash::set('error', 'Выберите новое время.');
            $this->redirect('/teacher/lessons/' . (int)$id);
        }

        try {
            $old = (string)$lesson['starts_at'];
            Lesson::moveByTeacher((int)$id, (int)$teacher['id'], $newSlotId);
            $updated = Lesson::find((int)$id);
            NotificationService::notifyStudent(
                (int)$lesson['student_id'],
                'lesson_rescheduled',
                'Занятие перенесено: было ' . date('d.m.Y H:i', strtotime($old)) . ', стало ' . date('d.m.Y H:i', strtotime((string)$updated['starts_at'])) . '.',
                '/student/lessons/' . (int)$id
            );
            Flash::set('success', 'Занятие перенесено.');
        } catch (\Throwable $e) {
            Flash::set('error', $e->getMessage());
        }
        $this->redirect('/teacher/lessons/' . (int)$id);
    }

    public function studentIndex(): void
    {
        $student = Auth::requireRole('student');
        $this->view('student/lessons', [
            'lessons' => Lesson::forStudent((int)$student['id']),
        ]);
    }

    public function studentShow(string $id): void
    {
        $student = Auth::requireRole('student');
        $lesson = Lesson::find((int)$id);
        if (!$lesson || (int)$lesson['student_id'] !== (int)$student['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }
        $this->view('student/lesson', [
            'lesson' => $lesson,
            'history' => Lesson::history((int)$id),
            'homeworkList' => Homework::forLesson((int)$id),
        ]);
    }

    public function studentCancel(string $id): void
    {
        $student = Auth::requireRole('student');
        Csrf::guard();
        $lesson = Lesson::find((int)$id);
        if (!$lesson || (int)$lesson['student_id'] !== (int)$student['id']) {
            http_response_code(404);
            exit('Занятие не найдено.');
        }

        try {
            Lesson::cancelByStudent((int)$id, (int)$student['id']);
            NotificationService::notifyTeacher(
                'lesson_cancelled',
                $student['first_name'] . ' ' . $student['last_name'] . ' отменил(а) занятие ' . date('d.m.Y H:i', strtotime((string)$lesson['starts_at'])) . '.',
                '/teacher/schedule'
            );
            Flash::set('success', 'Занятие отменено.');
            $this->redirect('/student/lessons');
        } catch (\Throwable $e) {
            Flash::set('error', $e->getMessage());
            $this->redirect('/student/lessons/' . (int)$id);
        }
    }
}
