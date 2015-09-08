<?php

namespace Muzikdj\Muzikdj\Controllers;

final class IndexController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
    	echo $this->view->render('Index/index');
    }
}