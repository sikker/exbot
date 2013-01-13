<?php

if($this->authenticate($messenger)) {
	$this->reloadModules();
	$this->reloadServices();
	$this->sendData('NOTICE', $messenger . ' :All modules and services has been reloaded.');
} else {
	$this->sendData('NOTICE', $messenger . ' :You are not authorized to do that.');
}
