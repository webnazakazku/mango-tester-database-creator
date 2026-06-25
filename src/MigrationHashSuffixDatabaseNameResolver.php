<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator;

class MigrationHashSuffixDatabaseNameResolver implements IDatabaseNameResolver
{

	private IDatabaseNameResolver $nameResolver;

	private IMigrationsDriver $migrationsDriver;

	public function __construct(IDatabaseNameResolver $nameResolver, IMigrationsDriver $migrationsDriver)
	{
		$this->nameResolver = $nameResolver;
		$this->migrationsDriver = $migrationsDriver;
	}

	public function getDatabaseName(): string
	{
		return $this->nameResolver->getDatabaseName() . '_' . $this->migrationsDriver->getMigrationsHash();
	}

}
