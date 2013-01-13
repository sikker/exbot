<?php

if($this->ex(4) !== null) {
	if($this->authenticate($messenger)) {
		$this->partChannel($this->ex[4]);
	} else {
		$this->sendData('NOTICE', $messenger . ' :You are not authorized to do that.');
	}
}

// EOF
