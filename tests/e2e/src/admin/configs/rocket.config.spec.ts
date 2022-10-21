import { test, expect } from '@playwright/test';

const configFile = () => {
    let allowed_status = [301, 200];
    
    test('should have config file', async ({ page }) => {

        page.on('response', async (response) => {
            expect(allowed_status).toContain(response.status());
        });

        await page.goto('/wp-content/wp-rocket-config/localhost.php');
    });
}


export default configFile;