<?php

if($this->authenticate($messenger)) {
	$this->partChannel($this->channels);
	$this->sendData('QUIT', '');
	$this->quit(true);
} else {
	$this->sendData('NOTICE', $messenger . ' :You are not authorized to do that.');
}

// EOF
