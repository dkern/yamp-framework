<?php
class Yamp_Core_Helper_Password
{
	/**
	 * create a salted hash
	 * @param string $content
	 * @param integer $saltLength
	 * @param string $salt
	 * @return string
	 */
	public function createHash($content, $saltLength = 2, $salt = NULL)
	{
		Profiler::start("Yamp_Core_Helper_Password::createHash");
		
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$length = strlen($chars) - 1;
		
		// create salt
		if( is_null($salt) )
		{
			$salt = "";
			
			for ( $i = 0; $i < $saltLength; $i++ )
			{
				$salt .= $chars[mt_rand(0, $length)];
			}
		}
		
		$hash = md5($salt . $content) . ":" . $salt;
		
		return Profiler::stop("Yamp_Core_Helper_Password::createHash", $hash);
	}

	/**
	 * validate content against a hash
	 * @param string $hash
	 * @param string $content
	 * @return boolean
	 */
	public function validateHash($hash, $content)
	{
		Profiler::start("Yamp_Core_Helper_Password::validateHash");
		
		$parts = explode(":", $hash, 2);
		
		if( count($parts) == 2 )
		{
			$content = $this->createHash($content, strlen($parts[1]), $parts[1]);
			
			if( $hash === $content )
			{
				return Profiler::stop("Yamp_Core_Helper_Password::validateHash", true);
			}
		}

		return Profiler::stop("Yamp_Core_Helper_Password::validateHash", false);
	}
}