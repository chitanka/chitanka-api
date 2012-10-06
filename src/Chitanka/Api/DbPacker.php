<?php
namespace Chitanka\Api;

class DbPacker {

	private $sqlDir = '';
	private $saveDir = '';
	private $startDate, $startLine;

	public function __construct($sqlDir, $saveDir) {
		$this->sqlDir = $sqlDir;
		$this->saveDir = $saveDir;
	}

	public function createDiffFile($startDate, $startLine, $id) {
		$this->startDate = $startDate;
		$this->startLine = $startLine;

		$startFile = "$this->sqlDir/$startDate.sql";
		$outputFile = sys_get_temp_dir().'/'.uniqid('db-');
		$fh = new FileHandler();
		$fh->concatFilesInDirAfterStartFile($startFile, $startLine, $outputFile, '-- ### file FILE');

		if (filesize($outputFile) == 0) {
			return false;
		}

		$this->initNextStartData($outputFile);

		$zip = new \ZipArchive;
		$archive = "$this->saveDir/$startDate-$startLine-$id.zip";
		if ($zip->open($archive, \ZipArchive::CREATE) !== true) {
			return false;
		}
		$zip->addFile($outputFile, 'db.sql');
		$zip->addFromString('.last', $this->getNextStart());
		$zip->close();

		return $archive;
	}

	private function getNextStart() {
		return "$this->startDate/$this->startLine";
	}

	private function initNextStartData($dbFile) {
		foreach (file($dbFile) as $line) {
			if (preg_match('/-- ### file (.+)\.sql/', $line, $matches)) {
				$this->startDate = $matches[1];
				$this->startLine = 1;
			} else {
				$this->startLine++;
			}
		}
	}
}
