const requireDir  = require('require-dir');
// Require all tasks.
requireDir('./src/js/gulp/tasks', { recurse: true });