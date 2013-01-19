<?php
namespace Chitanka\Api;

class GitPacker {

	private $gitDir = '';
	private $saveDir = '';
	private $shell;

	public function __construct($gitDir, $saveDir, Shell $shell) {
		$this->gitDir = $gitDir;
		$this->saveDir = $saveDir;
		$this->shell = $shell;
	}

	public function createDiffFile($timestamp) {
		chdir($this->gitDir);

		$lastTimestamp = trim($this->shell->exec("git log -1 --format='%ct'"));
		if ($lastTimestamp <= $timestamp) { // no newer commits
			return false;
		}

		$tmpDir = sys_get_temp_dir()."/chitanka-source-".uniqid();
		mkdir($tmpDir);
		file_put_contents("$tmpDir/.last", $lastTimestamp);

		$commitBeforeDate = trim($this->shell->exec("git log --before='$timestamp' -n 1 --oneline | awk '{ print $1 }'"));
		$gitdiff = "git diff --name-status $commitBeforeDate HEAD";
		$this->shell->exec("cp -R --parents `$gitdiff | grep -Pv \"D\t\" | awk '{ print $2 }'` $tmpDir");
		$this->shell->exec("$gitdiff | grep -P \"D\t\" | awk '{ print $2 }' > $tmpDir/.deleted");

		$zip = new \ZipArchive;
		$archive = sprintf('%s/%s-%d.zip', $this->saveDir, $timestamp, time());
		if ($zip->open($archive, \ZipArchive::CREATE) !== true) {
			return false;
		}
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS));
		foreach ($iterator as $file) {
			$zip->addFile($file->getPathname(), $iterator->getSubPathName());
		}
		$zip->close();

		return $archive;
	}
}
