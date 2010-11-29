<?php

if(isset($this->ex[4]))
{
	$data = $this->module_data('war');
	$message = FALSE;
	$notice = FALSE;
	// TODO: Some sort of purging/reminder routine, probably in a service
	switch($this->ex[4])
	{
		case 'start':
			if( ! isset($this->ex[5]))
			{
				$notice = 'No delay parameter given. Must be !war start DELAY DURATION';
			}
			elseif( ! isset($this->ex[6]))
			{
				$notice = 'No duration parameter given. Must be !war start DELAY DURATION';
			}
			else
			{
				$name = $data['names'][ rand(0,count($data['names'])-1) ];
				$delay = $this->ex[5];
				$duration = $this->ex[6];
				$message = 'Word war "' . $name . '" will start in ' . $delay . ' ' . ($delay!==1 ? 'minutes' : 'minute') . ' and will last for ' . $duration . ' ' . ($duration!==1 ? 'minutes' : 'minute');
				// TODO: Save the war data in the data array
			}
			break;
		case 'join':
			if( ! isset($this->ex[5]) || $this->ex[5] === 'last')
			{
				// Join the latest war, if any
			}
			else
			{
				// Join the desired war, if any
			}
			break;
		case 'leave':
			if( ! isset($this->ex[5]))
			{
				// NOTICE: You have to specify a war to leave
			}
			else
			{
				// Leave the desired war, if any
			}
			break;
		case 'stop':
			if( ! isset($this->ex[5]))
			{
				// NOTICE: you need to specify a war to stop
			}
			else
			{
				// Check if we're authed or if we started the war, and stop it if true
			}
			break;
		case 'name':
			if( ! isset($this->ex[5]) || $this->ex[5] === 'last')
			{
				$notice = 'No name given.';
			}
			else
			{
				$data['names'][] = $this->ex[5];
				$notice = 'Name added.';
			}
			break;
	}
	$this->module_data('war', $data);
	if($notice) $this->send_data('NOTICE', $messenger . ' :' . $notice);
	if($message) $this->send_data('PRIVMSG', $channel . ' :' . $message);
}

// EOF
