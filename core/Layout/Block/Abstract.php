<?php
class Yamp_Layout_Block_Abstract extends Yamp_Core_Block_Abstract
{
	/**
	 * block children
	 * @var array
	 */
	protected $children = array();

	/**
	 * if the block should be cached
	 * @var boolean
	 */
	protected $cacheEnabled = false;

	/**
	 * name of the cache for this block
	 * @var string
	 */
	protected $cacheKey = NULL;

	/**
	 * block cache lifetime
	 * @var integer
	 */
	protected $cacheLifetime = 3600;

	/**
	 * raw content positions
	 * @var string
	 */
	const CONTENT_BEFORE = "before";
	const CONTENT_AFTER = "after";

	/**
	 * raw contents
	 * @var array
	 */
	protected $rawContent = array();
	
	
	
	/*
	** public
	*/


	/**
	 * construct
	 */
	public function _construct()
	{
		$this->rawContent[self::CONTENT_BEFORE] = array();
		$this->rawContent[self::CONTENT_AFTER] = array();
	}
	
	/**
	 * activate the block caching
	 * @param integer $lifetime
	 * @param string $key
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function enableCache($lifetime = 3600, $key = NULL)
	{
		$this->cacheEnabled = true;
		$this->cacheKey = $key;
		$this->cacheLifetime = $lifetime;
		
		return $this;
	}
	
	/**
	 * add a block as children
	 * @param string $name
	 * @param Yamp_Layout_Block_Abstract|object $block
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function addChild($name, $block)
	{
		if( $block instanceof Yamp_Layout_Block_Abstract )
		{
			$path  = $block->getBlockModule() . "_";
			$path .= $block->getBlockName() . "_";
			$path .= $this->getBlockPath() . "_";
			$path .= $name;
			
			$block->setBlockPath($path);
			$this->children[$name] = $block;
		}
		
		return $this;
	}

	/**
	 * check if a child with this name exists
	 * @param string $name
	 * @return boolean
	 */
	public function existsChild($name)
	{
		Profiler::start("Yamp_Layout_Block_Abstract::existsCild");

		if( isset($this->children[$name])  )
		{
			return Profiler::stop("Yamp_Layout_Block_Abstract::existsCild", true);
		}

		return Profiler::stop("Yamp_Layout_Block_Abstract::existsCild", false);
	}
	
	/**
	 * replace a block by name in the layout
	 * @param string $name
	 * @param Yamp_Layout_Block_Abstract $block
	 * @return boolean
	 */
	public function replaceBlock($name, $block)
	{
		Profiler::start("Yamp_Layout_Block_Abstract::replaceBlock");

		if( isset($this->children[$name]) && $block instanceof Yamp_Layout_Block_Abstract )
		{
			$this->children[$name] = $block;
			return Profiler::stop("Yamp_Layout_Block_Abstract::replaceBlock", true);
		}

		return Profiler::stop("Yamp_Layout_Block_Abstract::replaceBlock", false);
	}
	
	/**
	 * remove a child by name from block
	 * @param string $name
	 * @return boolean
	 */
	public function removeChild($name)
	{
		Profiler::start("Yamp_Template_Block_Abstract::removeChild");

		if( $name == "*" )
		{
			$this->children = array();
			return Profiler::stop("Yamp_Template_Block_Abstract::removeChild", true);
		}
		
		if( isset($this->children[$name]) )
		{
			$this->children[$name] = NULL;
			unset($this->children[$name]);

			return Profiler::stop("Yamp_Template_Block_Abstract::removeChild", true);
		}

		return Profiler::stop("Yamp_Template_Block_Abstract::removeChild", false);
	}
	
	/**
	 * get block content by name
	 * @param string $name
	 * @return string
	 */
	public function getChild($name)
	{
		if( isset($this->children[$name]) && $this->children[$name] instanceof Yamp_Layout_Block_Abstract )
		{
			return $this->children[$name]->toHtml();
		}
		
		return NULL;
	}

	/**
	 * get block by name
	 * @param string $name
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function getBlock($name)
	{
		Profiler::start("Yamp_Template_Block_Abstract::getBlock");

		if( isset($this->children[$name]) && $this->children[$name] instanceof Yamp_Layout_Block_Abstract )
		{
			return Profiler::stop("Yamp_Template_Block_Abstract::getBlock", $this->children[$name]);
		}

		return Profiler::stop("Yamp_Template_Block_Abstract::getBlock", NULL);
	}

	/**
	 * print out all children in a row
	 * @return void
	 */
	public function getChildren()
	{
		foreach( $this->children as $name => $block )
		{
			if( isset($this->children[$name]) && $this->children[$name] instanceof Yamp_Layout_Block_Abstract )
			{
				echo $this->children[$name]->toHtml();
			}
		}
		
		return;
	}

	/**
	 * get best layout file
	 * @return string
	 */
	public function getTemplate()
	{
		$directory = yamp::getModulDir($this->getBlockModule(), "template");
		
		if( $this->hasTemplate() )
		{
			if( file_exists($directory . parent::getTemplate()) )
			{
				return $directory . parent::getTemplate();
			}
			
			return yamp::getBaseDir("template") . parent::getTemplate();
		}

		$path = str_replace("_", DS, strtolower($this->getBlockName()));
		return $directory . $path . ".phtml";
	}

	/**
	 * add raw content to the block output
	 * @param string $content
	 * @param string $position
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function addRawContent($content, $position = self::CONTENT_BEFORE)
	{
		if( $position != self::CONTENT_BEFORE && $position != self::CONTENT_AFTER )
		{
			$position = self::CONTENT_BEFORE;
		}
		
		$this->rawContent[$position][] = $content;
		return $this;
	}

	/**
	 * get current content language
	 * @return string
	 */
	public function getContentLanguage()
	{
		$language = $this->getHelper("translator")->getSystemLanguage();
		return substr($language, 0, 2);
	}
	
	/**
	 * get block content html output
	 * @return string
	 */
	public function toHtml()
	{
		$cache = NULL;
		
		if( $this->cacheEnabled )
		{
			$cache = $this->getSingleton("cache/cache");
			
			// set basic options
			$cache->setCacheDirectory("var/cache/");
			$cache->setUseDivider(false);
			$cache->setCacheKey("block");
			$cache->setCacheLifetime($this->cacheLifetime);
			
			if( ($html = $cache->getCache($this->getCacheKey())) !== false )
			{
				return $html;
			}
		}
		
		$template = $this->getTemplate();
		
		if( file_exists($template) )
		{
			ob_start();
			
			// include raw content before childs
			if( isset($this->rawContent[self::CONTENT_BEFORE]) )
				foreach( $this->rawContent[self::CONTENT_BEFORE] as $content )
					echo $content;
			
			include($template);

			// include raw content after childs
			if( isset($this->rawContent[self::CONTENT_AFTER]) )
				foreach( $this->rawContent[self::CONTENT_AFTER] as $content )
					echo $content;
			
			$html = ob_get_clean();

			if( $this->cacheEnabled )
			{
				$cache->setCache($this->getCacheKey(), $html);
			}
			
			return $html;
		}
				
		return NULL;
	}



	/*
	** protected
	*/



	/**
	 * get module of this class (even extendet)
	 * @return boolean
	 */
	protected function getCurrentModule()
	{
		$class = explode("_", get_called_class());
		$name = $class[0] . "_" . $class[1];

		foreach( yamp::registry("_module") as $module )
		{
			if( $name == $module["name"] )
			{
				return $module["alias"];
			}
		}

		return false;
	}



	/*
	** private
	*/

	

	/**
	 * get unique block cache key
	 * @return string
	 */
	private function getCacheKey()
	{
		if( !is_null($this->cacheKey) )
		{
			return $this->cacheKey;
		}
		
		return md5($this->getBlockPath()); 
	}
}
