<?php

if(isset($this->ex[4]))
{
	include('data/about.dat.php');

	if(isset($this->ex[5]))
	{
		// Combine everything after the fourth segment into a string
		$combined = $this->ex;
		for($i=0;$i<5;$i++)
		{
			unset($combined[$i]);
		}
		
		$MODDATA[ $this->ex[4] ] = implode(' ', $combined);
		file_put_contents('data/about.dat.php', '<?php'."\n\n\$MODDATA = unserialize('".serialize($MODDATA)."');\n\n//EOF");
	}
	else
	{
		$this->send_data('PRIVMSG', (preg_match('/^#/', $channel) ? $channel : $messenger) . ' :' . $this->ex[4] . ' is ' . $MODDATA[ $this->ex[4] ]);
	}
	
}

// EOF
