<?php

if($this->ex(4) !== null) {
	if($this->ex(5) !== null) {
		// Combine everything after the fourth segment into a string
		$combined = $this->ex;
		for($i=0;$i<5;$i++) {
			unset($combined[$i]);
		}
		
		$this->storage->put('module.about.' . $this->ex(4), implode(' ', $combined));
	} elseif($this->storage->get('module.about.' . $this->ex(4)) !== null) {
		$this->sendData('PRIVMSG', (preg_match('/^#/', $channel) ? $channel : $messenger) . ' :' . $this->ex(4) . ' is ' . $this->storage->get('module.about.' . $this->ex(4)));
	} else {
		$this->sendData('NOTICE', $messenger . ' :I don\'t know about ' . $this->ex(4));
	}
}

// EOF
