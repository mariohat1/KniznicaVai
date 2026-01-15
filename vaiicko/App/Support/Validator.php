<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Author;
use App\Models\Genre;

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
     * Validate birth date (optional). Accepts only a 4-digit year (YYYY) and must not be in the future.
     */
    public static function validateYear(?string $value, string $field): ?string
    {
        $v = trim(($value ?? ''));
        if ($v === '') {
            return null;
        }

        if (!preg_match('/^\d{1,4}$/', $v)) {
            return ucfirst($field) . ' has invalid format (expected year).';
        }

        $year = (int)$v;
        $current = (int)date('Y');

        if ($year < 0 || $year > $current) {
            return ucfirst($field) . " must be between 0 and $current.";
        }

        return null;
    }

    public static function validateYearRange(
        ?string $from,
        ?string $to,
        int     $maxSpan = 100
    ): ?string
    {
        if ($error = self::validateYear($from, 'birth year')) {
            return $error;
        }
        if ($error = self::validateYear($to, 'death year')) {
            return $error;
        };
        if ($from === '' || $from === null) return null;
        if ($to === '' || $to === null) return null;

        if ((int)$from > (int)$to) {
            return 'Birth year "from" must be less than or equal to "to".';
        }
        if (((int)$to - (int)$from) > $maxSpan) {
            return "Year range must not exceed $maxSpan years.";
        }
        return null;
    }
    public static function validateAuthorId(?int $authorId): ?string
    {
        if ($authorId === null) {
            return 'Autor musí byť zvolený.';
        }
        try {
            Author::getOne($authorId);
        } catch (\Exception $e) {
            return 'Zvolený autor neexistuje.';
        }
        return null;
    }

    public static function validateCategoryId(?int $categoryId): ?string
    {
        if ($categoryId === null) {
            return 'Kategória musí byť zvolená.';
        }
        try {
            Category::getOne($categoryId);
        } catch (\Exception $e) {
            return 'Zvolená kategória neexistuje.';
        }
        return null;
    }

    public static function validateGenreId(?int $genreId): ?string
    {
        if ($genreId === null) {
            return 'Žáner musí byť zvolený.';
        }
        try {
            Genre::getOne($genreId);
        } catch (\Exception $e) {
            return 'Zvolený žáner neexistuje.';
        }
        return null;
    }

    /**
     * Validate ISBN (format only). Accepts ISBN-10 or ISBN-13 with or without separators.
     * Returns null when valid or an error message string when invalid.
     */
    public static function validateIsbn(?string $value, string $field = 'ISBN'): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') return null;

        // remove non-digit and non-X characters
        $normalized = preg_replace('/[^0-9Xx]/', '', $v);
        if ($normalized === null) return ucfirst($field) . ' has invalid format.';

        // ISBN-13 check
        if (strlen($normalized) === 13 && ctype_digit($normalized)) {
            $sum = 0;
            for ($i = 0; $i < 13; $i++) {
                $digit = (int)$normalized[$i];
                $sum += ($i % 2 === 0) ? $digit : $digit * 3;
            }
            if (($sum % 10) === 0) return null;
            return ucfirst($field) . ' is not a valid ISBN-13.';
        }

        // ISBN-10 check
        if (strlen($normalized) === 10) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                if (!isset($normalized[$i]) || !ctype_digit($normalized[$i])) {
                    return ucfirst($field) . ' is not a valid ISBN-10.';
                }
                $sum += (10 - $i) * (int)$normalized[$i];
            }
            $check = $normalized[9];
            $checkVal = ($check === 'X' || $check === 'x') ? 10 : (ctype_digit($check) ? (int)$check : -1);
            if ($checkVal < 0) return ucfirst($field) . ' is not a valid ISBN-10.';
            $sum += 1 * $checkVal;
            if (($sum % 11) === 0) return null;
            return ucfirst($field) . ' is not a valid ISBN-10.';
        }

        return ucfirst($field) . ' has invalid format (expected ISBN-10 or ISBN-13).';
    }
}

