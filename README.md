# Welcome to the WP Rocket GitHub Repository

[![Unit/Integration tests](https://github.com/wp-media/wp-rocket/actions/workflows/test_wprocket_php8.yml/badge.svg)](https://github.com/wp-media/wp-rocket/actions/workflows/test_wprocket_php8.yml)

Feel free to browse the source and keep track of our plugin's progress. You can stay informed of our latest versions via our [blog](https://wp-rocket.me/blog/?utm_source=github&utm_medium=wp_rocket_profile) or via Twitter [@wp_rocket](https://twitter.com/wp_rocket).

We aim to help make the web faster, one WordPress website at a time. That’s why we created *WP Rocket*. It's a caching plugin that simplifies the process and helps decrease a website’s load time.

If you are not a developer, visit our [documentation](http://docs.wp-rocket.me/?utm_source=github&utm_medium=wp_rocket_profile).

## Documentation

Need detailed setup instructions?

We are very proud of WP Rocket’s knowledge base.
We have [documentation](http://docs.wp-rocket.me/?utm_source=github&utm_medium=wp_rocket_profile) in English and French.

You can also check out our [changelog](https://wp-rocket.me/changelog/?utm_source=github&utm_medium=wp_rocket_profile).

## Composer installation

You can use composer to install the plugin as a dependency:

```
composer require wp-media/wp-rocket
```

To be able to validate your license and use the plugin, you will also have to manually define 2 constants in your wp-config.php file:

- `WP_ROCKET_EMAIL` which is the email for your WP Rocket account
- `WP_ROCKET_KEY` which is your API Key

## Gulp Tasks

| Command                        |                                                           Description                                                            |
|--------------------------------|:--------------------------------------------------------------------------------------------------------------------------------:|
| **CSS Tasks**                  |                                                                                                                                  |
| `gulp build:saas:unmin`        |                                  Builds Full admin CSS, the unminified version (wpr-admin.css)                                   |
| `gulp build:saas:min`          |                                 Builds Full admin CSS, the minified version (wpr-admin.min.css)                                  |
| `gulp build:sass:all`          |             Builds all admin CSS files (wpr-admin.css, wpr-admin.min.css, wpr-admin-rtl.css, wpr-admin-rtl.min.css)              |
| `gulp sass:watch`              |                        Watches all admin CSS files mentioned above and builds them again with any change.                        |
| **JS Tasks**                   |                                                                                                                                  |
| `gulp build:js:app:unmin`      |                                 Builds admin app js file, the unminified version (wpr-admin.js)                                  |
| `gulp build:js:app:min`        |                                Builds admin app js file, the minified version (wpr-admin.min.js)                                 |
| `gulp build:js:lazyloadcss:min` |                             Builds lazyload CSS js file, the minified version (lazyload-css.min.js)                              |
| `gulp build:js:lcp`         |                  Builds lcp beacon script, the minified version (lcp-beacon.min.js, source file, and map file)                   |
| `gulp build:js:all`            |              Builds all js files mentioned above (wpr-admin.js, wpr-admin.min.js, lazyload-css.min.js, lcp-beacon)               |
| `gulp js:watch`                |                                Watches all js files changes and build them again with any change.                                |


## Support

Need help with something? Open a [ticket](https://wp-rocket.me/support/?utm_source=github&utm_medium=wp_rocket_profile) and we will be happy to help you out!

## Bugs

If you find an issue in WP Rocket, please let us know [here](https://github.com/wp-media/wp-rocket/issues).
Be advised, this point of contact is to be used to report bugs and not to receive support. 
Check out our [support page](https://wp-rocket.me/support/?utm_source=github&utm_medium=wp_rocket_profile) if you need to submit a ticket. 

## Security Policy  
  
### Reporting Security Bugs  
  
You can report any security bugs found in the source code of the site-reviews plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/wp-rocket). The Patchstack team will assist you with verification, CVE assignment and take care of notifying the developers of this plugin.

## Contributions

Feel free to check out our [public roadmap](https://trello.com/b/CrUcz6Jy/wp-rocket-roadmap) if you would like to request a feature. We always look forward to feedback and suggestions from the community to help us improve our plugins!

## Want to know more about our WordPress plugins? 

Visit [wp-media.me](https://wp-media.me/?utm_source=github&utm_medium=wp_rocket_profile). 

We also make other plugins that help speed up WordPress websites. Check out:

* [Imagify](https://imagify.io): it's a great WordPress plugin to optimize your images and speed up your website.

## Special thanks

Thank you BrowserStack for your support and helping us do cross-browser testing easily!

[![BrowserStack](https://raw.githubusercontent.com/wp-media/wp-rocket/trunk/bin/browserstack.png)](https://browserstack.com)
