<?php
class Yamp_Core_Controller_Error extends Yamp_Core_Controller_Abstract
{
	/**
	 * http error codes
	 * @var array
	 */
	private $errors = array();
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * construct
	 */
	public function _construct()
	{
		$this->errors["400"] = $this->t("Bad Request");
		$this->errors["401"] = $this->t("Unauthorized");
		$this->errors["402"] = $this->t("Payment Required");
		$this->errors["403"] = $this->t("Forbidden");
		$this->errors["404"] = $this->t("Not Found");
		$this->errors["405"] = $this->t("Method Not Allowed");
		$this->errors["406"] = $this->t("Not Acceptable");
		$this->errors["407"] = $this->t("Proxy Authentication Required");
		$this->errors["408"] = $this->t("Request Time-out");
		$this->errors["409"] = $this->t("Conflict");
		$this->errors["410"] = $this->t("Gone");
		$this->errors["411"] = $this->t("Length Required");
		$this->errors["412"] = $this->t("Precondition Failed");
		$this->errors["413"] = $this->t("Request Entity Too Large");
		$this->errors["414"] = $this->t("Request-URL Too Long");
		$this->errors["415"] = $this->t("Unsupported Media Type");
		$this->errors["416"] = $this->t("Requested Range Not Satisfiable");
		$this->errors["417"] = $this->t("Expectation Failed");
		$this->errors["420"] = $this->t("Policy Not Fulfilled");
		$this->errors["421"] = $this->t("There Are Too Many Connections From Your Internet Address");
		$this->errors["422"] = $this->t("Unprocessable Entity");
		$this->errors["423"] = $this->t("Locked");
		$this->errors["424"] = $this->t("Failed Dependency");
		$this->errors["425"] = $this->t("Unordered Collection");
		$this->errors["426"] = $this->t("Upgrade Required");
		$this->errors["429"] = $this->t("Too Many Requests");
		$this->errors["444"] = $this->t("No Response");
		$this->errors["451"] = $this->t("Unavailable For Legal Reasons");
		$this->errors["500"] = $this->t("Internal Server Error");
		$this->errors["501"] = $this->t("Not Implemented");
		$this->errors["502"] = $this->t("Bad Gateway");
		$this->errors["503"] = $this->t("Service Unavailable");
		$this->errors["504"] = $this->t("Gateway Time-out");
		$this->errors["505"] = $this->t("HTTP Version Not Supported");
		$this->errors["506"] = $this->t("Variant Also Negotiates");
		$this->errors["507"] = $this->t("Insufficient Storage");
		$this->errors["509"] = $this->t("Bandwidth Limit Exceeded");
		$this->errors["510"] = $this->t("Not Extended");
	}
	
	/**
	 * prepare layout
	 * @return void
	 */
	public function prepareLayout()
	{
		parent::prepareLayout();
		
		$this->getLayout()->setTemplate("http_error.phtml");
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
	 * default error page
	 * @return void
	 */
	public function defaultAction()
	{
		// set content information
		$root = $this->getLayout()->getBlock("root");
		$root->setStatusCode("unkown");
		$root->setErrorMessage("an error occured");

		// set title
		$this->getLayout()->getBlock("head")->setTitle("unkown - an error occured");

		$this->getLayout()->render();
	}

	/**
	 * redirect error calls
	 * @param $name
	 * @param $args
	 */
	public function __call($name, $args)
	{
		if( preg_match("/http([0-9]{3})Action/Usi", $name, $match) )
		{
			$httpStatus = $match[1];
			
			if( isset($this->errors[$httpStatus]) )
			{
				// set even http response code
				$this->getResponse()->setResponseCode($httpStatus);
				
				// set content information
				$root = $this->getLayout()->getBlock("root");
				$root->setStatusCode($httpStatus);
				$root->setErrorMessage($this->errors[$httpStatus]);
				
				// set title
				$this->getLayout()->getBlock("head")->setTitle($httpStatus . " - " . $this->errors[$httpStatus]);
				
				$this->getLayout()->render();
				return;
			}
		}
		
		return $this->forward("core/error/default");
	}
}
