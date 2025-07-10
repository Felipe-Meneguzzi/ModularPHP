<?php

// Inclui o Composer e o nosso bootstrap de telemetria
require '/app/vendor/autoload.php';
require '/app/src/opentelemetry_bootstrap.php';

use App\Core\AppDIContainer;
use App\Core\Exception\AppException;
use App\Core\Exception\AppStackException;
use App\Core\Http\DefaultResponse;
use App\Core\Http\HTTPRequest;
use App\Core\Exception\PaginationStackException;
use App\Router;
use Dotenv\Dotenv;
use OpenTelemetry\API\Globals;
use OpenTelemetry\SemConv\TraceAttributes;

try {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    $dotenv = Dotenv::createImmutable('/app');
    $dotenv->load();

    $request = new HTTPRequest();
    $response = new DefaultResponse(statusCode: 500, message: 'Internal Server Error');

    $container = AppDIContainer::build();


    /********************************************************************************************************************/
    /*****************************************************CORS***********************************************************/

    header_remove("Access-Control-Allow-Origin");

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed_origins = [
        'https://modularphp.com',
        'https://dev.modularphp.com',
        'https://api.modularphp.com',
        'https://dashboard.modularphp.com'
    ];

    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Vary: Origin");
    }

    if ($_ENV['ENVIRONMENT'] === 'dev') {
        header_remove("Access-Control-Allow-Origin");
        header("Access-Control-Allow-Origin: *");
    }

    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    /********************************************************************************************************************/
    /********************************************************************************************************************/



    /********************************************************************************************************************/
    /*************************************************TELEMETRIA*********************************************************/

    if($_ENV['OBSERVABILITY'] === 'true'){
        initialize_opentelemetry();

        $tracer = Globals::tracerProvider()->getTracer('io.opentelemetry.contrib.php', '1.0.0');

        $rootSpan = $tracer->spanBuilder(sprintf('%s %s', $request->method, $request->uri))
            ->setAttribute('http.request.method', $request->method)
            ->setAttribute('url.full', $request->uri)
            ->setAttribute('url.path', $request->uri)
            ->setAttribute('url.query', $request->uri)
            ->setAttribute('user_agent.original', $request->userAgent)
            ->setAttribute('client.address', $request->requestIP)
            ->startSpan();

        $scope = $rootSpan->activate();
    }

    /********************************************************************************************************************/
    /********************************************************************************************************************/


    $router = new Router($request, $container);
    $routeDefiner = require '/app/src/Api.php';
    $routeDefiner($router, $container);

    $response = $router->dispatch();

    if(isset($rootSpan))
        $rootSpan->setStatus('Ok', $response->body['message'] ?? '');

}  catch (UnexpectedValueException $jwtException) {

    $response = new DefaultResponse(statusCode: 401, message: 'JWTToken Authorization ERROR', errors: [$jwtException->getMessage()]);
    if(isset($rootSpan))
        $rootSpan->setStatus('Ok', '401- JWTToken Authorization ERROR: ' . $jwtException->getMessage() ?? '');

}  catch (PaginationStackException $paginationException) {

    $response = new DefaultResponse(statusCode: 400, message: 'Pagination parameter ERROR', errors: [$paginationException->getMessage()]);
    if(isset($rootSpan))
        $rootSpan->setStatus('Ok', '400- Pagination parameter ERROR: ' . $paginationException->getMessage() ?? '');

} catch (AppStackException $appStackException) {

    $response = new DefaultResponse(statusCode: $appStackException->getCode(), message: 'ERROR', errors: $appStackException->getErrors());
    if(isset($rootSpan))
        $rootSpan->setStatus('Ok', $appStackException->getCode() . '- ' . $appStackException->getMessage() ?? '');

} catch (AppException $appException) {

    $response = new DefaultResponse(statusCode: $appException->getCode(), message: 'ERROR', errors: [$appException->getMessage()]);
    if(isset($rootSpan))
        $rootSpan->setStatus('Ok', $appException->getCode() . '- ' . $appException->getMessage() ?? '');

} catch (\Throwable $unhandledException) {

    $response = new DefaultResponse(statusCode: 500, message: 'ERROR not expected, contact support', errors: ['ERROR' => $unhandledException->getMessage()]);
    if(isset($rootSpan)) {
        $rootSpan->recordException($unhandledException, [TraceAttributes::EXCEPTION_ESCAPED => true]);
        $rootSpan->setStatus('Error', '500- ' . $unhandledException->getMessage() ?? 'Internal Server Error with no message');
    }

} finally {

    if(isset($rootSpan)) {
        $rootSpan->setStatus($response->getStatusCode());
        $rootSpan->setAttribute('http.status_code', $response->getStatusCode());
    }

    $response->sendResponse();

    if(isset($rootSpan) && isset($scope)) {
        $scope->detach();
        $rootSpan->end();
    }
}