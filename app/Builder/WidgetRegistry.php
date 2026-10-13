<?php

namespace App\Builder;

use App\Builder\Contracts\Widget;
use App\Builder\Widgets\ClientsMarqueeWidget;
use App\Builder\Widgets\ContactSectionWidget;
use App\Builder\Widgets\CtaBannerWidget;
use App\Builder\Widgets\CtaCardWidget;
use App\Builder\Widgets\FeatureCardsWidget;
use App\Builder\Widgets\FormEmbedWidget;
use App\Builder\Widgets\HeroWidget;
use App\Builder\Widgets\MarqueeBandWidget;
use App\Builder\Widgets\MediaTextWidget;
use App\Builder\Widgets\PageHeroWidget;
use App\Builder\Widgets\PortfolioGridWidget;
use App\Builder\Widgets\PostsGridWidget;
use App\Builder\Widgets\PostsListWidget;
use App\Builder\Widgets\ProcessStepsWidget;
use App\Builder\Widgets\ProjectsGridWidget;
use App\Builder\Widgets\RecordBodyWidget;
use App\Builder\Widgets\RecordCoverWidget;
use App\Builder\Widgets\RecordRelatedWidget;
use App\Builder\Widgets\RichTextWidget;
use App\Builder\Widgets\SectionWidget;
use App\Builder\Widgets\ServicesGridWidget;
use App\Builder\Widgets\ServicesListWidget;
use App\Builder\Widgets\ShowcaseGridWidget;
use App\Builder\Widgets\StatementWidget;
use App\Builder\Widgets\StatsGridWidget;
use App\Builder\Widgets\TestimonialsGridWidget;
use App\Builder\Widgets\ValuesGridWidget;
use Illuminate\Support\Str;

/**
 * The catalogue of widgets the page builder can place.
 *
 * Widgets are registered by hand here (an explicit manifest, mirroring the
 * ElementsKit list) so ordering in the palette is stable and discoverable.
 */
class WidgetRegistry
{
    /**
     * Widget classes in palette order.
     *
     * @var list<class-string<Widget>>
     */
    private array $widgets = [
        SectionWidget::class,
        HeroWidget::class,
        PageHeroWidget::class,
        StatementWidget::class,
        MarqueeBandWidget::class,
        MediaTextWidget::class,
        RichTextWidget::class,
        FeatureCardsWidget::class,
        StatsGridWidget::class,
        ProcessStepsWidget::class,
        ValuesGridWidget::class,
        ServicesGridWidget::class,
        ServicesListWidget::class,
        ProjectsGridWidget::class,
        PortfolioGridWidget::class,
        ShowcaseGridWidget::class,
        TestimonialsGridWidget::class,
        ClientsMarqueeWidget::class,
        PostsGridWidget::class,
        PostsListWidget::class,
        FormEmbedWidget::class,
        ContactSectionWidget::class,
        CtaBannerWidget::class,
        CtaCardWidget::class,
        RecordCoverWidget::class,
        RecordBodyWidget::class,
        RecordRelatedWidget::class,
    ];

    /**
     * Resolved instances keyed by machine name.
     *
     * @var array<string, Widget>|null
     */
    private ?array $resolved = null;

    /**
     * Every widget keyed by its machine name.
     *
     * @return array<string, Widget>
     */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $this->resolved = [];

        foreach ($this->widgets as $class) {
            $widget = new $class;
            $this->resolved[$widget->name()] = $widget;
        }

        return $this->resolved;
    }

    /**
     * Look up a single widget by machine name.
     */
    public function get(string $name): ?Widget
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Machine names of every registered widget.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->all());
    }

    /**
     * Widgets grouped by category for the builder palette.
     *
     * @return array<string, array<int, Widget>>
     */
    public function grouped(): array
    {
        $groups = [];

        foreach ($this->all() as $widget) {
            $groups[$widget->category()][] = $widget;
        }

        return $groups;
    }

    /**
     * Public metadata (no control schema) for the palette UI.
     *
     * @return array<int, array{name: string, title: string, category: string, description: string, icon: string, container: bool}>
     */
    public function manifest(): array
    {
        $manifest = [];

        foreach ($this->all() as $widget) {
            $manifest[] = [
                'name' => $widget->name(),
                'title' => $widget->title(),
                'category' => $widget->category(),
                'description' => $widget->description(),
                'icon' => $widget->icon(),
                'container' => $widget->isContainer(),
            ];
        }

        return $manifest;
    }

    /**
     * A fresh unique block id, matching the JS-side generator's shape.
     */
    public static function newBlockId(): string
    {
        return Str::lower(Str::random(10));
    }
}
