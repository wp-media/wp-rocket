module.exports = {
	tasks: {
		css: [
			{
				task: 'build:admin:css:unmin',
				method: 'compileAdminSaas',
			},
			{
				task: 'build:admin:css:min',
				method: 'compileAdminSaasMin',
			},
			{
				task: 'sass_all_unmin',
				method: 'compileAdminFullSaas',
			},
		],
	},
};