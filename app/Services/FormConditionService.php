<?php

namespace App\Services;

use App\Models\FormField;

/**
 * Decides whether a conditional field applies to a set of answers.
 *
 * A field with no condition always applies. A field with conditions applies
 * only when every one of them is met — the rules are ANDed, so a field
 * needs both "country is India" and "budget is over 100" to show.
 */
class FormConditionService
{
    /**
     * Whether a field's condition is met.
     *
     * @param  array<int, array<string, mixed>>|null  $condition
     * @param  array<string, mixed>  $answers
     */
    public function isVisible(?array $condition, array $answers): bool
    {
        if ($condition === null || $condition === []) {
            return true;
        }

        foreach ($condition as $rule) {
            if (! is_array($rule) || ! $this->matches($rule, $answers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The fields that apply to a set of answers.
     *
     * @param  iterable<int, FormField>  $fields
     * @param  array<string, mixed>  $answers
     * @return array<string, FormField>
     */
    public function visibleFields(iterable $fields, array $answers): array
    {
        $visible = [];

        foreach ($fields as $field) {
            if ($this->isVisible($field->condition, $answers)) {
                $visible[$field->name] = $field;
            }
        }

        return $visible;
    }

    /**
     * Whether one rule is met.
     *
     * @param  array<string, mixed>  $rule
     * @param  array<string, mixed>  $answers
     */
    private function matches(array $rule, array $answers): bool
    {
        $field = $rule['field'] ?? null;

        if (! is_string($field) || $field === '') {
            return true;
        }

        $operator = is_string($rule['operator'] ?? null) ? $rule['operator'] : 'equals';
        $expected = $rule['value'] ?? null;
        $actual = $this->readable($answers[$field] ?? null);

        return match ($operator) {
            'not_equals' => $actual !== $this->readable($expected),
            'contains' => $actual !== '' && str_contains($actual, $this->readable($expected)),
            'not_contains' => ! str_contains($actual, $this->readable($expected)),
            'is_filled' => $actual !== '',
            'is_empty' => $actual === '',
            default => $actual === $this->readable($expected),
        };
    }

    /**
     * A value as a comparable string.
     */
    private function readable(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return $value === true ? '1' : '';
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($item): string => $this->readable($item), $value));
        }

        return (string) $value;
    }
}
