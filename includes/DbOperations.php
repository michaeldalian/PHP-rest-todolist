<?php

/**
 * @format
 */

require_once dirname(__FILE__) . '/../interfaces/iReadOperation.php';
require_once dirname(__FILE__) . '/../interfaces/iCreateOperation.php';
include_once dirname(__FILE__) . '/Constants.php';

class MysqlDatabase implements iReadOperation, iCreateOperation
{
    // The database connection variable
    private $con;

    // Constructor
    function __construct()
    {

        // require_once dirname(__FILE__) . '/MysqlPdoDbConnect.php';
        // $db = new MysqlPdoDbConnect();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Todolist_rest/classes/MysqlConnection.php';
        $db = new MysqlConnection();
        $db->setAddress(DB_HOST);
        $db->setUser(DB_USER);
        $db->setPassword(DB_PASS);
        $db->setDbName(DB_NAME);
        // prévoir charset

        $this->con = $db->connect();
    }

    // getAllTodos is returning all the todos from database
    public function getAllTodos(): array
    {
        $stmt = $this->con->prepare(
            "SELECT td_id as id, td_done as done, td_date as date, td_label as label, td_comment as comment  FROM todos;"
        );

        // For eache row, put the column value in the linked variable
        $stmt->bindColumn('id', $id, PDO::PARAM_INT);
        $stmt->bindColumn('done', $done, PDO::PARAM_INT);
        $stmt->bindColumn('date', $date, PDO::PARAM_STR);
        $stmt->bindColumn('label', $label, PDO::PARAM_STR);
        $stmt->bindColumn('comment', $commentary, PDO::PARAM_STR);
        // met le resultat de la vue sql dans le PDO
        $stmt->execute();
        $result = array();

        // browse each record of the view from the sql
        while ($stmt->fetch(PDO::FETCH_BOUND)) {
            // smallint to bool
            $done =  $done == 1 ? true : false;
            // format the date
            $date = date_create($date);
            $date =  $date->format('Y-m-d H:i');

            $element = array(
                'id' => $id, 'label' => $label, 'done' => $done,
                'date' => $date, 'commentary' => $commentary
            );
            array_push($result, $element);
        }

        return $result;
        $stmt = null;
    }

    // getDetailById is returning the detail of one Todo from the database
    public function getDetailById($id): array
    {
        $stmt = $this->con->prepare(
            "SELECT id, done, date, label, comment  FROM todos WHERE id = :id;"
        );

        // Bind the parameters with their sql name == put the value of the parameter in the sql.
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        // For eache row, put the column value in the linked variable
        $stmt->bindColumn('done', $done, PDO::PARAM_INT);
        $stmt->bindColumn('date', $date, PDO::PARAM_STR);
        $stmt->bindColumn('label', $label, PDO::PARAM_STR);
        $stmt->bindColumn('comment', $commentary, PDO::PARAM_STR);
        // met le resultat de la vue sql dans le PDO
        $stmt->execute();
        $result = array();

        // browse each record of the view from the sql
        while ($stmt->fetch(PDO::FETCH_BOUND)) {
            // smallint to bool
            $done =  $done == 1 ? true : false;
            // format the date
            $date = date_create($date);
            $date =  $date->format('Y-m-d H:i');

            $element = array(
                'id' => $id, 'label' => $label, 'done' => $done,
                'date' => $date, 'commentary' => $commentary
            );
            array_push($result, $element);
        }

        return $result;
        $stmt = null;
    }

    // searchTodos is returning an array of matching todos
    public function searchTodos(array $query): array
    {
        $stmt = $this->con->prepare(
            'SELECT id, label, date, comment FROM todos WHERE id != 0' . (is_null($query['equal-to'])
                ? ''
                : ' AND date = ' . $query['equal-to']) . (is_null($query['more-than'])
                ? ''
                : ' AND date > ' . $query['more-than']) . (is_null($query['less-than'])
                ? ''
                : ' AND date < ' . $query['less-than']) . (is_null($query['date-is'])
                ? ''
                : " AND DATEDIFF('" .
                $query['date-is'] .
                "', date) = 0") . (is_null($query['before-date'])
                ? ''
                : " AND DATEDIFF('" .
                $query['before-date'] .
                "', date) > 0") . (is_null($query['after-date'])
                ? ''
                : " AND DATEDIFF('" .
                $query['after-date'] .
                "', date) < 0") . (is_null($query['in-year'])
                ? ''
                : ' AND EXTRACT(YEAR FROM date) = ' . $query['in-year'])
        );

        // echo "<br>", $query;

        // For eache row, put the column value in the linked variable
        $stmt->bindColumn('id', $id, PDO::PARAM_INT);
        $stmt->bindColumn('done', $done, PDO::PARAM_INT);
        $stmt->bindColumn('date', $date, PDO::PARAM_STR);
        $stmt->bindColumn('label', $label, PDO::PARAM_STR);
        $stmt->bindColumn('comment', $commentary, PDO::PARAM_STR);
        // met le resultat de la vue sql dans le PDO
        $stmt->execute();
        $result = array();

        // browse each record of the view from the sql
        while ($stmt->fetch(PDO::FETCH_BOUND)) {
            // smallint to bool
            $done =  $done == 1 ? true : false;
            // format the date
            $date = date_create($date);
            $date =  $date->format('Y-m-d H:i');

            $element = array(
                'id' => $id, 'label' => $label, 'done' => $done,
                'date' => $date, 'commentary' => $commentary
            );
            array_push($result, $element);
        }

        return $result;
        $stmt = null;
    }

    public function createTodo($values): array
    {
        $stmt = $this->con->prepare(
            'INSERT INTO todos(label, done, date, comment) VALUES ( :label, :done, :date, :comment)'
        );
        // Bind the parameters with their sql name == put the value of the parameter in the sql.
        $stmt->bindParam(':label', $label, PDO::PARAM_STR);
        $stmt->bindParam(':done', $done, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $commentary, PDO::PARAM_STR);
        $label = $values['label'];
        $done = $values['done'];
        $date = $values['date'];
        $commentary = $values['comment'];

        // insert sql result in bdd
        if ($stmt->execute()) {
            return array(
                'error' => false,
                'Successfully inserted data ' => $values, $label, $done, $date, $commentary
            );
        } else {
            return array(
                'error' => true,
                'error inserted data ' => $values
            );
        }

        $stmt = null;
    }
}
