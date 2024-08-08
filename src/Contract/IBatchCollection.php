<?php
declare(strict_types=1);

namespace WebLoader\Contract;

interface IBatchCollection
{
	/** @return array<int|string, mixed> */
	public function getBatches(): array;

	/** @param array<int|string, string> $batch */
	public function addBatch(string $type, string $name, array $batch): void;
}
