<?php
class Yamp_Layout_Block_Head extends Yamp_Layout_Block_Abstract
{
	/**
	 * head tags
	 * @var array
	 */
	private $tags = array();
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * construct
	 */
	public function _construct()
	{
		$this->setTemplate("html/head.phtml");
	}

	/**
	 * add a new css entry to head
	 * @param string $file
	 * @param string $media
	 * @return boolean
	 */
	public function addCss($file, $media = "all")
	{
		if( file_exists($this->getBaseDir("template") . $file) )
		{
			$url = $this->getBaseUrl("template") . "/" . $file;
			$this->addTag("css", "link", array("rel" => "stylesheet", "type" => "text/css", "href" => $url, "media" => $media));
			return true;
		}

		return false;
	}

	/**
	 * add a new css from module entry to head
	 * @param string $modul
	 * @param string $file
	 * @param string $media
	 * @return boolean
	 */
	public function addModulCss($modul, $file, $media = "all")
	{
		if( file_exists($this->getModulDir($modul, "template") . $file) )
		{
			$url = yamp::getModulUrl($modul, "template") . "/" . $file;
			$this->addTag("css", "link", array("rel" => "stylesheet", "type" => "text/css", "href" => $url, "media" => $media));
			return true;
		}

		return false;
	}

	/**
	 * add a new js entry to head
	 * @param string $file
	 * @return boolean
	 */
	public function addJs($file)
	{
		if( file_exists($this->getBaseDir("template") . $file) )
		{
			$url = $this->getBaseUrl("template") . "/" . $file;
			$this->addTag("js", "script", array("type" => "text/javascript", "src" => $url), true);
			return true;
		}

		return false;
	}

	/**
	 * add a new js module entry to head
	 * @param string $modul
	 * @param string $file
	 * @return boolean
	 */
	public function addModulJs($modul, $file)
	{
		if( file_exists($this->getModulDir($modul, "template") . $file) )
		{
			$url = yamp::getModulUrl($modul, "template") . "/" . $file;
			$this->addTag("js", "script", array("type" => "text/javascript", "src" => $url), true);
			return true;
		}
		
		return false;
	}

	/**
	 * add a script to head
	 * @param string $inner
	 */
	public function addScript($inner)
	{
		$this->addTag("script", "script", array("type" => "text/javascript"), true, $inner);
	}
	
	/**
	 * add a new link entry to head
	 * @param string $rel
	 * @param string $href
	 * @param array $properties
	 * @return void
	 */
	public function addLink($rel, $href, $properties)
	{
		$_properties = array();
		$_properties["rel"] = $rel;
		$_properties["href"] = $href;
		
		if( is_array($properties) )
			foreach( $properties as $name => $value )
				$_properties[$name] = $value;
		
		$this->addTag("link", "link", $_properties);
	}

	/**
	 * add a new meta entry to head
	 * @param string $content
	 * @param array $properties
	 * @return void
	 */
	public function addMeta($content, $properties)
	{
		$_properties = array();
		$_properties["content"] = $content;

		if( is_array($properties) )
			foreach( $properties as $name => $value )
				$_properties[$name] = $value;
		
		$this->addTag("meta", "meta", $_properties);
	}

	/**
	 * add a new tag entry to head
	 * @param string $type
	 * @param string $tag
	 * @param array $properties
	 * @param boolean $close
	 * @param string $inner
	 * @return void
	 */
	public function addTag($type, $tag, $properties, $close = false, $inner = NULL)
	{
		if( !isset($this->tags[$type]) )
		{
			$this->tags[$type] = array();
		}
		
		$exists = true;
		
		if( $exists )
		{
			$_tag = "<" . strtolower($tag);
			$_tag .= $this->createAttributes($properties);
			
			if( $close )
			{
				$_tag .= ">";
				
				if( !is_null($inner) )
				{
					$_tag .= "\n";
					$_tag .= "      " . $inner;
					$_tag .= "\n  ";
				}
				
				$_tag .= "</" . strtolower($tag) . ">";
			}
			else
			{
				$_tag .= " />";
			}
			
			$this->tags[$type][] = $_tag;
		}
	}



	/*
	** public getter
	*/
	
	
	
	/**
	 * receive all css tags
	 * @return string
	 */
	public function getCss()
	{
		return $this->getTags("css");
	}

	/**
	 * receive all js tags
	 * @return string
	 */
	public function getJs()
	{
		return $this->getTags("js");
	}

	/**
	 * receive all js tags
	 * @return string
	 */
	public function getScripts()
	{
		return $this->getTags("script");
	}

	/**
	 * receive all link tags
	 * @return string
	 */
	public function getLink()
	{
		return $this->getTags("link");
	}

	/**
	 * receive all meta tags
	 * @return string
	 */
	public function getMeta()
	{
		return $this->getTags("meta");
	}

	/**
	 * receive all tags by a type or all non causual tags
	 * @param string $type
	 * @return string
	 */
	public function getTags($type = NULL)
	{
		$tags = "";
		
		if( !is_null($type) && isset($this->tags[$type]) )
		{
			foreach( $this->tags[$type] as $tag )
			{
				$tags .= "  " . $tag . "\n";
			}
		}
		elseif( is_null($type) )
		{
			foreach( $this->tags as $type => $data )
			{
				if( !in_array($type, array("css", "js", "script", "link", "meta")) )
				{
					$tags .= $this->getTags($type);
				}
			}
		}
		
		return $tags;
	}



	/*
	** private
	*/
	
	
	
	/**
	 * create attribute string by tag properties
	 * @param array $properties
	 * @return string
	 */
	private function createAttributes($properties)
	{
		$attributes = "";

		if( is_array($properties) )
			foreach( $properties as $name => $value )
				$attributes .= " " . $name . "=\"" . $value . "\"";
		
		return $attributes;
	}
}
