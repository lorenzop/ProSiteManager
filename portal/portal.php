<?php namespace psm;
//defines:
//define('PORTAL_DONT_USE_ERROR_HANDLER', TRUE);


// constants
define('DIR_SEP', DIRECTORY_SEPARATOR);
define('LN', "\n"); // new line

// class loader
include('ClassLoader.php');
ClassLoader::registerClassPath('psm', __DIR__.DIR_SEP.'classes');
define('PORTAL_INDEX_FILE', TRUE);

// debug mode
if(file_exists(__DIR__.'/php_error.php')) {
	// log display
	error_reporting(E_ALL | E_STRICT);
	//ini_set('display_errors', 'On');
	//ini_set('html_errors', 'On');
	if(!(defined('PORTAL_DONT_USE_ERROR_HANDLER') && PORTAL_DONT_USE_ERROR_HANDLER===TRUE)) {
		// log file
		ini_set('log_errors', 'On');
		ini_set('error_log', 'php_errors.log');
		// error handler
		require('php_error.php');
		\php_error\reportErrors(array(
			'catch_ajax_errors'      => TRUE,
			'catch_supressed_errors' => FALSE,
			'catch_class_not_found'  => FALSE,
			'snippet_num_lines'      => 11,
			'application_root'       => __DIR__,
			'background_text'        => 'PSM',
		));
	}
}


class portal {

	// portal core
	private static $portal = NULL;
	private $portalName;

	// paths
	private $root;

	// template engine
	private $engine = NULL;

	// page
	private $page = NULL;
	private $defaultPage = 'home';
	// action
	private $action = NULL;


	// new portal
	public function __construct($portalName) {
		// portal instance
		if(self::$portal != NULL) {
			echo '<p>Portal already loaded!</p>';
			exit();
		}
		self::$portal = $this;
		// portal name
		if(empty($portalName)) {
			echo '<p>portalName not set!</p>';
			exit();
		}
		$this->portalName = $portalName;
		// paths
		$this->root = realpath(__DIR__.'/../');
		// no page caching
		Utils::NoPageCache();
		// set timezone
		try {
			if(!@date_default_timezone_get())
				@date_default_timezone_set('America/New_York');
		} catch(\Exception $ignore) {}
		// load portal index
		$portalIndex = $this->root.'/'.$this->portalName.'/index.php';
		include($portalIndex);
	}
	public static function getPortal() {
		return self::$portal;
	}


	/**
	 *
	 *
	 */
	public function genericRender() {
		// load page
		$page = Page::LoadPage($this->getPage());
		// failed to load
		if($page == NULL) {
echo '<p>PAGE IS NULL</p>';
			return;
		}
		// get engine
		$engine = $this->getEngine();
		if($engine == NULL) {
echo '<p>ENGINE IS NULL</p>';
			return;
		}
		$engine->addToPage($page);
		$engine->Build();
	}


	/**
	 * Gets the main template engine instance, creating a new one if needed.
	 *
	 * @return html_engine
	 */
	public function getEngine() {
		if($this->engine == NULL)
			$this->engine = new html_engine();
		return $this->engine;
	}


	// page
	public function getPage() {
		// already set
		if($this->page !== NULL)
			return $this->page;
		// get page
		$this->page = variables::getVar('page', 'str');
		// default page
		if(empty($this->page))
			$this->page = $this->defaultPage;
		$this->page = Utils_File::SanFilename($this->page);
		return $this->page;
	}
	// default page
	public function setDefaultPage($defaultPage) {
		$this->defaultPage = Utils_File::SanFilename($defaultPage);
	}


	// action
	public function getAction() {
		// already set
		if($this->action !== NULL)
			return $this->action;
		// get action
		$this->action = variables::getVar('action', 'str');
		$this->action = SanFilename($this->action);
		return $this->action;
	}


}
?>