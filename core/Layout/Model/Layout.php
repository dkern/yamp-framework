<?php
class Yamp_Layout_Model_Layout
{
	/**
	 * root block object
	 * @var Yamp_Layout_Block_Abstract
	 */
	private $root = NULL;
	
	
	
	/*
	** public
	*/


	
	/**
	 * construct
	 */
	public function _construct()
	{
		Profiler::start("Yamp_Template_Model_Layout::_construct");
		
		// create root block
		$this->root = yamp::getBlock("layout/root");
		
		// add default childs
		$this->root->addChild("head", $this->createBlock("layout/head", "head"));
		$this->root->addChild("messages", $this->createBlock("layout/messages", "messages"));
		$this->root->addChild("header", $this->createBlock("layout/header", "header"));
		$this->root->addChild("content", $this->createBlock("layout/content", "content"));
		$this->root->addChild("left", $this->createBlock("layout/left", "left"));
		$this->root->addChild("right", $this->createBlock("layout/right", "right"));
		$this->root->addChild("footer", $this->createBlock("layout/footer", "footer"));

		Profiler::stop("Yamp_Template_Model_Layout::_construct");
	}

	/**
	 * set root template
	 * @param string $template
	 * @return boolean
	 */
	public function setTemplate($template)
	{
		Profiler::start("Yamp_Template_Model_Layout::setTemplate");
		
		if( file_exists(yamp::getBaseDir("template") . $template) )
		{
			$this->root->setTemplate($template);
			return Profiler::stop("Yamp_Template_Model_Layout::setTemplate", true);
		}
		
		return Profiler::stop("Yamp_Template_Model_Layout::setTemplate", false);
	}

	/**
	 * create a new block object in layout
	 * @param string $block
	 * @param string $name
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function createBlock($block, $name = NULL)
	{
		Profiler::start("Yamp_Template_Model_Layout::createBlock");

		$block = yamp::getBlock($block);
		
		if( !is_null($name) )
		{
			$this->root->addChild($name, $block);
			$this->root->getBlock($name)->setBlockName($name);
			$this->root->getBlock($name)->setBlockName($name);
			
			return Profiler::stop("Yamp_Template_Model_Layout::createBlock", $this->root->getBlock($name));
		}
		
		return Profiler::stop("Yamp_Template_Model_Layout::createBlock", $block);
	}

	/**
	 * replace a block by name in the layout
	 * @param string $name
	 * @param Yamp_Layout_Block_Abstract $block
	 * @return boolean
	 */
	public function replaceBlock($name, $block)
	{
		if( $name == "root" )
		{
			if( $block instanceof Yamp_Layout_Block_Abstract )
			{
				$this->root = $block;
				return true;
			}
		}
		
		return $this->root->replaceBlock($name, $block);
	}
	
	/**
	 * remove a child from layout
	 * @param string $name
	 * @return boolean
	 */
	public function removeChild($name)
	{
		return $this->root->removeChild($name);
	}
	
	/**
	 * get block by name
	 * @param string $name
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function getBlock($name)
	{
		if( $name == "root" )
		{
			return $this->root;
		}
		
		return $this->root->getBlock($name);
	}

	/**
	 * get child output by name
	 * @param string $name
	 * @return Yamp_Layout_Block_Abstract
	 */
	public function getChild($name)
	{
		return $this->root->getChild($name);
	}
	
	/**
	 * flush whole layout to browser
	 * @return void
	 */
	public function render()
	{
		Profiler::start("Yamp_Template_Model_Layout::render");
		
		yamp::dispatch("before_render_layout", $this->root);
		echo $this->root->toHtml();
		yamp::dispatch("after_render_layout");
		
		Profiler::stop("Yamp_Template_Model_Layout::render");
		return;
	}
}