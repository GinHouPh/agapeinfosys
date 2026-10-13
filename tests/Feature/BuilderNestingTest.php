<?php

namespace Tests\Feature;

use App\Builder\Contracts\Widget;
use App\Builder\PageRenderer;
use App\Builder\SchemaSanitizer;
use App\Builder\WidgetRegistry;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderNestingTest extends TestCase
{
    use RefreshDatabase;

    private WidgetRegistry $registry;

    private PageRenderer $renderer;

    private SchemaSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = app(WidgetRegistry::class);
        $this->renderer = app(PageRenderer::class);
        $this->sanitizer = app(SchemaSanitizer::class);
    }

    public function test_section_is_the_only_widget_that_can_hold_children(): void
    {
        $containers = array_keys(array_filter(
            $this->registry->all(),
            fn (Widget $widget) => $widget->isContainer(),
        ));

        $this->assertSame(['section'], $containers);

        $manifest = collect($this->registry->manifest())->keyBy('name');

        $this->assertTrue($manifest['section']['container']);
        $this->assertFalse($manifest['statement']['container']);
    }

    public function test_the_sanitizer_keeps_children_on_containers_only(): void
    {
        $blocks = $this->sanitizer->sanitize([
            'blocks' => [
                [
                    'id' => 'band',
                    'widget' => 'section',
                    'settings' => ['columns' => '2'],
                    'children' => [
                        ['id' => 'left', 'widget' => 'statement', 'settings' => [], 'column' => 0],
                        ['id' => 'wide', 'widget' => 'statement', 'settings' => [], 'column' => 9],
                        [
                            'id' => 'inner',
                            'widget' => 'section',
                            'settings' => [],
                            'children' => [['id' => 'leaf', 'widget' => 'statement', 'settings' => [], 'column' => 0]],
                        ],
                    ],
                ],
                ['id' => 'loose', 'widget' => 'statement', 'settings' => [], 'children' => [['id' => 'stray', 'widget' => 'statement', 'settings' => []]]],
            ],
        ])['blocks'];

        // Top level carries no column; children do, clamped to a sane index.
        $this->assertArrayNotHasKey('column', $blocks[0]);
        $this->assertCount(3, $blocks[0]['children']);
        $this->assertSame(0, $blocks[0]['children'][0]['column']);
        $this->assertSame(3, $blocks[0]['children'][1]['column']);
        $this->assertArrayHasKey('children', $blocks[0]['children'][2]);

        // A widget that can't hold children has its children discarded.
        $this->assertArrayNotHasKey('children', $blocks[1]);
    }

    public function test_nesting_depth_is_capped_so_a_page_stays_walkable(): void
    {
        $blocks = $this->sanitizer->sanitize([
            'blocks' => [
                ['id' => 'd0', 'widget' => 'section', 'settings' => [], 'children' => [
                    ['id' => 'd1', 'widget' => 'section', 'settings' => [], 'children' => [
                        ['id' => 'd2', 'widget' => 'section', 'settings' => [], 'children' => [
                            ['id' => 'd3', 'widget' => 'statement', 'settings' => []],
                        ]],
                    ]],
                ]],
            ],
        ])['blocks'];

        $this->assertSame('d0', $blocks[0]['id']);
        $this->assertSame('d1', $blocks[0]['children'][0]['id']);
        $this->assertSame('d2', $blocks[0]['children'][0]['children'][0]['id']);
        // Level four would exceed MAX_DEPTH, so the deepest section stays empty
        // rather than silently storing content the renderer would never walk.
        $this->assertSame([], $blocks[0]['children'][0]['children'][0]['children']);
    }

    public function test_a_published_page_renders_children_inside_their_column(): void
    {
        $page = Page::factory()->published()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'band',
                        'widget' => 'section',
                        'settings' => ['columns' => '2'],
                        'children' => [
                            ['id' => 'left', 'widget' => 'statement', 'settings' => ['accent' => 'LeftColumn', 'text' => 'is here.'], 'column' => 0],
                            ['id' => 'right', 'widget' => 'statement', 'settings' => ['accent' => 'RightColumn', 'text' => 'is here too.'], 'column' => 1],
                        ],
                    ],
                ],
            ],
        ]);

        $html = $this->renderer->render($page);

        $this->assertStringContainsString('LeftColumn', $html);
        $this->assertStringContainsString('RightColumn', $html);
        $this->assertLessThan(strpos($html, 'RightColumn'), strpos($html, 'LeftColumn'));

        $this->get(route('pages.show', $page))
            ->assertOk()
            ->assertSee('LeftColumn')
            ->assertSee('RightColumn');
    }

    public function test_a_child_claims_a_column_the_section_no_longer_has_and_still_renders(): void
    {
        $page = Page::factory()->published()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'band',
                        'widget' => 'section',
                        'settings' => ['columns' => '1'],
                        'children' => [
                            ['id' => 'stray', 'widget' => 'statement', 'settings' => ['accent' => 'StillHere', 'text' => 'is here.'], 'column' => 3],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('StillHere', $this->renderer->render($page));
    }

    public function test_the_canvas_loads_the_nested_tree_with_empty_column_slots(): void
    {
        $page = Page::factory()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'band',
                        'widget' => 'section',
                        'settings' => ['columns' => '2'],
                        'children' => [
                            ['id' => 'kid', 'widget' => 'statement', 'settings' => ['accent' => 'CanvasChild', 'text' => 'is nested.'], 'column' => 1],
                        ],
                    ],
                ],
            ],
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.pages.edit', $page))
            ->assertOk()
            // The child's markup rides along in the initial block tree.
            ->assertSee('CanvasChild')
            ->assertSee('data-pb-col', false)
            ->assertSee('maxDepth', false);
    }

    public function test_the_live_page_renders_children_in_place_of_the_drops_zones(): void
    {
        $page = Page::factory()->published()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'band',
                        'widget' => 'section',
                        'settings' => ['columns' => '2'],
                        'children' => [
                            ['id' => 'kid', 'widget' => 'statement', 'settings' => ['accent' => 'LiveChild', 'text' => 'is nested.'], 'column' => 0],
                        ],
                    ],
                ],
            ],
        ]);

        $html = $this->get(route('pages.show', $page))->assertOk()->getContent();

        $this->assertStringContainsString('LiveChild', $html);
        $this->assertStringNotContainsString('data-pb-col', $html);
    }

    public function test_a_nested_schema_round_trips_through_the_update_endpoint(): void
    {
        $page = Page::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->putJson(route('admin.pages.update', $page), [
                'title' => $page->title,
                'status' => 'draft',
                'schema' => [
                    'blocks' => [
                        [
                            'id' => 'band',
                            'widget' => 'section',
                            'settings' => ['columns' => '2'],
                            'children' => [
                                ['id' => 'kid', 'widget' => 'statement', 'settings' => ['accent' => 'Kept', 'text' => 'nested.'], 'column' => 1],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $blocks = $page->refresh()->blocks();

        $this->assertCount(1, $blocks);
        $this->assertSame('section', $blocks[0]['widget']);
        $this->assertCount(1, $blocks[0]['children']);
        $this->assertSame(1, $blocks[0]['children'][0]['column']);
        $this->assertSame('Kept', $blocks[0]['children'][0]['settings']['accent']);
    }

    public function test_the_preview_endpoint_renders_a_section_with_slots_for_the_canvas(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->postJson(route('admin.builder.preview'), [
                'widget' => 'section',
                'settings' => ['columns' => '3'],
            ]);

        $response->assertOk();

        $html = $response->json('html');

        $this->assertStringContainsString('data-pb-col="0"', $html);
        $this->assertStringContainsString('data-pb-col="2"', $html);
        $this->assertStringContainsString('md:grid-cols-3', $html);
    }

    public function test_the_section_widget_can_opt_out_of_the_band_padding(): void
    {
        $html = $this->renderer->renderBlock('section', ['spacing' => 'none']);

        $this->assertStringContainsString('section--container', $html);
        $this->assertStringNotContainsString('class="section ', $html);
        $this->assertStringNotContainsString('data-pb-col', $html);
    }
}
