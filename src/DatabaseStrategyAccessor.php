<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator;

use Webnazakazku\MangoTester\DatabaseCreator\Strategies\IDatabaseCreationStrategy;

interface DatabaseStrategyAccessor
{

	public function get(): IDatabaseCreationStrategy;

}
