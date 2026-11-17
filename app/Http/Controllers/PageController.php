<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $page = Page::published()->where('slug', Page::HOME)->firstOrFail();

        return view('pages.show', ['page' => $page]);
    }

    public function show(Page $page): View|RedirectResponse
    {
        if ($page->isHome()) {
            return redirect()->route('home', status: 301);
        }

        abort_unless($page->is_published, 404);

        return view('pages.show', ['page' => $page]);
    }
}
