<?php

/**
 * Extended PHP IRC Bot
 *
 * Heavily extended by Per Sikker Hansen to work more generically, be modular and extensible
 * and able to reload it's modules without need for restarting the bot. It also has a service
 * API for allowing certain commands to be run on each tick. Below is kept  the original header
 * as per the redistribution instructions. Modified to run primarily on the commandline.
 *
 * @author	Per Sikker Hansen <per@sikker-hansen.dk>
 * @copyright	2010, Per Sikker Hansen
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

define('EXBOT_DIR', 'EDITTHIS');

set_time_limit(0);
ini_set('display_errors', 'on');

require_once('ircbot.class.php');

class ExBot extends IRCBot {

	// Various modules available to the bot. Stored as plain text for eval() use
	private $modules = array();

	// Various services available to the bot. Run before any modules are run. 
	// Stored as plain texct for eval() use
	private $services = array();

	/**
	 * Construct item, opens the server connection, logs the bot in
	 *
	 * @param	array
	 */
	public function __construct($config)
	{
		// Load services and modules for the first time
		$this->reload_services();
		$this->reload_modules();

		parent::__construct($config);		
	}

	/**
	 * This is the workhorse function, grabs the data from the server and displays it. Evaluates services
	 * and any modules the user may request through eval() - this approach is chosen to enable runtime reloading
	 * of modules while keeping disk I/O at a minimum.
	 */
	protected function main()
	{
		// Grab the data from this cycle of the IRC room, printing it for debugging purposes.
		$data = fgets($this->socket, 256);
		echo $data;
		flush();

		// Prepare the exploded segments of the message packet
		$this->ex = explode(' ', $data);
		for($i=0;$i<count($this->ex);$i++)
		{
			$this->ex[$i] = trim($this->ex[$i]);
			$this->ex[$i] = trim($this->ex[$i]);
		}

		// Plays ping-pong with the server to stay connected.
		if($this->ex[0] == 'PING')
		{
			$this->send_data('PONG', $this->ex[1]); 
		}

		$messenger = preg_replace('/:(.+)!(.+)/', "$1", $this->ex[0]);
		$channel = $this->ex[2];
		echo $channel ."\n";
		
		// Grab and strip the first real part of the message, i.e. the "command" part of the message 
		$command = str_replace(array(chr(10), chr(13)), '', $this->ex[3]);
		$command = str_replace(':', '', $command);

		// We don't need the command signal if the user is PM'ing us directly
		if($channel===$this->nick)
		{
			$command = $this->command_signal . $command;
		}

		// Before continuing to executing any commands, we'll first run all our services. This way
		// a service can override a command or similar (creating aliases, for example)
		foreach($this->services as $service)
		{
			eval($service);
		}
				
		// Check if this is a command intended for the bot, or a simple message to the crowd. Checks
		// if the command begins with "!" and executes the appropriate, loaded module if that is the case.
		// If there is no such module, or the module hasn't been (re)loaded, nothing will happen.
		if(preg_match('/^'.$this->command_signal.'(.+)$/', $command))
		{
			$command = preg_replace('/^'.$this->command_signal.'(.+)$/', "$1", $command);
			if(isset($this->modules[$command]))
			{
				eval($this->modules[$command]);
			}
		}
 
		// This delay is in place because otherwise the script won't wait until the bot is fully
		// connected, thus attempting to join a channel on no network, meaning the default channel
		// will never be joined.
		if($this->delay==10)
		{
			$this->join_channel($this->autojoin);
		}
		else
		{
			$this->delay++;
		}

		$this->main();
	}

	/**
	 * Reloads all modules into the bot's memory for eval() use. Removes any  "<?php" tags 
	 * there may be in the file.
	 */
	private function reload_modules()
	{
		if($modules = glob(EXBOT_DIR . 'modules/*.mod.php'))
		{
			foreach($modules as $module)
			{
				$this->modules[basename($module, '.mod.php')] = preg_replace('/^\<\?php/', '', file_get_contents($module));
			}
		}
	}

	/**
	 * Reloads all services into the bot's memory for eval() use. removes any "<?php" tags
	 * there may be in the file.
	 */
	private function reload_services()
	{
		if($services = glob(EXBOT_DIR . 'services/*.ser.php'))
		{
			foreach($services as $service)
			{
				$this->services[basename($service, '.ser.php')] = preg_replace('/^\<\?php/', '', file_get_contents($service));
			}
		}
	}

	/**
	 * Returns data related to the module in question. If $new_value is set, the data will be replaced.
	 *
	 * @param	string	the name of the module. The file will be data/$module.dat.php
	 * @param	mixed	the data to be stored. Can be a string, array, object, or anything else
	 * @return	mixed	the data the module has stored.
	 */
	protected function module_data($module, $new_value = NULL)
	{
		include(EXBOT_DIR . 'data/' . $module . '.dat.php');
		if($new_value!==NULL)
		{
			$new_value = $this->base64_encode_recursive($new_value);
			$service_data = $this->base64_encode_recursive($service_data);

			file_put_contents(EXBOT_DIR . 'data/' . $module . '.dat.php',
'<?php

$module_data = unserialize(\''.serialize($new_value).'\');
$service_data = unserialize(\''.serialize($service_data).'\');

// EOF');
			return $new_value;
		}
		return $this->base64_decode_recursive($module_data);
	}

	private function base64_encode_recursive($data)
	{
		if(is_array($data))
		{
			$new_data = array();
			foreach($data as $key=>$value)
			{
				$new_data[ base64_encode($key) ] = $this->base64_encode_recursive($value);
			}
			return $new_data;
		}
		else
		{
			return base64_encode($data);
		}
	}

	private function base64_decode_recursive($data)
	{
		if(is_array($data))
		{
			$new_data = array();
			foreach($data as $key=>$value)
			{
				$new_data[ base64_decode($key) ] = $this->base64_decode_recursive($value);
			}
			return $new_data;
		}
		else
		{
			return base64_decode($data);
		}
	}

	/**
	 * Returns the data related to the service in question. If $new_value is set, the data will be replaced.
	 * 
	 * @param	string	the name of the service. The file will be data/$service.dat.php
	 * @param	mixed	the data to be stored. Can be a string, array, object or anything else
	 * @return	mixed	the data the service has stored
	 */
	protected function service_data($service, $new_value = NULL)
	{
		include(EXBOT_DIR . 'data/' . $module . '.dat.php');
		if($new_value!==NULL)
		{
			$new_value = $this->base64_encode_recursive($new_value);
			$module_data = $this->base64_encode_recursive($module_data);

			file_put_contents(EXBOT_DIR . 'data/' . $module . '.dat.php',
'<?php

$module_data = unserialize(\''.serialize($service_data).'\');
$service_data = unserialize(\''.serialize($new_value).'\');

// EOF');
			return $new_value;
		}
		return $this->base64_decode_recursive($service_data);
	}

}

// Start the bot
if( ! isset($argv[1]) && ! isset($_GET['network'])) die('No network parameter provided, aborting' . PHP_EOL);
require_once(EXBOT_DIR . 'config.php');
if( ! isset($config[ (isset($argv[1]) ? $argv[1] : $_GET['network']) ]) ) die('No such network in config, aborting' . PHP_EOL);

$bot = new ExBot($config[ ( isset($argv[1]) ? $argv[1] : $_GET['network']) ]);

// EOF
