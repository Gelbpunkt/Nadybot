<?php

namespace Nadybot\Modules\TIMERS_MODULE;

/**
 * @author Tyrence (RK2)
 *
 * @Instance
 *
 * Commands this class contains:
 *	@DefineCommand(
 *		command     = 'countdown',
 *		accessLevel = 'rl',
 *		description = 'Start a 5-second countdown',
 *		help        = 'countdown.txt',
 *		alias		= 'cd'
 *	)
 */
class CountdownController {

	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public $moduleName;

	/**
	 * @var \Nadybot\Core\DB $db
	 * @Inject
	 */
	public $db;

	/**
	 * @var \Nadybot\Core\Nadybot $chatBot
	 * @Inject
	 */
	public $chatBot;

	/**
	 * @var \Nadybot\Core\AccessManager $accessManager
	 * @Inject
	 */
	public $accessManager;

	/**
	 * @var \Nadybot\Core\Text $text
	 * @Inject
	 */
	public $text;

	/**
	 * @var \Nadybot\Core\Util $util
	 * @Inject
	 */
	public $util;
	
	private $lastCountdown = 0;

	/**
	 * @HandlesCommand("countdown")
	 * @Matches("/^countdown$/i")
	 * @Matches("/^countdown (.+)$/i")
	 */
	public function countdownCommand($message, $channel, $sender, $sendto, $args) {
		$message = "GO GO GO";
		if (count($args) == 2) {
			$message = $args[1];
		}

		if ($this->lastCountdown >= (time() - 30)) {
			$msg = "You can only start a countdown once every 30 seconds.";
			$sendto->reply($msg);
			return;
		}

		$this->lastCountdown = time();

		for ($i = 5; $i > 0; $i--) {
			if ($i == 5) {
				$color = "<red>";
			} elseif ($i == 4) {
				$color = "<red>";
			} elseif ($i == 3) {
				$color = "<orange>";
			} elseif ($i == 2) {
				$color = "<orange>";
			} elseif ($i == 1) {
				$color = "<orange>";
			}
			$msg = "$color-------&gt; $i &lt;-------<end>";
			$sendto->reply($msg);
			sleep(1);
		}

		$msg = "<green>------&gt; $message &lt;-------<end>";
		$sendto->reply($msg);
	}
}
