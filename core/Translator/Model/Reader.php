<?php
class Yamp_Translator_Model_Reader extends Yamp_Core_Model_Abstract
{
	/**
	 * translator pairs
	 * @var array
	 */
	private $pairs = array();

	
	
	/*
	** public
	*/
	
	
	
	/**
	 * read translation date from file
	 * @param string $module
	 * @param string $file
	 * @return boolean
	 */
	public function readTranslationFile($module, $file)
	{
		Profiler::start("Yamp_Translator_Model_Reader::readTranslationFile");
		
		$this->pairs = array();
		$file = $this->getModulDir($module, "etc") . $file;
		
		if( file_exists($file) )
		{
			if( ($handle = fopen($file, "r")) !== false )
			{
				while( ($data = fgetcsv($handle, 0, ",", "\"") ) !== false )
				{
					// if this row got two columns
					if( count($data) == 2 )
					{
						$this->pairs[$data[0]] = $data[1];
					}
				}
				
				fclose($handle);
				return Profiler::stop("Yamp_Translator_Model_Reader::readTranslationFile", true);
			}
		}
		
		return Profiler::stop("Yamp_Translator_Model_Reader::readTranslationFile", false);
	}

	/**
	 * receive value pairs to translate
	 * @return array
	 */
	public function getTranslationPairs()
	{
		return $this->pairs;
	}
}
