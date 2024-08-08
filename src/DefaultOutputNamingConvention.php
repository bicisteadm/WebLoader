<?php

declare(strict_types=1);

namespace WebLoader;

use WebLoader\Contract\IOutputNamingConvention;

/**
 * DefaultNamingConvention
 *
 * @author Jan Marek
 */
class DefaultOutputNamingConvention implements IOutputNamingConvention
{
	private string $prefix = '';
	private string $suffix = '';


	public static function createCssConvention(): self
	{
		$convention = new self;
		$convention->setSuffix('.css');

		return $convention;
	}


	public static function createJsConvention(): self
	{
		$convention = new self;
		$convention->setSuffix('.js');

		return $convention;
	}


	/**
	 * Get generated file name prefix
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}


	/**
	 * Set generated file name prefix
	 * @param string $prefix generated file name prefix
	 */
	public function setPrefix(string $prefix): void
	{
		$this->prefix = (string) $prefix;
	}


	/**
	 * Get generated file name suffix
	 */
	public function getSuffix(): string
	{
		return $this->suffix;
	}


	/**
	 * Set generated file name suffix
	 * @param string $suffix generated file name suffix
	 */
	public function setSuffix(string $suffix): void
	{
		$this->suffix = (string) $suffix;
	}


	/**
	 * Filename of generated file
	 * @param array<int|string, string> $files
	 */
	public function getFilename(array $files, Compiler $compiler): string
	{
		return $this->prefix . $this->createHash($files, $compiler) . $this->suffix;
	}


	/** @param array<int|string, string> $files */
	protected function createHash(array $files, Compiler $compiler): string
	{
		$parts = $files;
		foreach ($files as $file) {
			$parts[] = @filemtime($file);
		}

		return substr(md5(implode('|', $parts)), 0, 12);
	}
}
