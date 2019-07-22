<?php

namespace IndeedHat\TokenSearch\Database;

use PDO;
use PDOException;

class Database extends PDO
{

    /**
     * @var array Database drivers that support SAVEPOINT * statements.
     */
    protected static $_supportedDrivers = ["pgsql", "mysql"];

    /**
     * @var int the current transaction depth
     */
    protected $_transactionDepth = 0;


    /**
     * Test if database driver support savepoints
     *
     * @return bool
     */
    protected function hasSavepoint()
    {
        return in_array(
            $this->getAttribute(PDO::ATTR_DRIVER_NAME),
            self::$_supportedDrivers
        );
    }


    /**
     * Start transaction
     *
     * @return bool|void
     */
    public function beginTransaction()
    {
        if (0 == $this->_transactionDepth || !$this->hasSavepoint()) {
            parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->_transactionDepth}");
        }

        $this->_transactionDepth++;
    }

    /**
     * Commit current transaction
     *
     * @return bool|void
     */
    public function commit()
    {
        $this->_transactionDepth--;

        if (0 == $this->_transactionDepth || !$this->hasSavepoint()) {
            parent::commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->_transactionDepth}");
        }
    }

    /**
     * Rollback current transaction,
     *
     * @throws PDOException if there is no transaction started
     *
     * @return bool|void
     */
    public function rollBack()
    {
        if (0 == $this->_transactionDepth) {
            throw new PDOException('Rollback error : There is no transaction started');
        }

        $this->_transactionDepth--;

        if (0 == $this->_transactionDepth || !$this->hasSavepoint()) {
            parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->_transactionDepth}");
        }
    }
}
