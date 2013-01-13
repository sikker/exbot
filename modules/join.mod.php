<?php

if(isset($this->ex[4]))
{
	if($this->authenticate($messenger))
	{
		$this->joinChannel($this->ex[4]);
	}
	else
	{
		$this->sendData('NOTICE', $messenger . ' :You are not authorized to do that.');
	}
}

// EOF
