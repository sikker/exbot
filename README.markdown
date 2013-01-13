The exbot
================================

exbot is a lightweight, modular IRC bot script written in PHP. It is based on the IRCBot class found on wildphp.net, but has been heavily modified and is now fully modular, as well as more tuned to run on the commandline. The core philosophy when making this bot is simplicity, favoring a small codebase over heavy functionality. The bot is quite powerful and usable in a lot of ways, and can be extended to do most things, but it is not a powerhouse like the Ruby-based rbot or similar heavy-duty bots. Exbot is intended to be easy to understand when browsing through the code, and as such the amount of code needs to be limited.

How to use it
-------------------------
The human whose name is written in this not-- Nah. Basically, you download the source, extract it and configure a network in config.php -- a config.php.example is provided for you to follow. Once you've done that, open a commandline window (be that the MSDOS Prompt or an xterm) and cd to the directory in question, and run the exbot.php script, with an argument consisting of the array key you gave the network you configured. If we assume you went with a 'freenode' connection, the way to start the bot would be:
  php exbot.php freenode
Please note that you need "php" to be in your PATH environment variable. On Linux, it usually already is if you've installed it, but on MS Windows you need to configure it manually by right-clicking Computer and following a complex maze of Redmondesque Userfriendliness. More on that on Google :0)

How to command it
-------------------------
There are a bunch of built-in commands (called modules) you can use. These are:
 - about (saves and states information about things)
 - auth (by /msg'ing the bot with the password you specified in config, you can gain admin access)
  - join (if you have auth, you can make the bot join a channel)
  - nick (if you have auth, you can make the bot change its own name)
  - part (if you have auth, you can make the bot leave a channel)
  - reload (if you have auth, you can make the bot reload its modules and services)
  - quit (if you have auth, you can make the bot leave the server)
 - quote (saves and states funny quotes. Can give you random quotes if you don't specify anything.)
 - roll (rolls dice. Do !roll help for more info.
 - onyx-roll (rolls dice based on the systems designed by Onyx Path Publishing (formerly White Wolf).

How to extend it
-------------------------
There are two ways to extend exbot: Services and Modules. Services are scripts that are run on every "tick", that is, every time new data arrives at the server. These can be checks on whether a certain nick leaves the room, enters, says something extraordinary or similar, and taking appropriate action. Modules are command-sets invoked by the configurable command signal specified in config.php - by default it's !. Someone who says !foo in a channel the bot is in would invoke the "foo" module in the bot. Services are placed in services/, modules in modules/ - simple, rite?

How to hack it
-------------------------
Hacking on the core is pretty easy, as almost everything is commented thoroughly. Exbot was developed with adaption to specific needs in mind!
