<?php

if($command !== NULL) {
	if(preg_match('/^\?.+$/', $command)) {
		$this->ex[4] = preg_replace('/^\?(.+)$/', "$1", $command);
		$command = "!about";
	}
}

// EOF
