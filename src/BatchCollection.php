<?php
declare(strict_types=1);

namespace WebLoader;

use WebLoader\Contract\IBatchCollection;
use WebLoader\Exception\BatchAlreadyExistsException;

class BatchCollection implements IBatchCollection
{
	/** @var array<int|string, mixed> */
	private array $batches = [];


	public function __construct()
	{
	}


	/** @return array<int|string, mixed> */
	public function getBatches(): array
	{
		return $this->batches;
	}


	/**
	 * @param array<int|string, mixed> $batch
	 * @throws BatchAlreadyExistsException
	 */
	public function addBatch(string $type, string $name, array $batch): void
	{
		if (isset($this->batches[$type][$name])) {
			throw new BatchAlreadyExistsException(
				sprintf("Batch '%s' of type '%s', already exists.", $name, $type)
			);
		}

		$this->batches[$type][$name] = $batch;
	}
}
