<?php

declare(strict_types=1);

namespace WebLoader\Filter;

use WebLoader\Compiler;

/**
 * Less CSS filter
 *
 * @author Jan Tvrdík
 * @license MIT
 */
class LessBinFilter
{
	/** @var array<string, string> */
	private array $env;


	/** @param array<string, string> $env */
	public function __construct(private string $bin = 'lessc', array $env = [])
	{
		$this->env = $env + $_ENV;
		unset($this->env['argv'], $this->env['argc']);
	}


	/**
	 * Invoke filter
	 */
	public function __invoke(string $code, Compiler $loader, string $file): string
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'less') {
			$code = Process::run("{$this->bin} -", $code, dirname($file), $this->env);
		}

		return $code;
	}
}
