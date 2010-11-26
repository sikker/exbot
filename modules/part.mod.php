<?php

if(isset($this->ex[4]))
{
	if($this->authenticate($messenger))
	{
		$this->part_channel($this->ex[4]);
	}
	else
	{
		$this->send_data('NOTICE', $messenger . ' :You are not authorized to do that.');
	}
}

// EOF
