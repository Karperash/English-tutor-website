<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PublicController extends Controller
{
    public function home(): void
    {
        $this->view('public/home', [], 'public');
    }
}
