<?php

$data = $this->storage->get('module.quote.' . $channel);

if($this->ex(4) !== null && $this->ex(4)==='add') {
	if($this->ex(5) !== null) {
		// Combine everything after the fourth segment into a string
		$combined = $this->ex;
		for($i=0;$i<5;$i++) {
			unset($combined[$i]);
		}

		if($data === null) $data = array('last_id'=>0,'quotes'=>array());

		$id = $data['last_id'] +1;
		$data['last_id'] = $id;
		$data['quotes'][$id] = array('id'=>$id, 'quote'=>implode(' ', $combined), 'nick'=>$messenger);
		$this->send_data('NOTICE', $messenger . ' :Quote added with id #' . $id . '.');
		$this->storage->put('module.quote.' . $channel, $data);
	} else {
		$this->send_data('NOTICE', $messenger . ' :You need to provide the actual quote.');
	}
} elseif($this->ex(4) !== null && is_numeric($this->ex(4))) {
	if(isset($data['quotes'][$this->ex(4)])) {
		$message = ' :Quote: ' . $data['quotes'][$this->ex(4)]['quote'] . ' (#'.$this->ex(4).' by '.$data['quotes'][$this->ex(4)]['nick'].').';
		if(preg_match('/^#/', $channel)) {
			$this->send_data('PRIVMSG', $channel . $message);
		} else {
			$this->send_data('NOTICE', $messenger . $message);
		}
	} else {
		$this->send_data('NOTICE', $messenger . ' :No such quote.');
	}
} elseif($this->ex(4) !== null && $this->ex(4) === 'help') {
	if(preg_match('/^#/', $channel)) {
		$this->send_data('PRIVMSG', $channel . ' :Random quote: ' . $this->command_signal . 'quote');
		$this->send_data('PRIVMSG', $channel . ' :Specific quote: ' . $this->command_signal . 'quote $num');
		$this->send_data('PRIVMSG', $channel . ' :New quote: ' . $this->command_signal . 'quote add $text');
	} else {
		$this->send_data('NOTICE', $messenger . ' :Random quote: ' . $this->command_signal . 'quote');
		$this->send_data('NOTICE', $messenger . ' :Specific quote: ' . $this->command_signal . 'quote $num');
		$this->send_data('NOTICE', $messenger . ' :New quote: ' . $this->command_signal . 'quote add $text');
	}
} else {
	if($data === null || empty($data['quotes'])) {
		$message = ' :No quotes.';
	} else {
		$id = rand(1,$data['last_id']);
		$message = ' :Quote: ' . $data['quotes'][$id]['quote'] . ' (#' . $id . ' by ' . $data['quotes'][$id]['nick'] . ').'; 
	}

	if(preg_match('/^#/', $channel)) {
		$this->send_data('PRIVMSG', $channel . $message);
	} else {
		$this->send_data('NOTICE', $messenger . $message);
	}
}

// EOF
