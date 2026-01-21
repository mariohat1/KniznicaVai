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
            return ucfirst($field) . ' je povinné pole.';
        }
        if (mb_strlen($v) > 50) {
            return ucfirst($field) . ' môže mať najviac 50 znakov.';
        }
        if (!preg_match("/^[\\p{L} '\\-]+$/u", $v)) {
            return ucfirst($field) . ' obsahuje neplatné znaky.';
        }
        return null;
    }

    /**
     * Validate a short name/title (category, genre name)
     * Disallow digits and plus sign; allow Unicode letters, spaces, hyphens and apostrophes.
     */
    public static function validateShortName(?string $value, string $field = 'name'): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') {
            return $field . ' je povinné.';
        }
        if (mb_strlen($v) > 100) {
            return $field . ' môže mať maximálne 100 znakov.';
        }
        if (!preg_match("/^[\\p{L} '\\-]+$/u", $v)) {
            return $field . ' obsahuje neplatné znaky';
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
        if (mb_strlen($v) > $max) return ucfirst($field) . " môže mať maximálne $max znakov.";
        return null;
    }

    /**
     * Validate birth date (optional). Accepts only a 1-4 digit year and must not be in the future.
     * Empty value is allowed (returns null) — use validateRequiredYear when the field is mandatory.
     */
    public static function validateYear(?string $value, string $field): ?string
    {
        $v = trim((string)($value ?? ''));
        // optional: empty is allowed
        if ($v === '') {
            return null;
        }

        if (!preg_match('/^\d{1,4}$/', $v)) {
            return ucfirst($field) . ' má neplatný formát (očakáva sa rok).';
        }

        $year = (int)$v;
        $current = (int)date('Y');

        if ($year < 0 || $year > $current) {
            return ucfirst($field) . " musí byť medzi 0 a $current.";
        }

        return null;
    }

    /**
     * Validate a required year field. Calls validateYear but ensures value is present.
     */
    public static function validateRequiredYear(?string $value, string $field): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') {
            return ucfirst($field) . ' je povinné pole.';
        }
        return self::validateYear($value, $field);
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
            return 'Rok narodenia musí byť menší alebo rovný roku úmrtia.';
        }
        if (((int)$to - (int)$from) > $maxSpan) {
            return "Rozpätie rokov nesmie presiahnuť $maxSpan rokov.";
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
        if ($normalized === null) return ucfirst($field) . ' má neplatný formát.';

        // ISBN-13 check
        if (strlen($normalized) === 13 && ctype_digit($normalized)) {
            $sum = 0;
            for ($i = 0; $i < 13; $i++) {
                $digit = (int)$normalized[$i];
                $sum += ($i % 2 === 0) ? $digit : $digit * 3;
            }
            if (($sum % 10) === 0) return null;
            return ucfirst($field) . ' nie je platné ISBN-13.';
        }

        // ISBN-10 check
        if (strlen($normalized) === 10) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                if (!isset($normalized[$i]) || !ctype_digit($normalized[$i])) {
                    return ucfirst($field) . ' nie je platné ISBN-10.';
                }
                $sum += (10 - $i) * (int)$normalized[$i];
            }
            $check = $normalized[9];
            $checkVal = ($check === 'X' || $check === 'x') ? 10 : (ctype_digit($check) ? (int)$check : -1);
            if ($checkVal < 0) return ucfirst($field) . ' nie je platné ISBN-10.';
            $sum += 1 * $checkVal;
            if (($sum % 11) === 0) return null;
            return ucfirst($field) . ' nie je platné ISBN-10.';
        }

        return ucfirst($field) . ' má neplatný formát (očakáva sa ISBN-10 alebo ISBN-13).';
    }

    /**
     * Validate publisher name (required, max 255 chars)
     */
    public static function validatePublisher(?string $value, string $field = 'vydavateľ'): ?string
    {
        $v = trim((string)($value ?? ''));
        if ($v === '') {
            return ucfirst($field) . ' je povinné pole.';
        }
        if (mb_strlen($v) > 255) return ucfirst($field) . ' môže mať maximálne 255 znakov.';
        if (!preg_match('~^[\p{L}0-9\s.,\'()/&-]+$~u', $v)) return ucfirst($field) . ' obsahuje neplatné znaky.';
        return null;
    }
}
