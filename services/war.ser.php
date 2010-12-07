<?php

$data = $this->module_data('war');

if(isset($data['wars']) && !empty($data['wars']))
{
	foreach($data['wars'] as $channelname => $wars)
	{
		foreach($wars as $war)
		{
			$name = $war['name'];
			$message = FALSE;
			if($war['end_time']<=time())
			{
				$message = "Word war $name has ended.";
				unset($data['wars'][$channelname][$name]);
			}
			elseif( ! isset($war['spoken_for']) && $war['start_time']<(time()+70) )
			{
				$name = $war['name'];
				$data['wars'][$channelname][$name]['spoken_for'] = 'soon';
				$message = "Word war $name will begin in about a minute, get ready!";
			}
			elseif( isset($war['spoken_for']) && $war['spoken_for']==='soon' && $war['start_time']<(time()+5))
			{
				$data['wars'][$channelname][$name]['spoken_for'] = 'start';
				$minutes = ($war['end_time'] - $war['start_time']) /60;
				$message = "Word war $name has BEGUN! It will last for $minutes " . ($minutes!==1 ? 'minutes' : 'minute') . ".";
			}
			if( ! $message) continue;
			if( ! empty($war['participants'])){
				foreach($war['participants'] as $recipient)
				{
					$this->send_data('NOTICE', $recipient . ' :' . $message);
				}
			}
			$this->send_data('PRIVMSG', $channelname . ' :' .$message);
		}
	}
}

$this->module_data('war', $data);

// EOF
