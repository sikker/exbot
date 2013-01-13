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

class IRCBot {

	// -- // Internal configuration variables. These are changed in config.php

	// Whether or not to trace and log runtime debug information. Set in $config
	protected $trace;
	protected $trace_log;

	// Whether or not to respond to user input with a NOTICE
	protected $notice_response;

	// The root password for the bot for restricted actions (like reloading modules)
	protected $password;

	// When the root session as mentioned above would expire
	protected $session_expire;

	// What signal the bot is going to respond to. Something like "!" or "."
	protected $command_signal;


	// -- // Content memory variables. These hold data needed for the bot to operate

	// This is going to hold our TCP/IP connection
	protected $socket;

	// This is going to hold all of the messages that hit the server
	protected $ex = array();

	// The name of the server we're connected to, for logfile naming purposes
	protected $server;

	// The current nick of the bot
	protected $nick;

	// A record of which channels the bot is currently in
	protected $channels = array();

	// The channels the bot should automatically join on startup
	protected $autojoin = array();

	// An integer containing the number of runs we've waited before attempting
	// to join a channel. This is because often the connection is not finished
	// before the main() method attempts to join us to a channel.
	protected $delay = 0;

	/**
	 * Construct item, opens the server connection, logs the bot in
	 *
	 * @param	array
	 */
	protected function __construct($config)
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
	}

	/**
	 * Logs the bot in on the server
	 *
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
 	 */
	protected function login($nick, $name, $domain, $password)
	{
		$this->send_data('USER', $nick.' '.$domain.' '.$nick.' :'.$name);
		$this->send_data('NICK', $nick);
		if($password!=='')
		{
			$this->send_data('PRIVMSG', 'NickServ :identify ' . $password);
		}
	}

	/**
	 * Displays stuff to the commandline and sends data to the server.
	 */
	protected function send_data($command, $message = NULL)
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
	protected function join_channel($channel)
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
	protected function part_channel($channel)
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
	 * Start sessions with a superuser or check integrity of a user
	 */
	protected function authenticate($nick, $password = FALSE)
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
	protected function trace($message)
	{
		if($this->trace) echo $message . "\n";
		if($this->trace_log) file_put_contents('trace/' . $this->server . '.log', $message);
	}

}

// EOF
