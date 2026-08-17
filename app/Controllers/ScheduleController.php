<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Lesson;
use App\Models\ScheduleSlot;
use App\Models\StudentAvailability;
use App\Services\NotificationService;
use DateTimeImmutable;
use PDOException;

final class ScheduleController extends Controller
{
    public function teacher(): void
    {
        $teacher = Auth::requireRole('teacher');
        $slots = ScheduleSlot::teacherUpcoming((int)$teacher['id']);
        foreach ($slots as &$slot) {
            $slot['availability_names'] = ScheduleSlot::availabilityNames((int)$slot['id']);
        }
        unset($slot);
        $this->view('teacher/schedule', ['slots' => $slots]);
    }

    public function createSlot(): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        $date = $this->input('date');
        $time = $this->input('time');

        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$dt || $dt <= new DateTimeImmutable()) {
            Flash::set('error', 'Укажите корректные будущие дату и время.');
            $this->redirect('/teacher/schedule');
        }

        try {
            ScheduleSlot::create((int)$teacher['id'], $dt->format('Y-m-d H:i:s'));
            Flash::set('success', 'Свободное время добавлено.');
        } catch (\Throwable $e) {
            Flash::set('error', $e instanceof PDOException ? 'Такой временной слот уже существует.' : $e->getMessage());
        }
        $this->redirect('/teacher/schedule');
    }

    public function deleteSlot(string $id): void
    {
        $teacher = Auth::requireRole('teacher');
        Csrf::guard();
        if (ScheduleSlot::deleteIfFree((int)$id, (int)$teacher['id'])) {
            Flash::set('success', 'Свободное время удалено.');
        } else {
            Flash::set('error', 'Нельзя удалить занятый слот. Сначала отмените или перенесите занятие.');
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
