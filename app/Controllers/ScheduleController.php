<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Lesson;
use App\Models\RecurringLesson;
use App\Models\User;
use App\Services\NotificationService;
use DateTimeImmutable;
use PDOException;

final class ScheduleController extends Controller
{
 public function teacher(): void
{
    $teacher = Auth::requireRole('teacher');

    $lessons = Lesson::scheduleForTeacher(
        (int)$teacher['id'],
        31
    );

    $recurringLessons = RecurringLesson::upcomingForTeacher(
        (int)$teacher['id'],
        60
    );

    $students = User::students();

    $this->view('teacher/schedule', [
        'lessons' => $lessons,
        'recurringLessons' => $recurringLessons,
        'students' => $students,
    ]);
}
public function createLesson(): void
{
    $teacher = Auth::requireRole('teacher');
    Csrf::guard();

    $studentId = (int)$this->input('student_id');
    $date = $this->input('date');
    $time = $this->input('time');

    $dt = DateTimeImmutable::createFromFormat(
        'Y-m-d H:i',
        $date . ' ' . $time
    );

    if (!$studentId || !$dt || $dt <= new DateTimeImmutable()) {
        Flash::set(
            'error',
            'Выберите ученика и укажите корректные будущие дату и время.'
        );

        $this->redirect('/teacher/schedule');
    }

    try {
        $lesson = Lesson::createByTeacher(
            (int)$teacher['id'],
            $studentId,
            $dt->format('Y-m-d H:i:s')
        );

        NotificationService::notifyUser(
            $studentId,
            'lesson_created',
            'Преподаватель назначил вам занятие на '
            . date('d.m.Y H:i', strtotime((string)$lesson['starts_at']))
            . '.',
            '/student/lessons/' . $lesson['id']
        );

        Flash::set('success', 'Занятие назначено.');
    } catch (\Throwable $e) {
        Flash::set('error', $e->getMessage());
    }

    $this->redirect('/teacher/schedule');
}

    public function student(): void
    {
        $student = Auth::requireRole('student');
        $slots = ScheduleSlot::studentAvailable((int)$student['id']);
        $this->view('student/schedule', ['slots' => $slots]);
    }

    public function book(string $id): void
    {
        $student = Auth::requireRole('student');
        Csrf::guard();
        try {
            $lesson = Lesson::book((int)$student['id'], (int)$id);
            NotificationService::notifyTeacher(
                'lesson_booked',
                $student['first_name'] . ' ' . $student['last_name'] . ' записался(ась) на ' . date('d.m.Y H:i', strtotime((string)$lesson['starts_at'])) . '.',
                '/teacher/lessons/' . $lesson['id']
            );
            Flash::set('success', 'Вы записались на занятие.');
            $this->redirect('/student/lessons/' . $lesson['id']);
        } catch (\Throwable $e) {
            Flash::set('error', $e->getMessage());
            $this->redirect('/student/schedule');
        }
    }

    public function toggleAvailability(string $id): void
    {
        $student = Auth::requireRole('student');
        Csrf::guard();
        $slot = ScheduleSlot::find((int)$id);
        if (!$slot || $slot['lesson_id'] || strtotime((string)$slot['starts_at']) <= time()) {
            Flash::set('error', 'Этот слот уже недоступен.');
            $this->redirect('/student/schedule');
        }

        $enabled = StudentAvailability::toggle((int)$student['id'], (int)$id);
        if ($enabled) {
            NotificationService::notifyTeacher(
                'availability_marked',
                $student['first_name'] . ' ' . $student['last_name'] . ' отметил(а), что также может заниматься ' . date('d.m.Y H:i', strtotime((string)$slot['starts_at'])) . '.',
                '/teacher/schedule'
            );
            Flash::set('success', 'Время отмечено как подходящее. Это не бронирование.');
        } else {
            Flash::set('success', 'Отметка дополнительного времени снята.');
        }
        $this->redirect('/student/schedule');
    }
}
