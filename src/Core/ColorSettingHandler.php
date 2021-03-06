<?php declare(strict_types=1);

namespace Nadybot\Core;

use Exception;

/**
 * Class to represent a setting with a color value for BudaBot
 */
class ColorSettingHandler extends SettingHandler {

	/**
	 * Get a displayable representation of the setting
	 */
	public function displayValue(): string {
		return $this->row->value . htmlspecialchars($this->row->value) . "</font>";
	}

	/**
	 * Describe the valid values for this setting
	 */
	public function getDescription(): string {
		$msg = "For this setting you can set any Color in the HTML Hexadecimal Color Format.\n";
		$msg .= "You can change it manually with the command: \n\n";
		$msg .= "/tell <myname> settings save {$this->row->name} <i>HTML-Color</i>\n\n";
		$msg .= "Or you can choose one of the following Colors\n\n";
		$msg .= "Red: <font color='#ff0000'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #ff0000'>Save it</a>) \n";
		$msg .= "White: <font color='#FFFFFF'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #FFFFFF'>Save it</a>) \n";
		$msg .= "Grey: <font color='#808080'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #808080'>Save it</a>) \n";
		$msg .= "Light Grey: <font color='#DDDDDD'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #DDDDDD'>Save it</a>) \n";
		$msg .= "Dark Grey: <font color='#9CC6E7'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #9CC6E7'>Save it</a>) \n";
		$msg .= "Black: <font color='#000000'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #000000'>Save it</a>) \n";
		$msg .= "Yellow: <font color='#FFFF00'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #FFFF00'>Save it</a>) \n";
		$msg .= "Blue: <font color='#8CB5FF'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #8CB5FF'>Save it</a>) \n";
		$msg .= "Deep Sky Blue: <font color='#00BFFF'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #00BFFF'>Save it</a>) \n";
		$msg .= "Green: <font color='#00DE42'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #00DE42'>Save it</a>) \n";
		$msg .= "Orange: <font color='#FCA712'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #FCA712'>Save it</a>) \n";
		$msg .= "Gold: <font color='#FFD700'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #FFD700'>Save it</a>) \n";
		$msg .= "Deep Pink: <font color='#FF1493'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #FF1493'>Save it</a>) \n";
		$msg .= "Violet: <font color='#EE82EE'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #EE82EE'>Save it</a>) \n";
		$msg .= "Brown: <font color='#8B7355'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #8B7355'>Save it</a>) \n";
		$msg .= "Cyan: <font color='#00FFFF'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #00FFFF'>Save it</a>) \n";
		$msg .= "Navy Blue: <font color='#000080'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #000080'>Save it</a>) \n";
		$msg .= "Dark Orange: <font color='#FF8C00'>Example Text</font> (<a href='chatcmd:///tell <myname> settings save {$this->row->name} #FF8C00'>Save it</a>) \n";
		return $msg;
	}

	/**
	 * Change this setting
	 *
	 * @throws \Exception when the string is not a valid HTML color
	 */
	public function save(string $newValue): string {
		if (preg_match("/^#([0-9a-f]{6})$/i", $newValue)) {
			return "<font color='$newValue'>";
		} elseif (preg_match("/^<font color='#[0-9a-f]{6}'>$/i", $newValue)) {
			return $newValue;
		}
		throw new Exception("<highlight>{$newValue}<end> is not a valid HTML-Color (example: <i>#FF33DD</i>).");
	}
}
