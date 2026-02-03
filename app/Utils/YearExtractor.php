<?php

namespace App\Utils;

use Carbon\Carbon;

class YearExtractor
{
    /**
     * Intenta devolver un año válido basado en la entrada proporcionada.
     *
     * @param mixed $value
     * @return int|null
     */
    public static function extractYear(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^\d{4}$/', $trimmed)) {
            return (int) $trimmed;
        }

        if (preg_match('/\d{4}/', $trimmed, $matches)) {
            return (int) $matches[0];
        }

        try {
            return Carbon::parse($trimmed)->year;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extrae el par (start_year, end_year) de una cadena que contiene un rango.
     */
    public static function extractYearsFromRange(mixed $value): array
    {
        $startYear = null;
        $endYear = null;

        if (!$value) {
            return [$startYear, $endYear];
        }

        $text = trim((string) $value);
        if ($text === '') {
            return [$startYear, $endYear];
        }

        if (preg_match('/(\d{4})\s*[\p{Pd}\/]\s*(\d{4}|presente|actualidad|en curso)?/iu', $text, $matches)) {
            $startYear = (int) $matches[1];

            $endHint = isset($matches[2]) ? trim($matches[2]) : '';
            if ($endHint !== '' && preg_match('/^\d{4}$/', $endHint)) {
                $endYear = (int) $endHint;
            }

            return [$startYear, $endYear];
        }

        return [$startYear, $endYear];
    }
}
