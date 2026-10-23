<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Serve robots.txt dynamically so the Sitemap: line is built from the
     * app's own configured URL rather than a hardcoded domain.
     */
    public function __invoke(): Response
    {
        return response(view('robots')->render(), 200, ['Content-Type' => 'text/plain']);
    }
}
