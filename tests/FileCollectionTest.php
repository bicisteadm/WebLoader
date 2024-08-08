<?php
declare(strict_types=1);

namespace WebLoader\Test;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use WebLoader\FileCollection;
use WebLoader\Exception\FileNotFoundException;

/**
 * FileCollection test
 *
 * @author Jan Marek
 */
class FileCollectionTest extends TestCase
{

	private FileCollection $object;


	protected function setUp(): void
	{
		$this->object = new FileCollection(__DIR__ . '/fixtures');
	}


	public function testAddGetFiles(): void
	{
		$this->object->addFile('a.txt');
		$this->object->addFile(__DIR__ . '/fixtures/a.txt');
		$this->object->addFile(__DIR__ . '/fixtures/b.txt');
		$this->object->addFiles([__DIR__ . '/fixtures/c.txt']);
		$expected = [
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'b.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'c.txt',
		];
		$this->assertEqualPaths($expected, $this->object->getFiles());
	}


	public function testAddNonExistingFile(): void
	{
		$this->expectException(FileNotFoundException::class);
		$this->object->addFile('sdfsdg.txt');
	}


	public function testRemoveFile(): void
	{
		$this->object->addFile(__DIR__ . '/fixtures/a.txt');
		$this->object->addFile(__DIR__ . '/fixtures/b.txt');

		$this->object->removeFile('a.txt');
		$expected = [
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'b.txt',
		];
		$this->assertEqualPaths($expected, $this->object->getFiles());

		$this->object->removeFiles([__DIR__ . '/fixtures/b.txt']);
	}


	public function testCannonicalizePath(): void
	{
		$abs = __DIR__ . '/./fixtures/a.txt';
		$rel = 'a.txt';
		$expected = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt';

		$this->assertEqualPaths($expected, $this->object->cannonicalizePath($abs));
		$this->assertEqualPaths($expected, $this->object->cannonicalizePath($rel));

		try {
			$this->object->cannonicalizePath('nesdagf');
			$this->fail('Exception was not thrown.');
		} catch (FileNotFoundException $e) {
		}
	}


	public function testClear(): void
	{
		$this->object->addFile('a.txt');
		$this->object->addWatchFile('b.txt');
		$this->object->clear();

		$this->assertEquals([], $this->object->getFiles());
		$this->assertEquals([], $this->object->getWatchFiles());
	}


	public function testWatchFiles(): void
	{
		$this->object->addWatchFile(__DIR__ . '/fixtures/a.txt');
		$this->object->addWatchFile(__DIR__ . '/fixtures/b.txt');
		$this->object->addWatchFiles([__DIR__ . '/fixtures/c.txt']);
		$expected = [
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'b.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'c.txt',
		];
		$this->assertEqualPaths($expected, $this->object->getWatchFiles());
	}


	public function testTraversableFiles(): void
	{
		$this->object->addFiles(new ArrayIterator(['a.txt']));
		$this->assertCount(1, $this->object->getFiles());
	}


	public function testSplFileInfo(): void
	{
		$this->object->addFile(new SplFileInfo(__DIR__ . '/fixtures/a.txt'));
		$this->assertCount(1, $this->object->getFiles());
	}


	/**
	 * @param mixed $expected
	 * @param mixed $actual
	 */
	private function assertEqualPaths($expected, $actual): void
	{
		$actual = (array) $actual;
		foreach ((array) $expected as $key => $path) {
			$this->assertTrue(isset($actual[$key]));
			$this->assertEquals(\WebLoader\Path::normalize($path), \WebLoader\Path::normalize($actual[$key]));
		}
	}
}
