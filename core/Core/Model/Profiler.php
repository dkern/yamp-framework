<?php
class Yamp_Core_Model_Profiler
{
	/**
	 * if profiler is enables
	 * @var boolean
	 */
    static private $isEnabled = false;
	
	/**
	 * if memory function exists
	 * @var boolean
	 */
    static private $functionAvailable = false;
	
	/**
	 * if profiler is enables
	 * @var boolean
	 */
	static private $profiles = array();
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * enables the profiler
	 * @return void
	 */
	public static function enable()
	{
		self::$isEnabled = true;
		self::$functionAvailable = function_exists("memory_get_usage");
	}
	
	/**
	 * disable the profiler
	 * @return void
	 */
	public static function disable()
	{
		self::$isEnabled = false;
	}
	
	/**
	 * start a new profile
	 * @param $name string
	 * @return void
	 */
	public static function start($name)
	{
		if( !self::$isEnabled )
		{
			return;
		}
		
		// get time as soon as possible
		$time = microtime(true);
		
		if( !isset(self::$profiles[$name]) )
		{
			self::resetProfile($name);
		}
		else
		{
			self::$profiles[$name]["_add"]  = self::$profiles[$name]["diff"];
			self::$profiles[$name]["diff"] = 0;
		}
		
		self::$profiles[$name]["_run"] = 1;
		self::$profiles[$name]["start"] = $time;
		self::$profiles[$name]["count"]++;
		
		if( self::$functionAvailable )
		{
			self::$profiles[$name]["realmem_start"] = memory_get_usage(true);
			self::$profiles[$name]["emalloc_start"] = memory_get_usage();
		}
	}
	
	/**
	 * stops a profile
	 * @param string $name
	 * @param string $return
	 * @return mixed
	 */
	public static function stop($name, $return = "unsetParameterData")
	{
		if( !self::$isEnabled )
		{
			if( $return !== "unsetParameterData" )
			{
				return $return;
			}
			
			return NULL;
		}

		// get time as soon as possible
		$time = microtime(true);
		
		if( !isset(self::$profiles[$name]) )
		{
			self::resetProfile($name);
		}
		
		if( self::$profiles[$name]["start"] !== false )
		{
			self::$profiles[$name]["_run"] = 0;
			self::$profiles[$name]["diff"] = $time - self::$profiles[$name]["start"] + self::$profiles[$name]["_add"];
			self::$profiles[$name]["avg"]  = self::$profiles[$name]["diff"] / self::$profiles[$name]["count"];
			self::$profiles[$name]["stop"] = $time;
			
			if( self::$functionAvailable )
			{
				self::$profiles[$name]["realmem"] += memory_get_usage(true) - self::$profiles[$name]["realmem_start"];
				self::$profiles[$name]["emalloc"] += memory_get_usage() - self::$profiles[$name]["emalloc_start"];
			}
		}
		
		if( $return !== "unsetParameterData" )
		{
			return $return;
		}
		
		return NULL;
	}
	
	/**
	 * get collected profiling data
	 * @return array
	 */
	public static function getProfiles()
	{
		return self::$profiles;
	}
	
	/**
	 * returns a single profile if available
	 * @param $name string
	 * @return mixed
	 */
	public static function getProfile($name)
	{
		if( isset(self::$profiles[$name]) )
		{
			return self::$profiles[$name];
		}
		
		return false;
	}
	
	/**
	 * print profiling data
	 * @return void
	 */
	public static function printData()
	{
		if( self::$isEnabled )
		{
			$padName = 8;
			$minTime = 1;
			$maxTime = 0;
			
			foreach( self::$profiles as $name => $profile )
			{
				if( ($lenght = strlen($name) + 3) > $padName )
				{
					$padName = $lenght;
				}
				
				if( ($min = $profile["diff"]) < $minTime )
				{
					$minTime = $min;
				}
				
				if( ($max = $profile["diff"]) > $maxTime )
				{
					$maxTime = $max;
				}
			}
			
			$difference = ($maxTime - $minTime) / 10;
			$colors = array();
			$colors["green"] = array($minTime, $minTime + $difference * 2);
			$colors["orange"] = array($minTime + $difference * 2, $minTime + $difference * 5);
			$colors["red"] = array($minTime + $difference * 5, $maxTime);
			
			echo "<style type='text/css'>\n";
			echo "  pre#profiler { font-family: \"Lucida Console\", Monaco, monospace; font-size: 11px; background-color: #fff; padding: 15px; }\n";
			echo "  pre#profiler * { font-family: \"Lucida Console\", Monaco, monospace; font-size: 11px; }\n";
			echo "</style>\n";
			echo "<pre id='profiler'>\n";
			echo "<strong>PROFILER</strong>\n";
			echo "- - - - - - - - - - -\n";
			echo "\n";
			
			echo "<strong>";
			echo str_pad("#", 5, " ", STR_PAD_RIGHT);
			echo str_pad("name", $padName, " ", STR_PAD_RIGHT);
			echo str_pad("start", 18, " ", STR_PAD_RIGHT);
			echo str_pad("stop", 18, " ", STR_PAD_RIGHT);
			echo str_pad("count", 8, " ", STR_PAD_RIGHT);
			echo str_pad("time / difference", 20, " ", STR_PAD_RIGHT);
			echo str_pad("avg", 20, " ", STR_PAD_RIGHT);
			echo str_pad("realmem", 10, " ", STR_PAD_RIGHT);
			echo "emalloc";
			echo "</strong>\n";
			
			$i = 0;
			$amount = 0;
			
			foreach( self::$profiles as $name => $profile )
			{
				$i++;
				
				if( $i > 1 ) $amount += $profile["diff"];
				
				echo str_pad($i, 5, " ", STR_PAD_RIGHT);
				echo str_pad($name, $padName, " ", STR_PAD_RIGHT);
				echo str_pad($profile["start"], 18, " ", STR_PAD_RIGHT);
				echo str_pad($profile["stop"], 18, " ", STR_PAD_RIGHT);
				echo str_pad($profile["count"], 8, " ", STR_PAD_RIGHT);

				$returned = false;

				foreach( $colors as $color => $data )
				{
					if( $profile["diff"] >= $data[0] && $profile["diff"] <= $data[1] )
					{
						$returned = true;

						echo "<font color=\"" . $color . "\">";
						echo str_pad(sprintf("%.15f", $profile["diff"]), 20, " ", STR_PAD_RIGHT);
						echo "</font>";
					}
				}

				if( !$returned )
				{
					echo str_pad(sprintf("%.15f", $profile["diff"]), 20, " ", STR_PAD_RIGHT);
				}

				$returned = false;

				foreach( $colors as $color => $data )
				{
					if( $profile["avg"] >= $data[0] && $profile["avg"] <= $data[1] )
					{
						$returned = true;

						echo "<font color=\"" . $color . "\">";
						echo str_pad(sprintf("%.15f", $profile["avg"]), 20, " ", STR_PAD_RIGHT);
						echo "</font>";
					}
				}

				if( !$returned && $profile["avg"] <= $colors["green"][0] )
				{
					echo "<font color=\"green\">";
					echo str_pad(sprintf("%.15f", $profile["avg"]), 20, " ", STR_PAD_RIGHT);
					echo "</font>";
				}
				else if( !$returned )
				{
					echo str_pad(sprintf("%.15f", $profile["avg"]), 20, " ", STR_PAD_RIGHT);
				}
				
				echo str_pad($profile["realmem"], 10, " ", STR_PAD_RIGHT);
				echo $profile["emalloc"];
				echo "\n";
			}

			$first = array_shift(self::$profiles);
			echo "\n";
			echo str_pad(sprintf("%.12f", $amount) . "  s", 66 + $padName, " ", STR_PAD_LEFT);
			echo "\n";
			echo str_pad(sprintf("%.12f", $amount * 1000) . " ms", 66 + $padName, " ", STR_PAD_LEFT);
			
			echo "\n\n";
			echo "</pre>\n";
		}
		
		return;
	}
	
	
	
	/*
	** private
	*/

	
	
	/**
	 * reset a single profile by name
	 * @param $name string
	 * @return void
	 */
    private static function resetProfile($name)
    {
        self::$profiles[$name] = array("start"   => false,
									   "stop"    => false,
									   "count"   => 0,
									   "diff"    => 0,
									   "avg"     => 0,
									   "realmem" => 0,
									   "emalloc" => 0,
									   "_add"    => 0,
									   "_run"    => 0,);
    }
}

// create a shorter name for profiler
class_alias('Yamp_Core_Model_Profiler', 'Profiler');
//class Profiler extends Yamp_Core_Model_Profiler {}
