import { test } from '@playwright/test';

/**
 * Local deps.
 */
import Preload from './admin/preload/index.spec';
import rocketLicense from './admin/rocket.license.auth.spec';
import configFile from './admin/configs/rocket.config.spec';
import miscellaneous from './admin/miscellaneous.spec';
import DeactivationModal from './admin/rocket.deactivation.modal.spec';
import safeMode from './admin/rocket.safe.mode.spec';
import deferJs from './admin/file_optimization/deferjs.spec';
import delayJs from './admin/file_optimization/delayjs.spec';
import AdvancedRules from './admin/advanced_rules/index.spec';

import deactivation from './admin/rocket.deactivation.spec';

// List tests.
test.describe('Rocket License', rocketLicense);
test.describe('Rocket Config File', configFile);
test.describe('Preload', Preload);
test.describe('Miscellaneous', miscellaneous);
test.describe('WPR Deactivation Modal', DeactivationModal);
test.describe('Safe Mode', safeMode);
test.describe('Defer JS', deferJs);
test.describe('Delay JS', delayJs);
test.describe('Advanced Rules', AdvancedRules);

test.describe('WPR Deactivation', deactivation);