<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Models\Novel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NovelController extends Controller
{
    public function create(Request $request): Response
    {
        $penNames = $request->user()->penNames()->get(['id', 'name', 'is_default']);

        return Inertia::render('Novels/Create', [
            'penNames' => $penNames,
            'genres' => $this->getGenres(),
            'povOptions' => $this->getPovOptions(),
            'tenseOptions' => $this->getTenseOptions(),
        ]);
    }

    public function store(StoreNovelRequest $request): RedirectResponse
    {
        $novel = $request->user()->novels()->create($request->validated());

        // Update onboarding state
        $onboardingState = $request->user()->onboardingState;
        if ($onboardingState && ! $onboardingState->first_novel_created) {
            $onboardingState->markFirstNovelCreated();
        }

        return redirect()
            ->route('dashboard')
            ->with('success', "Novel \"{$novel->title}\" created successfully!");
    }

    public function destroy(Request $request, Novel $novel): RedirectResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $title = $novel->title;
        $novel->delete();

        return back()->with('success', "Novel \"{$title}\" has been deleted.");
    }

    /**
     * @return array<string, string>
     */
    private function getGenres(): array
    {
        return [
            'fantasy' => 'Fantasy',
            'sci-fi' => 'Science Fiction',
            'romance' => 'Romance',
            'mystery' => 'Mystery',
            'thriller' => 'Thriller',
            'horror' => 'Horror',
            'literary' => 'Literary Fiction',
            'historical' => 'Historical Fiction',
            'young-adult' => 'Young Adult',
            'other' => 'Other',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getPovOptions(): array
    {
        return [
            'first_person' => 'First Person',
            'third_person_limited' => 'Third Person Limited',
            'third_person_omniscient' => 'Third Person Omniscient',
            'second_person' => 'Second Person',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getTenseOptions(): array
    {
        return [
            'past' => 'Past Tense',
            'present' => 'Present Tense',
        ];
    }
}
