<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * A read-only picture of how the platform is doing right now.
 *
 * Every check is a projection over something that already exists — a
 * connection, a queue depth, a directory permission. Nothing here writes.
 */
class SystemHealthService
{
    /**
     * The full health report.
     *
     * @return array<string, array{label: string, value: string, ok: bool, detail?: string}>
     */
    public function report(): array
    {
        return [
            ...$this->runtime(),
            ...$this->infrastructure(),
            ...$this->commerce(),
            ...$this->content(),
        ];
    }

    /**
     * The application itself.
     *
     * @return array<string, array{label: string, value: string, ok: bool, detail?: string}>
     */
    private function runtime(): array
    {
        return [
            'environment' => [
                'label' => 'Environment',
                'value' => (string) config('app.env'),
                'ok' => config('app.env') !== 'production' || ! config('app.debug'),
                'detail' => config('app.debug') ? 'Debug mode is on.' : null,
            ],
            'url' => [
                'label' => 'Application URL',
                'value' => (string) config('app.url'),
                'ok' => str_starts_with((string) config('app.url'), 'https://'),
                'detail' => str_starts_with((string) config('app.url'), 'https://') ? null : 'Not served over HTTPS.',
            ],
            'php' => [
                'label' => 'PHP',
                'value' => PHP_VERSION,
                'ok' => version_compare(PHP_VERSION, '8.3', '>='),
            ],
            'laravel' => [
                'label' => 'Laravel',
                'value' => app()->version(),
                'ok' => true,
            ],
            'database' => [
                'label' => 'Database',
                'value' => (string) config('database.default'),
                'ok' => $this->databaseIsUp(),
            ],
        ];
    }

    /**
     * The things the platform depends on.
     *
     * @return array<string, array{label: string, value: string, ok: bool, detail?: string}>
     */
    private function infrastructure(): array
    {
        $writable = $this->diskIsWritable();

        $queue = config('queue.default');
        $backlog = $queue === 'database' ? $this->queueBacklog() : null;

        return [
            'disk' => [
                'label' => 'Media storage',
                'value' => $writable ? 'Writable' : 'Read only',
                'ok' => $writable,
                'detail' => $writable ? null : 'Uploads will fail until this is fixed.',
            ],
            'queue' => [
                'label' => 'Queue',
                'value' => $backlog === null ? $queue : $queue.' — '.$backlog.' waiting',
                'ok' => $backlog === null || $backlog < 100,
                'detail' => $backlog !== null && $backlog >= 100 ? 'The queue is backing up.' : null,
            ],
            'mail' => [
                'label' => 'Mail',
                'value' => (string) config('mail.default'),
                'ok' => config('mail.default') !== 'log',
                'detail' => config('mail.default') === 'log' ? 'Mail is only being written to the log.' : null,
            ],
        ];
    }

    /**
     * The payment side of the platform.
     *
     * @return array<string, array{label: string, value: string, ok: bool, detail?: string}>
     */
    private function commerce(): array
    {
        $configured = PaymentGateway::query()
            ->enabled()
            ->get()
            ->filter(fn (PaymentGateway $gateway): bool => $gateway->isConfigured())
            ->count();

        $default = PaymentGateway::query()->where('is_default', true)->first();

        return [
            'gateways' => [
                'label' => 'Payment gateways',
                'value' => $configured.' configured',
                'ok' => $configured > 0,
                'detail' => $configured > 0 ? null : 'No gateway has both credentials and the switch on.',
            ],
            'default_gateway' => [
                'label' => 'Default gateway',
                'value' => $default?->name ?? 'None',
                'ok' => $default !== null,
                'detail' => $default !== null ? null : 'Checkout has no default gateway to use.',
            ],
        ];
    }

    /**
     * How much content the site carries.
     *
     * @return array<string, array{label: string, value: string, ok: bool, detail?: string}>
     */
    private function content(): array
    {
        $submissions = FormSubmission::query()->count();

        return [
            'pages' => [
                'label' => 'Pages',
                'value' => (string) Page::query()->count(),
                'ok' => true,
            ],
            'services' => [
                'label' => 'Services',
                'value' => (string) Service::query()->count(),
                'ok' => true,
            ],
            'posts' => [
                'label' => 'Articles',
                'value' => (string) Post::query()->count(),
                'ok' => true,
            ],
            'forms' => [
                'label' => 'Forms',
                'value' => (string) Form::query()->count(),
                'ok' => true,
            ],
            'submissions' => [
                'label' => 'Submissions',
                'value' => (string) $submissions,
                'ok' => true,
            ],
        ];
    }

    /**
     * Whether the database answers.
     */
    private function databaseIsUp(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Whether uploads can be written.
     */
    private function diskIsWritable(): bool
    {
        try {
            $path = Storage::disk('public')->path('');

            return is_dir($path) && is_writable($path);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * How many jobs are waiting.
     */
    private function queueBacklog(): ?int
    {
        try {
            return DB::table('jobs')->count();
        } catch (\Throwable) {
            return null;
        }
    }
}
