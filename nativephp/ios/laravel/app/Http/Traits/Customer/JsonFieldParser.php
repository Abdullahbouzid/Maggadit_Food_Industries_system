<?php

namespace App\Http\Traits\Customer;

trait JsonFieldParser
{
    protected function parseJsonField($field)
    {
        if (empty($field)) {
            return [];
        }

        if (is_array($field)) {
            return $field;
        }

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded ?? [];
            }
        }

        return [];
    }

    protected function parseStatsField($field)
    {
        if (empty($field)) {
            return [];
        }

        $stats = [];

        if (is_array($field)) {
            $stats = $field;
        } elseif (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $stats = $decoded;
            }
        }

        if (! empty($stats) && ! isset($stats[0]['key']) && ! isset($stats[0]['value'])) {
            $formatted = [];
            $keys = array_keys($stats);
            foreach ($keys as $key) {
                if (! is_numeric($key) && ! is_null($stats[$key])) {
                    $formatted[] = ['key' => $key, 'value' => $stats[$key]];
                }
            }

            return $formatted;
        }

        return $stats;
    }
}
