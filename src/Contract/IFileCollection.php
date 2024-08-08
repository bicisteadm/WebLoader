<?php

declare(strict_types=1);

namespace WebLoader\Contract;

/**
 * @author Jan Marek
 */
interface IFileCollection
{
	public function getRoot(): string;

	/** @return array<int, string> */
	public function getFiles(): array;

	/** @return array<int, string> */
	public function getWatchFiles(): array;
}
