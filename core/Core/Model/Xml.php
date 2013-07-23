<?php
class Yamp_Core_Model_Xml
{
	/**
	 * created xml object
	 * @var simplexmlelement
	 */
	protected $xml;
	
	/**
	 * current xml object pointer
	 * @var simplexmlelement
	 */
	protected $current;
	
	/**
	 * namespace container
	 * @var array
	 */
	protected $namespaces = array();
	
	/**
	 * xml version
	 * @var string
	 */
	protected $version = "1.0";
	
	/**
	 * xml data encoding
	 * @var string
	 */
	protected $encoding = "UTF-8";
	
	
	
	/*
	** construct
	*/
	
	
	
	/**
	 * creates a new xml class instance
	 * @param string $rootTag
	 * @param string $encoding
	 * @return Yamp_Core_Model_Xml
	 */
	public function _construct($rootTag = NULL, $encoding = "UTF-8")
	{
		$this->setEncoding($encoding);
		
		if( !empty($rootTag) )
		{
			$this->createRoot($rootTag);
		}
		
		return $this;
	}
	
	
	
	/*
	** setter
	*/
	
	
	
	/**
	 * set a new version for the xml document
	 * @param string $version
	 * @return Yamp_Core_Model_Xml
	 */
	public function setVersion($version)
	{
		if( !empty($version) )
		{
			$this->version = $version;
		}
		
		return $this;
	}
	
	/**
	 * set a new encoding for the xml document
	 * @param string $encoding
	 * @return Yamp_Core_Model_Xml
	 */
	public function setEncoding($encoding)
	{
		if( !empty($encoding) && !is_numeric($encoding) )
		{
			$this->encoding = $encoding;
		}
		
		return $this;
	}
	
	/**
	 * creates an new namespace for the xml document
	 * @param string $shortTag
	 * @param string $uri
	 * @return Yamp_Core_Model_Xml
	 */
	public function addNamespace($shortTag, $uri)
	{
		if( !empty($shortTag) && !empty($uri) )
		{
			$this->namespaces[$shortTag] = $uri;
		}
		
		return $this;
	}
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * create the base document by given xml document string
	 * @param string $xml
	 * @return Yamp_Core_Model_Xml
	 */
	 public function createByXml($xml)
	 {
		$this->xml = new SimpleXMLElement($xml);
		$this->updateCurrent();
		
		return $this;
	 }
	
	/**
	 * create a new root tag for the xml document
	 * @param string $rootTag
	 * @param boolean $includeNs
	 * @param string $encoding
	 * @throws Exception
	 * @return Yamp_Core_Model_Xml
	 */
	public function createRoot($rootTag, $includeNs = true, $encoding = "UTF-8")
	{
		$this->setEncoding($encoding);
		
		if( !empty($rootTag) )
		{
			$ns = NULL;

			if( $includeNs )
			{
				$ns = $this->getNamespaces();
			}
			
			$firstLine = "<?xml version=\"" . $this->version . "\" encoding=\"" . $this->encoding . "\" ?>";
			$rootTag   = "<" . $rootTag . $ns . "></" . $rootTag . ">";
			
			$this->xml = new SimpleXMLElement($firstLine . $rootTag);
			$this->updateCurrent();
			
			return $this;
		}
		
		throw new Exception("no base-tag set");
	}
	
	/**
	 * selects subnote of current xml pointer
	 * @param string | SimpleXMLElement $selectable
	 * @return Yamp_Core_Model_Xml
	 */
	public function select($selectable = "")
	{
		if( $selectable instanceof SimpleXMLElement )
		{
			$this->current = $selectable;
			return $this;
		}
		
		if( !empty($selectable) )
		{
			$e = explode("/", $selectable);
			$this->updateCurrent();
			
			foreach( $e as $node )
			{
				$this->current = $this->current->{$node};
			}
		}
		else
		{
			$this->updateCurrent();
		}
		
		return $this;
	}
	
	/**
	 * creates a new subnote inside the current xml pointer
	 * @param string $name
	 * @param string $ns
	 * @param boolean $returnSimpleXml
	 * @return mixed
	 */
	public function create($name, $ns = NULL, $returnSimpleXml = false)
	{
		if( $ns != NULL )
		{
			//$ns = $this->getNamespace($ns);
		}
		
		$this->add($name, NULL, $ns);
		
		if( $returnSimpleXml )
		{
			return $this->current;
		}
		
		return $this;
	}
	
	/**
	 * creates a new subnote inside the current xml pointer with optional value
	 * @param string $name
	 * @param string $value
	 * @param string $ns
	 * @param boolean $cdata
	 * @return Yamp_Core_Model_Xml
	 */
	public function add($name, $value = NULL, $ns = NULL, $cdata = false)
	{
		if( $cdata && $value != NULL )
		{
			return $this->addCData($name, $value, $ns);
		}
		
		if( $value == NULL )
		{
			if( $ns == NULL )
			{
				$this->current = $this->current->addChild($name);
			}
			else
			{
				$ns = $this->getNamespace($ns);
				$this->current = $this->current->addChild($name, NULL, $ns);
			}
		}
		else
		{
			$value = $this->prepareValue($value);
			
			if( $ns == NULL )
			{
				$this->current->addChild($name, $value);
			}
			else
			{
				$ns = $this->getNamespace($ns);
				$this->current->addChild($name, $value, $ns);
			}
		}
		
		return $this;
	}
	
	/**
	 * creates a new subnote inside the current xml pointer with optional value
	 * @param string $name
	 * @param string $value
	 * @param string $ns
	 * @return Yamp_Core_Model_Xml
	 */
	public function addCData($name, $value, $ns = NULL)
	{
		$this->current->{$name} = NULL;
		
		$node  = dom_import_simplexml($this->current->{$name});
    	$owner = $node->ownerDocument;
		
		$value = $this->prepareValue($value);
    	$node->appendChild($owner->createCDATASection($value));
		
		return $this;
	}
	
	/**
	 * adds an attribute to the current xml pointer
	 * @param string $name
	 * @param string $value
	 * @param string $sub
	 * @return Yamp_Core_Model_Xml
	 */
	public function attribute($name, $value = NULL, $sub = NULL)
	{
		if( $sub == NULL )
		{
			if( $value == NULL )
			{
				$this->current->addAttribute($name);
			}
			else
			{
				$value = utf8_encode($value);
				$this->current->addAttribute($name, $value);
			}
		}
		else
		{
			if( $value == NULL )
			{
				$this->current->{$sub}->addAttribute($name);
			}
			else
			{
				$value = utf8_encode($value);
				$this->current->{$sub}->addAttribute($name, $value);
			}
		}
		return $this;
	}
	
	/**
	 * prepare values before inserting into document
	 * @param mixed $value
	 * @return mixed
	 */
	public function prepareValue($value)
	{
		$value = utf8_decode($value);
		$value = utf8_encode($value);
		
		return $value;
	}
	
	/**
	 * return xml as array
	 * @return SimpleXMLElement
	 */
	public function asArray()
	{
		$json = json_encode((array)$this->xml);
		$json = str_replace("true", "1", $json);
		$json = str_replace("false", "0", $json);
		
		return json_decode($json, true);
	}
	
	/**
	 * return xml as class object
	 * @return SimpleXMLElement
	 */
	public function asObject()
	{		
		$object = yamp::getModel("core/object");
		$object->setData($this->asArray());
		
		return $object;
	}
	
	/**
	 * return the whole xml document as string from object
	 * @return string
	 */
	public function getXml()
	{
		$xml = $this->xml->asXML();
		
		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);
		
		return $dom->saveXML();
	}
	
	/**
	 * return the whole xml document to the browser
	 * @return void
	 */
	public function returnXml()
	{
		$xml = $this->getXml();
		
		header("Content-Type: text/xml");
		print_r($xml);
		
		return;
	}
	
	/**
	 * save complete xml content to given filename
	 * @param string $folder
	 * @param string $filename
	 * @return Yamp_Core_Model_Xml
	 */
	public function saveXml($folder, $filename)
	{
		$xml = $this->getXml();
		file_put_contents($folder . "/" . $filename, $xml);
		
		return $this;
	}
	
	
	
	/*
	** protected
	*/
	
	
	
	/**
	 * updates the current xml pointer
	 * @return simplexmlelement
	 */
	protected function updateCurrent()
	{
		$this->current = $this->xml;
		return $this->current;
	}
	
	/**
	 * get the namespace uri by short tag
	 * @param string $shortTag
	 * @return string
	 */
	protected function getNamespace($shortTag)
	{
		if( array_key_exists($shortTag, $this->namespaces) )
		{
			return $this->namespaces[$shortTag];
		}
		
		return NULL;
	}
	
	/**
	 * return namespace string for xml base tag
	 * @return string
	 */
	protected function getNamespaces()
	{
		$namespaces = "";
		
		foreach( $this->namespaces as $tag => $uri )
		{
			$namespaces .= " xmlns:" . $tag . "=\"" . $uri . "\"";
		}
		
		return $namespaces;
	}
}