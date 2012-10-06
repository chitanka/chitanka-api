<?php
namespace Chitanka\Api;

class FileHandler {

	public function concatFilesInDirAfterStartFile($startFile, $startLine, $outputFile, $delimiter = '') {
		$init = "tail -n+$startLine $startFile > $outputFile";
		$startFileDir = dirname($startFile).'/'; // trailing slash for an eventual symlink
		$find = "find $startFileDir -type f | sort | grep -A 100000 '$startFile' | tail -n+2";
		$loop = "echo '$delimiter' | sed \"s/FILE/`basename \$f`/\" >> $outputFile; cat \$f >> $outputFile";
		shell_exec("$init; for f in `$find`; do $loop; done");

		return $outputFile;
	}

}
