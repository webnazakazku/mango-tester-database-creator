<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Strategies;

use Webnazakazku\MangoTester\DatabaseCreator\CannotContinueMigrationException;
use Webnazakazku\MangoTester\DatabaseCreator\Drivers\IDatabaseDriver;
use Webnazakazku\MangoTester\DatabaseCreator\IMigrationsDriver;

class ContinueOrResetDatabaseStrategy implements IDatabaseCreationStrategy
{

	/** @var IDatabaseDriver */
	private $databaseDriver;

	/** @var IMigrationsDriver */
	private $migrationsDriver;

	public function __construct(IDatabaseDriver $databaseDriver, IMigrationsDriver $migrationsDriver)
	{
		$this->databaseDriver = $databaseDriver;
		$this->migrationsDriver = $migrationsDriver;
	}


	public function prepareDatabase(string $name): void
	{
		$this->databaseDriver->connectToDatabase($name);
		try {
			$this->migrationsDriver->continue();
		} catch (CannotContinueMigrationException $e) {
			$this->migrationsDriver->reset();
		}
	}

}
