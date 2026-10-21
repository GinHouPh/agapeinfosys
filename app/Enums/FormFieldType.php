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

    case Select = 'select';

    case Radio = 'radio';

    case Checkbox = 'checkbox';

    case Date = 'date';

    case File = 'file';

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
            self::File => 'file',
            default => null,
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
            self::Textarea => 'Long text',
            self::Select => 'Dropdown',
            self::Radio => 'Single choice',
            self::Checkbox => 'Multiple choice',
            self::Date => 'Date',
            self::File => 'File upload',
        };
    }
}
