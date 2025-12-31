<?php

namespace App\Http\Controllers;

use App\Models\UserOnboardingState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Mark onboarding welcome as completed.
     */
    public function completeWelcome(Request $request): RedirectResponse
    {
        $this->getOrCreateState($request->user()->id)->update([
            'welcome_completed' => true,
        ]);

        return back();
    }

    /**
     * Skip the onboarding flow entirely.
     */
    public function skip(Request $request): RedirectResponse
    {
        $this->getOrCreateState($request->user()->id)->update([
            'onboarding_skipped' => true,
        ]);

        return back();
    }

    /**
     * Get or create the user's onboarding state.
     */
    private function getOrCreateState(int $userId): UserOnboardingState
    {
        return UserOnboardingState::firstOrCreate(
            ['user_id' => $userId],
            [
                'welcome_completed' => false,
                'first_novel_created' => false,
                'editor_toured' => false,
                'onboarding_skipped' => false,
            ]
        );
    }
}
