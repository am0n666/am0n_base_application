<?php

namespace Amon\Error;

use Amon\Di;
use Amon\DiInterface;
use Amon\Di\Injectable;
use Amon\Routing\Url;
use Amon\Helper\Str;

class Handler extends Injectable 
{
    public function register()
    {
		\error_reporting(E_ALL);
		\ini_set('display_errors', '1');

        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                if (!($errno & error_reporting())) {
                    return;
                }

                $options = [
                    'type'    => $errno,
                    'message' => $errstr,
                    'file'    => $errfile,
                    'line'    => $errline,
                    'isError' => true,
                ];

                $this->handle(
                    new Error($options)
                );
            }
        );

        set_exception_handler(
            function ($e) {
                $options = [
                    'type'        => $e->getCode(),
                    'message'     => $e->getMessage(),
                    'file'        => $e->getFile(),
                    'line'        => $e->getLine(),
                    'isException' => true,
                    'exception'   => $e,
                ];
                $this->handle(
                    new Error($options)
                );
            }
        );

        register_shutdown_function(
            function () {
                if (!is_null($options = error_get_last())) {
					$this->handle(
                        new Error($options)
                    );
                }
            }
        );
    }

    public function handle($error)
    {
        $di = $this->di;

        switch ($error->type()) {
            case E_WARNING:
            case E_NOTICE:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_ALL:
                break;

            case 0:
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
        }
		$router = $di->getRouter();
		$error_type = $this->getErrorType($error->attributes['type']);
		$error->attributes['type'] = $error_type;
		$params['errors'] = (array) $error->attributes;

		if ($error->attributes['isError']) {
			$params['error_title'] = 'Error';
		}
		if ($error->attributes['isException']) {
			$params['error_title'] = 'Uncaught exception';
		}
		$router->callCustomControllerAction('error', 'handler', $params);
		die();
    }

    private function getErrorType($code)
    {
        switch ($code) {
            case 0:
                return 'Uncaught exception';

            case E_ERROR:
                return 'E_ERROR';

            case E_WARNING:
                return 'E_WARNING';

            case E_PARSE:
                return 'E_PARSE';

            case E_NOTICE:
                return 'E_NOTICE';

            case E_CORE_ERROR:
                return 'E_CORE_ERROR';

            case E_CORE_WARNING:
                return 'E_CORE_WARNING';

            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';

            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';

            case E_USER_ERROR:
                return 'E_USER_ERROR';

            case E_USER_WARNING:
                return 'E_USER_WARNING';

            case E_USER_NOTICE:
                return 'E_USER_NOTICE';

            case E_STRICT:
                return 'E_STRICT';

            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';

            case E_DEPRECATED:
                return 'E_DEPRECATED';

            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }

        return $code;
    }
}
