<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/DbOperations.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 * @format
 */
$app = AppFactory::create();
$app->setBasePath('/Todolist_rest/public');

// Add Routing Middleware
$app->addRoutingMiddleware();

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.
 
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

/**
 * Define app routes
 *
 * /Todos return alltodos
 * /Todos/detail/{id} return a single Todo detail
 * /search return a set of matchingtodos
 * /create insert a Todo with parameters
 * / return a default page
 */
$app->get('/Todos', function (Request $request, Response $response, $args) {
    $db = new MysqlDatabase();
    $Todos = $db->getAllTodos();

    $response_data = array(
        'error' => false,
        'endpoint' => $request->getUri()->getPath(),
        'data' => $Todos
    );

    $response->getBody()->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/Todos/detail/{id}', function (Request $request, Response $response, $args) {
    // récupère valeur de {id}
    $id = $args['id'];
    $regex = '/[^0-9]/m';
    // Return 403 bad query if the provided id is not a number
    if (preg_match($regex, $id) !== 0) {
        $response_data = array(
            'error' => true,
            'error-message' => 'Bad query: ' . $id . ' is not a valid number.',
            'endpoint' => $request->getUri()->getPath(),
            'data' => null
        );

        $response->getBody()->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(400);
    }

    $db = new MysqlDatabase();
    $Todo = $db->getDetailById($id);

    $response_data = array(
        'error' => false,
        'endpoint' => $request->getUri()->getPath(),
        'data' => $Todo
    );

    $response->getBody()->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/search', function (Request $request, Response $response, $args) {

    $query = formatGetArgs([
        'equal-to' => null,
        'more-than' => null,
        'less-than' => null,
        'date-is' => null,
        'before-date' => null,
        'after-date' => null,
        'in-year' => null
    ]);
    // if before-date == 'now' compare with today
    if ($query['before-date'] !== null) {
        $query['before-date'] = (strcasecmp('now', $query['before-date']) === 0)        ? date('Y-m-d')        : $query['before-date'];
    }

    $db = new MysqlDatabase();
    $results = $db->searchTodos($query);

    $response_data = array(
        'error' => false,
        'endpoint' => $request->getUri()->getPath(),
        'query' => $query,
        'data' => $results
    );

    $response->getBody()->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/create', function (Request $request, Response $response, $args) {

    $values = formatpostargs([
        'label' => 'new', 'done' => 0,
        'date' => date('Y-m-d'),
        'comment' => 'new ' . date('Y-m-d')
    ]);

    $db = new MysqlDatabase();
    $result = $db->createTodo($values);

    $response->getBody()->write(json_encode($result));

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(
        json_encode(array(
            'message' => 'You are not at a valid endpoint',
            'endpoints' => array(
                '/Todos' => 'List alltodos',
                '/create' => 'create one Todo',
                '/Todos/detail/{id}' =>
                'Get the detail of an Todo by its id',
                '/search' => 'Search for an Todo or a set oftodos'
            )
        ))
    );
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(403);
});

// Run app
$app->run();

// return a Key=>Value array containing Names => (default)Values from $_POST
// $argsArray must contains argumentName => defaultValue (mandatory, could be '')


/**
 * fill empy values from $_POST with customized default values
 * 
 * @var mixed[] $argsArray array containing KeyValues [argumentName => defaultValue] (defaultValue is mandatory, but could be empty)  
 * @return mixed[]  a Key=>Value array containing Names => (default)Values from $_POST
 * @example formatpostargs(['label' => 'new']) // return label value or 'new' if empty
 * 
 */
function formatpostargs($argsArray)
{
    $result = array();
    foreach ($argsArray as $key => $value) {
        // si valeur: valeur, sinon défaut
        $result[$key] = array_key_exists($key, $_POST) ? $_POST[$key] : $value;
    }
    return $result;
}

/**
 * fill empy values from $_GET with customized default values
 * 
 * @var mixed[] $argsArray array containing KeyValues [argumentName => defaultValue] (defaultValue is mandatory, but could be empty)  
 * @return mixed[]  a Key=>Value array containing Names => (default)Values from $_GET
 * @example formatpostargs(['label' => 'new']) // return label value or 'new' if empty
 * 
 */
function formatGetArgs($argsArray)
{
    $result = array();
    foreach ($argsArray as $key => $value) {
        // si valeur: valeur, sinon défaut
        $result[$key] = array_key_exists($key, $_GET) ? $_GET[$key] : $value;
    }
    return $result;
}
