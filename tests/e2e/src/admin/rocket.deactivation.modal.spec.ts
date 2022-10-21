import { test } from '@playwright/test';

/**
 * Local deps.
 */
import { deactivationModal } from '../common/deactivation.modal';

const DeactivationModal = () => {
    test('should pop up deactivation modal', async ( { page } ) => {
        await deactivationModal( page );
    });
}

export default DeactivationModal;