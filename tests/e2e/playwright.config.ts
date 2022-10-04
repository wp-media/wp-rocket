import path from 'path';
import type { PlaywrightTestConfig } from '@playwright/test';
import { devices } from '@playwright/test';

// Internal dependencies
import { WP_BASE_URL } from './config/wp.config';

const config: PlaywrightTestConfig = {
	globalSetup: require.resolve('./setup/global-setup'),
	testDir: './src',
	/* Maximum time one test can run for. */
	timeout: 90000,
	globalTimeout: 900000,
	reportSlowTests: null,
	/* Fail the build on CI if you accidentally left test.only in the source code. */
	forbidOnly: !!process.env.CI,
	/* Opt out of parallel tests on CI. */
	workers: 1,
	/* Reporter to use. See https://playwright.dev/docs/test-reporters */
	reporter: process.env.CI ? 'dot' : 'list',
	outputDir: path.join( process.cwd(), 'artifacts' ),
	/* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
	use: {
		baseURL: WP_BASE_URL,
		headless: true,
		viewport: {
			width: 960,
			height: 700,
		},
		ignoreHTTPSErrors: true,
		locale: 'en-US',
		contextOptions: {
			reducedMotion: 'reduce',
			strictSelectors: true,
		},
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
		// Tell all tests to load signed-in state from 'storageState.json'.
		storageState: 'tests/e2e/config/storageState.json',
		actionTimeout: 10_000, // 10 seconds.
	},

	/* Configure projects for major browsers */
	projects: [
		{
			name: 'chrome',
			use: {
				...devices['Desktop Chrome'],
			},
		},

		/* Test against mobile viewports. */
		// {
		//   name: 'Mobile Chrome',
		//   use: {
		//     ...devices['Pixel 5'],
		//   },
		// },
		// {
		//   name: 'Mobile Safari',
		//   use: {
		//     ...devices['iPhone 12'],
		//   },
		// },

		/* Test against branded browsers. */
		// {
		//   name: 'Microsoft Edge',
		//   use: {
		//     channel: 'msedge',
		//   },
		// },
		// {
		//   name: 'Google Chrome',
		//   use: {
		//     channel: 'chrome',
		//   },
		// },
	],
};

export default config;
