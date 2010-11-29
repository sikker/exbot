<?php

if(isset($this->ex[4]))
{
	$data = $this->module_data('war');
	$message = FALSE;
	$notice = FALSE;
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
				$message = 'War started: ' . $name . ', woohoo!';
			}
			break;
		case 'addname':
			if( ! isset($this->ex[5]))
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
