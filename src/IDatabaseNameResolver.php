<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator;

interface IDatabaseNameResolver
{

	public function getDatabaseName(): string;

}
