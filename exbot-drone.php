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

require_once('ircbot.class.php');

class ExBotDrone extends IRCBot {

	/**
	 * Extended constructor to override the nick with an appended d for "drone"
	 *
	 * @param	array
	 */
	public function __construct($config)
	{
		$config['nick'] = $config['nick'] . 'd';
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
 
		// Grab and strip the first real part of the message, i.e. the "command" part of the message 
		$command = str_replace(array(chr(10), chr(13)), '', $this->ex[3]);
		$command = str_replace(':', '', $command);

		sleep(2);
		$this->send_data('PRIVMSG', preg_replace('/(.*)d$/', "$1", $this->nick) . ' :ping'); 

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

}

// Start the bot
if( ! isset($argv[1]) && ! isset($_GET['network'])) die('No network parameter provided, aborting' . PHP_EOL);
require_once('config.php');
if( ! isset($config[ (isset($argv[1]) ? $argv[1] : $_GET['network']) ]) ) die('No such network in config, aborting' . PHP_EOL);

$bot = new ExBotDrone($config[ ( isset($argv[1]) ? $argv[1] : $_GET['network']) ]);

// EOF
