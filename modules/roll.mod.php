<?php

$matches = array();
$ex = strtolower($this->ex());

if($this->ex(4) === 'help') {
	$this->send_data('PRIVMSG', $channel . ' :Roll syntax: ' . $this->command_signal . 'roll XdY: rolls X dice with Y sides.');
} elseif(preg_match('/([0-9]+)d([0-9]+)/', $ex, $matches)){
	$diceNum = $matches[1];
	$diceStrength = $matches[2];
	$result = array();
	for($i = 0; $i < $diceNum; $i++) {
		$result[] = rand(1, $diceStrength);
	}

	$this->send_data('PRIVMSG', $channel . ' :Result: ' . implode(', ', $result));
} else {
	$this->send_data('PRIVMSG', $channel . ' :Did you mean ' . $this->command_signal . 'onyx-roll ? If not, try !roll help');
}

// EOF
