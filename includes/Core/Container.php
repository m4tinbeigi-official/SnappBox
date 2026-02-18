<?php
declare(strict_types=1);
declare(strict_types=1);

namespace Snappbox\Core;

use DI\ContainerBuilder;
use DI\Container as DIContainer;

/**
 * Enterprise-grade Dependency Injection Container wrapper.
 */
class Container {

	/** @var DIContainer|null */
	private static $container = null;

	/**
	 * Get the DI Container instance.
	 */
	public static function instance(): DIContainer {
		if ( self::$container === null ) {
			$builder = new ContainerBuilder();

			// Define enterprise-level service mappings
			$builder->addDefinitions(
				array(
				// Interface to Implementation mappings can go here
				)
			);

			self::$container = $builder->build();
		}
		return self::$container;
	}

	/**
	 * Resolve a service from the container.
	 */
	public static function get( string $name ) {
		return self::instance()->get( $name );
	}
}
