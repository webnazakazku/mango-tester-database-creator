<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Presets;

use Webnazakazku\MangoTester\Infrastructure\InfrastructureConfigurator;

class NextrasStackPreset
{

	public static function installMysql(InfrastructureConfigurator $configurator): void
	{
		$configurator->addConfig(__DIR__ . '/nextras-mysql.neon');
	}


	public static function installPostgresql(InfrastructureConfigurator $configurator): void
	{
		$configurator->addConfig(__DIR__ . '/nextras-postgresql.neon');
	}

}
