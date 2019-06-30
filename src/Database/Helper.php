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
}
