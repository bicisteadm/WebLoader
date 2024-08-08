<?php

declare(strict_types=1);

namespace WebLoader;

use SplFileInfo;
use Traversable;
use WebLoader\Contract\IFileCollection;
use WebLoader\Exception\FileNotFoundException;

/**
 * FileCollection
 *
 * @author Jan Marek
 */
class FileCollection implements IFileCollection
{
	/** @var array<int, string> */
	private array $files = [];

	/** @var array<int, string> */
	private array $watchFiles = [];


	public function __construct(private readonly string $root)
	{
	}


	/** @return list<string> */
	public function getFiles(): array
	{
		return array_values($this->files);
	}


	/**
	 * Make path absolute
	 *
	 * @throws FileNotFoundException
	 */
	public function cannonicalizePath(string $path): string
	{
		$rel = Path::normalize($this->root . '/' . $path);
		if (file_exists($rel)) {
			return $rel;
		}

		$abs = Path::normalize($path);
		if (file_exists($abs)) {
			return $abs;
		}

		throw new FileNotFoundException("File '$path' does not exist.");
	}


	/**
	 * @param SplFileInfo|string $file
	 * @throws FileNotFoundException
	 */
	public function addFile(SplFileInfo|string $file): void
	{
		$file = $this->cannonicalizePath((string) $file);

		if (in_array($file, $this->files, true)) {
			return;
		}

		$this->files[] = $file;
	}


	/**
	 * Add files
	 * @param iterable<int|string, string> $files array list of files
	 */
	public function addFiles(iterable $files): void
	{
		foreach ($files as $file) {
			$this->addFile($file);
		}
	}


	public function removeFile(string $file): void
	{
		$this->removeFiles([$file]);
	}


	/** @param array<int|string, string> $files */
	public function removeFiles(array $files): void
	{
		$files = array_map([$this, 'cannonicalizePath'], $files);
		$this->files = array_diff($this->files, $files);
	}


	/**
	 * Remove all files
	 */
	public function clear(): void
	{
		$this->files = [];
		$this->watchFiles = [];
	}


	public function getRoot(): string
	{
		return $this->root;
	}


	public function addWatchFile(string $file): void
	{
		$file = $this->cannonicalizePath((string) $file);

		if (in_array($file, $this->watchFiles, true)) {
			return;
		}

		$this->watchFiles[] = $file;
	}


	/**
	 * Add watch files
	 * @param iterable<int|string, string> $files array list of files
	 */
	public function addWatchFiles(iterable $files): void
	{
		foreach ($files as $file) {
			$this->addWatchFile($file);
		}
	}


	/** @return list<string> */
	public function getWatchFiles(): array
	{
		return array_values($this->watchFiles);
	}
}
