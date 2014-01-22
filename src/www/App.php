<?php

namespace Tmont\PHPMP;

use Tmont\Facilius\WebApplication;
use Tmont\Facilius\Request;
use Tmont\Facilius\DefaultViewLocator;
use Tmont\Facilius\ErrorModel;
use Tmont\Facilius\View;
use Tmont\Facilius\UnknownActionException;
use Tmont\Facilius\ControllerConstructionException;
use Tmont\Facilius\RenderingContext;
use Exception;

class App extends WebApplication {

	public function __construct() {
		parent::__construct(__DIR__ . '/views');
		$this->debugEnabled = true;
	}

	public final static function start() {
		$app = new self();
		$app->run(Request::create());
	}

	protected function onError(Exception $e) {
		$response = $this->getResponse();
		$response->clear();

		if ($e instanceof UnknownActionException || $e instanceof ControllerConstructionException) {
			$response->setStatus(404);
			$viewPath = '/shared/404.php';
		} else {
			$response->setStatus(500);
			$viewPath = '/shared/500.php';
		}

		$view = new View($this->getViewPath() . $viewPath);
		$context = new RenderingContext(
			$view,
			$this->getRequest(),
			$this->getRoutes(),
			null,
			new ErrorModel($e, $this->debugEnabled)
		);
		$view->render($context);
	}

	protected function onStart() {
		$this->registerRoute('', array('controller' => 'Home', 'action' => 'index'));
	}

	/**
	 * @param string $name
	 * @return \Tmont\Facilius\Controller
	 */
	protected function createController($name) {
		$class = __NAMESPACE__ . '\Controllers\\' . $name . 'Controller';
		if (!class_exists($class) || !in_array('Tmont\Facilius\Controller', class_parents($class))) {
			return null;
		}

		$controller = new $class();
		$controller->setViewLocator(new DefaultViewLocator(__DIR__));
		return $controller;
	}
}
