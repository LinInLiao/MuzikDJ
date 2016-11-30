<?php

namespace Muzikdj\Api\Plugins;

use \Phalcon\Mvc\Micro\MiddlewareInterface;
use \Firebase\JWT\JWT;

use Muzikdj\Api\Plugins\Middleware;

final class ResponseMiddleware extends Middleware implements MiddlewareInterface {

    public function call(\Phalcon\Mvc\Micro $app) {
        try {
            $returned = $app->getReturnedValue();

            $bad_request = false;
            if (!is_array($returned)) {
                $bad_request = true;
            } else {
                if (count($returned) === 0) {
                    $bad_request = true;
                }
            }

            if ($bad_request) {
                $app->response->setStatusCode(400)->sendHeaders();
                $app->response->setJsonContent([ 'status' => 'err', 'messages' => 'Bad Request.' ]);
            } else {
                $data = $returned[0];

                if (isset($data['force_redirect'])
                    && true === $data['force_redirect']
                    && !empty($data['redirect_uri'])
                ) {
                    $app->response->redirect($data['redirect_uri'], true, 302)->sendHeaders();
                    return false;
                }

                $status = 200;
                if (isset($returned[1])) {
                    if (is_numeric($returned[1])) {
                        $status = (int) $returned[1];
                    }
                }

                $app = $this->setHeader($app);

                if (isset($data['authenticate'])
                    && !empty($data['authenticate'])) {
                    $data['token'] = JWT::encode([
                        'sub' => $data['authenticate'],
                        'iat' => time(),
                        'exp' => time() + $this->config->cookie->lifetime
                    ], $this->config->cookie->crypt);
                    unset($data['authenticate']);
                }

                $app->response->setStatusCode($status)->sendHeaders();
                $app->response->setJsonContent($data, JSON_UNESCAPED_UNICODE);
            }

            if (getenv('MUZIKDJ_TESTING') !== 'testing') {
                $app->response->send();
            }
        } catch (\Exception $e) {
            $app->response->setStatusCode(500)->sendHeaders();
            $app->response->setJsonContent([
                'status' => 'err',
                'messages' => $e->getMessage(),
                'results' => $e->getTrace()
            ], JSON_UNESCAPED_UNICODE);
            if (getenv('MUZIKDJ_TESTING') !== 'testing') {
                $app->response->send();
            }
        }
    }
}