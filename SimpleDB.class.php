<?php

namespace Birdey;

/**
 * Summary of SimpleDB_ErrorConnecting_Exception
 * @author Birdey
 * @copyright (c) $CURRENT_YEAR
 */
class SimpleDB_ErrorConnecting_Exception extends \Exception
{
}

/**
 * Summary of SimpleDB_ConnectionNotStarted_Exception
 * @author Birdey
 * @copyright (c) $CURRENT_YEAR
 */
class SimpleDB_ConnectionNotStarted_Exception extends \Exception
{
}

/**
 * Summary of SimpleDB_Order
 * @author Birdey
 * @copyright (c) 2023
 */
enum SimpleDB_Order: string
{
    case DESC = 'DESC';
    case ASC = 'ASC';
}

/**
 * Summary of SimpleDB
 * @author Birdey
 * @copyright (c) 2023
 */
class SimpleDB
{

    private bool $connected = false;
    private \PDO $PDOConnection;
    private string $host;
    private string $dbname;
    private string $user;
    private string $password;
    private string $queryString;
    private string $previouslyAttemptedQueryString;
    private \PDOStatement|bool $query;
    private array $result;
    private int $resultCount;
    private string $errorMessage = '';
    private array $pdoValues = [];



    /**
     * Summary of Connect
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $password
     * @throws SimpleDB_ErrorConnecting_Exception
     * @return SimpleDB
     */
    public function Connect(string $host, string $dbname, string $user, string $password): SimpleDB
    {
        $this->host     = $host;
        $this->dbname   = $dbname;
        $this->user     = $user;
        $this->password = $password;

        //echo $host, $dbname, $user, $password;

        try {
            $this->PDOConnection = new \PDO(
                'mysql:host=' . $this->host . '; dbname=' . $this->dbname,
                $this->user,
                $this->password
            );
        } catch (\PDOException $e) {
            throw new SimpleDB_ErrorConnecting_Exception('Error Connecting to Database with provided information');
        }

        $this->PDOConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->connected = true;

        return $this;
    }

    /**
     * Summary of InsertInto
     * @param string $table
     * @param array $keys
     * @param array $values
     * @throws \UnexpectedValueException
     * @return SimpleDB
     */
    public function InsertInto(string $table, array $keys, array $values): SimpleDB
    {
        $keys_string = "`" . implode('`, `', $keys) . "`";

        $values_string = '';
        $keyIndex      = 0;

        foreach ($values as $value) {
            $pdoPlaceholder                   = ':' . $keys[$keyIndex++];
            $this->pdoValues[$pdoPlaceholder] = $value;
            $values_string .= "$pdoPlaceholder";
            $values_string .= ", ";
        }
        $values_string     = trim($values_string, ', ');
        $this->queryString = "INSERT INTO `$table` ($keys_string) VALUES ($values_string)";
        return $this;
    }

    /**
     * Summary of Delete
     * @return SimpleDB
     */
    public function Delete(): SimpleDB
    {
        $this->queryString = "DELETE ";
        return $this;
    }
    /**
     * Summary of SelectAll
     * @return SimpleDB
     */
    public function SelectAll(): SimpleDB
    {
        $this->queryString = "SELECT * ";
        return $this;
    }
    /**
     * Summary of Select
     * @param array $values
     * @return SimpleDB
     */
    public function Select(array $values): SimpleDB
    {
        $this->queryString = "SELECT ";
        foreach ($values as $key => $value) {
            $this->queryString .= "`$value`" . (($key != array_key_last($values)) ? ', ' : ' ');
        }
        //echo $this->queryString;

        return $this;
    }

    /**
     * Summary of SelectCount
     * @param array $values
     * @return SimpleDB
     */
    public function SelectCount(array $values): SimpleDB
    {
        $this->queryString = "SELECT COUNT(*) ";
        foreach ($values as $key => $value) {
            $this->queryString .= "`$value`" . (($key != array_key_last($values)) ? ', ' : ' ');
        }
        //echo $this->queryString;

        return $this;
    }

    /**
     * Summary of From
     * @param string $tableName
     * @return SimpleDB
     */
    public function From(string $tableName): SimpleDB
    {
        $this->queryString .= "FROM `$tableName` ";
        return $this;
    }

    /**
     * Summary of Where
     * @param string $field
     * @param string|int $is
     * @return SimpleDB
     */
    public function Where(string $field, string|int $is): SimpleDB
    {
        $pdoString                   = ":" . $this->generateRandomString();
        $this->pdoValues[$pdoString] = $is;
        $this->queryString .= "WHERE `$field` = $pdoString ";
        return $this;
    }

    /**
     * Summary of And
     * @param string $field
     * @param string|int $is
     * @return SimpleDB
     */
    public function And (string $field, string|int $is): SimpleDB
    {
        $pdoString                   = ":" . $this->generateRandomString();
        $this->pdoValues[$pdoString] = $is;
        $this->queryString .= "AND `$field` = $pdoString ";
        return $this;
    }

    /**
     * Summary of OrderBy
     * @param string $field
     * @param SimpleDB_Order $order
     * @return SimpleDB
     */
    public function OrderBy(string $field, SimpleDB_Order $order): SimpleDB
    {
        $this->queryString .= "ORDER BY `$field` {$order->value} ";
        return $this;
    }

    /**
     * Summary of Limit
     * @param int $limit
     * @param int $start
     * @return SimpleDB
     */
    public function Limit(int $limit, int $start = 0): SimpleDB
    {
        $this->queryString .= "LIMIT $start, $limit ";
        return $this;
    }

    /**
     * Get data from database
     * @throws SimpleDB_ConnectionNotStarted_Exception
     * @return array|bool array with data or false if unsucessfull
     */
    public function Get(): array|bool
    {
        //echo "<code>Query: \"{$this->queryString}\"</code><br>";
        $this->previouslyAttemptedQueryString = $this->queryString;
        if (!$this->connected)
            throw new SimpleDB_ConnectionNotStarted_Exception("Must connect to database before fetching data");

        if ($this->query = $this->PDOConnection->prepare($this->queryString)) {
            foreach ($this->pdoValues as $pdoKey => $pdoVal) {
                if ($this->query->bindValue($pdoKey, $pdoVal)) {
                    $this->queryString = preg_replace("/$pdoKey/", "$pdoVal", $this->queryString);
                } else {
                    Logger::Error("$pdoVal not bound for query: \"$this->queryString\"");
                }
            }
            $this->pdoValues = [];
            Logger::Verbose("SimpleDB executing query: \n\r" . $this->queryString);
            if ($this->query->execute()) {
                $this->result                         = $this->query->fetchAll(\PDO::FETCH_OBJ);
                $this->resultCount                    = $this->query->rowCount();
                $this->previouslyAttemptedQueryString = $this->query->queryString;
                return $this->result;
            }
        }
        Logger::DrawLogBox();
        return false;
    }

    public function GetTimed(): array|bool
    {
        $startTime        = microtime(true);
        $returnValue      = $this->Get();
        $runTime          = microtime(true) - $startTime;
        $formattedRunTime = number_format($runTime, 4, ",", ".");
        $className        = get_class($this);
        echo "<p>$className called function Get() and it took $formattedRunTime ms to execute!</p>";
        return $returnValue;
    }

    /**
     * Summary of Run
     * @throws SimpleDB_ConnectionNotStarted_Exception
     * @return void
     */
    public function Run(): void
    {
        $this->Get();
    }

    /**
     * Summary of GetPreviouslyAtemtedQueryString
     * @return string
     */
    public function GetPreviouslyAttemptedQueryString(): string
    {
        return $this->previouslyAttemptedQueryString;
    }

    /**
     * Summary of GetPDOError
     * @return array|null
     */
    public function GetPDOError(): array|null
    {
        if ($this->query) {
            return $this->query->errorInfo();
        }
        return null;
    }

    /**
     * Summary of GetResults
     * @return array|bool
     */
    public function GetResults(): array|bool
    {
        return $this->result ?? false;
    }

    private function generateRandomString($length = 10)
    {
        $characters       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        Logger::Verbose("Generated random string: $randomString");
        return $randomString;
    }

}