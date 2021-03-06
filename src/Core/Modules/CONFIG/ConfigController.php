<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONFIG;

use ReflectionClass;
use Nadybot\Core\{
	AccessManager,
	CommandAlias,
	CommandManager,
	CommandReply,
	DB,
	EventManager,
	HelpManager,
	LoggerWrapper,
	Registry,
	SettingManager,
	SubcommandManager,
	Text,
};
use Nadybot\Core\DBSchema\{
	EventCfg,
	CmdCfg,
	Setting,
};

/**
 * @Instance
 */
class ConfigController {

	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public string $moduleName;

	/** @Inject */
	public Text $text;

	/** @Inject */
	public DB $db;

	/** @Inject */
	public CommandManager $commandManager;

	/** @Inject */
	public EventManager $eventManager;

	/** @Inject */
	public SubcommandManager $subcommandManager;

	/** @Inject */
	public CommandAlias $commandAlias;

	/** @Inject */
	public HelpManager $helpManager;

	/** @Inject */
	public SettingManager $settingManager;
	
	/** @Inject */
	public AccessManager $accessManager;
	
	/** @Logger */
	public LoggerWrapper $logger;

	/**
	 * @Setup
	 * This handler is called on bot startup.
	 */
	public function setup(): void {

		// construct list of command handlers
		$filename = [];
		$reflectedClass = new ReflectionClass($this);
		$className = Registry::formatName(get_class($this));
		foreach ($reflectedClass->getMethods() as $reflectedMethod) {
			if (preg_match('/command$/i', $reflectedMethod->name)) {
				$filename []= "{$className}.{$reflectedMethod->name}";
			}
		}
		$filename = implode(',', $filename);

		$this->commandManager->activate("msg", $filename, "config", "mod");
		$this->commandManager->activate("guild", $filename, "config", "mod");
		$this->commandManager->activate("priv", $filename, "config", "mod");

		$this->helpManager->register($this->moduleName, "config", "config.txt", "mod", "Configure Commands/Events");
	}

	/**
	 * This command handler lists list of modules which can be configured.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config$/i")
	 */
	public function configCommand(string $message, string $channel, string $sender, CommandReply $sendto, array $args): void {
		$blob = "<header2>Quick config<end>\n".
			"<tab>Org Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable guild') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable guild') . "\n" .
			"<tab>Private Channel Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable priv') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable priv') . "\n" .
			"<tab>Private Message Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable msg') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable msg') . "\n" .
			"<tab>ALL Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable all') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable all') . "\n\n\n";
	
		$sql = "SELECT ".
				"module, ".
				"SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) count_enabled, ".
				"SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) count_disabled ".
			"FROM ".
				"(SELECT module, status FROM cmdcfg_<myname> WHERE `cmdevent` = 'cmd' ".
					"UNION ".
				"SELECT module, status FROM eventcfg_<myname> ".
					"UNION ".
				"SELECT module, 2 FROM settings_<myname>) t ".
			"GROUP BY ".
				"module ".
			"ORDER BY ".
				"module ASC";
	
		$data = $this->db->query($sql);
		$count = count($data);
		foreach ($data as $row) {
			if ($row->count_enabled > 0 && $row->count_disabled > 0) {
				$a = "<yellow>Partial<end>";
			} elseif ($row->count_disabled === 0) {
				$a = "<green>Running<end>";
			} else {
				$a = "<red>Disabled<end>";
			}
	
			$c = $this->text->makeChatcmd("Configure", "/tell <myname> config $row->module");
	
			$on = "<black>On<end>";
			if ($row->count_disabled > 0) {
				$on = $this->text->makeChatcmd("On", "/tell <myname> config mod $row->module enable all");
			}
			$off = "<black>Off<end>";
			if ($row->count_enabled > 0) {
				$off = $this->text->makeChatcmd("Off", "/tell <myname> config mod $row->module disable all");
			}
			$blob .= "($on / $off / $c) " . strtoupper($row->module) . " ($a)\n";
		}
	
		$msg = $this->text->makeBlob("Module Config ($count)", $blob);
		$sendto->reply($msg);
	}

	/**
	 * This command handler turns a channel of all modules on or off.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config cmd (enable|disable) (all|guild|priv|msg)$/i")
	 */
	public function toggleChannelOfAllModulesCommand(string $message, string $channel, string $sender, CommandReply $sendto, array $args): void {
		$status = ($args[1] == "enable" ? 1 : 0);
		$sqlArgs = [];
		$confirmString = "all";
		if ($args[2] == "all") {
			$typeSql = "`type` = 'guild' OR `type` = 'priv' OR `type` = 'msg'";
		} else {
			 $typeSql = "`type` = ?";
			 $sqlArgs[] = $args[2];
			 $confirmString = "all " . $args[2];
		}
	
		$sql = "SELECT type, file, cmd, admin FROM cmdcfg_<myname> WHERE `cmdevent` = 'cmd' AND ($typeSql)";
		$data = $this->db->fetchAll(CmdCfg::class, $sql);
		foreach ($data as $row) {
			if (!$this->accessManager->checkAccess($sender, $row->admin)) {
				continue;
			}
			if ($status === 1) {
				$this->commandManager->activate($row->type, $row->file, $row->cmd, $row->admin);
			} else {
				$this->commandManager->deactivate($row->type, $row->file, $row->cmd);
			}
		}
	
		$sql = "UPDATE cmdcfg_<myname> SET `status` = ? WHERE (`cmdevent` = 'cmd' OR `cmdevent` = 'subcmd') AND ($typeSql)";
		$sqlArgs []= $status;
		$this->db->exec($sql, ...$sqlArgs);
	
		$msg = "Successfully <highlight>" . ($status === 1 ? "enabled" : "disabled") . "<end> $confirmString commands.";
		$sendto->reply($msg);
	}

	/**
	 * This command handler turns a channel of a single command, subcommand,
	 * module or event on or off.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config (subcmd|mod|cmd|event) (.+) (enable|disable) (priv|msg|guild|all)$/i")
	 */
	public function toggleChannelCommand(string $message, string $channel, string $sender, CommandReply $sendto, array $args): void {
		if ($args[1] === "event") {
			$temp = explode(" ", $args[2]);
			$event_type = strtolower($temp[0]);
			$file = $temp[1];
		} elseif ($args[1] === 'cmd' || $args[1] === 'subcmd') {
			$cmd = strtolower($args[2]);
			$type = $args[4];
		} else { // $args[1] == 'mod'
			$module = strtoupper($args[2]);
			$type = $args[4];
		}
	
		if ($args[3] == "enable") {
			$status = 1;
		} else {
			$status = 0;
		}
	
		$sqlArgs = [];
		if ($args[1] === "mod" && $type === "all") {
			$sql = "SELECT status, type, file, cmd, admin, cmdevent FROM cmdcfg_<myname> WHERE `module` = ?
						UNION
					SELECT status, type, file, '' AS cmd, '' AS admin, 'event' AS cmdevent FROM eventcfg_<myname> WHERE `module` = ? AND `type` != 'setup'";
			$sqlArgs = [$module, $module];
		} elseif ($args[1] === "mod" && $type !== "all") {
			$sql = "SELECT status, type, file, cmd, admin, cmdevent FROM cmdcfg_<myname> WHERE `module` = ? AND `type` = ?
						UNION
					SELECT status, type, file, cmd AS '', admin AS '', cmdevent AS 'event' FROM eventcfg_<myname> WHERE `module` = ? AND `type` = ? AND `type` != 'setup'";
			$sqlArgs = [$module, $type, $module, $event_type];
		} elseif ($args[1] === "cmd" && $type !== "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'cmd'";
			$sqlArgs = [$cmd, $type];
		} elseif ($args[1] === "cmd" && $type === "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `cmdevent` = 'cmd'";
			$sqlArgs = [$cmd];
		} elseif ($args[1] === "subcmd" && $type !== "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'subcmd'";
			$sqlArgs = [$cmd, $type];
		} elseif ($args[1] === "subcmd" && $type === "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `cmdevent` = 'subcmd'";
			$sqlArgs = [$cmd];
		} elseif ($args[1] === "event" && $file !== "") {
			$sql = "SELECT *, 'event' AS cmdevent FROM eventcfg_<myname> WHERE `file` = ? AND `type` = ? AND `type` != 'setup'";
			$sqlArgs = [$file, $event_type];
		} else {
			return;
		}
	
		/** @var CmdCfg[] $data */
		$data = $this->db->fetchAll(CmdCfg::class, $sql, ...$sqlArgs);
		
		if ($args[1] === 'cmd' || $args[1] === 'subcmd') {
			if (!$this->checkCommandAccessLevels($data, $sender)) {
				$msg = "You do not have the required access level to change this command.";
				$sendto->reply($msg);
				return;
			}
		}
	
		if (count($data) === 0) {
			if ($args[1] === "mod" && $type === "all") {
				$msg = "Could not find Module <highlight>$module<end>.";
			} elseif ($args[1] === "mod" && $type !== "all") {
				$msg = "Could not find module <highlight>$module<end> for channel <highlight>$type<end>.";
			} elseif ($args[1] === "cmd" && $type !== "all") {
				$msg = "Could not find command <highlight>$cmd<end> for channel <highlight>$type<end>.";
			} elseif ($args[1] === "cmd" && $type === "all") {
				$msg = "Could not find command <highlight>$cmd<end>.";
			} elseif ($args[1] === "subcmd" && $type !== "all") {
				$msg = "Could not find subcommand <highlight>$cmd<end> for channel <highlight>$type<end>.";
			} elseif ($args[1] === "subcmd" && $type === "all") {
				$msg = "Could not find subcommand <highlight>$cmd<end>.";
			} elseif ($args[1] === "event" && $file !== "") {
				$msg = "Could not find event <highlight>$event_type<end> for handler <highlight>$file<end>.";
			}
			$sendto->reply($msg);
			return;
		}
	
		if ($args[1] === "mod" && $type === "all") {
			$msg = "Updated status of module <highlight>$module<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] === "mod" && $type !== "all") {
			$msg = "Updated status of module <highlight>$module<end> in channel <highlight>$type<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] === "cmd" && $type !== "all") {
			$msg = "Updated status of command <highlight>$cmd<end> to <highlight>".$args[3]."d<end> in channel <highlight>$type<end>.";
		} elseif ($args[1] === "cmd" && $type === "all") {
			$msg = "Updated status of command <highlight>$cmd<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] === "subcmd" && $type !== "all") {
			$msg = "Updated status of subcommand <highlight>$cmd<end> to <highlight>".$args[3]."d<end> in channel <highlight>$type<end>.";
		} elseif ($args[1] === "subcmd" && $type === "all") {
			$msg = "Updated status of subcommand <highlight>$cmd<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] === "event" && $file !== "") {
			$msg = "Updated status of event <highlight>$event_type<end> to <highlight>".$args[3]."d<end>.";
		}
	
		$sendto->reply($msg);
	
		foreach ($data as $row) {
			// only update the status if the status is different
			if ($row->status !== $status) {
				if ($row->cmdevent === "event") {
					if ($status === 1) {
						$this->eventManager->activate($row->type, $row->file);
					} else {
						$this->eventManager->deactivate($row->type, $row->file);
					}
				} elseif ($row->cmdevent === "cmd") {
					if ($status === 1) {
						$this->commandManager->activate($row->type, $row->file, $row->cmd, $row->admin);
					} else {
						$this->commandManager->deactivate($row->type, $row->file, $row->cmd, $row->admin);
					}
				}
			}
		}
	
		if ($args[1] === "mod" && $type === "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `module` = ?", $status, $module);
			$this->db->exec("UPDATE eventcfg_<myname> SET `status` = ? WHERE `module` = ? AND `type` != 'setup'", $status, $module);
		} elseif ($args[1] === "mod" && $type !== "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `module` = ? AND `type` = ?", $status, $module, $type);
			$this->db->exec("UPDATE eventcfg_<myname> SET `status` = ? WHERE `module` = ? AND `type` = ? AND `type` != 'setup'", $status, $module, $event_type);
		} elseif ($args[1] === "cmd" && $type !== "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'cmd'", $status, $cmd, $type);
		} elseif ($args[1] === "cmd" && $type === "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `cmdevent` = 'cmd'", $status, $cmd);
		} elseif ($args[1] === "subcmd" && $type !== "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'subcmd'", $status, $cmd, $type);
		} elseif ($args[1] === "subcmd" && $type === "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `cmdevent` = 'subcmd'", $status, $cmd);
		} elseif ($args[1] === "event" && $file !== "") {
			$this->db->exec("UPDATE eventcfg_<myname> SET `status` = ? WHERE `type` = ? AND `file` = ? AND `type` != 'setup'", $status, $event_type, $file);
		}
	
		// for subcommands which are handled differently
		$this->subcommandManager->loadSubcommands();
	}

	/**
	 * This command handler sets command's access level on a particular channel.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config (subcmd|cmd) (.+) admin (msg|priv|guild|all) (.+)$/i")
	 */
	public function setAccessLevelOfChannelCommand(string $message, string $channel, string $sender, CommandReply $sendto, array $args): void {
		$category = strtolower($args[1]);
		$command = strtolower($args[2]);
		$channel = strtolower($args[3]);
		$accessLevel = $this->accessManager->getAccessLevel($args[4]);
	
		if ($category === "cmd") {
			$sqlArgs = [$command];
			if ($channel === "all") {
				$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `cmdevent` = 'cmd'";
			} else {
				$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'cmd'";
				$sqlArgs []= $channel;
			}
			/** @var CmdCfg[] $data */
			$data = $this->db->fetchAll(CmdCfg::class, $sql, ...$sqlArgs);
	
			if (count($data) === 0) {
				if ($channel === "all") {
					$msg = "Could not find command <highlight>$command<end>.";
				} else {
					$msg = "Could not find command <highlight>$command<end> for channel <highlight>$channel<end>.";
				}
			} elseif (!$this->checkCommandAccessLevels($data, $sender)) {
				$msg = "You do not have the required access level to change this command.";
			} elseif (!$this->accessManager->checkAccess($sender, $accessLevel)) {
				$msg = "You may not set the access level for a command above your own access level.";
			} else {
				$this->commandManager->updateStatus($channel, $command, null, 1, $accessLevel);
		
				if ($channel == "all") {
					$msg = "Updated access of command <highlight>$command<end> to <highlight>$accessLevel<end>.";
				} else {
					$msg = "Updated access of command <highlight>$command<end> in channel <highlight>$channel<end> to <highlight>$accessLevel<end>.";
				}
			}
		} else {  // if ($category == 'subcmd')
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `type` = ? AND `cmdevent` = 'subcmd' AND `cmd` = ?";
			/** @var CmdCfg[] $data */
			$data = $this->db->fetchAll(CmdCfg::class, $sql, $channel, $command);
			if (count($data) === 0) {
				$msg = "Could not find subcommand <highlight>$command<end> for channel <highlight>$channel<end>.";
			} elseif (!$this->checkCommandAccessLevels($data, $sender)) {
				$msg = "You do not have the required access level to change this subcommand.";
			} elseif (!$this->accessManager->checkAccess($sender, $accessLevel)) {
				$msg = "You may not set the access level for a subcommand above your own access level.";
			} else {
				$this->db->exec("UPDATE cmdcfg_<myname> SET `admin` = ? WHERE `type` = ? AND `cmdevent` = 'subcmd' AND `cmd` = ?", $accessLevel, $channel, $command);
				$this->subcommandManager->loadSubcommands();
				$msg = "Updated access of subcommand <highlight>$command<end> in channel <highlight>$channel<end> to <highlight>$accessLevel<end>.";
			}
		}
		$sendto->reply($msg);
	}
	
	/**
	 * Check if sender has access to all commands in $data
	 *
	 * @param CmdCfg[] $data
	 * @param string $sender
	 * @return bool
	 */
	public function checkCommandAccessLevels(array $data, string $sender): bool {
		foreach ($data as $row) {
			if (!$this->accessManager->checkAccess($sender, $row->admin)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * This command handler shows information and controls of a command in
	 * each channel.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config cmd ([a-z0-9_]+)$/i")
	 */
	public function configCommandCommand(string $message, string $channel, string $sender, CommandReply $sendto, array $args): void {
		$cmd = strtolower($args[1]);
	
		$aliasCmd = $this->commandAlias->getBaseCommandForAlias($cmd);
		if ($aliasCmd !== null) {
			$cmd = $aliasCmd;
		}
	
		/** @var CmdCfg[] $data */
		$data = $this->db->fetchAll(CmdCfg::class, "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ?", $cmd);
		if (count($data) === 0) {
			$msg = "Could not find command <highlight>$cmd<end>.";
			$sendto->reply($msg);
			return;
		}
		$blob = '';
	
		$blob .= "<header2>Tells:<end> ";
		$blob .= $this->getCommandInfo($cmd, 'msg');
		$blob .= "\n\n";

		$blob .= "<header2>Private Channel:<end> ";
		$blob .= $this->getCommandInfo($cmd, 'priv');
		$blob .= "\n\n";
		
		$blob .= "<header2>Guild Channel:<end> ";
		$blob .= $this->getCommandInfo($cmd, 'guild');
		$blob .= "\n\n";
	
		$subcmd_list = '';
		$output = $this->getSubCommandInfo($cmd, 'msg');
		if ($output) {
			$subcmd_list .= "<header>Available Subcommands in tells<end>\n\n";
			$subcmd_list .= $output;
		}
	
		$output = $this->getSubCommandInfo($cmd, 'priv');
		if ($output) {
			$subcmd_list .= "<header>Available Subcommands in Private Channel<end>\n\n";
			$subcmd_list .= $output;
		}
	
		$output = $this->getSubCommandInfo($cmd, 'guild');
		if ($output) {
			$subcmd_list .= "<header>Available Subcommands in Guild Channel<end>\n\n";
			$subcmd_list .= $output;
		}
	
		if ($subcmd_list) {
			$blob .= $subcmd_list;
		}
	
		$help = $this->helpManager->find($cmd, $sender);
		if ($help !== null) {
			$blob .= "<header>Help ($cmd)<end>\n\n" . $help;
		}
	
		$msg = $this->text->makeBlob(ucfirst($cmd)." Config", $blob);
		$sendto->reply($msg);
	}

	/**
	 * Get a blob like "Aliases: alias1, alias2" for command $cmd
	 */
	public function getAliasInfo(string $cmd): string {
		$aliases = $this->commandAlias->findAliasesByCommand($cmd);
		$aliasesList = [];
		foreach ($aliases as $row) {
			if ($row->status === 1) {
				$aliasesList []= "<highlight>{$row->alias}<end>";
			}
		}
		$aliasesBlob = join(", ", $aliasesList);

		$blob = '';
		if (count($aliasesList) > 0) {
			$blob .= "Aliases: $aliasesBlob\n\n";
		}
		return $blob;
	}

	/**
	 * This command handler shows configuration and controls for a single module.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config ([a-z0-9_]+)$/i")
	 */
	public function configModuleCommand(string $message, string $channel, string $sender, CommandReply $sendto, array $args): void {
		$module = strtoupper($args[1]);
		$found = false;
	
		$on = $this->text->makeChatcmd("Enable", "/tell <myname> config mod {$module} enable all");
		$off = $this->text->makeChatcmd("Disable", "/tell <myname> config mod {$module} disable all");
	
		$blob = "Enable/disable entire module: ($on/$off)\n";
	
		/** @var Setting[] $data */
		$data = $this->db->fetchAll(Setting::class, "SELECT * FROM settings_<myname> WHERE `module` = ? ORDER BY mode, description", $module);
		if (count($data) > 0) {
			$found = true;
			$blob .= "\n<header2>Settings<end>\n";
		}
	
		foreach ($data as $row) {
			$blob .= $row->description ?? "";
	
			if ($row->mode === "edit") {
				$blob .= " (" . $this->text->makeChatcmd("Modify", "/tell <myname> settings change $row->name") . ")";
			}
	
			$settingHandler = $this->settingManager->getSettingHandler($row);
			$blob .= ": " . $settingHandler->displayValue() . "\n";
		}
	
		$sql = "SELECT ".
				"*, ".
				"SUM(CASE WHEN type = 'guild' THEN 1 ELSE 0 END) guild_avail, ".
				"SUM(CASE WHEN type = 'guild' AND status = 1 THEN 1 ELSE 0 END) guild_status, ".
				"SUM(CASE WHEN type ='priv' THEN 1 ELSE 0 END) priv_avail, ".
				"SUM(CASE WHEN type = 'priv' AND status = 1 THEN 1 ELSE 0 END) priv_status, ".
				"SUM(CASE WHEN type ='msg' THEN 1 ELSE 0 END) msg_avail, ".
				"SUM(CASE WHEN type = 'msg' AND status = 1 THEN 1 ELSE 0 END) msg_status ".
			"FROM ".
				"cmdcfg_<myname> c ".
			"WHERE ".
				"(`cmdevent` = 'cmd' OR `cmdevent` = 'subcmd') ".
				"AND `module` = ? ".
			"GROUP BY ".
				"cmd";
		/** @var CmdCfg[] $data */
		$data = $this->db->fetchAll(CmdCfg::class, $sql, $module);
		if (count($data) > 0) {
			$found = true;
			$blob .= "\n<header2>Commands<end>\n";
		}
		foreach ($data as $row) {
			$guild = '';
			$priv = '';
			$msg = '';
	
			if ($row->cmdevent === 'cmd') {
				$on = $this->text->makeChatcmd("ON", "/tell <myname> config cmd $row->cmd enable all");
				$off = $this->text->makeChatcmd("OFF", "/tell <myname> config cmd $row->cmd disable all");
				$cmdNameLink = $this->text->makeChatcmd($row->cmd, "/tell <myname> config cmd $row->cmd");
			} elseif ($row->cmdevent === 'subcmd') {
				$on = $this->text->makeChatcmd("ON", "/tell <myname> config subcmd $row->cmd enable all");
				$off = $this->text->makeChatcmd("OFF", "/tell <myname> config subcmd $row->cmd disable all");
				$cmdNameLink = $row->cmd;
			}
	
			$tell = "<red>T<end>";
			if ($row->msg_avail == 0) {
				$tell = "|_";
			} elseif ($row->msg_status === 1) {
				$tell = "<green>T<end>";
			}
	
			$guild = "|<red>G<end>";
			if ($row->guild_avail === 0) {
				$guild = "|_";
			} elseif ($row->guild_status === 1) {
				$guild = "|<green>G<end>";
			}
	
			$priv = "|<red>P<end>";
			if ($row->priv_avail === 0) {
				$priv = "|_";
			} elseif ($row->priv_status === 1) {
				$priv = "|<green>P<end>";
			}
	
			if ($row->description !== null && $row->description !== "") {
				$blob .= "$cmdNameLink ($tell$guild$priv): $on  $off - ($row->description)\n";
			} else {
				$blob .= "$cmdNameLink - ($tell$guild$priv): $on  $off\n";
			}
		}
	
		/** @var EventCfg[] */
		$data = $this->db->fetchAll(EventCfg::class, "SELECT * FROM eventcfg_<myname> WHERE `type` != 'setup' AND `module` = ?", $module);
		if (count($data) > 0) {
			$found = true;
			$blob .= "\n<header2>Events<end>\n";
		}
		foreach ($data as $row) {
			$on = $this->text->makeChatcmd("ON", "/tell <myname> config event ".$row->type." ".$row->file." enable all");
			$off = $this->text->makeChatcmd("OFF", "/tell <myname> config event ".$row->type." ".$row->file." disable all");
	
			if ($row->status == 1) {
				$status = "<green>Enabled<end>";
			} else {
				$status = "<red>Disabled<end>";
			}
	
			if ($row->description !== null && $row->description !== "none") {
				$blob .= "$row->type ($row->description) - ($status): $on  $off \n";
			} else {
				$blob .= "$row->type - ($status): $on  $off \n";
			}
		}
	
		if ($found) {
			$msg = $this->text->makeBlob("$module Configuration", $blob);
		} else {
			$msg = "Could not find module <highlight>$module<end>.";
		}
		$sendto->reply($msg);
	}

	/**
	 * This helper method converts given short access level name to long name.
	 */
	private function getAdminDescription(string $admin): string {
		$desc = $this->accessManager->getDisplayName($admin);
		return ucfirst(strtolower($desc));
	}

	/**
	 * This helper method builds information and controls for given command.
	 */
	private function getCommandInfo(string $cmd, string $type): string {
		$msg = "";
		/** @var CmdCfg[] $data */
		$data = $this->db->fetchAll(CmdCfg::class, "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `type` = ?", $cmd, $type);
		if (count($data) == 0) {
			$msg .= "<red>Unused<end>\n";
		} elseif (count($data) > 1) {
			$this->logger->log("ERROR", "Multiple rows exists for cmd: '$cmd' and type: '$type'");
			return $msg;
		}
		$row = $data[0];

		$row->admin = $this->getAdminDescription($row->admin);

		if ($row->status === 1) {
			$status = "<green>Enabled<end>";
		} else {
			$status = "<red>Disabled<end>";
		}

		$msg .= "$status (Access: $row->admin) \n";
		$msg .= "Set status: ";
		$msg .= $this->text->makeChatcmd("Enabled", "/tell <myname> config cmd {$cmd} enable {$type}") . "  ";
		$msg .= $this->text->makeChatcmd("Disabled", "/tell <myname> config cmd {$cmd} disable {$type}") . "\n";

		$msg .= "Set access level: ";
		$showRaidAL = $this->db->queryRow(
			"SELECT * from cmdcfg_<myname> WHERE module=? AND status=?",
			'RAID_MODULE',
			1
		) !== null;
		foreach ($this->accessManager->getAccessLevels() as $accessLevel => $level) {
			if ($accessLevel === 'none') {
				continue;
			}
			if (substr($accessLevel, 0, 5) === "raid_" && !$showRaidAL) {
				continue;
			}
			$alName = $this->getAdminDescription($accessLevel);
			$msg .= $this->text->makeChatcmd("{$alName}", "/tell <myname> config cmd {$cmd} admin {$type} $accessLevel") . "  ";
		}
		$msg .= "\n";
		return $msg;
	}

	/**
	 * This helper method builds information and controls for given subcommand.
	 */
	private function getSubCommandInfo($cmd, $type) {
		$subcmd_list = '';
		/** @var CmdCfg[] $data */
		$data = $this->db->fetchAll(CmdCfg::class, "SELECT * FROM cmdcfg_<myname> WHERE dependson = ? AND `type` = ? AND `cmdevent` = 'subcmd'", $cmd, $type);
		$showRaidAL = $this->db->queryRow(
			"SELECT * from cmdcfg_<myname> WHERE module=? AND status=?",
			'RAID_MODULE',
			1
		) !== null;
		foreach ($data as $row) {
			$subcmd_list .= "<pagebreak><header2>$row->cmd<end> ($type)\n";
			if ($row->description != "") {
				$subcmd_list .= "<tab>Description: <highlight>$row->description<end>\n";
			}

			$row->admin = $this->getAdminDescription($row->admin);

			if ($row->status == 1) {
				$status = "<green>Enabled<end>";
			} else {
				$status = "<red>Disabled<end>";
			}

			$subcmd_list .= "<tab>Current Status: $status (Access: $row->admin) \n";
			$subcmd_list .= "<tab>Set status: ";
			$subcmd_list .= $this->text->makeChatcmd("Enabled", "/tell <myname> config subcmd {$row->cmd} enable {$type}") . "  ";
			$subcmd_list .= $this->text->makeChatcmd("Disabled", "/tell <myname> config subcmd {$row->cmd} disable {$type}") . "\n";

			$subcmd_list .= "<tab>Set access level: ";
			foreach ($this->accessManager->getAccessLevels() as $accessLevel => $level) {
				if ($accessLevel == 'none') {
					continue;
				}
				if (substr($accessLevel, 0, 5) === "raid_" && !$showRaidAL) {
					continue;
				}
				$alName = $this->getAdminDescription($accessLevel);
				$subcmd_list .= $this->text->makeChatcmd($alName, "/tell <myname> config subcmd {$row->cmd} admin {$type} $accessLevel") . "  ";
			}
			$subcmd_list .= "\n\n";
		}
		return $subcmd_list;
	}
}
