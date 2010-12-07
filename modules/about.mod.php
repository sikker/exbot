<?php

if(isset($this->ex[4]))
{
	$data = $this->module_data('about');
	if(isset($this->ex[5]))
	{
		// Combine everything after the fourth segment into a string
		$combined = $this->ex;
		for($i=0;$i<5;$i++)
		{
			unset($combined[$i]);
		}
		
		$data[ $this->ex[4] ] = implode(' ', $combined);
		$this->module_data('about', $data);
	}
	elseif(isset($data[ $this->ex[4] ]))
	{
		$this->send_data('PRIVMSG', (preg_match('/^#/', $channel) ? $channel : $messenger) . ' :' . $this->ex[4] . ' is ' . $data[ $this->ex[4] ]);
	}
	else
	{
		$this->send_data('NOTICE', $messenger . ' :I don\'t know about ' . $this->ex[4]);
	}
}

// EOF
