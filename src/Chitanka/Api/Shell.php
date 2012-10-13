<?php
namespace Chitanka\Api;

class Shell {

	public function exec($command) {
		return shell_exec($command);
	}
}
