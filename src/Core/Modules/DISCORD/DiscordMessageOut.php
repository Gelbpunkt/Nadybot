<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use DateTime;

class DiscordMessageOut {
	public string $content;
	public $nonce = null;
	public bool $tts = false;
	public ?string $file = null;
	public ?object $embed = null;
	public ?string $payload_json = null;
	public ?object $allowed_Mentions = null;

	public function __construct(string $content) {
		$this->content = $content;
	}

	public function toJSON(): string {
		return json_encode($this, JSON_PRETTY_PRINT);
	}
}
