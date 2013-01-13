<?php

if($this->ex(1)==='QUIT') {
	if(isset($this->authenticatedUsers[$messenger])) {
		trace('user deleted');
		unset($this->authenticatedUsers[$messenger]);
	}
}

// EOF
