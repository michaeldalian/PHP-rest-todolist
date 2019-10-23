<?php

/**
 * @format
 */

// require_once dirname(__FILE__) . '/../interfaces/iDbConnect.php';

// abstract class  DbConnect implements iDbConnect
abstract class  DbConnect
{
    public $dbAdress = '';
    public $dbName = '';
    public $dbUser = '';
    private $dbPassWord = '';

    /**
     * connection par un driver
     *
     * @abstract
     */
    public abstract function connect();



    // private skletton
}
