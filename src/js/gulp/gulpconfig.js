module.exports = {
	tasks: {
		css: [
			{
				task: 'build:saas',
				method: 'compileAdminFullSaasMin',
			},
			{
				task: 'sass:watch',
				method: 'watch',
			},
		],
		js: [
			{
				task: 'build:js',
				method: 'compile',
			},
		],
	},
};