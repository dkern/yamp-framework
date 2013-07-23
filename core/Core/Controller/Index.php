<?php
class Yamp_Core_Controller_Index extends Yamp_Core_Controller_Abstract
{
	public function prepareLayout()
	{
		parent::prepareLayout();
		
		$head = $this->getLayout()->getBlock("head");
		
		// title
		$head->setTitle("YAMP Framework");
		
		// meta
		$head->addMeta("text/html; charset=utf-8", array("http-equiv" => "Content-Type"));
		$head->addMeta($head->getContentLanguage(), array("http-equiv" => "Content-Language"));
		$head->addMeta($head->getContentLanguage(), array("name" => "language"));
		$head->addMeta("YAMP is a fast, extensible, multi-purpose php framework.", array("name" => "description"));
		$head->addMeta("yamp,framework,php,development,extensible,fast", array("name" => "keywords"));
		$head->addMeta("INDEX,FOLLOW", array("name" => "robots"));
		$head->addMeta("YAMP Framework", array("name" => "generator"));
		$head->addMeta("eisbehr.de", array("name" => "author"));
		$head->addMeta("eisbehr.de", array("name" => "publisher"));
		$head->addMeta("eisbehr.de", array("name" => "company"));
		$head->addMeta("eisbehr.de", array("name" => "copyright"));
		$head->addMeta("development", array("name" => "page-topic"));
		$head->addMeta("2", array("name" => "revisit-after"));
		$head->addMeta($head->getTitle(), array("property" => "og:title"));
		$head->addMeta("YAMP is a fast, extensible, multi-purpose php framework.", array("property" => "og:description"));
		$head->addMeta($head->getTitle(), array("property" => "og:site_name"));
		$head->addMeta($this->getBaseUrl(), array("property" => "og:url"));
		$head->addMeta($this->getImageUrl("og_image.png"), array("property" => "og:image"));
		
		// css
		$head->addCss("css/styles.css");
		
		// links
		$head->addLink("icon", $this->getImageUrl("favicon.ico"), array("type" => "image/x-icon"));
		$head->addLink("shortcut icon", $this->getImageUrl("favicon.ico"), array("type" => "image/x-icon"));	
	}

	/**
	 * default framework index page
	 * @return void
	 */
	public function indexAction()
	{
		// create raw content
		$html  = "      <h1>You're now running YAMP Framework!</h1>\n";
		$html .= "      <h2>Thank you very mutch for giving YAMP a try.</h2>\n";
		$html .= "      <h3>Getting Started</h3>\n";
		$html .= "      <p>\n";
		$html .= "        If you're new to YAMP you can install the <a href=\"http://github.com/eisbehr-/yamp-framework\">example module</a> to get into the code and see the framework working.\n";
		$html .= "        For further details take a look to the <a href=\"http://github.com/eisbehr-/yamp-framework/wiki\">official documentation</a>.\n";
		$html .= "      </p>\n";
		$html .= "      <h3>Links</h3>\n";
		$html .= "      <ul>\n";
		$html .= "        <li><a href=\"http://github.com/eisbehr-/yamp-framework\">YAMP Homepage</a></li>\n";
		$html .= "        <li><a href=\"http://github.com/eisbehr-/yamp-framework/wiki\">YAMP Documentation</a></li>\n";
		$html .= "        <li><a href=\"http://github.com/eisbehr-/yamp-framework\">GitHub Repo</a></li>\n";
		$html .= "        <li><a href=\"http://github.com/eisbehr-/yamp-framework/issues\">Report a Bug</a></li>\n";
		$html .= "      </ul>\n";
		
		// add content
		$this->getLayout()->getBlock("content")->addRawContent($html);
		
		// render layout and flush to browser
		$this->getLayout()->render();
	}
	
	public function emptyAction()
	{
		
	}
}
