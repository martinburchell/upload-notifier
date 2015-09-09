<?php
foreach ( $mock_function_args as $original_name => $args ) {
	if ( ! runkit_function_copy( $original_name, "real_$original_name" ) ) {
		throw new Exception( "Failed to copy $original_name" );
	}

	if ( ! runkit_function_redefine( $original_name, $args, "return mock_$original_name($args);" ) ) {
		throw new Exception( "Failed to redefine $original_name" );
	}
}
