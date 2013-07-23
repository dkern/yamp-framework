<?php
class Yamp_Translator_Model_Translator extends Yamp_Core_Model_Abstract
{
	/**
	 * current system language
	 * @var string
	 */
	private $language = NULL;
	
	/**
	 * available languages
	 * @var array
	 */
	private $languages = array();

	/**
	 * loaded translations
	 * @var array
	 */
	private $translations = array();
	
	
	
	/*
	** public
	*/


	
	/**
	 * translation function 
	 * @return string
	 */
	public function __()
	{
		$args = func_get_args();
		return $this->translate($args);
	}

	/**
	 * translation function
	 * @return string
	 */
	public function t()
	{
		$args = func_get_args();
		return call_user_func_array(array($this, "__"), $args);
	}

	/**
	 * set current translation language
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * get current translation language
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}
	
	/**
	 * set available language files
	 * @param $languages
	 * @return void
	 */
	public function setLanguages($languages)
	{
		$this->languages = $languages;
	}



	/*
	** public
	*/
	
	
	
	/**
	 * translate a text to current system language
	 * @param array $parameter
	 * @return string
	 */
	private function translate($parameter)
	{
		Profiler::start("Yamp_Translator_Model_Translator::translate");
		
		$text = array_shift($parameter);
		
		if( (is_string($text) && empty($text)) || is_null($text) || (is_bool($text) && $text === false) || is_object($text) )
		{
			return Profiler::stop("Yamp_Translator_Model_Translator::translate", NULL);
		}
		
		// format string
		$translated = $this->getTranslatedString($text);
		$result = @vsprintf($translated, $parameter);
		
		if( $result === false )
		{
			$result = $translated;
		}

		return Profiler::stop("Yamp_Translator_Model_Translator::translate", $result);
	}

	/**
	 * receive the translated string
	 * @param string $text
	 * @return string
	 */
	private function getTranslatedString($text)
	{
		Profiler::start("Yamp_Translator_Model_Translator::getTranslatedString");
		
		if( !isset($this->translations[$this->language]) )
		{
			$this->loadTranslation($this->language);
		}
		
		if( isset($this->translations[$this->language][$text]) )
		{
			return Profiler::stop("Yamp_Translator_Model_Translator::getTranslatedString", $this->translations[$this->language][$text]);
		}

		return Profiler::stop("Yamp_Translator_Model_Translator::getTranslatedString", $text);
	}

	/**
	 * load all translation files for a language
	 * @param $languageShort
	 * @return boolean
	 */
	private function loadTranslation($languageShort)
	{
		Profiler::start("Yamp_Translator_Model_Translator::translate");
		
		// is allready loaded
		if( isset($this->translations[$languageShort]) )
		{
			return Profiler::stop("Yamp_Translator_Model_Translator::loadTranslation", true);
		}

		$this->translations[$languageShort] = array();

		// may translation is allready in cache
		if( config::useSystemCache )
		{
			if( ($cache = $this->getHelper("translator/cache")->getCacheInstance()->getCache($languageShort)) !== false )
			{
				$this->translations[$languageShort] = $cache;
				return Profiler::stop("Yamp_Translator_Model_Translator::loadTranslation", true);
			}
		}
		
		$helper = $this->getModel("translator/reader");
		
		// load all translations from modules
		foreach( $this->languages[$languageShort] as $language)
			if( $helper->readTranslationFile($language["module"], $language["file"]) )
				$this->translations[$languageShort] = array_merge($this->translations[$languageShort], $helper->getTranslationPairs());
		
		// renew translation cache
		if( config::useSystemCache )
		{
			$this->getHelper("translator/cache")->getCacheInstance()->setCache($languageShort, $this->translations[$languageShort]);
		}
		
		return Profiler::stop("Yamp_Translator_Model_Translator::loadTranslation", true);
	}
}
