# PHP_CodeSniffer Standards Composer Installer Plugin

![Project Stage][project-stage-shield]
![Maintenance][maintenance-shield]
![Awesome][awesome-shield]
[![License][license-shield]](LICENSE.md)

[![Travis][travis-shield]][travis]
[![Scrutinizer][scrutinizer-shield]][scrutinizer]
[![Dependency Status][versioneye-shield]][versioneye]
[![Latest Version on Packagist][packagist-version-shield]][packagist-version]
[![Packagist][packagist-shield]][packagist]

This composer installer plugin allows for easy installation of [PHP_CodeSniffer][codesniffer] coding standards (rulesets).

No more symbolic linking of directories, checking out repositories on specific locations or changing
the `phpcs` configuration.

_Note: This plugin is compatible with both version 2.x and 3.x of_ [PHP_CodeSniffer][codesniffer]

## Usage

Installation can be done with [composer], by requiring this package as a development dependency:

```bash
composer require --dev dealerdirect/phpcodesniffer-composer-installer
```

That's it.

### How it works

Basically, this plugin executes the following steps:

- This plugin search for `phpcodesniffer-standard` packages in all of your currently installed Composer packages.
- Matching packages and the project itself are scanned for PHP_CodeSniffer rulesets.
- The plugin will call PHP_CodeSniffer and configure the `installed_paths` option.

### Example project

The following is an example Composer project and has included
multiple `phpcodesniffer-standard` packages.

```json
{
    "name": "dealerdirect/example-project",
    "description": "Just an example project",
    "type": "project",
    "require": {},
    "require-dev": {
        "dealerdirect/phpcodesniffer-composer-installer": "*",
        "object-calisthenics/phpcs-calisthenics-rules": "*",
        "wimg/php-compatibility": "*",
        "wp-coding-standards/wpcs": "*"
    }
}
```

After running `composer install` PHP_CodeSniffer just works:

```bash
$ ./vendor/bin/phpcs -i
The installed coding standards are MySource, PEAR, PSR1, PSR2, Squiz, Zend, PHPCompatibility, WordPress,
WordPress-Core, WordPress-Docs, WordPress-Extra and WordPress-VIP
```

### Calling the plugin directly

In some circumstances, it is desirable to call this plugin's functionality
directly. For instance, during development or in [CI][definition-ci] environments.

As the plugin requires Composer to work, direct calls need to be wired through a
project's `composer.json`.

This is done by adding a call to the `Plugin::run` function in the `script`
section of the `composer.json`:

```json
{
    "scripts": {
        "install-codestandards": [
            "Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run"
        ]
    }
}

```

The command can then be called using `composer run-script install-codestandards` or
referenced from other script configurations, as follows:

```json
{
    "scripts": {
        "install-codestandards": [
            "Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run"
        ],
        "post-install-cmd": [
            "@install-codestandards"
        ]
    }
}

```

For more details about Composer scripts, please refer to [the section on scripts
in the Composer manual][composer-manual-scripts].

### Caveats

When this plugin is installed globally, composer will load the _global_ plugin rather
than the one from the local repository. Despite [this behavior being documented
in the composer manual][using-composer-plugins], it could potentially confuse
as an other version of the plugin could be run and not the one specified by the project.

## Developing Coding Standards

Coding standard can be developed normally, as documented by [PHP_CodeSniffer][codesniffer], in the [Coding Standard Tutorial][tutorial].

Create a composer package of your coding standard by adding a `composer.json` file.

```json
{
  "name" : "acme/phpcodesniffer-our-standards",
  "description" : "Package contains all coding standards of the Acme company",
  "require" : {
    "php" : ">=5.4.0,<8.0.0-dev",
    "squizlabs/php_codesniffer" : "^2.0"
  },
  "type" : "phpcodesniffer-standard"
}
```

Requirements:
* The repository may contain one or more standards.
* Each standard can have a separate directory no deeper than 3 levels from the repository root.
* The package `type` must be `phpcodesniffer-standard`. Without this, the plugin will not trigger.

## Changelog

This repository does not contain a `CHANGELOG.md` file, however, we do publish a changelog on each release
using the [GitHub releases][changelog] functionality.

## Contributing

This is an active open-source project. We are always open to people who want to
use the code or contribute to it.

We've set up a separate document for our [contribution guidelines][contributing-guidelines].

Thank you for being involved! :heart_eyes:

## Authors & contributors

The original idea and setup of this repository is by [Franck Nijhof][frenck], employee @ Dealerdirect.

For a full list off all author and/or contributors, check [the contributors page][contributors].

## Working @ Dealerdirect

Dealerdirect is always on the looking for energetic and hard working developers
and devops engineers.

Interested in working at Dealerdirect?
Then please be sure to check out [our vacancies][vacancies].

Did not find a matching vacancy? Just [get in touch][get-in-touch]!

[dealerdirect.com][dealerdirectcom]

## License

The MIT License (MIT)

Copyright (c) 2016-2017 Dealerdirect B.V.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

[awesome-shield]: https://img.shields.io/badge/awesome%3F-yes-brightgreen.svg
[changelog]: https://github.com/Dealerdirect/phpcodesniffer-composer-installer/releases
[codesniffer]: https://github.com/squizlabs/PHP_CodeSniffer
[composer-manual-scripts]: https://getcomposer.org/doc/articles/scripts.md
[composer]: https://getcomposer.org/
[contributing-guidelines]: CONTRIBUTING.md
[contributors]: https://github.com/Dealerdirect/phpcodesniffer-composer-installer/graphs/contributors
[dealerdirectcom]: http://www.dealerdirect.com/en
[definition-ci]: https://en.wikipedia.org/wiki/Continuous_integration
[frenck]: https://github.com/frenck
[get-in-touch]: https://www.dealerdirect.com/en/contact
[license-shield]: https://img.shields.io/github/license/dealerdirect/phpcodesniffer-composer-installer.svg
[maintenance-shield]: https://img.shields.io/maintenance/yes/2017.svg
[packagist-shield]: https://img.shields.io/packagist/dt/dealerdirect/phpcodesniffer-composer-installer.svg
[packagist-version-shield]: https://img.shields.io/packagist/v/dealerdirect/phpcodesniffer-composer-installer.svg
[packagist-version]: https://packagist.org/packages/dealerdirect/phpcodesniffer-composer-installer
[packagist]: https://packagist.org/packages/dealerdirect/phpcodesniffer-composer-installer
[project-stage-shield]: https://img.shields.io/badge/Project%20Stage-Development-yellowgreen.svg
[scrutinizer-shield]: https://img.shields.io/scrutinizer/g/dealerdirect/phpcodesniffer-composer-installer.svg
[scrutinizer]: https://scrutinizer-ci.com/g/dealerdirect/phpcodesniffer-composer-installer/
[travis-shield]: https://img.shields.io/travis/Dealerdirect/phpcodesniffer-composer-installer.svg
[travis]: https://travis-ci.org/Dealerdirect/phpcodesniffer-composer-installer
[tutorial]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial
[using-composer-plugins]: https://getcomposer.org/doc/articles/plugins.md#using-plugins
[vacancies]: https://www.dealerdirect.com/en/vacancies
[versioneye-shield]: https://www.versioneye.com/user/projects/580be0d1d65a7716b613a790/badge.svg
[versioneye]: https://www.versioneye.com/user/projects/580be0d1d65a7716b613a790
