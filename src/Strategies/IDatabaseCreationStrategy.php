<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Strategies;

interface IDatabaseCreationStrategy
{

	public function prepareDatabase(string $name): void;

}
