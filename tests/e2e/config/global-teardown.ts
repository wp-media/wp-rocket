async function globalTeardown() {
    
  // Stop dev server
  const {execSync} = require('child_process');
  execSync('npm run wp-env clean all');
  execSync('npm run wp-env stop');

  // Delete 'storageState.json'.
  const fs = require('fs');
  fs.unlink('tests/e2e/config/storageState.json', err => {
    if (err) {
        console.log('Unable to clear storage.')
    }
  });
}

export default globalTeardown;