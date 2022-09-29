'use strict';

const fs = require('fs');

const wpEnv = require('../../.wp-env.json');
const { WP_ROCKET_KEY, WP_ROCKET_EMAIL } = process.env;

if (!WP_ROCKET_KEY) {
	console.error('missing env var WP_ROCKET_KEY');
	process.exit(1);
	return;
}

if (!WP_ROCKET_EMAIL) {
	console.error('missing env var WP_ROCKET_EMAIL');
	process.exit(1);
	return;
}

wpEnv.config = {
    "WP_ROCKET_KEY": WP_ROCKET_KEY,
    "WP_ROCKET_EMAIL": WP_ROCKET_EMAIL
};

fs.writeFileSync('.wp-env.json', JSON.stringify(wpEnv, null, 4));