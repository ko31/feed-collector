<?php
/**
 * Utility functions
 */

/**
 * Default cron interval.
 */
function fc_default_cron_interval() {

	/**
	 * Filters default cron interval.
	 *
	 * @param int
	 */
	return apply_filters( 'fc_default_cron_interval', 1 );
}
