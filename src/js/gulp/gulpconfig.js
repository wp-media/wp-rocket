module.exports = {
	tasks: {
		css: [
			{
				task: 'build:saas:unmin',
				method: 'compileAdminFullSaasUnmin',
			},
			{
				task: 'build:saas:min',
				method: 'compileAdminFullSaasMin',
			},
			{
				task: 'build:sass:all',
				method: 'buildAll',
			},
			{
				task: 'build:sass:rocket-insights:unmin',
				method: 'compileRocketInsightsSaas',
			},
			{
				task: 'build:sass:rocket-insights:min',
				method: 'compileRocketInsightsSaasMin',
			},
			{
				task: 'sass:watch',
				method: 'watch',
			},
		],
		js: [
			{
				task: 'build:js:app:unmin',
				method: 'buildAppUnmin',
			},
			{
				task: 'build:js:app:min',
				method: 'buildAppMin',
			},

			{
				task: 'build:js:lazyloadcss:min',
				method: 'buildLazyloadCssMin',
			},

			{
				task: 'build:js:rocket-insights:unmin',
				method: 'buildRocketInsightsUnmin',
			},

			{
				task: 'build:js:rocket-insights:min',
				method: 'buildRocketInsightsMin',
			},

			{
				task: 'build:js:beacon',
				method: 'buildLcpBeacon',
			},

			{
				task: 'build:js:all',
				method: 'buildAll',
			},

			{
				task: 'js:watch',
				method: 'watch',
			},
		],
	},
};