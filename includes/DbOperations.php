<?php

/**
 * @format
 */

require_once dirname(__FILE__) . '/../interfaces/iReadOperation.php';
require_once dirname(__FILE__) . '/../interfaces/iCreateOperation.php';

class MysqlDatabase implements iReadOperation, iCreateOperation
{
    // The database connection variable
    private $con;

    // Constructor
    function __construct()
    {
        require_once dirname(__FILE__) . '/MysqlPdoDbConnect.php';
        $db = new MysqlPdoDbConnect();
        $this->con = $db->connect();
    }

    // getAllTodos is returning all the Todos from database
    public function getAllTodos(): array
    {
        $stmt = $this->con->prepare(
            "SELECT td_id, td_done, td_date, td_label, td_comment  FROM Todos;"
        );

        // For eache row, put the column value in the linked variable
        $stmt->bindColumn('td_id', $id, PDO::PARAM_INT);
        $stmt->bindColumn('td_done', $done, PDO::PARAM_INT);
        $stmt->bindColumn('td_date', $date, PDO::PARAM_STR);
        $stmt->bindColumn('td_label', $label, PDO::PARAM_STR);
        $stmt->bindColumn('td_comment', $commentary, PDO::PARAM_STR);
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
            "SELECT td_id, td_done, td_date, td_label, td_comment  FROM Todos WHERE td_id = :id;"
        );

        // Bind the parameters with their sql name == put the value of the parameter in the sql.
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        // For eache row, put the column value in the linked variable
        $stmt->bindColumn('td_done', $done, PDO::PARAM_INT);
        $stmt->bindColumn('td_date', $date, PDO::PARAM_STR);
        $stmt->bindColumn('td_label', $label, PDO::PARAM_STR);
        $stmt->bindColumn('td_comment', $commentary, PDO::PARAM_STR);
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

    // searchTodos is returning an array of matching Todos
    public function searchTodos(array $query): array
    {
        $stmt = $this->con->prepare(
            'SELECT td_id, td_label, td_date, td_comment FROM Todos WHERE td_id != 0' . (is_null($query['equal-to'])
                ? ''
                : ' AND td_date = ' . $query['equal-to']) . (is_null($query['more-than'])
                ? ''
                : ' AND td_date > ' . $query['more-than']) . (is_null($query['less-than'])
                ? ''
                : ' AND td_date < ' . $query['less-than']) . (is_null($query['date-is'])
                ? ''
                : " AND DATEDIFF('" .
                $query['date-is'] .
                "', td_date) = 0") . (is_null($query['before-date'])
                ? ''
                : " AND DATEDIFF('" .
                $query['before-date'] .
                "', td_date) > 0") . (is_null($query['after-date'])
                ? ''
                : " AND DATEDIFF('" .
                $query['after-date'] .
                "', td_date) < 0") . (is_null($query['in-year'])
                ? ''
                : ' AND EXTRACT(YEAR FROM td_date) = ' . $query['in-year'])
        );

        echo "<br>", $query;

        // For eache row, put the column value in the linked variable
        $stmt->bindColumn('td_id', $id, PDO::PARAM_INT);
        $stmt->bindColumn('td_done', $done, PDO::PARAM_INT);
        $stmt->bindColumn('td_date', $date, PDO::PARAM_STR);
        $stmt->bindColumn('td_label', $label, PDO::PARAM_STR);
        $stmt->bindColumn('td_comment', $commentary, PDO::PARAM_STR);
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
            'INSERT INTO Todos(td_label, td_done, td_date, td_comment) VALUES ( :label, :done, :date, :comment)'
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
