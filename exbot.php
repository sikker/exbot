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

set_time_limit(0);
ini_set('display_errors', 'on');

class IRCBot {

	// -- // Internal configuration variables. These are changed in config.php

	// Whether or not to trace and log runtime debug information. Set in $config
	private $trace;
	private $trace_log;

	// Whether or not to respond to user input with a NOTICE
	private $notice_response;

	// The root password for the bot for restricted actions (like reloading modules)
	private $password;

	// When the root session as mentioned above would expire
	private $session_expire;

	// What signal the bot is going to respond to. Something like "!" or "."
	private $command_signal;


	// -- // Content memory variables. These hold data needed for the bot to operate

	// This is going to hold our TCP/IP connection
	private $socket;

	// This is going to hold all of the messages that hit the server
	private $ex = array();

	// The name of the server we're connected to, for logfile naming purposes
	private $server;

	// The current nick of the bot
	private $nick;

	// A record of which channels the bot is currently in
	private $channels = array();

	// The channels the bot should automatically join on startup
	private $autojoin = array();

	// Various modules available to the bot. Stored as plain text for eval() use
	private $modules = array();

	// Various services available to the bot. Run before any modules are run. 
	// Stored as plain texct for eval() use
	private $services = array();

	// A list of authenticated root users for the bot and when they started the session
	private $authenticated_users = array();

	// An integer containing the number of runs we've waited before attempting
	// to join a channel. This is because often the connection is not finished
	// before the main() method attempts to join us to a channel.
	private $delay = 0;

	/**
	 * Construct item, opens the server connection, logs the bot in
	 *
	 * @param	array
	 */
	public function __construct($config)
	{
		$this->socket = fsockopen($config['server'], $config['port']);
		$this->login($config['nick'], $config['name'], $config['domain'], $config['pass']);

		// Sets global bot variables grabbed from the config array
		$this->server = $config['server'];
		$this->nick = $config['nick'];
		$this->autojoin = $config['channel'];
		$this->password = $config['auth_password'];
		$this->session_expire = $config['session_expire'];
		$this->trace = $config['trace'];
		$this->trace_log = $config['trace_log'];
		$this->notice_response = $config['notice_response'];
		$this->command_signal = $config['command_signal'];

		// Load services and modules for the first time
		$this->reload_services();
		$this->reload_modules();

		$this->main();
	}

	/**
	 * Logs the bot in on the server
	 *
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
 	 */
	private function login($nick, $name, $domain, $password)
	{
		$this->send_data('USER', $nick.' '.$domain.' '.$nick.' :'.$name);
		$this->send_data('NICK', $nick);
		if($password!=='')
		{
			$this->send_data('PRIVMSG', 'NickServ :identify ' . $password);
		}
	}

	/**
	 * This is the workhorse function, grabs the data from the server and displays it. Evaluates services
	 * and any modules the user may request through eval() - this approach is chosen to enable runtime reloading
	 * of modules while keeping disk I/O at a minimum.
	 */
	private function main()
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
	 * Displays stuff to the commandline and sends data to the server.
	 */
	private function send_data($command, $message = NULL)
	{
		// Some users prefer their bots to remain silent, rather than sending notices
		if($command==='NOTICE' && $this->notice_response===FALSE) return;

		if($message == NULL)
		{
			fputs($this->socket, $command."\r\n");
			echo "***".$command."***\r\n";
		}
		else
		{
			fputs($this->socket, $command.' '.$message."\r\n");
			echo "***".$command." ".$message."***\r\n";
		}
	}

	/**
	 * Joins a channel, used in the join module. Recursive if an array is provided.
	 */
	private function join_channel($channel)
	{
		if(is_array($channel))
		{
			foreach($channel as $chan)
			{
				$this->join_channel($chan);
			}

		}
		else
		{
			if(isset($this->channels[$channel])) return;
			$this->send_data('JOIN', $channel);
			$this->channels[$channel] = $channel;
		}
	}

	/**
	 * Parts with a channel, used in the part module. Recursive if an array is provided.
	 */
	private function part_channel($channel)
	{
		if(is_array($channel))
		{
			foreach($channel as $chan)
			{
				$this->part_channel($chan);
			}
		}
		if( ! isset($this->channels[$channel])) return;
		$this->send_data('PART', $channel);
		unset($this->channels[$channel]);
	}

	/**
	 * Reloads all modules into the bot's memory for eval() use. Removes any  "<?php" tags 
	 * there may be in the file.
	 */
	private function reload_modules()
	{
		if($modules = glob('modules/*.mod.php'))
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
		if($services = glob('services/*.ser.php'))
		{
			foreach($services as $service)
			{
				$this->services[basename($service, '.ser.php')] = preg_replace('/^\<\?php/', '', file_get_contents($service));
			}
		}
	}

	/**
	 * Start sessions with a superuser or check integrity of a user
	 */
	private function authenticate($nick, $password = FALSE)
	{
		// if the user is authorizing, save his session
		if($password!==FALSE && $password===$this->password)
		{
			$this->authenticated_users[$nick] = time();
		} 

		// if a module is checking whether the user is authenticated or not, check the session
		if(isset($this->authenticated_users[$nick]) && $this->authenticated_users[$nick]>(time()-$this->session_expire))
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Trace data. If trace is true, it will be outputted to the commandline. If trace_log is true,
	 * it will be saved in a logfile. Neither are mutually exclusive.
	 */
	private function trace($message)
	{
		if($this->trace) echo $message . "\n";
		if($this->trace_log) file_put_contents('trace/' . $this->server . '.log', $message);
	}

	/**
	 * Returns data related to the module in question. If $new_value is set, the data will be replaced.
	 *
	 * @param	string	the name of the module. The file will be data/$module.dat.php
	 * @param	mixed	the data to be stored. Can be a string, array, object, or anything else
	 * @return	mixed	the data the module has stored.
	 */
	private function module_data($module, $new_value = NULL)
	{
		include('data/' . $module . '.dat.php');
		if($new_value!==NULL)
		{
			file_put_contents('data/' . $module . '.dat.php',
'<?php

$module_data = unserialize(\''.serialize($new_value).'\');
$service_data = unserialize(\''.serialize($service_data).'\');

// EOF');
			return $new_value;
		}
		return $module_data;
	}

	/**
	 * Returns the data related to the service in question. If $new_value is set, the data will be replaced.
	 * 
	 * @param	string	the name of the service. The file will be data/$service.dat.php
	 * @param	mixed	the data to be stored. Can be a string, array, object or anything else
	 * @return	mixed	the data the service has stored
	 */
	private function service_data($service, $new_value = NULL)
	{
		include('data/' . $service . '.dat.php');
		if($new_value!==NULL)
		{
			file_put_contents('data/' . $service . '.dat.php',
'<?php

$module_data = unserialize(\''.serialize($module_data).'\');
$service_data = unserialize(\''.serialize($new_value).'\');

// EOF');
			return $new_value;
		}
		return $service_data;
	}

}

// Start the bot
if( ! isset($argv[1]) && ! isset($_GET['network'])) die('No network parameter provided, aborting' . PHP_EOL);
require_once('config.php');
if( ! isset($config[ (isset($argv[1]) ? $argv[1] : $_GET['network']) ]) ) die('No such network in config, aborting' . PHP_EOL);

$bot = new IRCBot($config[ ( isset($argv[1]) ? $argv[1] : $_GET['network']) ]);

// EOF
