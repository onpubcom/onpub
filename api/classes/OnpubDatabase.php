<?php

/**
 * Manage an Onpub database.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubDatabase
{
  private $pdo;

  /**
   * Connect to a database.
   *
   * All the methods in this class which query the database use the database
   * connection provided by the PDO object required by this constructor.
   * Currently, Onpub only supports MySQL as a database for storing content.
   * Therefore, when constructing the PDO object, only the
   * {@link PHP_MANUAL#ref.pdo-mysql PDO_MYSQL} driver is supported
   * as a PDO {@link PHP_MANUAL#ref.pdo-mysql.connection data source}.
   *
   * @param PDO $pdo A {@link PHP_MANUAL#function.pdo-construct PHP Data Object}.
   */
  function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
    $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
  }

  /**
   * Get the name of the currently connected-to MySQL database.
   *
   * @return mixed NULL if no database is currently selected. Otherwise, the
   * name of the MySQL that's currently being used.
   */
  public function current()
  {
    $result = $this->pdo->query('SELECT DATABASE() AS current');
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    if (!($row = $result->fetch(PDO::FETCH_ASSOC))) {
      return 0;
    }

    $result->closeCursor();

    return $row["current"];
  }

  /**
   * Delete the Onpub schema.
   *
   * Calling this method will delete all Onpub content and schema in the
   * PDO-connected database! Use with caution.
   *
   * @return mixed TRUE if the schema was successfully deleted. An array
   * of PDOException objects will be returned if any errors occured.
   */
  public function delete()
  {
    $sqlfile = array();
    $line = 0;
    $exceptions = array();

    $sqlfile = file('../api/sql/deleteonpubtables.sql');

    // advance past all comments
    while (strpos($sqlfile[$line], '--') !== FALSE) {
      $line++;

      while ($sqlfile[$line] == "\n") {
        $line++;
      }
    }

    for ($i = $line; $i < sizeof($sqlfile); $i++) {
      $query = '';

      while (strpos($sqlfile[$i], ';') === FALSE) {
        $query .= $sqlfile[$i];
        $i++;
      }

      $query .= $sqlfile[$i];

      if (($i + 1) < sizeof($sqlfile)) {
        while ($sqlfile[$i + 1] == "\n") {
          $i++;
        }
      }

      $result = NULL;
      $result = $this->pdo->exec($query);

      try {
        OnpubDatabase::verifyExec($this->pdo, $result, FALSE);
      }
      catch (PDOException $e) {
        $exceptions[] = $e;
      }
    }

    if (sizeof($exceptions)) {
      return $exceptions;
    }

    return TRUE;
  }

  /**
   * Install the {@link http://onpub.com/pdfs/onpub_schema.pdf Onpub schema}.
   *
   * Calling this method will install the Onpub tables in the PDO-connected
   * database.
   *
   * @param int $version Optional argument to specify what version of the Onpub
   * schema to install. If NULL (the default), the latest version of the schema
   * will be added to the database.
   * @return mixed TRUE if the schema was successfully installed. An array
   * of PDOException objects will be returned if any errors occured.
   */
  public function install($version = NULL)
  {
    $sqlfile = array();
    $line = 0;
    $exceptions = array();

    $sqlfile = file('../api/sql/createonpubtables-rev0.sql');

    // advance past all comments
    while (strpos($sqlfile[$line], '--') !== FALSE) {
      $line++;

      while ($sqlfile[$line] == "\n") {
        $line++;
      }
    }

    for ($i = $line; $i < sizeof($sqlfile); $i++) {
      $query = '';

      while (strpos($sqlfile[$i], ';') === FALSE) {
        $query .= $sqlfile[$i];
        $i++;
      }

      $query .= $sqlfile[$i];

      if (($i + 1) < sizeof($sqlfile)) {
        while ($sqlfile[$i + 1] == "\n") {
          $i++;
        }
      }

      $result = NULL;
      $result = $this->pdo->exec($query);

      try {
        OnpubDatabase::verifyExec($this->pdo, $result, FALSE);
      }
      catch (PDOException $e) {
        $exceptions[] = $e;
      }
    }

    if (sizeof($exceptions)) {
      return $exceptions;
    }

    return TRUE;
  }

  /**
   * Gets a list of MySQL databases that the logged-in user has access to.
   * System database names are excluded from the list.
   *
   * @return array Array will be empty if user has no database access.
   * Otherwise array will contain the names of the MySQL databases she has
   * access to.
   */
  public function listDBs()
  {
    $result = $this->pdo->query('SHOW DATABASES');
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $excludes = array('mysql', 'performance_schema', 'information_schema');
    $dbs = array();

    if ($rows) {
      foreach ($rows as $row) {
        if (!in_array($row['Database'], $excludes))
        {
          $dbs[] = $row['Database'];
        }
      }
    }

    $result->closeCursor();
    return $dbs;
  }

  /**
   * Check the status of the Onpub schema.
   *
   * @return mixed The version of the schema in the database as an int. An array
   * of PDOException objects will be returned if the schema is incomplete or
   * not installed.
   */
  public function status()
  {
    $oaamaps = new OnpubAAMaps($this->pdo);
    $oarticles = new OnpubArticles($this->pdo);
    $oauthors = new OnpubAuthors($this->pdo);
    $oimages = new OnpubImages($this->pdo);
    $osamaps = new OnpubSAMaps($this->pdo);
    $osections = new OnpubSections($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $owsmaps = new OnpubWSMaps($this->pdo);
    $queryOptions = new OnpubQueryOptions($this->pdo);
    $queryOptions->setPage(1, 1);
    $exceptions = array();
    $version = 0;

    try {
      $oaamaps->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $oarticles->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $oauthors->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $oimages->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $osamaps->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $osections->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $owebsites->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    try {
      $owsmaps->select($queryOptions);
    }
    catch (PDOException $e) {
      $exceptions[] = $e;
    }

    if (sizeof($exceptions)) {
      return $exceptions;
    }

    $version = 1;

    return $version;
  }

  /**
   * Verify the results of a call to {@link PHP_MANUAL#function.pdostatement-execute PDOStatement->execute()}.
   *
   * Used internally to verify whether or not a PDOStatement->execute() call was
   * successful or not. If the call returned FALSE then an exception is
   * thrown with an error message and error code, explaining what went wrong.
   * If the execute() call fails during a running database transaction,
   * {@link PHP_MANUAL#function.pdo-rollback PDOStatement->rollback()} is called to
   * roll back the transaction to put the database back in the state it was
   * before the error occured; then the appropriate exception is thrown.
   *
   * @param PDO $pdo The PDO object which called {@link PHP_MANUAL#function.pdo-prepare prepare()}.
   * @param mixed $result The value execute() returned when it was called.
   * @param bool $isTransaction TRUE if execute() was called during a running
   *                            database transaction, FALSE otherwise.
   * @param array $errorInfo The array returned by {@link PHP_MANUAL#function.pdostatement-errorinfo PDOStatement->errorInfo()}.
   * @return void
   * @throws PDOException if the execute() call failed.
   */
  public static function verifyExecute(PDO $pdo, $result, $isTransaction, $errorInfo)
  {
    if ($isTransaction) {
      if (!$result) {
        $status = $pdo->rollBack();

        if (!$status) {
          $errorInfo = $pdo->errorInfo();
          $e = new PDOException($errorInfo[2], $errorInfo[1]);
          $e->errorInfo = $errorInfo;

          throw $e;
        }

        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
    else {
      if (!$result) {
        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
  }

  /**
   * Verify the results of a call to {@link PHP_MANUAL#function.pdo-exec PDO->exec()}.
   *
   * Used internally to verify whether or not a PDO->exec() call was
   * successful or not. If the call returned FALSE then an exception is
   * thrown with an error message and error code, explaining what went wrong.
   * If the exec() call fails during a running database transaction,
   * {@link PHP_MANUAL#function.pdo-rollback PDO->rollback()} is called to
   * roll back the transaction to put the database back in the state it was
   * before the error occured; then the appropriate exception is thrown.
   *
   * @param PDO $pdo The PDO object which called exec().
   * @param mixed $result The value exec() returned when it was called.
   * @param bool $isTransaction TRUE if exec() was called during a running
   *                            database transaction, FALSE otherwise.
   * @return void
   * @throws PDOException if the exec() call failed.
   */
  public static function verifyExec(PDO $pdo, $result, $isTransaction)
  {
    if ($isTransaction) {
      if ($result === FALSE) {
        $errorInfo = $pdo->errorInfo();
        $status = $pdo->rollBack();

        if (!$status) {
          $errorInfo = $pdo->errorInfo();
          $e = new PDOException($errorInfo[2], $errorInfo[1]);
          $e->errorInfo = $errorInfo;

          throw $e;
        }

        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
    else {
      if ($result === FALSE) {
        $errorInfo = $pdo->errorInfo();
        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
  }

  /**
   * Verify the results of a call to {@link PHP_MANUAL#function.pdo-query PDO->query()}.
   *
   * Used internally to verify whether or not a PDO->query() call was
   * successful or not. If the call returned FALSE then an exception is
   * thrown with an error message and error code, explaining what went wrong.
   * If the query() call fails during a running database transaction,
   * {@link PHP_MANUAL#function.pdo-rollback PDO->rollback()} is called to
   * roll back the transaction to put the database back in the state it was
   * before the error occured; then the appropriate exception is thrown.
   *
   * @param PDO $pdo The PDO object which called query().
   * @param mixed $result The value query() returned when it was called.
   * @param bool $isTransaction TRUE if query() was called during a running
   *                            database transaction, FALSE otherwise.
   * @return void
   * @throws PDOException if the query() call failed.
   */
  public static function verifyQuery(PDO $pdo, $result, $isTransaction)
  {
    if ($isTransaction) {
      if (!$result) {
        $errorInfo = $pdo->errorInfo();
        $status = $pdo->rollBack();

        if (!$status) {
          $errorInfo = $pdo->errorInfo();
          $e = new PDOException($errorInfo[2], $errorInfo[1]);
          $e->errorInfo = $errorInfo;

          throw $e;
        }

        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
    else {
      if (!$result) {
        $errorInfo = $pdo->errorInfo();
        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
  }

  /**
   * Verify the results of a call to {@link PHP_MANUAL#function.pdo-beginTransaction PDO->beginTransaction()} or {@link PHP_MANUAL#function.pdo-commit PDO->commit()}.
   *
   * Used internally to verify whether or not a PDO->beginTransaction() or a
   * PDO->rollback() call was successful or not. If the call returned FALSE
   * then an exception is thrown with an error message and error code,
   * explaining what went wrong.
   *
   * @param PDO $pdo The PDO object which called beginTransaction() or rollback().
   * @param mixed $result The value beginTransaction() or rollback() returned when it was called.
   * @return void
   * @throws PDOException if the beginTransaction() or rollback() call failed.
   */
  public static function verifyTransaction(PDO $pdo, $result)
  {
    if (!$result) {
      $errorInfo = $pdo->errorInfo();
      $e = new PDOException($errorInfo[2], $errorInfo[1]);
      $e->errorInfo = $errorInfo;

      throw $e;
    }
  }

  /**
   * Verify the results of a call to {@link PHP_MANUAL#function.pdo-prepare PDO->prepare()}.
   *
   * Used internally to verify whether or not a PDO->prepare() call was
   * successful or not. If the call returned FALSE then an exception is
   * thrown with an error message and error code, explaining what went wrong.
   * If the prepare() call fails during a running database transaction,
   * {@link PHP_MANUAL#function.pdo-rollback PDO->rollback()} is called to
   * roll back the transaction to put the database back in the state it was
   * before the error occured; then the appropriate exception is thrown.
   *
   * @param PDO $pdo The PDO object which called prepare().
   * @param mixed $result The value prepare() returned when it was called.
   * @param bool $isTransaction TRUE if prepare() was called during a running
   *                            database transaction, FALSE otherwise.
   * @return void
   * @throws PDOException if the prepare() call failed.
   */
  public static function verifyPrepare(PDO $pdo, $result, $isTransaction)
  {
    if ($isTransaction) {
      if ($result === FALSE) {
        $errorInfo = $pdo->errorInfo();
        $status = $pdo->rollBack();

        if (!$status) {
          $errorInfo = $pdo->errorInfo();
          $e = new PDOException($errorInfo[2], $errorInfo[1]);
          $e->errorInfo = $errorInfo;

          throw $e;
        }

        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
    else {
      if ($result === FALSE) {
        $errorInfo = $pdo->errorInfo();
        $e = new PDOException($errorInfo[2], $errorInfo[1]);
        $e->errorInfo = $errorInfo;

        throw $e;
      }
    }
  }

  /**
   * Decode a UTF8-encoded string to a latin1-encoded string.
   *
   * @param string $in_str UTF8-encoded string.
   * @return string latin1-encoded string.
   */
  public static function utf8Decode($in_str)
  {
    // utf8Decode is courtesy nospam@jra.nu and was found
    // at: http://us4.php.net/utf8-decode in the comments section
    // Replace ? with a unique string
    $new_str = str_replace("?", "q0u0e0s0t0i0o0n", $in_str);

    // Try the utf8_decode
    $new_str = utf8_decode($new_str);

    // if it contains ? marks
    if (strpos($new_str, "?") !== FALSE) {
      // Something went wrong, set new_str to the original string.
      $new_str = $in_str;
    }
    else {
      // If not then all is well, put the ?-marks back where is belongs
      $new_str = str_replace("q0u0e0s0t0i0o0n", "?", $new_str);
    }

    return $new_str;
  }
}
?>