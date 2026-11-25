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
        'meta',
        'page_url',
        'is_spam',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'meta' => 'array',
            'is_spam' => 'boolean',
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

    /**
     * Convert flat answers into the standardized 2-level SRSFormData dictionary
     * keyed by sectionId -> fieldId.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toSrsFormData(): array
    {
        $this->loadMissing('form.fields');

        $answers = $this->answers ?? [];
        $data = [];

        foreach ($this->form->fields as $field) {
            $sectionId = ($field->section_id !== null && $field->section_id !== '')
                ? $field->section_id
                : Str::slug($field->group ?: 'general', '_');

            if (array_key_exists($field->name, $answers)) {
                $val = $answers[$field->name];
                if ($val !== null && $val !== '' && $val !== []) {
                    $data[$sectionId][$field->name] = $val;
                }
            }

            $textKey = $field->name.'_text';
            if (array_key_exists($textKey, $answers) && is_string($answers[$textKey]) && trim($answers[$textKey]) !== '') {
                $data[$sectionId][$textKey] = $answers[$textKey];
            }

            $fileKey = $field->name.'_file_name';
            if (array_key_exists($fileKey, $answers) && is_string($answers[$fileKey]) && trim($answers[$fileKey]) !== '') {
                $data[$sectionId][$fileKey] = $answers[$fileKey];
            }
        }

        return $data;
    }

    /**
     * Serialize answers into the standardized LLM prompt Markdown format
     * matching formatFormDataForPrompt(SRSFormData).
     */
    public function toSrsPrompt(): string
    {
        $output = '';

        foreach ($this->toSrsFormData() as $sectionId => $fields) {
            $sectionTitle = strtoupper(str_replace('_', ' ', $sectionId));
            $output .= "\n## {$sectionTitle}\n";

            foreach ($fields as $fieldId => $value) {
                if ($value === null || $value === '' || $value === []) {
                    continue;
                }

                $fieldLabel = str_replace('_', ' ', $fieldId);

                if (is_array($value)) {
                    $output .= '- '.$fieldLabel.': '.implode(', ', $value)."\n";
                } else {
                    $output .= '- '.$fieldLabel.': '.$value."\n";
                }
            }
        }

        return ltrim($output);
    }
}
