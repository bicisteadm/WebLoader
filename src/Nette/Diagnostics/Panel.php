<?php

declare(strict_types=1);

namespace WebLoader\Nette\Diagnostics;

use Latte;
use Latte\Essential\Filters;
use Tracy\Debugger;
use Tracy\IBarPanel;
use WebLoader\Compiler;

/**
 * Debugger panel.
 * @author Adam Klvač
 */
class Panel implements IBarPanel
{
	public static array $types = [
		'css' => 'CSS files',
		'js' => 'JavaScript files',
		'less' => 'Less files',
		'scss' => 'Sass files',
	];

	/** @var Compiler[] */
	private array $compilers = [];

	private ?array $size = null;
	private array $files;
	private array $sizes;
	private string $root;


	public function __construct(?string $appDir = null)
	{
		$this->root = $appDir
			? str_replace('\\', DIRECTORY_SEPARATOR, (string) realpath(dirname($appDir)))
			: '';
		Debugger::getBar()->addPanel($this);
	}


	/**
	 * Registers a compiler.
	 *
	 * @param string $name
	 * @param Compiler $compiler
	 * @return Panel
	 */
	public function addLoader(string $name, Compiler $compiler): self
	{
		$this->compilers[$name] = $compiler;
		return $this;
	}


	/**
	 * Computes the info.
	 */
	private function compute(): array
	{
		if ($this->size !== null) {
			return $this->size;
		}

		$size = [
			'original' => 0,
			'combined' => 0,
		];
		$this->files = $this->sizes = [];

		/**
		 * @var string $name
		 * @var Compiler $compiler
		 */
		foreach ($this->compilers as $name => $compiler) {
			$group = lcfirst(substr($name, $name[0] === 'c' ? 3 : 2));

			if (!isset($this->files[$group])) {
				$this->files[$group] = [];
			}
			if (!isset($this->sizes[$group])) {
				$this->sizes[$group] = ['.' => ['original' => 0, 'combined' => 0]];
			}

			$compilerCombinedSize = 0;

			$generated = $compiler->generate();

			if (null === $generated) {
				continue;
			}

			$generatedSize = filesize($compiler->getOutputDir() . DIRECTORY_SEPARATOR . $generated->getFileName());
			$size['combined'] += $generatedSize;

			$compilerCombinedSize += $generatedSize;

			foreach ($generated->getSourceFiles() as $file) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$file = str_replace('\\', DIRECTORY_SEPARATOR, (string) realpath($file));

				if (!isset($this->files[$group][$extension])) {
					$this->files[$group][$extension] = [];
				}
				if (!isset($this->sizes[$group][$extension])) {
					$this->sizes[$group][$extension] = ['original' => 0, 'combined' => 0];
				}

				$this->files[$group][$extension][] = [
					'name' => basename($file),
					'full' => $file,
					'size' => $fileSize = filesize($file),
				];

				$size['original'] += $fileSize;
				$this->sizes[$group][$extension]['original'] += $fileSize;
				$this->sizes[$group]['.']['original'] += $fileSize;
			}

			$this->sizes[$group]['.']['combined'] += $compilerCombinedSize;
		}

		return $this->size = $size + ['ratio' => $size['original'] !== 0 ? ($size['combined'] / $size['original']) * 100 : 0];
	}


	/**
	 * Renders loaded files table.
	 */
	private function getTable(): string
	{
		$latte = new Latte\Engine;

		$latte->addFilter('extension', fn($extension) => self::$types[$extension] ?? $extension);

		return $latte->renderToString(__DIR__ . '/panel.latte', [
			'files' => $this->files,
			'sizes' => $this->sizes,
			'size' => $this->size,
		]);
	}


	/**
	 * Returns panel content.
	 */
	public function getPanel(): string
	{
		return $this->compute() ? $this->getTable() : '';
	}


	/**
	 * Returns panel tab.
	 */
	public function getTab(): string
	{
		$this->compute();

		if (empty($this->size['combined'])) {
			return '';
		}

		return '<span title="WebLoader">'
			. '<svg viewBox="0 -50 600 600" style="vertical-align: bottom; width:1.23em; height:1.55em"><polygon fill="#1565C0" points="75.089,23.98 58.245,108.778 403.138,108.778 392.289,163.309 47.111,163.309 30.549,248.104 375.445,248.104 356.027,344.887 217.273,390.856 96.789,344.887 105.069,302.921 20.272,302.921 0,404.559 199.286,480.791 428.831,404.559 504.771,23.98"/></svg>'
			. (new Filters())->bytes($this->size['combined'])
		. '</span>';
	}
}
