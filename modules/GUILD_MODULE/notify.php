<?php
   /*
   ** Author: Derroylo (RK2)
   ** Description: Adding/Removing Guildmembers
   ** Version: 1.0
   **
   ** Developed for: Budabot(http://sourceforge.net/projects/budabot)
   **
   ** Date(created): 23.11.2005
   ** Date(last modified): 10.12.2006
   ** 
   ** Copyright (C) 2005, 2006 Carsten Lohmann
   **
   ** Licence Infos: 
   ** This file is part of Budabot.
   **
   ** Budabot is free software; you can redistribute it and/or modify
   ** it under the terms of the GNU General Public License as published by
   ** the Free Software Foundation; either version 2 of the License, or
   ** (at your option) any later version.
   **
   ** Budabot is distributed in the hope that it will be useful,
   ** but WITHOUT ANY WARRANTY; without even the implied warranty of
   ** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   ** GNU General Public License for more details.
   **
   ** You should have received a copy of the GNU General Public License
   ** along with Budabot; if not, write to the Free Software
   ** Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
   */

if (preg_match("/^notify (on|add) (.+)$/i", $message, $arr)) {
    // Get User id
    $uid = AoChat::get_uid($arr[2]);
	$name = ucfirst(strtolower($arr[2]));
    $db->query("SELECT * FROM org_members_<myname> WHERE `name` = '$name'");
	$numrows = $db->numrows();
	if($numrows != 0) {
	    $row = $db->fObject();
	}
    // Is the player already a member?
    if($numrows != 0 && $row->mode != "del") {
        $msg = "<highlight>$name<end> is already on the Notify list.";
    // If the member was deleted set him as manual added again
    } elseif ($numrows != 0 && $row->mode == "del") {
        $db->query("UPDATE org_members_<myname> SET `mode` = 'man' WHERE `name` = '$name'");
        $this->add_buddy($name, 'org');
	    
    	$msg = "<highlight>$name<end> has been added to the Notify list.";
    // Is the player name valid?
    } else if ($uid) {
        // update player info
        Player::get_by_name($name);

        // Add him as a buddy and put his infos into the DB
		$this->add_buddy($name, 'org');

        $db->query("INSERT INTO org_members_<myname> (`mode`, `name`) VALUES ('man', '".$name."')");
    	$msg = "<highlight>".$name."<end> has been added to the Notify list.";
    // Player name is not valid
    } else {
        $msg = "Player <highlight>".$name."<end> does not exist.";
	}

    // Send info back
    bot::send($msg, $sendto);
} else if (preg_match("/^notify (off|rem) (.+)$/i", $message, $arr)) {
    $name = ucfirst(strtolower($arr[2]));
    $query = $db->query("SELECT * FROM org_members_<myname> WHERE `name` = '$name'");
	$numrows = $db->numrows();
	if($numrows != 0)
	    $row = $db->fObject();
	    
    // Is the player a member of this bot?
    if ($numrows != 0 && $row->mode != "del") {
        $db->query("UPDATE org_members_<myname> SET `mode` = 'del' WHERE `name` = '$name'");
        $db->query("DELETE FROM guild_chatlist_<myname> WHERE `name` = '$name'");
        $msg = "Removed <highlight>$name<end> from the Notify list.";
    // Player is not a member of this bot
    } else {
        $msg = "<highlight>$name<end> is not a member of this bot.";
	}

    // Send info back
    bot::send($msg, $sendto);
} else {
	$syntax_error = true;
}
?>
