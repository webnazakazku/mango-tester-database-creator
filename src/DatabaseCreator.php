<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator;

class DatabaseCreator
{

	private bool $created = false;

	private IDatabaseNameResolver $databaseNameResolver;

	private DatabaseStrategyAccessor $databaseStrategyAccessor;

	public function __construct(DatabaseStrategyAccessor $databaseStrategyAccessor, IDatabaseNameResolver $databaseNameResolver)
	{
		$this->databaseNameResolver = $databaseNameResolver;
		$this->databaseStrategyAccessor = $databaseStrategyAccessor;
	}

	public function getDatabaseName(): string
	{
		return $this->databaseNameResolver->getDatabaseName();
	}

	public function createTestDatabase(): void
	{
		if ($this->created) {
			return;
		}

		$this->databaseStrategyAccessor->get()->prepareDatabase($this->databaseNameResolver->getDatabaseName());
		$this->created = true;
	}

}
