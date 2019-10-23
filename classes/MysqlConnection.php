<?php
// on déclare un type strict pour chaque variable
declare(strict_types=1);

require_once dirname(__FILE__) . '/../interfaces/iDbConnect.php';

abstract class AbstractConnection  implements iDbConnect
{

    // properties
    // à faire : renommer en tititutu
    protected $db_name = '';
    protected $db_user = '';
    protected $db_password = '';
    protected $db_address = '';

    // control attributes set = true
    protected $valid_db_name = false;
    protected $valid_db_user = false;
    protected $valid_db_password = false;
    protected $valid_db_address = false;
    // protected $validAttributes = array(
    //     "setDbName" => FALSE, "setUser" => FALSE,
    //     "setPassword" => FALSE, "setAddress" => FALSE
    // );

    // methods

    // implemented in Connection class (sub)
    public abstract function connect();
    // get sur chaque attribut et crée la chaine param_connection


    // setters : will be impleted in each subClass / return a boolean
    public abstract function setDbName($dbName): bool;
    public abstract function setUser($user): bool;
    public abstract function setPassword($pw): bool;
    public abstract function setAddress($address): bool;

    // getters
    protected function getDbName()
    {
        return $this->db_name;
    }

    protected function getUser()
    {
        return $this->db_user;
    }

    protected function getPassword()
    {
        return $this->db_password;
    }

    protected function getAddress()
    {
        return $this->db_address;
    }

    // common structure for all the subClasses
    protected function connectionSkeleton($dsn, $user, $password): object
    {

        try {
            $result = new PDO($dsn, $user, $password);

            // Configuration du pilote : nous voulons des exceptions
            $result->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        // todo : à implémenter avec messages personnalisés en fonction des grandes familles de codes d'erreur
        catch (Exception $e) {
            $result = (object) ['code' => $e->getMessage(), 'message' => 'Probleme de connexion'];
        } finally {
            return $result;
        }
    }
}

// subClass MySQL
class MysqlConnection extends AbstractConnection
{

    // properties
    private const DB_DRIVER = 'mysql';

    // setters
    public function setAddress($address): bool
    {
        $this->db_address = $address;
        // todo : implémenter tests / validations
        if ($address === '') {
            return false;
        }
        $this->valid_db_address = true;
        return true;
    }

    public function setDbName($dbName): bool
    {
        $this->db_name = $dbName;
        // todo : implémenter tests / validations
        if ($dbName === '') {
            return false;
        }
        $this->valid_db_name = true;
        return true;
    }

    public function setUser($user): bool
    {
        $this->db_user = $user;
        // todo : implémenter tests / validations
        if ($user === '') {
            return false;
        }
        $this->valid_db_user = true;
        return true;
    }

    public function setPassword($pw): bool
    {
        $this->db_password = $pw;
        // todo : implémenter tests / validations
        if ($pw === '') {
            return false;
        }
        $this->valid_db_password = true;
        return true;
    }



    /**
     * connect mysql
     * 
     * @return object if connection success, return PDO / Else return array
     * @example 
     * 
     */
    public function connect(): object
    {

        $inValidAttributes = $this->findEmptyAttribs();

        if (!empty($inValidAttributes)) {
            return (object) $inValidAttributes;
        }

        return parent::connectionSkeleton(self::DB_DRIVER . ':host=' . parent::getAddress() .
            ';dbname=' . parent::getDbName(), parent::getUser(), parent::getPassword());
    }



    /**
     * check if all neeeded connection attributes are filled 
     * @return array containing setters methods of empty attributs
     * - add here all new connection attribute
     */
    private function findEmptyAttribs(): array
    {
        $inValidAttributes = array();
        if (!$this->valid_db_name) {
            array_push($inValidAttributes, array("setDbName"));
        }
        if (!$this->valid_db_user) {
            array_push($inValidAttributes, array("setUser"));
        }
        if (!$this->valid_db_password) {
            array_push($inValidAttributes, array("setPassword"));
        }
        if (!$this->valid_db_address) {
            array_push($inValidAttributes, array("setAddress"));
        }
        return $inValidAttributes;
    }
}




// faire un check sur l'os pour voir les formats acceptés
//getter 
// limite des ports jusqu'à 65000
// le host 
