<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall, not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove options used by current version of the plugin.

// Remove options that may be hanging around from old versions.
delete_option( 'sc_sub_initialized' );
