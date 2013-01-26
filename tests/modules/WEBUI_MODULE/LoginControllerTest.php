<?php
namespace WebUiTest;

require_once 'Phake.php';
require_once "PHPUnit/Autoload.php";
require_once "tests/helpers/BudabotTestCase.php";
require_once "modules/WEBUI_MODULE/LoginController.php";
require_once "core/HTTPAPI_MODULE/HttpApiController.class.php";
require_once "core/PREFERENCES/Preferences.class.php";

interface MockRequest {
}

interface MockResponse {
	function writeHead();
	function end();
}

class LoginControllerTest extends \BudabotTestCase {

	private $ctrl;

	function setUp() {
		$this->ctrl = new \WebUi\LoginController();
		$this->ctrl->moduleName = 'WEBUI_MODULE';
		$this->httpApi = $this->injectMock($this->ctrl, 'httpapi', 'HttpApiController');
		$this->preferences = $this->injectMock($this->ctrl, 'preferences', 'Preferences');
	}

	function testIsAutoInstanced() {
		$this->assertTrue($this->isAutoInstanced($this->ctrl));
	}

	function testHasSetupHandler() {
		$this->assertTrue($this->hasSetupHandler($this->ctrl));
	}

	function testHasHttpApiInject() {
		$this->assertTrue($this->hasInjection($this->ctrl, 'httpapi'));
	}

	function testHasPreferencesInject() {
		$this->assertTrue($this->hasInjection($this->ctrl, 'preferences'));
	}

	function testSetupHandlerRegistersLoginResource() {
		$this->callSetupHandler($this->ctrl);
		\Phake::verify($this->httpApi)->registerHandler("|^/WEBUI_MODULE/login|i", $this->isCallable());
	}

	function testLoginHandlerWritesLoginHtmlResource() {
		list($request, $response) = $this->getHandlerMocks();
		$this->callHandlerCallback("|^/WEBUI_MODULE/login|i", $request, $response);

		\Phake::verify($response)->writeHead(200);
		\Phake::verify($response)->end(file_get_contents("modules/WEBUI_MODULE/resources/login.html"));
	}

	private function getHandlerMocks() {
		return array(
			\Phake::mock('WebUiTest\MockRequest'),
			\Phake::mock('WebUiTest\MockResponse')
		);
	}

	private function callHandlerCallback($path, $request, $response, $data = '') {
		$this->callSetupHandler($this->ctrl);
		$callback = null;
		\Phake::verify($this->httpApi)->registerHandler($path, \Phake::capture($callback));
		call_user_func($callback, $request, $response, $data);
	}

	function testSetupHandlerRegistersLoginJsResource() {
		$this->callSetupHandler($this->ctrl);
		\Phake::verify($this->httpApi)->registerHandler("|^/WEBUI_MODULE/js/login.js|i", $this->isCallable());
	}

	function testLoginJsHandlerWritesLoginJsResource() {
		list($request, $response) = $this->getHandlerMocks();
		$this->callHandlerCallback("|^/WEBUI_MODULE/js/login.js|i", $request, $response);

		\Phake::verify($response)->writeHead(200);
		\Phake::verify($response)->end(file_get_contents("modules/WEBUI_MODULE/resources/js/login.js"));
	}

	function testSetupHandlerRegistersCheckLoginResource() {
		$this->callSetupHandler($this->ctrl);
		\Phake::verify($this->httpApi)->registerHandler("|^/WEBUI_MODULE/check_login|i", $this->isCallable());
	}

	function testCheckLoginHandlerWritesSuccessOnValidCredentials() {
		$this->setApiPassword('fooman', 'foopass');
		list($request, $response) = $this->getHandlerMocks();
		$this->callHandlerCallback("|^/WEBUI_MODULE/check_login|i", $request, $response, http_build_query(array(
			'username' => 'fooman',
			'password' => 'foopass'
		)));

		\Phake::verify($response)->writeHead(200);
		\Phake::verify($response)->end('1');
	}

	private function setApiPassword($username, $password) {
		\Phake::when($this->preferences)->get($username, 'apipassword')->thenReturn($password);
	}

	function testCheckLoginHandlerDeniesAccessOnWrongPassword() {
		$this->setApiPassword('fooman', 'wrong');
		list($request, $response) = $this->getHandlerMocks();
		$this->callHandlerCallback("|^/WEBUI_MODULE/check_login|i", $request, $response, http_build_query(array(
			'username' => 'fooman',
			'password' => 'foopass'
		)));

		\Phake::verify($response)->end('0');
	}

	function testCheckLoginHandlerDeniesAccessOnEmptyPassword() {
		$this->setApiPassword('fooman', '');
		list($request, $response) = $this->getHandlerMocks();
		$this->callHandlerCallback("|^/WEBUI_MODULE/check_login|i", $request, $response, http_build_query(array(
			'username' => 'fooman',
			'password' => ''
		)));

		\Phake::verify($response)->end('0');
	}
}