<?php
namespace Revmura\Core\Contracts;

interface MetaRegistry {
	/**
	* Register multiple meta fields for a post type.
	* $fields: [ [ 'key' => 'itc_price_sar', 'type' => 'number', 'single' => true, 'sanitize' => 'floatval' ], ... ]
	*/
	public function register( string $postType, array $fields ): void;
}
