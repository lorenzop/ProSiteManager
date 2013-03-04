<?php namespace psm\html;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';} else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
\ob_start();
class Engine {

	// main engine instance
	private static $engine = NULL;
	private static $hasDisplayed = FALSE;

	// main html file
	private $htmlMain;

	// tag parsers
	private $tagString;
	private $tagPaths;


	/**
	 * Gets the main template engine instance, creating a new one if needed.
	 *
	 * @return html_Engine
	 */
	public static function getEngine() {
		if(self::$engine == NULL)
			self::$engine = new self();
		return self::$engine;
	}


	public function __construct(html_File &$htmlMain=NULL) {
		// load main html file
		if($htmlMain == NULL)
//TODO: add theme
			$this->htmlMain = \psm\html\tplFile::LoadFile('default', 'main');
		else
			$this->htmlMain = $htmlMain;
		// validate html_File class type
		\psm\Utils\Utils::Validate('psm\html\tplFile_Main', $this->htmlMain);
		// tag parsers
		$this->tagString = new Tag_String();
$paths = array(
'{path=static}'=>'portal/static/',
'{path=theme}'=>'wa/html/default/',
);
		$this->tagPaths = new Tag_String(
$paths
//			Portal::getPortal()->getPathsArray()
		);
//		self::$globalTags = new listenerGroup();
//		// global tags
//		self::$globalTags->registerListener(new listenerGlobalTags());
//		// block arrays
//		$this->blocksHeader = new html_BlockArray('portal - appended header');
//		$this->blocksCss    = new html_BlockArray('portal - appended css');
//			$this->blocksCss->setPrepend ('<style type="text/css">');
//			$this->blocksCss->setPostpend('</style>');
//		$this->blocksJs    = new html_BlockArray('portal - appended javascript');
//			$this->blocksJs->setPrepend ('<script type="text/javascript" language="javascript">');
//			$this->blocksJs->setPostpend('</script>');
//		$this->blocksPage   = new html_BlockArray('portal - page contents');
//		$this->blocksFooter = new html_BlockArray('portal - footer contents');
	}


	// build page
	public function Display() {
		// run only once
		if(self::_hasDisplayed(TRUE))
			return;
		// end output buffer
		$this->addToPage(
			\ob_get_clean()
		);
		/* build header */
		// split by {header content} tag
		$splitHeader = new SplitBlock('{header content}', $this->htmlMain->getBlock('head'));
		// open header block
		$this->_echo(
			$splitHeader->getPart(0)
		);
		// build header
		$this->_echo(
			$this->htmlMain->getBlock('header')
		);
		// build inline css
		$this->_echo(
			'<style type="text/css" title="currentStyle">'.NEWLINE.
			$this->htmlMain->getBlock('css').
			'</style>'
		);
		// build inline javascript
		$this->_echo(
			$this->htmlMain->getBlock('js')
		);
		// close header block
		$this->_echo(
			$splitHeader->getPart(1)
		);
		unset($splitHeader, $this->blocksHeader,
			$this->blocksCss, $this->blocksJs);

		/* build page */
		// split by {page content} tag
		$splitPage = new SplitBlock('{page content}', $this->htmlMain->getBlock('body'));
		// open body block
		$this->_echo(
			$splitPage->getPart(0)
		);
		// build page content
		$this->_echo(
			$this->htmlMain->getBlock('page')
		);
		// close body block
		$this->_echo(
			$splitPage->getPart(1)
		);
		unset($splitPage, $this->blocksPage);

		/* build footer */
		// split by {footer content} tag
		$splitFooter = new SplitBlock('{footer content}', $this->htmlMain->getBlock('foot'));
		// open footer block
		$this->_echo(
			$splitFooter->getPart(0)
		);
		$this->_echo(
			$this->htmlMain->getBlock('footer')
		);
		// close footer block
		$this->_echo(
			$splitFooter->getPart(1)
		);
		unset($splitFooter, $this->blocksFooter);

	}


	private function _echo($data) {
		// string tags
		$args = array('data' => &$data);
		$this->tagString->trigger($args);
		// path tags
		$args = array('data' => &$data);
		$this->tagPaths->trigger($args);
		echo $data;
		ob_flush();
	}


	/* add to block arrays */
	// add to header
	public static function addHeader($data, $top=FALSE) {
		self::getEngine()->addToHeader($data, $top);
	}
	public function addToHeader($data, $top=FALSE) {
		$this->htmlMain->addBlock('header', $data, $top);
//		$this->blocksHeader->add(self::renderObject($data), $top);
	}


	// add to css
	public static function addCSS($data, $top=FALSE) {
		self::getEngine()->addToCSS($data, $top);
	}
	public function addToCSS($data, $top=FALSE) {
		$this->htmlMain->addBlock('css', $data, $top);
//		$this->blocksCss->add(self::renderObject($data), $top);
	}


	// add to page
	public static function addPage($data, $top=FALSE) {
		self::getEngine()->addToPage($data, $top);
	}
	public function addToPage($data, $top=FALSE) {
		$this->htmlMain->addBlock('page', $data, $top);
//		$this->blocksPage->add(self::renderObject($data), $top);
	}


	// add to footer
	public static function addFooter($data, $top=FALSE) {
		self::getEngine()->addToFooter($data, $top);
	}
	public function addToFooter($data, $top=FALSE) {
		$this->htmlMain->addBlock('footer', $data, $top);
//		$this->blocksFooter->add(self::renderObject($data), $top);
	}


	// render class objects to html
	public static function renderObject(&$data) {
		if($data == NULL)
			return NULL;
		// page class
		if($data instanceof \psm\Portal\Page)
			return $data->Render();
		// default to string
		$data = (string) $data;
		// file:
		if(\psm\Utils\Utils_Strings::startsWith($data, 'file:', TRUE))
			return substr(5, $data);
		// string
		return $data;
	}


//	// call global tag parsers
//	public static function renderGlobalTags(&$data) {
//		$args = array();
//		$args[0] = &$data;
//		self::$globalTags->trigger($args);
//	}


	public static function hasDisplayed() {
		return self::_hasDisplayed();
	}
	private static function _hasDisplayed($hasDisplayed=FALSE) {
		$hasBefore = self::$hasDisplayed;
		if($hasDisplayed === TRUE)
			self::$hasDisplayed = TRUE;
		return $hasBefore;
	}


}
?>