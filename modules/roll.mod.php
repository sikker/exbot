<?php

if( ! class_exists('Dice'))
{
	class Dice {

		private function d10()
		{
			return rand(1, 10);
		}
		
		static function wod($pool, $reroll_threshold = 10)
		{
			$successes = 0;
			for($i=0;$i<$pool;$i++)
			{
				$result = self::d10();
				echo "result: $result\n";
				if($result>7)
				{
					$successes++;
				}
				if($result>=$reroll_threshold)
				{
					while($reroll_result = self::d10())
					{
						echo "reroll result: $reroll_result\n";
						if($reroll_result>7) $successes++;
						if($reroll_result<$reroll_threshold) break;
					}
				}
			}
			
			if($successes>0)
			{
				return $successes . ' ' . ($successes!==1 ? 'successes' : 'success');
			}
			else
			{
				return 'Failure';
			}
		}

		static function owod($pool, $difficulty, $reroll_tens = FALSE)
		{
			$successes = 0;
			$botch = TRUE; // default to a botch
			for($i=0;$i<$pool;$i++)
			{
				$result = self::d10();
				echo "result: $result\n";
				if($result===10 && $reroll_tens===TRUE)
				{
					while($reroll_result = self::d10())
					{
						echo "reroll result: $reroll_result\n";
						if($reroll_result>=$difficulty) $successes++;
						if($reroll_result===1) $successes--;
						if($reroll_result!==10) break;
					}
				}
				if($result>=$difficulty)
				{
					$botch = FALSE; // if we have just one success we won't botch
					$successes++;
				}
				elseif($result===1)
				{
					$successes--;
				}
				else
				{
					$botch = FALSE; // If there was neither a success nor a one, no botch, just failure
				}
			}
			if($successes<0) $successes = 0;
			if($successes===0 && $botch===TRUE)
			{
				return 'Botch!';
			}
			elseif($successes>0)
			{
				return $successes . ' ' . ($successes!==1 ? 'successes' : 'success');				
			}
			else
			{
				return 'Failure';
			}
		}

		static function wod_chanceroll()
		{
			$result = self::d10();
			if($result===1) return 'Dramatic failure!';

			$successes = 0;
			if($result===10)
			{
				$successes++;
				while($result = self::d10())
				{
					if($result!==10)
					{
						break;
					}
					$successes++;
				}
			}
			
			if($successes>0)
			{
				return $successes . ' ' . ($successes!==1 ? 'successes' : 'success');
			}
			else
			{
				return 'Failure';
			}
		}
		
	}
}

if(isset($this->ex[4]))
{
	if($this->ex[4]==='wod')
	{ // A World of Darkness dice roll
		if( ! isset($this->ex[5]))
		{ // No arguments, so Chance Roll
			$message = Dice::wod_chanceroll();
		}
		elseif(isset($this->ex[5]) && ! isset($this->ex[6]))
		{ // One argument, so a regular NWoD roll
			$message = Dice::wod($this->ex[5]);
		}
		elseif(isset($this->ex[5]) && isset($this->ex[6]) && is_numeric($this->ex[6]) && !isset($this->ex[7]))
		{ // Two arguments, both numeric, so an OWoD roll
			$message = Dice::owod($this->ex[5], $this->ex[6]);
		}
		elseif(isset($this->ex[5]) && isset($this->ex[6]) && !isset($this->ex[7]))
		{ // Two arguments, only the first numeric, so an NWoD roll with a different reroll threshold
			echo "reroll more\n";
			$message = Dice::wod($this->ex[5], (int) str_replace('-again', '', $this->ex[6]));
		}
		elseif(isset($this->ex[5]) && isset($this->ex[6]) && isset($this->ex[7]))
		{ // Three arguments, so an OWoD roll with rerolled tens
			echo "reroll tens\n";
			$message = Dice::owod($this->ex[5], $this->ex[6], TRUE);
		}
		else
		{
			$message = FALSE;
		}
	}
	elseif(preg_match('/^([0-9]+)d([0-9]+)$/', $this->ex[4], $parameters))
	{ // A regular XdY dice roll
		echo 'foo!!!';
		$rolls = array();
		for($i=0;$i<$parameters[1];$i++)
		{
			$rolls[] = rand(1, $parameters[2]);
		}
		$message = implode(', ', $rolls);
	}
	else
	{
		$message = FALSE;
	}

	if($message!==FALSE) $this->send_data('PRIVMSG', $channel . ' :Roll result: ' . $message);
}