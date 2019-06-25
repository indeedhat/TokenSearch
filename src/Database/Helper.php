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
}
