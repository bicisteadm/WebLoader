<?php
declare(strict_types=1);

namespace WebLoader\Test\Nette;

use Nette\Bootstrap\Configurator;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\Utils\Finder;
use PHPUnit\Framework\TestCase;
use WebLoader\Compiler as WebloaderCompiler;
use WebLoader\Nette\Extension;
use WebLoader\Path;

class ExtensionTest extends TestCase
{
	private Container $container;
	private string $appDir;
	private string $wwwDir;
	private string $tempDir;
	private string $fixturesDir;
	private bool $debugMode;


	protected function setUp(): void
	{
		$this->appDir = __DIR__;
		$this->wwwDir = $this->appDir . '/..';
		$this->tempDir = $this->wwwDir . '/../temp';
		$this->fixturesDir = $this->appDir . '/../fixtures';
		$this->debugMode = false;
	}


	/** @param list<string> $configFiles */
	private function prepareContainer(array $configFiles): void
	{
		$finder = Finder::findFiles('*')
			->exclude('.gitignore')
			->from($this->tempDir . '/cache');

		foreach ($finder as $file) {
			unlink((string) $file);
		}

		$configurator = new Configurator();
		$configurator->setTempDirectory($this->tempDir);

		/** @var string $file */
		foreach ($configFiles as $file) {
			$configurator->addConfig($file);
		}

		$configurator->addParameters([
			'wwwDir' => $this->wwwDir,
			'fixturesDir' => $this->fixturesDir,
			'tempDir' => $this->tempDir,
		]);

		$extension = new Extension($this->appDir, $this->wwwDir, $this->debugMode);
		$extension->install($configurator);

		$this->container = @$configurator->createContainer(); // sends header X-Powered-By, ...
	}


	public function testJsCompilerService(): void
	{
		$this->prepareContainer([$this->fixturesDir . '/extension.neon']);
		$this->assertInstanceOf('WebLoader\Compiler', $this->container->getService('webloader.jsDefaultCompiler'));
	}


	public function testExcludeFiles(): void
	{
		$this->prepareContainer([$this->fixturesDir . '/extension.neon']);
		$files = $this->container->getService('webloader.jsExcludeCompiler')->getFileCollection()->getFiles();

		$this->assertTrue(in_array(Path::normalize($this->fixturesDir . '/a.txt'), $files, true));
		$this->assertFalse(in_array(Path::normalize($this->fixturesDir . '/dir/one.js'), $files, true));
	}


	public function testAsyncOn(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionAsyncTrue.neon',
		]);
		$this->assertTrue($this->container->getService('webloader.jsDefaultCompiler')->isAsync());
	}


	public function testAsyncOff(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionAsyncFalse.neon',
		]);
		$this->assertFalse($this->container->getService('webloader.jsDefaultCompiler')->isAsync());
	}


	public function testDeferOn(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionDeferTrue.neon',
		]);
		$this->assertTrue($this->container->getService('webloader.jsDefaultCompiler')->isDefer());
	}


	public function testDeferOff(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionDeferFalse.neon',
		]);
		$this->assertFalse($this->container->getService('webloader.jsDefaultCompiler')->isDefer());
	}


	public function testAbsoluteUrlOn(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionAbsoluteUrlTrue.neon',
		]);
		$this->assertTrue($this->container->getService('webloader.jsDefaultCompiler')->isAbsoluteUrl());
	}


	public function testAbsoluteUrlOff(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionAbsoluteUrlFalse.neon',
		]);
		$this->assertFalse($this->container->getService('webloader.jsDefaultCompiler')->isAbsoluteUrl());
	}


	public function testNonceSet(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
			$this->fixturesDir . '/extensionNonce.neon',
		]);
		$this->assertEquals('rAnd0m123', $this->container->getService('webloader.jsDefaultCompiler')->getNonce());
	}


	public function testNonceNotSet(): void
	{
		$this->prepareContainer([
			$this->fixturesDir . '/extension.neon',
		]);
		$this->assertNull($this->container->getService('webloader.jsDefaultCompiler')->getNonce());
	}


	public function testExtensionName(): void
	{
		$class = 'ExtensionNameServiceContainer';

		$configurator = new Configurator();
		$configurator->setTempDirectory($this->tempDir);
		$configurator->addParameters(['container' => ['class' => $class]]);
		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) {
			$extension = new Extension($this->appDir, $this->wwwDir, $this->debugMode);
			$compiler->addExtension('Foo', $extension);
		};
		$configurator->addConfig($this->fixturesDir . '/extensionName.neon');
		$container = $configurator->createContainer();

		$this->assertInstanceOf(
			WebloaderCompiler::class,
			$container->getService('Foo.cssDefaultCompiler')
		);
		$this->assertInstanceOf(
			WebloaderCompiler::class,
			$container->getService('Foo.jsDefaultCompiler')
		);
	}
}
