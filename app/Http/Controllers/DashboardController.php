<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $novels = $user->novels()
            ->with('penName:id,name')
            ->orderByDesc('last_edited_at')
            ->orderByDesc('updated_at')
            ->get();

        $stats = [
            'total_novels' => $novels->count(),
            'total_words' => $novels->sum('word_count'),
            'in_progress' => $novels->where('status', 'in_progress')->count(),
            'completed' => $novels->where('status', 'completed')->count(),
        ];

        $onboardingState = $user->onboardingState;

        return Inertia::render('Dashboard/Index', [
            'novels' => $novels,
            'stats' => $stats,
            'showOnboarding' => $onboardingState && ! $onboardingState->isComplete(),
            'onboardingState' => $onboardingState,
        ]);
    }
}
