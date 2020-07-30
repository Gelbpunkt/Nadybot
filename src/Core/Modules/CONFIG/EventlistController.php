<?php

namespace Nadybot\Core\Modules\CONFIG;

/**
 * @Instance
 *
 * Commands this controller contains:
 *	@DefineCommand(
 *		command       = 'eventlist',
 *		accessLevel   = 'guild',
 *		description   = 'Shows a list of all events on the bot',
 *		help          = 'eventlist.txt',
 *		defaultStatus = '1'
 *	)
 */
class EventlistController {

	/**
	 * @var \Nadybot\Core\Text $text
	 * @Inject
	 */
	public $text;

	/**
	 * @var \Nadybot\Core\DB $db
	 * @Inject
	 */
	public $db;

	/**
	 * This command handler shows a list of all events on the bot.
	 * Additionally, event type can be provided to show only events of that type.
	 *
	 * @HandlesCommand("eventlist")
	 * @Matches("/^eventlist$/i")
	 * @Matches("/^eventlist (.+)$/i")
	 */
	public function eventlistCommand($message, $channel, $sender, $sendto, $args) {
		$params = array();
		if (isset($args[1])) {
			$params []= $args[1];
			$cmdSearchSql = "WHERE type LIKE ?";
		}
	
		$sql = "
			SELECT
				type,
				description,
				module,
				file,
				status
			FROM
				eventcfg_<myname>
			$cmdSearchSql
			ORDER BY
				type ASC";
		$data = $this->db->query($sql, $params);
		$count = count($data);
	
		if ($count > 0) {
			$blob = '';
			foreach ($data as $row) {
				$on = $this->text->makeChatcmd('ON', "/tell <myname> config event $row->type $row->file enable all");
				$off = $this->text->makeChatcmd('OFF', "/tell <myname> config event $row->type $row->file disable all");
	
				if ($row->status == 1) {
					$status = "<green>Enabled<end>";
				} else {
					$status = "<red>Disabled<end>";
				}
	
				if ($row->description != '') {
					$blob .= "$row->type [$row->module] ($status): $on  $off - $row->description\n";
				} else {
					$blob .= "$row->type [$row->module] ($status): $on  $off\n";
				}
			}
	
			$msg = $this->text->makeBlob("Event List ($count)", $blob);
		} else {
			$msg = "No events were found.";
		}
		$sendto->reply($msg);
	}
}
