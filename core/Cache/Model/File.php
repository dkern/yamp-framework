<?php
class Yamp_Cache_Model_File extends Yamp_Core_Model_Abstract implements Yamp_Cache_Model_Handler
{
	/**
	 * Cache Directory
	 * @var string
	 */
	private $cacheDirectory = "cache";

	/**
	 * use dividing steps
	 * @var boolean
	 */
	private $useDivider = true;

	/**
	 * cache folder dividing steps
	 * @var int
	 */
	private $dividerSteps = 2;



	/*
	** getter / setter
	*/



	/**
	 * set use divider steps
	 * @param boolean $use
	 * @return Yamp_Cache_Model_Cache
	 */
	public function setUseDivider($use)
	{
		$this->useDivider = $use;
		return $this;
	}

	/**
	 * get divider steps usage
	 * @return boolean
	 */
	public function getUseDivider()
	{
		return $this->useDivider;
	}

	/**
	 * set the number of folder divider steps
	 * @param int $steps
	 * @return Yamp_Cache_Model_Cache
	 */
	public function setDividerSteps($steps)
	{
		if( is_numeric($steps) )
		{
			$this->dividerSteps = $steps;
		}

		return $this;
	}

	/**
	 * get the number of folder divider steps
	 * @return int
	 */
	public function getDividerSteps()
	{
		return $this->dividerSteps;
	}

	/**
	 * set the cache directory
	 * @param string $directory
	 * @return Yamp_Cache_Model_Cache
	 */
	public function setCacheDirectory($directory)
	{
		// correction of directory
		$directory = str_replace("/", DS, $directory);
		$directory = str_replace("\\", DS, $directory);
		$directory = rtrim($directory, DS);

		$this->cacheDirectory = $directory . DS;
		return $this;
	}

	/**
	 * get the current cache directory
	 * @return string
	 */
	public function getCacheDirectory()
	{
		return $this->cacheDirectory;
	}

	/**
	 * get the cache filename
	 * @param string $key
	 * @param string $identifier
	 * @return string
	 */
	public function getCacheFilename($key, $identifier)
	{
		$str = $key . $identifier;
		$hash = md5($str);
		$filename = $hash . ".cache";

		return $filename;
	}
	

	
	/*
	** public
	*/



	/**
	 * initialize cache handler
	 * @return boolean
	 */
	public function init()
	{
		Profiler::start("Yamp_Cache_Model_Database::init");
		return Profiler::stop("Yamp_Cache_Model_Database::init", true);
	}
	
	/**
	 * set data to the cache by key and identidier
	 * @param string $key
	 * @param string $identifier
	 * @param integer $lifetime
	 * @param mixed $data
	 * @param boolean $override
	 * @return boolean
	 */
	public function setCache($key, $identifier, $lifetime, $data, $override = false)
	{
		Profiler::start("Yamp_Cache_Model_File::setCache");

		$key = md5($key);
		$path = $this->getShortenedCacheDirectory($key, $identifier);
		$filename = $this->getCacheFilename($key, $identifier);
		
		// create folder
		$this->mkDirRecursive($path);
		
		// check if a cache exists and verify the lifetime
		$cache = $this->getCache($key, $identifier);
		
		if( $cache === false || $override )
		{
			$serialized = serialize($data);
			$cacheData = array("timestamp" => time(),
			                   "lifetime"  => $lifetime,
			                   "ident"     => $identifier,
			                   "content"   => base64_encode($serialized),
			                   "filename"  => $filename);

			if( $this->createCacheFile($path . $filename, $cacheData) )
			{
				return Profiler::stop("Yamp_Cache_Model_File::setCache", true);
			}
		}

		return Profiler::stop("Yamp_Cache_Model_File::setCache", false);
	}

	/**
	 * get a cache entry by key and identifier
	 * @param string $key
	 * @param string $identifier
	 * @return mixed
	 */
	public function getCache($key, $identifier)
	{
		Profiler::start("Yamp_Cache_Model_File::getCache");
		
		$key = md5($key);
		$path = $this->getShortenedCacheDirectory($key, $identifier);
		$filename = $this->getCacheFilename($key, $identifier);
		
		// cache file exists
		if( $this->fileExists($path . $filename) )
		{
			// get file content
			$contents = file_get_contents($path . $filename);
			$cacheData = json_decode($contents);
			
			if( $cacheData )
			{
				// cache is active
				if( $this->activeCache($cacheData->timestamp, $cacheData->lifetime) )
				{
					if( $cacheData->ident == $identifier )
					{
						$serialized = base64_decode($cacheData->content);
						return Profiler::stop("Yamp_Cache_Model_File::getCache", unserialize($serialized));
					}
				}
			}
		}

		return Profiler::stop("Yamp_Cache_Model_File::getCache", false);
	}

	/**
	 * remove a whole cache by key or a single one by identifier
	 * @param string $key
	 * @param string $identifier
	 * @return boolean
	 */
	public function removeCache($key, $identifier = null)
	{
		Profiler::start("Yamp_Cache_Model_File::removeCache");
		
		// only remove cache for a specified key
		if( !is_null($key) )
		{
			$key = md5($key);
			
			// delete all cache files with the given key
			if( is_null($identifier) )
			{
				$cacheDir = rtrim($this->getCacheDirectory(), DS);
				$cacheKey = $key;
				$directory = $cacheDir . DS . $cacheKey;
				
				$this->rmDirRecursive($directory);
				return Profiler::stop("Yamp_Cache_Model_File::removeCache", true);
			}
			
			// only delete cache with the identifier
			else
			{
				$directory = $this->getShortenedCacheDirectory($key, $identifier);
				$filename = $directory. $this->getCacheFilename($key, $identifier);
				
				if( $this->fileExists($filename) )
				{
					unlink($filename);
					return Profiler::stop("Yamp_Cache_Model_File::removeCache", true);
				}
			}
		}

		return Profiler::stop("Yamp_Cache_Model_File::removeCache", false);
	}

	/**
	 * check if the cache is active with the lifetime setting
	 * @param integer $timestamp
	 * @param integer $lifetime
	 * @return boolean
	 */
	public function activeCache($timestamp, $lifetime)
	{
		if( (time() - $timestamp) <= $lifetime )
		{
			return true;
		}

		return false;
	}

	/**
	 * cache file cleanup
	 * @param string $directory
	 * @param boolean $empty
	 * @return boolean
	 */
	public function cleanup($directory = NULL, $empty = false)
	{
		Profiler::start("Yamp_Cache_Model_File::cleanup");
		
		// if no folder is set use default directory
		if( is_null($directory) )
		{
			$directory = $this->getCacheDirectory();
		}
		
		// accessable directory
		if( empty($directory) || !file_exists($directory) || !is_dir($directory) || !is_readable($directory) )
		{
			return Profiler::stop("Yamp_Cache_Model_File::cleanup", false);
		}
		
		$handle = opendir($directory);
		
		// read all sub-files and directories
		while( $contents = readdir($handle) )
		{
			if( $contents != "." && $contents != ".." )
			{
				$path = $directory . DS . $contents;
				
				// cleanup sub-dir
				if( is_dir($path) )
				{
					$this->cleanup($path);
				}
				
				// remove old cache files 
				else
				{
					if( strpos($contents, ".cache") !== false )
					{
						$content = file_get_contents($path);
						$cacheData = json_decode($content);
						
						if( $cacheData && $cacheData->timestamp && $cacheData->lifetime )
						{
							if( !$this->activeCache($cacheData->timestamp, $cacheData->lifetime) )
							{
								@unlink($path);
							}
						}
					}  
				}
			}
		}
		
		if( count(scandir($directory)) == 2 )
		{
			@rmdir($directory);
		}
		
		closedir($handle);

		return Profiler::stop("Yamp_Cache_Model_File::cleanup", true);
	}
	
	
	
	/*
	** private
	*/

	

	/**
	 * Get a shortened cache directory
	 * @param string $key
	 * @param string $identifier
	 * @return string
	 */
	private function getShortenedCacheDirectory($key, $identifier)
	{
		Profiler::start("Yamp_Cache_Model_File::getShortenedCacheDirectory");
		
		$key = md5($key);
		$cacheDir = rtrim($this->getCacheDirectory(), DS);
		$structure = str_split($identifier);
		
		$folder = $cacheDir . DS . $key . DS;
		
		// only if folder structure should be devided
		if( $this->getUseDivider() )
		{
			$i = 0;
			$indv = "";
			
			foreach( $structure as $_struct )
			{
				if( $i >= $this->getDividerSteps() )
				{
					$indv .= DS;
					$i = 0;
				}
				
				$indv .= $_struct;
				$i++;
			}
			
			$folder = $cacheDir . DS . $key . DS . $indv . DS;
		}

		return Profiler::stop("Yamp_Cache_Model_File::getShortenedCacheDirectory", $folder);
	}

	/**
	 * create the cache file
	 * @param string $filename
	 * @param string $cacheData
	 * @return boolean
	 */
	private function createCacheFile($filename, $cacheData)
	{
		Profiler::start("Yamp_Cache_Model_File::createCacheFile");
		
		$json = json_encode($cacheData);
		
		if( file_put_contents($filename, $json) !== false )
		{
			return Profiler::stop("Yamp_Cache_Model_File::createCacheFile", true);
		}
		
		return Profiler::stop("Yamp_Cache_Model_File::createCacheFile", false);
	}

	/**
	 * check if a file exists
	 * @param string $filename
	 * @return bool
	 */
	private function fileExists($filename)
	{
		Profiler::start("Yamp_Cache_Model_File::fileExists");
		
		if( file_exists($filename) )
		{
			return Profiler::stop("Yamp_Cache_Model_File::fileExists", true);
		}

		return Profiler::stop("Yamp_Cache_Model_File::fileExists", false);
	}

	/**
	 * make a directory structure recursive
	 * @param string $directory
	 * @return bool
	 */
	private function mkDirRecursive($directory)
	{
		Profiler::start("Yamp_Cache_Model_File::mkDirRecursive");
		
		if( !is_dir($directory) )
		{
			$this->mkDirRecursive( dirname($directory) );
			mkdir($directory);

			return Profiler::stop("Yamp_Cache_Model_File::mkDirRecursive", true);
		}

		return Profiler::stop("Yamp_Cache_Model_File::mkDirRecursive", false);
	}

	/**
	 * remove a directory structure recursive
	 * @param string $directory The directory to remove
	 * @return void
	 */
	private function rmDirRecursive($directory)
	{
		Profiler::start("Yamp_Cache_Model_File::rmDirRecursive");
		
		if( is_dir($directory) )
		{
			$objects = scandir($directory);

			foreach( $objects as $object )
			{
				if( $object != "." && $object != ".." )
				{
					if( filetype($directory . DS . $object) == "dir" )
					{
						$this->rmDirRecursive($directory . DS . $object);
					}
					else
					{
						@unlink($directory . DS . $object);
					}
				}
			}
			
			reset($objects);
			rmdir($directory);
		}

		Profiler::stop("Yamp_Cache_Model_File::rmDirRecursive");
	}
}
