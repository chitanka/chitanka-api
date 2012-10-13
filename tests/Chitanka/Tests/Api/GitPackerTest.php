<?php
namespace Chitanka\Tests\Api;

use Chitanka\Api\GitPacker;
use Chitanka\Api\Shell;

class GitPackerTest extends \PHPUnit_Framework_TestCase {

	public function testCreateDiffFile() {
		$packer = new GitPacker(ROOT_DIR.'/source/content', ROOT_DIR.'/web/cache/content', new Shell);
		$file = $packer->createDiffFile('1349890259');
	}
}
