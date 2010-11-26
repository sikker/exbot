<?php

if($this->authenticate($messenger))
{
	$this->reload_modules();
	$this->reload_services();
	$this->send_data('NOTICE', $messenger . ' :All modules and services has been reloaded.');
}
else
{
	$this->send_data('NOTICE', $messenger . ' :You are not authorized to do that.');
}
