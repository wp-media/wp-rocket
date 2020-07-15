<?php

namespace Savvii;

/**
 * Mocked Savvii\CacheFlusherPlugin to the minimum requirement for tests to run.
 */
if ( ! class_exists( 'Savvii\CacheFlusherPlugin' ) ) {
	class CacheFlusherPlugin {
		const NAME_FLUSH_NOW       = 'warpdrive_flush_now';
    	const NAME_DOMAINFLUSH_NOW = 'warpdrive_domainflush_now';
	}
}
