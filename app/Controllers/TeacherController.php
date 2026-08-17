<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Homework;
use App\Models\Lesson;

final class TeacherController extends Controller
{
    public function dashboard(): void
    {
        $teacher = Auth::requireRole('teacher');
        $today = Lesson::todayForTeacher((int)$teacher['id']);
        $upcoming = Lesson::upcomingForTeacher((int)$teacher['id'], 6);

        $this->view('teacher/dashboard', [
            'todayLessons' => $today,
            'upcomingLessons' => $upcoming,
            'unpaidCount' => Lesson::unpaidCountForTeacher((int)$teacher['id']),
            'submittedHomeworkCount' => Homework::countSubmittedForTeacher((int)$teacher['id']),
        ]);
    }
}
