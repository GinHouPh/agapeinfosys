<?php

namespace App\Enums;

/**
 * Input types a builder-managed form field can render.
 */
enum FormFieldType: string
{
    case Text = 'text';

    case Email = 'email';

    case Phone = 'phone';

    case Number = 'number';

    case Textarea = 'textarea';

    case LargeTextEditor = 'large_text_editor';

    case Select = 'select';

    case Radio = 'radio';

    case Checkbox = 'checkbox';

    case Date = 'date';

    case Time = 'time';

    case Url = 'url';

    case File = 'file';

    case Hidden = 'hidden';

    case SectionBreak = 'section_break';

    case CustomHtml = 'custom_html';

    case GdprConsent = 'gdpr';

    case Terms = 'terms';

    /**
     * Field types that render a list of predefined options.
     */
    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox], true);
    }

    /**
     * Field types that collect more than one value per submission.
     */
    public function isMultivalue(): bool
    {
        return $this === self::Checkbox;
    }

    /**
     * Whether the field carries an uploaded file rather than a typed answer.
     */
    public function isFile(): bool
    {
        return $this === self::File;
    }

    /**
     * Whether the field is purely decorative/structural and collects no input.
     */
    public function isLayoutOnly(): bool
    {
        return in_array($this, [self::SectionBreak, self::CustomHtml], true);
    }

    /**
     * Whether the field is a single consent/agreement checkbox.
     */
    public function isConsent(): bool
    {
        return in_array($this, [self::GdprConsent, self::Terms], true);
    }

    /**
     * Native HTML input type, or null for types rendered differently.
     */
    public function inputType(): ?string
    {
        return match ($this) {
            self::Text => 'text',
            self::Email => 'email',
            self::Phone => 'tel',
            self::Number => 'number',
            self::Date => 'date',
            self::Time => 'time',
            self::Url => 'url',
            self::File => 'file',
            self::Hidden => 'hidden',
            default => null,
        };
    }

    /**
     * Palette category for the visual form editor.
     */
    public function category(): string
    {
        return match ($this) {
            self::Text, self::Email, self::Phone, self::Number, self::Url, self::Textarea, self::LargeTextEditor => 'basic',
            self::Select, self::Radio, self::Checkbox => 'choice',
            self::Date, self::Time, self::File, self::Hidden => 'advanced',
            self::SectionBreak, self::CustomHtml => 'layout',
            self::GdprConsent, self::Terms => 'compliance',
        };
    }

    /**
     * Copy for badges, selects and labels.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Short text',
            self::Email => 'Email',
            self::Phone => 'Phone',
            self::Number => 'Number',
            self::Url => 'Website / URL',
            self::Textarea => 'Long text',
            self::LargeTextEditor => 'Markdown / Spec editor',
            self::Select => 'Dropdown',
            self::Radio => 'Single choice',
            self::Checkbox => 'Multiple choice',
            self::Date => 'Date',
            self::Time => 'Time',
            self::File => 'File upload',
            self::Hidden => 'Hidden field',
            self::SectionBreak => 'Section break',
            self::CustomHtml => 'Custom HTML',
            self::GdprConsent => 'GDPR consent',
            self::Terms => 'Terms & conditions',
        };
    }
}
