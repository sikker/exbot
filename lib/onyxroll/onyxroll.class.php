<?php

class OnyxRoll {

	const AS_ARRAY = 0;
	const AS_STRING = 1;
	
	private $dicePool = 5;
	private $chanceRoll = false;
	private $targetNumber = 8;
	private $explodeThreshold = 10;
	private $explodeDice = true;
	private $onesSubtract = false;

	private $results = array();
	private $resultsString = '';
	private $successes = 0;

	public function execute($type = OnyxRoll::AS_ARRAY) {
		$results = $this->results;
		$resultsString = $this->resultsString;
		$successes = $this->successes;
	
		$dicePool = $this->getDicePool();
		$targetNumber = $this->getTargetNumber();
		$explodeThreshold = $this->getExplodeThreshold();
		$explodeDice = $this->isExplodeDice();
		$onesSubtract = $this->isOnesSubtract();
		$chanceRoll = $this->isChanceRoll();

		if($chanceRoll) {
			$dicePool = 1;
			$targetNumber = 10;
		}
		
		for($i = 0; $i < $dicePool; $i++) {
			$results[] = rand(1, 10);
			$resultsString .= $results[$i];

			if($results[$i] >= $targetNumber) {
				$successes++;
			}

			if($onesSubtract && $results[$i] === 1) {
				$successes--;
			}
			
			if($results[$i] >= $explodeThreshold && $explodeDice) {
				$resultsString .= ' ( ';
				$subRoll = new OnyxRoll();
				$subRoll->setDicePool(1);
				$subRoll->setChanceRoll($chanceRoll);
				$subRoll->setTargetNumber($targetNumber);
				$subRoll->setExplodeThreshold($explodeThreshold);
				$subRoll->setExplodeDice($explodeDice);
				$subRoll->setOnesSubtract($onesSubtract);

				$subResults = $subRoll->execute();
				$subResultsString = $subRoll->getResults(OnyxRoll::AS_STRING);
				$results[$i] = array($results[$i], $subResults);
				$resultsString .= $subResultsString . ') ';
				$successes += $subRoll->getSuccesses();
			} else {
				$resultsString .= ' ';
			}
		}

		$this->successes = $successes;
		$this->results = $results;
		$this->resultsString = $resultsString;
		return $this->getResults($type);
	}

	public function getResults($type = OnyxRoll::AS_ARRAY) {
		switch($type) {
			case OnyxRoll::AS_STRING:
				return $this->resultsString;
				break;
			case OnyxRoll::AS_ARRAY:
			default:
				return $this->results;
				break;
		}
	}

	public function getSuccesses() {
		return $this->successes;
	}

	public function getDicePool() {
		return $this->dicePool;
	}
	
	public function setDicePool($dicePool) {
		$this->dicePool = (int) $dicePool;
	}

	public function isChanceRoll() {
		return $this->chanceRoll;
	}

	public function setChanceRoll($chanceRoll) {
		$this->chanceRoll = (bool) $chanceRoll;
	}

	public function getTargetNumber() {
		return $this->targetNumber;
	}

	public function setTargetNumber($targetNumber) {
		$this->targetNumber = (int) $targetNumber;
	}

	public function getExplodeThreshold() {
		return $this->explodeThreshold;
	}

	public function setExplodeThreshold($explodeThreshold) {
		$this->explodeThreshold = $explodeThreshold;
	}

	public function isExplodeDice() {
		return $this->explodeDice;
	}

	public function setExplodeDice($explodeDice) {
		$this->explodeDice = (bool) $explodeDice;
	}

	public function isOnesSubtract() {
		return $this->onesSubtract;
	}

	public function setOnesSubtract($onesSubtract) {
		$this->onesSubtract = (bool) $onesSubtract;
	}

}

// EOF
