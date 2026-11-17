<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(Service $service): View
    {
        abort_unless($service->is_published, 404);

        return view('services.show', [
            'service' => $service,
            'otherServices' => Service::published()->ordered()->whereKeyNot($service->getKey())->limit(3)->get(),
        ]);
    }
}
