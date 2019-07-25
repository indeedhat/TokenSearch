<?php

namespace IndeedHat\TokenSearch\Database;

use PDOStatement;

class Helper
{
    /**
     * Check if a statement was successful
     *
     * @param PDOStatement $statement
     *
     * @return bool
     */
    public static function ok(PDOStatement $statement): bool
    {
        return "00000" == $statement->errorCode();
    }

    /**
     * Check if a response code is for a duplicate key
     *
     * @param PDOStatement $statement
     *
     * @return bool
     */
    public static function duplicateKey(PDOStatement $statement): bool
    {
        return "23000" == $statement->errorCode();
    }

    /**
     * build a template for a multi row insert query
     *
     * @param array $rows
     *
     * @return string
     */
    public static function buildMultiRowQueryTemplate(array $rows): string
    {
        $template = [];

        foreach ($rows as $row) {
            $template[] = "(" . substr(str_repeat("?, ", count($row)), 0, -2) . ")";
        }

        return implode(",", $template);
    }

    /**
     * Flatten out a nested array of row data
     *
     * @param array $rows
     *
     * @return array
     */
    public static function flattenParamArray(array $rows): array
    {
        $flat = [];
        foreach ($rows as $row) {
            $flat = array_merge($flat, $row);
        }

        return $flat;
    }
}
