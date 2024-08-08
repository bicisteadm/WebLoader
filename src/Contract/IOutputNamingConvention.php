<?php

declare(strict_types=1);

namespace WebLoader\Contract;

use WebLoader\Compiler;

/**
 * IOutputNamingConvention
 *
 * @author Jan Marek
 */
interface IOutputNamingConvention
{
	/** @param array<int|string, string> $files */
	public function getFilename(array $files, Compiler $compiler): string;
}
