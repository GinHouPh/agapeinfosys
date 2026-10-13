<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Database\Factories\FormSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One answered form, stored as a field-name keyed answer map.
 */
class FormSubmission extends Model
{
    /** @use HasFactory<FormSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'form_id',
        'reference',
        'answers',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'status' => SubmissionStatus::class,
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if ($submission->reference !== null && $submission->reference !== '') {
                return;
            }

            $prefix = $submission->form?->reference_prefix ?: 'REQ';

            do {
                $reference = $prefix.'-'.Str::upper(Str::random(6));
            } while (static::query()->where('reference', $reference)->exists());

            $submission->reference = $reference;
        });
    }

    /**
     * The answer for one field, rendered for the admin detail page.
     */
    public function answerFor(FormField $field): string
    {
        $answer = $this->answers[$field->name] ?? null;

        if (is_array($answer)) {
            return implode(', ', array_filter($answer, fn ($value): bool => is_scalar($value)));
        }

        if (is_bool($answer)) {
            return $answer ? 'Yes' : 'No';
        }

        if ($answer === null || $answer === '') {
            return '—';
        }

        return (string) $answer;
    }
}
