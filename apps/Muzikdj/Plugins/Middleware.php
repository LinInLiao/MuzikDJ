<?php

namespace Muzikdj\Api\Plugins;

use Muzikdj\Models\Users;
use \Phalcon\Mvc\Micro\MiddlewareInterface;
use \Firebase\JWT\JWT;

class Middleware implements MiddlewareInterface {

    protected $whitelist = [
        '/api/login', '/api/logout', '/api/auth/check'
    ];

    public function setHeader(\Phalcon\Mvc\Micro $app) {
        $config = $app->di->getShared('config');
        $whitelist = $config->allow_origin[ENVIRONMENT]->toArray();
        $request_hostname =  '';
        $origin = $app->request->getHeader('Origin');
        if (!empty($origin)) {
            $request_hostname = $origin;
        }

        if (ENVIRONMENT === 'development') {
            $whitelist = '*';
        } else {
            if (!is_array($whitelist)) {
                $whitelist = 'https://'.$app->request->getServerName();
            } else {
                if (count($whitelist) === 0) {
                    $whitelist = 'https://'.$app->request->getServerName();
                } else {
                    if (in_array($request_hostname, $whitelist)) {
                        $whitelist = $request_hostname;
                    } else {
                        $whitelist = 'https://'.$app->request->getServerName();
                    }
                }
            }
        }
        $datetime = gmdate("D, d M Y H:i:s").' GMT';
        // $app->response->setHeader('Pragma', 'no-cache');
        $app->response->setHeader(
            'Cache-Control',
            'no-cache, private, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0, max-stale=0'
        );
        // $app->response->setHeader('Last-Modified', $datetime);
        $app->response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $app->response->setHeader('Access-Control-Allow-Origin', $whitelist);
        $app->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, OPTIONS');
        $app->response->setHeader(
            'Access-Control-Allow-Headers',
            'X-Requested-With, X-HTTP-Method-Override, Content-Type, Cache-Control, Accept, Origin, Accept, Key, Authorization'
        );
        $app->response->setContentType('application/json', 'UTF-8');
        $app->response->setExpires(new \DateTime());
        $app->response->setEtag(md5($datetime));

        return $app;
    }

    public function call(\Phalcon\Mvc\Micro $app) {
        if ($app->request->isOptions()) {
            return [ 'status' => 204, 'messages' => 'No Content.' ];
        }

        $action = $app['router']->getRewriteUri();
        if (true === in_array($action, $this->whitelist, true)) {
            return true;
        }

        if ('production' === ENVIRONMENT) {
            if ($app->request->isAjax()) {
                $origin = $app->request->getHeader('Origin');
                if (empty($origin)) {
                    return [ 'status' => 400, 'messages' => 'Origin does not exists.' ];
                }
            } else {
                $referer = $app->request->getHeader('Referer');
                if (empty($referer)) {
                    return [ 'status' => 400, 'messages' => 'Referer does not exists.' ];
                }
            }
        }

        $params = [];
        $auth = false;

        $authorization = $app->request->getHeader('Authorization');
        if (!is_null($authorization)) {
            if (strpos(trim($authorization), 'Bearer') !== false) {
                $authorization = str_replace('Bearer ', '', $authorization);
            }
        }
        if (is_null($authorization) || empty($authorization)) {
            if ($app->request->isGet()) {
                $authorization = $app->request->get('token');
            } else {
                $params = $app->request->getJsonRawBody(true);
                if (!is_null($params) && isset($params['token'])) {
                    $authorization = $params['token'];
                } else {
                    $token = $app->request->getPost('token');
                    if (!is_null($token)) {
                        $authorization = $token;
                    }
                }
            }
        }
        if (!empty($authorization)) {
            if (strpos(trim($authorization), 'Bearer') !== false) {
                $authorization = str_replace('Bearer ', '', $authorization);
            }
        }
        $authorization = trim($authorization);
        if (!empty($authorization)
            && !is_null($authorization)
            && $authorization !== 'Bearer'
        ) {
            try {
                $token = JWT::decode($authorization, $app->config->cookie->crypt, ['HS256']);
                if (!is_null($token)) {
                    if (time() < $token->exp) {
                        $auth = (array) $token->sub;
                        $user = Users::findFirst(array(
                            'conditions' => 'id = :id:',
                            'bind' => array(
                                'id' => $auth['id'],
                            ),
                            'bindTypes' => array(
                                'id' => \Phalcon\Db\Column::BIND_PARAM_STR,
                            ),
                        ));

                        if ($user) {
                            $user = array(
                                'id' => $user->id,
                                'email' => $user->email,
                                'name' => $user->name,
                                'token' => $user->token,
                            );
                            $app->session->set('USER', (object) $user);
                            unset($token);
                            unset($user);
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return true;
    }
}

