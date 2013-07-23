<?php
class Yamp_Translator_Model_Languages extends Yamp_Core_Model_Abstract
{
	/**
	 * browser accepted languages
	 * @var array
	 */
	private $browserLanguages = NULL;

	/**
	 * detected system language files
	 * @var array
	 */
	private $systemLanguages = NULL;



	/*
	** public
	*/
	
	
	
	/**
	 * get all available system languages
	 * @return array
	 */
	public function detectSystemLanguages()
	{
		Profiler::start("Yamp_Translator_Model_Languages::detectSystemLanguages");
		
		if( !$this->systemLanguages )
		{
			// read from cache
			if( config::useSystemCache )
			{
				if( ($cache = $this->getHelper("translator/cache")->getCacheInstance()->getCache("system")) !== false )
				{
					$this->systemLanguages = $cache;
					return Profiler::stop("Yamp_Translator_Model_Languages::detectSystemLanguages", $this->systemLanguages);
				}
			}
			
			$this->systemLanguages = array();
			$basicEnglish = array();
			
			// read all module languages
			foreach( yamp::registry("_module") as $alias => $module )
				if( isset($module["translation"]) )
					foreach( $module["translation"] as $language => $file )
					{
						if( $language == "enUS" )
						{
							$basicEnglish[] = array("module" => $alias, "file" => $file);
						}

						$this->systemLanguages[$language][] = array("module" => $alias, "file" => $file);
					}

			// set as enUS as default english if not available
			if( !isset($this->systemLanguages["en"]) && count($basicEnglish) > 0 )
			{
				$this->systemLanguages["en"] = $basicEnglish;
			}

			// renew cache
			if( config::useSystemCache )
			{
				$this->getHelper("translator/cache")->getCacheInstance()->setCache("system", $this->systemLanguages);
			}
		}

		return Profiler::stop("Yamp_Translator_Model_Languages::detectSystemLanguages", $this->systemLanguages);
	}

	/**
	 * detect browser languages
	 * @return array
	 */
	public function determineBrowserLanguages()
	{
		Profiler::start("Yamp_Translator_Model_Languages::determineBrowserLanguages");
		
		if( !$this->browserLanguages )
		{
			$language = $this->getRequest()->getServer()->getHttpAcceptLanguage();
			$language = explode(",", $language);
			
			// check all accepted languages
			foreach( $language as $lang )
			{
				$lang = explode(";", $lang);
				$order = !isset($lang[1]) ? 1 : str_replace("q=", "", $lang[1]);
				$lang = str_replace("-", "_", $lang[0]);
				$lang =  preg_replace_callback("/(.*)_(.*)/", function($m) { return $m[1] . strtoupper($m[2]); }, $lang);
				
				$this->browserLanguages[$order] = $lang;
			}
			
			// sort by key value
			krsort($this->browserLanguages);
		}

		return Profiler::stop("Yamp_Translator_Model_Languages::determineBrowserLanguages", $this->browserLanguages);
	}
}
