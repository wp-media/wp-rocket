async function globalTeardown() {
  let command: String;

  command = 'npm run wp-env run cli wp db reset -- --yes\n';
  command += 'npm run wp-env run cli wp core install -- --url=localhost:8888 --title=wp-rocket --admin_user=admin --admin_password=password --admin_email=admin@test.com\n';
  command += 'npm run wp-env stop';
    
  // Run teardown commands.
  const {execSync} = require('child_process');
  execSync(command);

  // Delete 'storageState.json'.
  const fs = require('fs');
  fs.unlink('tests/e2e/config/storageState.json', err => {
    if (err) {
        console.log('Unable to clear storage.')
    }
  });
}

export default globalTeardown;