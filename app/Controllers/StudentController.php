<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Homework;
use App\Models\Lesson;

final class StudentController extends Controller
{
    public function dashboard(): void
    {
        $student = Auth::requireRole('student');
        $upcoming = Lesson::upcomingForStudent((int)$student['id'], 3);
        $homework = Homework::forStudent((int)$student['id']);

        $this->view('student/dashboard', [
            'nextLesson' => $upcoming[0] ?? null,
            'upcomingLessons' => $upcoming,
            'homework' => array_slice($homework, 0, 5),
        ]);
    }
}
