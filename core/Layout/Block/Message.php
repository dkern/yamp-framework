<?php
class Yamp_Layout_Block_Message extends Yamp_Layout_Block_Abstract
{
	/**
	 * construct
	 */
	public function _construct()
	{
		$this->setTemplate("html/message.phtml");
	}

	/**
	 * get block html
	 * @return string
	 */
	public function toHtml()
	{
		// this block will better never be cacheable
		$this->cacheEnabled = false;
		
		return parent::toHtml();
	}
}
