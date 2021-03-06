<?php

if( ! class_exists('OnyxRoll')) {
	require_once(EXBOT_DIR . 'lib/onyxroll/onyxroll.class.php');
}

$ex = $this->ex();
$roll = new OnyxRoll();
$matches = array();

if($this->ex(4) === 'help') {
	$this->sendData('PRIVMSG', $channel . ' :NWoD example: ' . $this->commandSignal . 'onyx-roll pool(5)');
	$this->sendData('PRIVMSG', $channel . ' :CWoD example: ' . $this->commandSignal . 'onyx-roll pool(5) target(6) ones-subtract no-explode');
	$this->sendData('PRIVMSG', $channel . ' :Result syntax: $successes [ $resultNumbers ($explodedResultNumbers) ]');
	$this->sendData('PRIVMSG', $channel . ' :-- Roll options: --');
	$this->sendData('PRIVMSG', $channel . ' :pool($num): Sets the dice pool to $num. Default 5.');
	$this->sendData('PRIVMSG', $channel . ' :target($num): Sets the target number to $num. Default 8.');
	$this->sendData('PRIVMSG', $channel . ' :explode($num): Sets the exploding dice threshold to $num. Default 10.');
	$this->sendData('PRIVMSG', $channel . ' :ones-subtract: With this flag on, each 1 rolled will remove a success. Default off.');
	$this->sendData('PRIVMSG', $channel . ' :no-explode: With this flag on, dice does not explode. Ignored if you use the explode() option. Default off.');
} else {
	if(preg_match('/pool\(([0-9]+)\)/', $ex, $matches)) {
		$roll->setDicePool($matches[1]);
	}

	if(preg_match('/no-explode/', $ex)) {
		$roll->setExplodeDice(false);
	}

	if(preg_match('/explode\(([0-9]+)\)/', $ex, $matches)) {
		$roll->setExplodeDice(true);
		$roll->setExplodeThreshold($matches[1]);
	}

	if(preg_match('/target\(([0-9]+)\)/', $ex, $matches)) {
		$roll->setTargetNumber($matches[1]);
	}

	if(preg_match('/ones-subtract/', $ex)) {
		$roll->setOnesSubtract(true);
	}

	$roll->execute();
	$successes = $roll->getSuccesses();
	$result = $roll->getResults(OnyxRoll::AS_STRING);

	$this->sendData('PRIVMSG', $channel . ' :Roll result: ' . $successes . 's [ ' . $result . ' ]');
}
