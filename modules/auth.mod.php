<?php

if(isset($this->ex[4]))
{
	if($this->authenticate($messenger, $this->ex[4]))
	{
		$this->send_data('NOTICE', $messenger . ' :You are now identified.');
	}
	else
	{
		$this->send_data('NOTICE', $messenger . ' :Invalid identity.');
	}
}
