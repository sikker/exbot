<?php

/**
 * Extended PHP IRC Bot
 *
 * Heavily extended to work more generically, be modular, extensible and able to reload its 
 * modules without need for restarting the bot. It also has a service API for allowing 
 * certain commands to be run on each tick. Below is kept  the original header as per the
 * redistribution instructions. Modified to run primarily on the commandline.
 *
 * @author	Per Sikker Hansen <persikkerhansen@gmail.com>
 * @copyright	2010-2013, Per Sikker Hansen
 * @version	1.1.0
 */

// ---------------------------------------------------------------------------------
 
/**
 * Simple PHP IRC Bot
 *
 * PHP Version 5
 *
 * LICENSE: This source file is subject to Creative Commons Attribution
 * 3.0 License that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
 * and use this script commercially/non-commercially. My only requirement is that
 * you keep this header as an attribution to my work. Enjoy!
 *
 * @category   Chat Room Scipt
 * @package    Simple PHP IRC Bot
 * @author     Super3boy <admin@wildphp.com>
 * @copyright  2010, The Nystic Network
 * @license    http://creativecommons.org/licenses/by/3.0/
 * @link       http://wildphp.com (Visit for updated versions and more free scripts!)
 * @version    1.0.0 (Last updated 03-20-2010)
 *
 */

define('EXBOT_DIR', __DIR__ . '/');

set_time_limit(0);
ini_set('display_errors', 'on');

require_once('ircbot.class.php');
require_once('lib/simplestorage/simple_storage.class.php');
require_once('lib/exbotstorage/exbotstorage.class.php');

class ExBot extends IRCBot {

	// Various modules available to the bot. Stored as plain text for eval() use
	private $modules = array();

	// Various services available to the bot. Run before any modules are run. 
	// Stored as plain texct for eval() use
	private $services = array();

	// Storage engine
	private $storage;

	/**
	 * Construct item, opens the server connection, logs the bot in
	 *
	 * @param	array
	 */
	public function __construct($config) {
		// Load services and modules for the first time
		$this->reloadServices();
		$this->reloadModules();
		
		parent::__construct($config);

		// Create a simplestorage instance
		$this->storage = new ExbotStorage($this->server);
	}

	/**
	 * This is the workhorse function, grabs the data from the server and displays it. Evaluates services
	 * and any modules the user may request through eval() - this approach is chosen to enable runtime 
	 * reloading of modules while keeping disk I/O at a minimum.
	 */
	public function main() {
		// Grab the data from this cycle of the IRC room, printing it for debugging purposes.
		$data = fgets($this->socket, 256);
		$this->trace($data);
		flush();

		// Prepare the exploded segments of the message packet
		$this->ex = explode(' ', $data);
		for($i=0;$i<count($this->ex);$i++) {
			$this->ex[$i] = trim($this->ex[$i]);
			$this->ex[$i] = trim($this->ex[$i]);
		}

		// Plays ping-pong with the server to stay connected.
		if($this->ex(0) == 'PING') {
			$this->sendData('PONG', $this->ex(1)); 
		}

		$messenger = preg_replace('/:(.+)!(.+)/', "$1", $this->ex(0));
		$channel = $this->ex(2);
		
		// Grab and strip the first real part of the message, i.e. the "command" part of the message.
		// This is so that services can override it.
		$command = str_replace(array(chr(10), chr(13)), '', $this->ex(3));
		$command = str_replace(':', '', $command);

		// We don't need the command signal if the user is PM'ing us directly
		if($channel===$this->nick) {
			$command = $this->commandSignal . $command;
		}

		// Before continuing to executing any commands, we'll first run all our services. This way
		// a service can override a command or similar (creating aliases, for example)
		foreach($this->services as $service) {
			eval($service);
		}
				
		// Check if this is a command intended for the bot, or a simple message to the crowd. Checks
		// if the command begins with the command signal and executes the appropriate, loaded module
		// if that is the case. If there is no such module, or the module hasn't been (re)loaded, 
		// nothing will happen.
		if(preg_match('/^'.$this->commandSignal.'(.+)$/', $command)) {
			$command = preg_replace('/^'.$this->commandSignal.'(.+)$/', "$1", $command);
			if(isset($this->modules[$command])) {
				eval($this->modules[$command]);
			}
		}
 
		// This delay is in place because otherwise the script won't wait until the bot is fully
		// connected, thus attempting to join a channel on no network, meaning the default channel
		// will never be joined.
		if($this->delay==10) {
			$this->joinChannel($this->autojoin);
		} else {
			$this->delay++;
		}

		sleep(1);
		$this->storage->flush();
	}

	/**
	 * Reloads all modules into the bot's memory for eval() use. Removes any  "<?php" tags 
	 * there may be in the file.
	 */
	private function reloadModules() {
		if($modules = glob(EXBOT_DIR . 'modules/*.mod.php')) {
			foreach($modules as $module) {
				$this->modules[basename($module, '.mod.php')] = preg_replace('/^\<\?php/', '', file_get_contents($module));
			}
		}
	}

	/**
	 * Reloads all services into the bot's memory for eval() use. removes any "<?php" tags
	 * there may be in the file.
	 */
	private function reloadServices() {
		if($services = glob(EXBOT_DIR . 'services/*.ser.php')) {
			foreach($services as $service) {
				$this->services[basename($service, '.ser.php')] = preg_replace('/^\<\?php/', '', file_get_contents($service));
			}
		}
	}

	private function ex($index = NULL) {
		if($index!==NULL) {
			return (isset($this->ex[$index]) ? $this->ex[$index] : NULL);
		}
		return implode(' ', $this->ex);
	}

}

// Start the bot
if( ! isset($argv[1]) && ! isset($_GET['network'])) die('No network parameter provided, aborting' . PHP_EOL);

if( ! file_exists(EXBOT_DIR) . 'config.php') {
	die('Rename config.php.example to config.php to get started.' . PHP_EOL);
}

require_once(EXBOT_DIR . 'config.php');
if( ! isset($config[ (isset($argv[1]) ? $argv[1] : $_GET['network']) ]) ) die('No such network in config, aborting' . PHP_EOL);

$bot = new ExBot($config[ ( isset($argv[1]) ? $argv[1] : $_GET['network']) ]);

while($bot->running()) {
	$bot->main();
}

// EOF
