<?php

/**
 * @format
 */

require_once dirname(__FILE__) . '/../interfaces/iDbConnect.php';

class MysqlPdoDbConnect implements iDbConnect
{
    private $con;

    function connect()
    {
        include_once dirname(__FILE__) . '/Constants.php';
        try {
            $this->con = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_PERSISTENT => true)
            );
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            die("Impossible de se connecter : " . $e->getMessage());
        }

        return $this->con;
    }
}
