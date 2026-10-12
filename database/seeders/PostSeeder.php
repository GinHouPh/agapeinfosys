<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed the public article index.
     */
    public function run(): void
    {
        $author = User::query()->first();

        $posts = [
            [
                'title' => 'Signs your legacy system is costing you more than a rewrite',
                'slug' => 'signs-your-legacy-system-is-costing-you-more-than-a-rewrite',
                'category' => 'Strategy',
                'reading_time' => '7 min read',
                'published_at' => now()->subDays(6),
                'excerpt' => 'Rewrites are expensive. So is keeping the system you have. Here is how to tell which of the two is actually costing you more.',
                'body' => implode("\n\n", [
                    'Every engineering team eventually has the same meeting. Someone proposes rewriting the system everyone complains about, and someone else points out that the last rewrite estimate was both wrong and enormous. Both of them are usually right.',
                    'The honest answer is that neither option is cheap, and the useful question is not "should we rewrite" but "what is this system costing us per month, in measurable terms".',
                    'Start with change cost. If a small product request routinely takes three weeks because of the number of places a change has to land, you have a number. Multiply it by the value of the requests you declined.',
                    'Then look at defect cost, hiring cost and risk cost. The system that only two people understand is not an asset with a moat — it is a key-person risk with a maintenance contract.',
                    'Only once those numbers are on the table does build-versus-rebuild become a conversation you can actually have with a CFO.',
                ]),
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'What a good delivery cadence looks like in practice',
                'slug' => 'what-a-good-delivery-cadence-looks-like-in-practice',
                'category' => 'Delivery',
                'reading_time' => '5 min read',
                'published_at' => now()->subDays(14),
                'excerpt' => 'Two-week sprints are not a delivery strategy. Here is the cadence we run on client engagements, and why each piece exists.',
                'body' => implode("\n\n", [
                    'Cadence is the part of a delivery process people copy first and understand least. The ceremonies are visible; the reasons they exist are not.',
                    'We run a fortnightly cycle with a demo at the end of it. The demo is not a status update — it is working software in a staging environment, and the client drives it. Anything that cannot be demoed is not done.',
                    'Behind that, there is a weekly planning touchpoint that is deliberately short. Its only job is to confirm that the next increment still targets the highest-value work, and to surface blockers while they are still cheap.',
                    'Daily asynchronous updates replace stand-ups for distributed teams. Written status catches nuance that a fifteen-minute call tends to lose, and it leaves a trail you can audit six months later.',
                    'None of this is novel. What matters is running it consistently enough that the team stops performing the process and starts trusting it.',
                ]),
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Cloud cost is a design decision, not a billing problem',
                'slug' => 'cloud-cost-is-a-design-decision-not-a-billing-problem',
                'category' => 'Cloud',
                'reading_time' => '6 min read',
                'published_at' => now()->subDays(26),
                'excerpt' => 'Rightsizing gets you the first 20%. The rest comes from how the system was architected in the first place.',
                'body' => implode("\n\n", [
                    'The monthly cloud bill is one of the few engineering metrics that arrives with a number attached, which is why it gets treated as a finance problem. It is not.',
                    'The first round of savings is administrative: rightsizing instances, deleting idle resources, moving storage to the right tier. That work is worth doing and it usually buys you twenty per cent.',
                    'The second round is architectural. Chatty services that cross an availability zone on every request, synchronous calls where an asynchronous queue would do, and caches with no eviction policy — these are design decisions that show up as money.',
                    'The useful habit is to give every significant component an owner and a cost-per-day figure on the same dashboard as its latency. When the number is visible next to the behaviour that drives it, engineers fix it without being asked.',
                ]),
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($posts as $post) {
            Post::query()->updateOrCreate(
                ['slug' => $post['slug']],
                $post + [
                    'author_id' => $author?->getKey(),
                    'cover_image' => null,
                    'is_published' => true,
                ],
            );
        }
    }
}
