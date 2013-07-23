<?php
class Yamp_Translator_Helper_Data extends Yamp_Core_Helper_Abstract
{
	/**
	 * language model
	 * @var Yamp_Translator_Model_Languages
	 */
	private $languages;

	/**
	 * language model
	 * @var Yamp_Translator_Model_Translator
	 */
	private $translator;

	/**
	 * default system language
	 * @var string
	 */
	private $defaultLanguage = "enUS";

	/**
	 * actual system language
	 * @var string
	 */
	private $selectedLanguage = NULL;


	
	/*
	** public
	*/


	
	/**
	 * construct
	 */
	public function _construct()
	{
		Profiler::start("Yamp_Translator_Helper_Data::_construct");
		
		$this->languages = $this->getModel("translator/languages");
		$this->translator = $this->getModel("translator/translator");
		
		$this->translator->setLanguages($this->languages->detectSystemLanguages());
		$this->translator->setLanguage($this->defaultLanguage);
		
		Profiler::stop("Yamp_Translator_Helper_Data::_construct");
	}
	
	/**
	 * get current system language
	 * @return string
	 */
	public function getSystemLanguage()
	{
		Profiler::start("Yamp_Translator_Helper_Data::getSystemLanguage");
		
		if( $this->selectedLanguage )
		{
			return Profiler::stop("Yamp_Translator_Helper_Data::getSystemLanguage", $this->selectedLanguage);
		}

		$systemLanguages = $this->languages->detectSystemLanguages();
		
		foreach( $this->languages->determineBrowserLanguages() as $language )
		{
			if( isset($systemLanguages[$language]) )
			{
				return Profiler::stop("Yamp_Translator_Helper_Data::getSystemLanguage", $language);
			}
		}

		return Profiler::stop("Yamp_Translator_Helper_Data::getSystemLanguage", $this->defaultLanguage);
	}
	
	/**
	 * set automatically system language or by language short 
	 * @param string $languageShort
	 * @return boolean
	 */
	public function setSystemLanguage($languageShort = NULL)
	{
		Profiler::start("Yamp_Translator_Helper_Data::setSystemLanguage");
		
		$systemLanguages = $this->languages->detectSystemLanguages();
		
		// set language if available
		if( !is_null($languageShort) )
		{
			if( isset($systemLanguages[$languageShort]) )
			{
				$this->selectedLanguage = $languageShort;
				$this->translator->setLanguage($this->selectedLanguage);
				return Profiler::stop("Yamp_Translator_Helper_Data::setSystemLanguage", true);
			}
		}
		
		// auto detect available default language
		else
		{
			// try to find language by request
			foreach( $this->languages->determineBrowserLanguages() as $language )
			{
				if( isset($systemLanguages[$language]) )
				{
					$this->selectedLanguage = $language;
					$this->translator->setLanguage($this->selectedLanguage);
					return Profiler::stop("Yamp_Translator_Helper_Data::setSystemLanguage", true);
				}
			}
			
			// fallback to default language
			$this->selectedLanguage = $this->defaultLanguage;
			$this->translator->setLanguage($this->selectedLanguage);
			return Profiler::stop("Yamp_Translator_Helper_Data::setSystemLanguage", true);
		}
		
		return Profiler::stop("Yamp_Translator_Helper_Data::setSystemLanguage", false);
	}

	/**
	 * get translator instance
	 * @return Yamp_Translator_Model_Translator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}
}