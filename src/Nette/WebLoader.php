<?php

declare(strict_types=1);

namespace WebLoader\Nette;

use Nette\Application\UI\Control;
use Nette\Utils\Html;
use WebLoader\Compiler;
use WebLoader\File;
use WebLoader\FileCollection;

/**
 * Web loader
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class WebLoader extends Control
{
	public function __construct(
		private Compiler $compiler,
		private string $tempPath,
		private bool $appendLastModified
	) {
	}


	public function getCompiler(): Compiler
	{
		return $this->compiler;
	}


	public function setCompiler(Compiler $compiler): void
	{
		$this->compiler = $compiler;
	}


	public function getTempPath(): string
	{
		return $this->tempPath;
	}


	public function setTempPath(string $tempPath): void
	{
		$this->tempPath = $tempPath;
	}


	/**
	 * Get html element including generated content
	 */
	abstract public function getElement(File $file): Html;


	abstract public function getInlineElement(File $file): Html;


	protected function getUrl(File $file): string
	{
		return $this->getGeneratedFilePath($file);
	}


	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render(): void
	{
		$hasArgs = func_num_args() > 0;

		if ($hasArgs) {
			$backup = $this->compiler->getFileCollection();
			$newFiles = new FileCollection($backup->getRoot());
			$newFiles->addFiles(func_get_args());
			$this->compiler->setFileCollection($newFiles);
		}

		$file = $this->compiler->generate();
		if ($file) {
			echo $this->getElement($file), PHP_EOL;
		}

		if ($hasArgs) {
			$this->compiler->setFileCollection($backup);
		}
	}


	public function renderInline(): void
	{
		$file = $this->compiler->generate();
		if ($file) {
			echo $this->getInlineElement($file), PHP_EOL;
		}
	}


	public function renderUrl(): void
	{
		$file = $this->compiler->generate();
		if ($file) {
			echo $this->getUrl($file), PHP_EOL;
		}
	}


	protected function getGeneratedFilePath(File $file): string
	{
		$path = $this->tempPath . '/' . $file->getFileName();

		if ($this->appendLastModified) {
			$path .= '?' . $file->getLastModified();
		}

		return $path;
	}
}
