<?php

/**
 * @format
 */

require_once dirname(__FILE__) . '/../interfaces/iDbConnect.php';

class MysqlDbConnect implements iDbConnect
{
    private $con;

    function connect()
    {
        include_once dirname(__FILE__) . '/Constants.php';

        $this->con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->con->set_charset('utf8');

        if (mysqli_connect_errno()) {
            return mysqli_connect_error();
        }
        return $this->con;
    }
}
