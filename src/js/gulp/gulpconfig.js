module.exports = {
	tasks: {
		css: [
			{
				task: 'build:admin:css',
				method: 'compileAdminSaas',
			},
			{
				task: 'build:admin:css:min',
				method: 'compileAdminSaasMin',
			},
		],
	},
};