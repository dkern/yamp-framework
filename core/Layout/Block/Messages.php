<?php
class Yamp_Layout_Block_Messages extends Yamp_Layout_Block_Abstract
{
	/**
	 * message instance
	 * @var Yamp_Session_Model_Messages
	 */
	private $messages = NULL;
	
	
	
	/*
	** public 
	*/
	
	
	
	/**
	 * construct
	 */
	public function _construct()
	{
		$this->setTemplate("html/messages.phtml");
	}

	/**
	 * get messages html as string
	 * @return string
	 */
	public function getMessagesHtml()
	{
		$messages = "";
		
		if( $this->getMessages()->hasMessages() )
		{
			while( ($entry = $this->getMessages()->readMessages()) !== false )
			{
				$msg = yamp::getBlock("layout/message");
				$msg->setMessageType($entry["type"]);
				$msg->setMessage($entry["message"]);
				
				$messages .= $msg->toHtml();
			}
		}
		
		return $messages;
	}

	/**
	 * only render block when messages available
	 * @return string
	 */
	public function toHtml()
	{
		if( $this->getMessages()->hasMessages() )
		{
			return parent::toHtml();
		}
		
		return NULL;
	}
	
	
	
	/*
	** private
	*/

	

	/**
	 * get messages object
	 * @return Yamp_Session_Model_Messages
	 */
	protected function getMessages()
	{
		if( !$this->messages )
		{
			$this->messages = yamp::getSingleton("session/messages");
		}
		
		return $this->messages;
	}
}
