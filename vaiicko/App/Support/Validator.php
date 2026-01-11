<?php

namespace App\Support;

class Validator
{
    /**
     * Validate personal name (first name / last name)
     * Allows Unicode letters, spaces, hyphens and apostrophes. Limits length.
     * Returns null when valid or an error message string when invalid.
     */
    public static function validatePersonName(?string $value, string $field = 'name'): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') {
            return ucfirst($field) . ' is required.';
        }
        if (mb_strlen($v) > 50) {
            return ucfirst($field) . ' must be at most 50 characters.';
        }
        // allow letters (all languages), spaces, hyphen and apostrophe
        if (!preg_match("/^[\\p{L} '\\-]+$/u", $v)) {
            return ucfirst($field) . ' contains invalid characters.';
        }
        return null;
    }

    /**
     * Validate a short name/title (category, genre name)
     */
    public static function validateShortName(?string $value, string $field = 'name'): ?string
    {
        $v = trim(($value ?? ''));
        if ($v === '') {
            return $field . ' is required.';
        }
        if (mb_strlen($v) > 100) {
            return $field . ' must be at most 100 characters.';
        }
        return null;
    }

    /**
     * Validate optional text/description
     */
    public static function validateDescription(?string $value, int $max = 2000, string $field = 'description'): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') {
            return null;
        }
        if (mb_strlen($v) > $max) return ucfirst($field) . " must be at most $max characters.";
        return null;
    }

    /**
     * Validate birth date (optional). Must be YYYY-MM-DD and not in the future.
     */
    public static function validateBirthDate(?string $value, string $field = 'birth date'): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return ucfirst($field) . ' has invalid format (expected YYYY-MM-DD).';
        }
        $ts = strtotime($v);
        if ($ts === false) return ucfirst($field) . ' is not a valid date.';
        // compare to end of today
        $todayEnd = strtotime('today 23:59:59');
        if ($ts > $todayEnd) return ucfirst($field) . ' cannot be in the future.';
        return null;
    }
}
