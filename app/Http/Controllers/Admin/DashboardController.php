<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Control panel landing view with content health at a glance.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                ['label' => 'Services', 'value' => Service::query()->count(), 'route' => 'admin.services.index'],
                ['label' => 'Case studies', 'value' => Project::query()->count(), 'route' => 'admin.projects.index'],
                ['label' => 'Articles', 'value' => Post::query()->count(), 'route' => 'admin.posts.index'],
                ['label' => 'Testimonials', 'value' => Testimonial::query()->count(), 'route' => 'admin.testimonials.index'],
                ['label' => 'Client accounts', 'value' => User::query()->where('is_admin', false)->count(), 'route' => 'admin.users.index'],
            ],
            'unreadMessages' => ContactMessage::query()->unread()->latest()->limit(5)->get(),
            'recentPosts' => Post::query()->with('author')->latest()->limit(5)->get(),
            'drafts' => [
                'services' => Service::query()->where('is_published', false)->count(),
                'projects' => Project::query()->where('is_published', false)->count(),
                'posts' => Post::query()->where('is_published', false)->count(),
            ],
        ]);
    }
}
