<?php

namespace Reno\Cms\Helpers;

class ValidatorHelper
{
    public static function normalizeRulesArray(string $fieldName, array $rules): array
    {
        if (empty($rules)) {
            return [$fieldName => []];
        }

        if (isset($rules[0]) && !is_array($rules[0])) {
            return [$fieldName => array_values($rules)];
        }

        $result = [];
        foreach ($rules as $key => $keyRules) {
            $key = trim($key);
            $key = !empty($key) ? preg_replace('/^([^\.]*)(\.?)/', $fieldName . '$2', $key) : $fieldName;
            $result[$key] = $keyRules;
        }

        return $result;
    }
}
