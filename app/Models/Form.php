<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A builder-managed public form (requirement briefs, applications, …).
 */
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use GeneratesSlug, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'submit_label',
        'success_message',
        'notification_settings',
        'throttle_per_minute',
        'reference_prefix',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notification_settings' => 'array',
            'throttle_per_minute' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The address new submissions are announced to, if any.
     */
    public function notificationEmail(): ?string
    {
        $settings = $this->notification_settings ?? [];

        if (! is_array($settings) || ($settings['notify'] ?? false) !== true) {
            return null;
        }

        $email = $settings['email'] ?? null;

        return is_string($email) && $email !== '' ? $email : null;
    }

    /**
     * The subject a new submission is announced with.
     */
    public function notificationSubject(): string
    {
        $settings = $this->notification_settings ?? [];
        $subject = is_array($settings) ? ($settings['subject'] ?? null) : null;

        return is_string($subject) && $subject !== ''
            ? str_replace('{form}', $this->title, $subject)
            : 'New '.$this->title.' submission';
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The public success message with the submission reference injected.
     */
    public function successMessageFor(FormSubmission $submission): string
    {
        $message = $this->success_message !== null && $this->success_message !== ''
            ? $this->success_message
            : 'Thank you — your submission ({{reference}}) has been received. Our team will revert shortly.';

        return str_replace(
            ['{{reference}}', '{{name}}'],
            [$submission->reference, $submission->answers['name'] ?? ''],
            $message,
        );
    }
}
