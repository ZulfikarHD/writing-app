<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'structure',
        'is_system',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'is_system' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for system templates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<StoryTemplate>  $query
     * @return \Illuminate\Database\Eloquent\Builder<StoryTemplate>
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user templates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<StoryTemplate>  $query
     * @return \Illuminate\Database\Eloquent\Builder<StoryTemplate>
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_system', true)
                ->orWhere('user_id', $userId);
        });
    }

    /**
     * Get the default system templates.
     *
     * @return array<int, array{name: string, description: string, structure: array}>
     */
    public static function getDefaultTemplates(): array
    {
        return [
            [
                'name' => '3 Act Structure',
                'description' => 'The classic three-act structure: Setup, Confrontation, and Resolution.',
                'structure' => [
                    ['title' => 'Act 1 - Setup', 'chapters' => [
                        ['title' => 'Chapter 1', 'summary' => 'Introduction to the protagonist and their world.'],
                        ['title' => 'Chapter 2', 'summary' => 'The inciting incident that disrupts the status quo.'],
                    ]],
                    ['title' => 'Act 2 - Confrontation', 'chapters' => [
                        ['title' => 'Chapter 3', 'summary' => 'Rising action and obstacles.'],
                        ['title' => 'Chapter 4', 'summary' => 'Midpoint twist or revelation.'],
                        ['title' => 'Chapter 5', 'summary' => 'All is lost moment.'],
                    ]],
                    ['title' => 'Act 3 - Resolution', 'chapters' => [
                        ['title' => 'Chapter 6', 'summary' => 'Climax and final confrontation.'],
                        ['title' => 'Chapter 7', 'summary' => 'Resolution and new equilibrium.'],
                    ]],
                ],
            ],
            [
                'name' => 'Save the Cat',
                'description' => 'Blake Snyder\'s popular 15-beat story structure.',
                'structure' => [
                    ['title' => 'Act 1', 'chapters' => [
                        ['title' => 'Opening Image', 'summary' => 'A visual that represents the struggle & tone of the story.'],
                        ['title' => 'Theme Stated', 'summary' => 'What your story is really about; the message.'],
                        ['title' => 'Setup', 'summary' => 'Introduce the hero, their world, and what\'s missing.'],
                        ['title' => 'Catalyst', 'summary' => 'The moment where life as it was changes.'],
                        ['title' => 'Debate', 'summary' => 'The hero doubts the journey they must take.'],
                    ]],
                    ['title' => 'Act 2A', 'chapters' => [
                        ['title' => 'Break Into Two', 'summary' => 'The hero makes a choice and the journey begins.'],
                        ['title' => 'B Story', 'summary' => 'A subplot that often carries the theme.'],
                        ['title' => 'Fun and Games', 'summary' => 'The promise of the premise is delivered.'],
                        ['title' => 'Midpoint', 'summary' => 'A false victory or false defeat.'],
                    ]],
                    ['title' => 'Act 2B', 'chapters' => [
                        ['title' => 'Bad Guys Close In', 'summary' => 'External and internal threats intensify.'],
                        ['title' => 'All Is Lost', 'summary' => 'The lowest point. Something or someone dies.'],
                        ['title' => 'Dark Night of the Soul', 'summary' => 'The hero is at their lowest; hopelessness.'],
                    ]],
                    ['title' => 'Act 3', 'chapters' => [
                        ['title' => 'Break Into Three', 'summary' => 'The solution is found, thanks to characters and lessons learned.'],
                        ['title' => 'Finale', 'summary' => 'The hero proves they have changed. Bad guys are defeated.'],
                        ['title' => 'Final Image', 'summary' => 'Opposite of the opening image, showing transformation.'],
                    ]],
                ],
            ],
            [
                'name' => 'Hero\'s Journey',
                'description' => 'Joseph Campbell\'s monomyth structure.',
                'structure' => [
                    ['title' => 'Departure', 'chapters' => [
                        ['title' => 'The Ordinary World', 'summary' => 'The hero\'s normal life before the adventure.'],
                        ['title' => 'Call to Adventure', 'summary' => 'The hero receives a challenge or quest.'],
                        ['title' => 'Refusal of the Call', 'summary' => 'The hero hesitates or refuses the call.'],
                        ['title' => 'Meeting the Mentor', 'summary' => 'A wise figure provides guidance or tools.'],
                        ['title' => 'Crossing the Threshold', 'summary' => 'The hero commits to the adventure.'],
                    ]],
                    ['title' => 'Initiation', 'chapters' => [
                        ['title' => 'Tests, Allies, Enemies', 'summary' => 'The hero faces challenges and makes friends/foes.'],
                        ['title' => 'Approach to the Cave', 'summary' => 'The hero prepares for the major challenge.'],
                        ['title' => 'The Ordeal', 'summary' => 'The hero faces their greatest fear or enemy.'],
                        ['title' => 'Reward', 'summary' => 'The hero takes possession of the treasure.'],
                    ]],
                    ['title' => 'Return', 'chapters' => [
                        ['title' => 'The Road Back', 'summary' => 'The hero begins the journey home.'],
                        ['title' => 'Resurrection', 'summary' => 'Final test where the hero must use everything learned.'],
                        ['title' => 'Return with the Elixir', 'summary' => 'The hero returns home transformed.'],
                    ]],
                ],
            ],
            [
                'name' => 'Freytag\'s Pyramid',
                'description' => 'Gustav Freytag\'s five-act dramatic structure.',
                'structure' => [
                    ['title' => 'Exposition', 'chapters' => [
                        ['title' => 'Introduction', 'summary' => 'Setting, characters, and background are established.'],
                    ]],
                    ['title' => 'Rising Action', 'chapters' => [
                        ['title' => 'Complication 1', 'summary' => 'The first major obstacle or conflict.'],
                        ['title' => 'Complication 2', 'summary' => 'Tension builds with more obstacles.'],
                    ]],
                    ['title' => 'Climax', 'chapters' => [
                        ['title' => 'The Turning Point', 'summary' => 'The point of highest tension and change.'],
                    ]],
                    ['title' => 'Falling Action', 'chapters' => [
                        ['title' => 'Consequences', 'summary' => 'The consequences of the climax unfold.'],
                    ]],
                    ['title' => 'Denouement', 'chapters' => [
                        ['title' => 'Resolution', 'summary' => 'Conflicts are resolved; the story concludes.'],
                    ]],
                ],
            ],
            [
                'name' => 'Dan Harmon\'s Story Circle',
                'description' => 'An 8-step simplified version of the Hero\'s Journey.',
                'structure' => [
                    ['title' => 'Part 1 - Comfort Zone', 'chapters' => [
                        ['title' => '1. You (Comfort Zone)', 'summary' => 'A character is in a zone of comfort.'],
                        ['title' => '2. Need (Want Something)', 'summary' => 'But they want something.'],
                    ]],
                    ['title' => 'Part 2 - The Journey', 'chapters' => [
                        ['title' => '3. Go (Unfamiliar Situation)', 'summary' => 'They enter an unfamiliar situation.'],
                        ['title' => '4. Search (Adapt)', 'summary' => 'They adapt to the new situation.'],
                        ['title' => '5. Find (Get What They Wanted)', 'summary' => 'They get what they wanted.'],
                    ]],
                    ['title' => 'Part 3 - The Return', 'chapters' => [
                        ['title' => '6. Take (Pay a Heavy Price)', 'summary' => 'But they pay a heavy price for it.'],
                        ['title' => '7. Return (Back to Familiar)', 'summary' => 'They return to their familiar situation.'],
                        ['title' => '8. Change (Having Changed)', 'summary' => 'Having changed as a result.'],
                    ]],
                ],
            ],
            [
                'name' => 'Fichtean Curve',
                'description' => 'A structure focused on rising action through a series of crises.',
                'structure' => [
                    ['title' => 'Rising Crises', 'chapters' => [
                        ['title' => 'Crisis 1', 'summary' => 'First major crisis or obstacle.'],
                        ['title' => 'Crisis 2', 'summary' => 'Second crisis, escalating tension.'],
                        ['title' => 'Crisis 3', 'summary' => 'Third crisis, stakes increase.'],
                        ['title' => 'Crisis 4', 'summary' => 'Fourth crisis, approaching breaking point.'],
                    ]],
                    ['title' => 'Climax', 'chapters' => [
                        ['title' => 'The Climax', 'summary' => 'The moment of greatest tension and decision.'],
                    ]],
                    ['title' => 'Falling Action', 'chapters' => [
                        ['title' => 'Resolution', 'summary' => 'Quick resolution following the climax.'],
                    ]],
                ],
            ],
            [
                'name' => 'Derek Murphy\'s 24 Chapters',
                'description' => 'A practical 24-chapter outline for novel writing.',
                'structure' => [
                    ['title' => 'Act 1 - Setup', 'chapters' => [
                        ['title' => 'Chapter 1', 'summary' => 'Hook - Grab the reader\'s attention.'],
                        ['title' => 'Chapter 2', 'summary' => 'Introduce the protagonist and their world.'],
                        ['title' => 'Chapter 3', 'summary' => 'Show the protagonist\'s flaws or limitations.'],
                        ['title' => 'Chapter 4', 'summary' => 'Introduce the antagonist or main conflict.'],
                        ['title' => 'Chapter 5', 'summary' => 'The call to adventure or inciting incident.'],
                        ['title' => 'Chapter 6', 'summary' => 'First threshold - entering the new world.'],
                    ]],
                    ['title' => 'Act 2A - Rising Action', 'chapters' => [
                        ['title' => 'Chapter 7', 'summary' => 'New world challenges and allies.'],
                        ['title' => 'Chapter 8', 'summary' => 'First major setback.'],
                        ['title' => 'Chapter 9', 'summary' => 'Learning and growing.'],
                        ['title' => 'Chapter 10', 'summary' => 'Building relationships.'],
                        ['title' => 'Chapter 11', 'summary' => 'False victory or progress.'],
                        ['title' => 'Chapter 12', 'summary' => 'Midpoint twist or revelation.'],
                    ]],
                    ['title' => 'Act 2B - Complications', 'chapters' => [
                        ['title' => 'Chapter 13', 'summary' => 'Stakes are raised.'],
                        ['title' => 'Chapter 14', 'summary' => 'Enemies close in.'],
                        ['title' => 'Chapter 15', 'summary' => 'Internal conflict intensifies.'],
                        ['title' => 'Chapter 16', 'summary' => 'Major setback or betrayal.'],
                        ['title' => 'Chapter 17', 'summary' => 'All hope seems lost.'],
                        ['title' => 'Chapter 18', 'summary' => 'Dark night of the soul.'],
                    ]],
                    ['title' => 'Act 3 - Resolution', 'chapters' => [
                        ['title' => 'Chapter 19', 'summary' => 'New determination or insight.'],
                        ['title' => 'Chapter 20', 'summary' => 'Gathering allies for final push.'],
                        ['title' => 'Chapter 21', 'summary' => 'Approaching the final confrontation.'],
                        ['title' => 'Chapter 22', 'summary' => 'The climax begins.'],
                        ['title' => 'Chapter 23', 'summary' => 'Final confrontation and victory.'],
                        ['title' => 'Chapter 24', 'summary' => 'Resolution and new equilibrium.'],
                    ]],
                ],
            ],
            [
                'name' => 'Story Clock',
                'description' => 'A 12-point structure mapped to clock positions.',
                'structure' => [
                    ['title' => 'Quarter 1 (12-3)', 'chapters' => [
                        ['title' => '12:00 - Hook', 'summary' => 'The opening that grabs attention.'],
                        ['title' => '1:00 - Setup', 'summary' => 'Establish the character and world.'],
                        ['title' => '2:00 - Inciting Incident', 'summary' => 'The event that starts the story.'],
                        ['title' => '3:00 - First Threshold', 'summary' => 'Crossing into the adventure.'],
                    ]],
                    ['title' => 'Quarter 2 (3-6)', 'chapters' => [
                        ['title' => '4:00 - Rising Action', 'summary' => 'Obstacles and challenges begin.'],
                        ['title' => '5:00 - First Pinch Point', 'summary' => 'Antagonist shows strength.'],
                        ['title' => '6:00 - Midpoint', 'summary' => 'Major revelation or shift.'],
                    ]],
                    ['title' => 'Quarter 3 (6-9)', 'chapters' => [
                        ['title' => '7:00 - Second Pinch Point', 'summary' => 'Stakes are raised again.'],
                        ['title' => '8:00 - All Is Lost', 'summary' => 'The lowest point.'],
                        ['title' => '9:00 - Second Threshold', 'summary' => 'Commitment to finish.'],
                    ]],
                    ['title' => 'Quarter 4 (9-12)', 'chapters' => [
                        ['title' => '10:00 - Climax Build', 'summary' => 'Racing toward the finale.'],
                        ['title' => '11:00 - Climax', 'summary' => 'The final confrontation.'],
                        ['title' => '12:00 - Resolution', 'summary' => 'The new normal is established.'],
                    ]],
                ],
            ],
        ];
    }
}
