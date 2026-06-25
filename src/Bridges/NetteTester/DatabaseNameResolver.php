<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteTester;

use Webnazakazku\MangoTester\DatabaseCreator\IDatabaseNameResolver;

class DatabaseNameResolver implements IDatabaseNameResolver
{

	public const DEFAULT_FORMAT = 'app_test_%d';

	private string $format;

	private string $id;

	public function __construct(string $format = self::DEFAULT_FORMAT)
	{
		$this->format = $format;

		$env = getenv('NETTE_TESTER_THREAD');
		$this->id = ($env !== false && $env !== '') ? $env : '0';
	}

	public function getDatabaseName(): string
	{
		return sprintf($this->format, $this->id);
	}

}
