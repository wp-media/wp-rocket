import { test } from '@playwright/test';

/**
 * Local deps.
 */
import { deactivationModal } from '../common/deactivation.modal';

test.describe('WPR Deactivation Modal', () => {
    test('should pop up deactivation modal', async ( { page } ) => {
        await deactivationModal( page );
    });
});