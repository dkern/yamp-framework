<?php
class Yamp_Layout_Block_Root extends Yamp_Layout_Block_Abstract
{
	/**
	 * construct
	 */
	public function _construct()
	{
		$this->setTemplate("page.phtml");
	}

	/**
	 * check root template before output
	 * @return string
	 */
	public function toHtml()
	{
		if( file_exists($this->getTemplate()) )
		{
			// this block will better never be cacheable
			$this->cacheEnabled = false;
			
			return parent::toHtml();
		}
		
		yamp::throwException($this->t("template file '%s' missing", $this->getTemplate()));
		return NULL;
	}
}
