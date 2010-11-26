<?php

if($this->ex[1]==='QUIT')
{
	if(isset($this->authenticated_users[$messenger]))
	{
		trace('user deleted');
		unset($this->authenticated_users[$messenger]);
	}
}

// EOF
