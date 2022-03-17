<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteTester;

use Webnazakazku\MangoTester\DatabaseCreator\IDatabaseNameResolver;

class DatabaseNameResolver implements IDatabaseNameResolver
{

	public const DEFAULT_FORMAT = 'app_test_%d';

	/** @var string */
	private $format;

	/** @var string */
	private $id;

	public function __construct(string $format = self::DEFAULT_FORMAT)
	{
		$this->format = $format;

		$this->id = getenv('NETTE_TESTER_THREAD') ?: '0';
	}


	public function getDatabaseName(): string
	{
		return sprintf($this->format, $this->id);
	}

}
