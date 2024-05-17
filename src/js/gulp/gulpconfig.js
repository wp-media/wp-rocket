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
				task: 'sass:watch',
				method: 'watch',
			},
		],
		js: [
			{
				task: 'build:js:unmin',
				method: 'buildAppUnmin',
			},
			{
				task: 'build:js:min',
				method: 'buildAppMin',
			},
			{
				task: 'js:watch',
				method: 'watch',
			},
		],
	},
};