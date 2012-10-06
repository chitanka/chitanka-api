<?php
namespace Chitanka\Tests\Api;

use Chitanka\Api\FileHandler;

class FileHandlerTest extends \PHPUnit_Framework_TestCase {

	public function testConcatFilesFromSortedList() {
		list($startFile, $startLine, $expected) = $this->prepareForConcatFilesFromSortedList();

		$fh = new FileHandler();
		$outputFile = sys_get_temp_dir().'/api-output.'.uniqid();
		$fh->concatFilesInDirAfterStartFile($startFile, $startLine, $outputFile);
		$this->assertEquals($expected, file_get_contents($outputFile));
	}

	private function prepareForConcatFilesFromSortedList() {
		$inDir = sys_get_temp_dir().'/api-sql.'.uniqid();
		mkdir($inDir);
		file_put_contents("$inDir/2012-06-01.sql", "dummy");
		file_put_contents("$inDir/2012-06-02.sql", implode("\n", range(1, 6))."\n");
		file_put_contents("$inDir/2012-06-03.sql", implode("\n", range(7, 9))."\n");
		file_put_contents("$inDir/2012-06-04.sql", implode("\n", range(10, 12))."\n");
		return array(
			"$inDir/2012-06-02.sql",
			3,
			implode("\n", range(3, 12))."\n",
		);
	}
}
