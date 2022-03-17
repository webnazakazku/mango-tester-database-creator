<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Drivers;

interface IDatabaseDriver
{

	public function getDatabaseName(): string;

	public function connectToDatabase(string $name): void;

}
