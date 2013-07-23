<?php
class Yamp_Core_Model_Response
{
	/**
	 * set http response code
	 * @param integer $code
	 * @return boolean
	 */
	public function setResponseCode($code)
	{
		if( is_numeric($code) && $code >= 100 )
		{
			$protocol = yamp::getSingleton("core/request")->getServer("server_protocol", "HTTP/1.0");
			$this->addHeader($protocol . " " . $code);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * add a header to current response
	 * @param string $header
	 * @return Yamp_Core_Model_Response
	 */
	public function addHeader($header)
	{
		Profiler::start("Yamp_Core_Model_Response::addHeader");
		
		header($header);
		
		return Profiler::stop("Yamp_Core_Model_Response::addHeader", $this);
	}
	
	/**
	 * remove a header from current response
	 * @param string $header
	 * @return Yamp_Core_Model_Response
	 */
	public function removeHeader($header)
	{
		Profiler::start("Yamp_Core_Model_Response::removeHeader");
		
		header_remove($header);
		
		return Profiler::stop("Yamp_Core_Model_Response::removeHeader", $this);
	}
	
	/**
	 * redirect to another location
	 * @param string $identifier
	 * @return void
	 */
	public function redirect($identifier)
	{
		Profiler::start("Yamp_Core_Model_Response::redirect");
		
		$url = yamp::getUrl($identifier);
		$this->redirectUrl($url);
		
		Profiler::stop("Yamp_Core_Model_Response::redirect");
	}
	
	/**
	 * redirect to another address
	 * @param string $url
	 * @return void
	 */
	public function redirectUrl($url)
	{
		Profiler::start("Yamp_Core_Model_Response::redirectUrl");

		header("Location: " . $url);

		Profiler::stop("Yamp_Core_Model_Response::redirectUrl");
	}
}
