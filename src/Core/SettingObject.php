<?php

namespace Budabot\Core;

/**
 * @Instance("setting")
 */
class SettingObject {
	/**
	 * @var \Budabot\Core\SettingManager $settingManager
	 * @Inject
	 */
	public $settingManager;

	public function __set($name, $value) {
		return $this->settingManager->save($name, $value);
	}

	public function __get($name) {
		return $this->settingManager->get($name);
	}
}