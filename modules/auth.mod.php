<?php

if($this->ex(4) !== null){
	if($this->authenticate($messenger, $this->ex(4))) {
		$this->sendData('NOTICE', $messenger . ' :You are now identified.');
	} else {
		$this->sendData('NOTICE', $messenger . ' :Invalid identity.');
	}
}
