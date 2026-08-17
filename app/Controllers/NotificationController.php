<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(): void
    {
        $user = Auth::requireLogin();
        $this->view('partials/notifications', [
            'notifications' => Notification::forUser((int)$user['id']),
        ]);
    }

    public function markAllRead(): void
    {
        $user = Auth::requireLogin();
        Csrf::guard();
        Notification::markAllRead((int)$user['id']);
        Flash::set('success', 'Все уведомления отмечены как прочитанные.');
        $this->redirect('/notifications');
    }
}
