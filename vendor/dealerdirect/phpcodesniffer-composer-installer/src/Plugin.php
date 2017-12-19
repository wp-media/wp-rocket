<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2016-2017 Dealerdirect B.V.
 * @license MIT
 */

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Package\RootpackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * PHP_CodeSniffer standard installation manager.
 *
 * @author Franck Nijhof <f.nijhof@dealerdirect.nl>
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    const MESSAGE_RUNNING_INSTALLER = 'Running PHPCodeSniffer Composer Installer';
    const MESSAGE_NOTHING_TO_INSTALL = 'Nothing to install or update';
    const MESSAGE_NOT_INSTALLED = 'PHPCodeSniffer is not installed';

    const PACKAGE_NAME = 'squizlabs/php_codesniffer';
    const PACKAGE_TYPE = 'phpcodesniffer-standard';

    const PHPCS_CONFIG_KEY = 'installed_paths';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array
     */
    private $installedPaths;

    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * Triggers the plugin's main functionality.
     *
     * Makes it possible to run the plugin as a custom command.
     *
     * @param Event $event
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     * @throws RuntimeException
     */
    public static function run(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();

        $instance = new static();

        $instance->io = $io;
        $instance->composer = $composer;
        $instance->init();
        $instance->onDependenciesChangedEvent();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     * @throws LogicException
     * @throws RuntimeException
     * @throws ProcessFailedException
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $this->init();
    }

    /**
     * Prepares the plugin so it's main functionality can be run.
     *
     * @throws \RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     * @throws RuntimeException
     */
    private function init()
    {
        $this->installedPaths = array();

        $this->processBuilder = new ProcessBuilder();
        $this->processBuilder->setPrefix($this->composer->getConfig()->get('bin-dir') . DIRECTORY_SEPARATOR . 'phpcs');

        $this->loadInstalledPaths();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => array(
                array('onDependenciesChangedEvent', 0),
            ),
            ScriptEvents::POST_UPDATE_CMD => array(
                array('onDependenciesChangedEvent', 0),
            ),
        );
    }

    /**
     * Entry point for post install and post update events.
     *
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    public function onDependenciesChangedEvent()
    {
        $io = $this->io;
        $isVerbose = $io->isVerbose();

        if ($isVerbose) {
            $io->write(sprintf('<info>%s</info>', self::MESSAGE_RUNNING_INSTALLER));
        }

        if ($this->isPHPCodeSnifferInstalled() === true) {
            $installPathCleaned = $this->cleanInstalledPaths();
            $installPathUpdated = $this->updateInstalledPaths();

            if ($installPathCleaned === true || $installPathUpdated === true) {
                $this->saveInstalledPaths();
            } elseif ($isVerbose) {
                $io->write(sprintf('<info>%s</info>', self::MESSAGE_NOTHING_TO_INSTALL));
            }
        } elseif ($isVerbose) {
            $io->write(sprintf('<info>%s</info>', self::MESSAGE_NOT_INSTALLED));
        }
    }

    /**
     * Load all paths from PHP_CodeSniffer into an array.
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    private function loadInstalledPaths()
    {
        if ($this->isPHPCodeSnifferInstalled() === true) {
            $output = $this->processBuilder
                ->setArguments(array('--config-show', self::PHPCS_CONFIG_KEY))
                ->getProcess()
                ->mustRun()
                ->getOutput();

            $phpcsInstalledPaths = str_replace(self::PHPCS_CONFIG_KEY . ': ', '', $output);
            $phpcsInstalledPaths = trim($phpcsInstalledPaths);

            if ($phpcsInstalledPaths !== '') {
                $this->installedPaths = explode(',', $phpcsInstalledPaths);
            }
        }
    }

    /**
     * Save all coding standard paths back into PHP_CodeSniffer
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    private function saveInstalledPaths()
    {
        // Check if we found installed paths to set.
        if (count($this->installedPaths) !== 0) {
            $paths = implode(',', $this->installedPaths);
            $arguments = array('--config-set', self::PHPCS_CONFIG_KEY, $paths);
            $configMessage = sprintf(
                'PHP CodeSniffer Config <info>%s</info> <comment>set to</comment> <info>%s</info>',
                self::PHPCS_CONFIG_KEY,
                $paths
            );
        } else {
            // Delete the installed paths if none were found.
            $arguments = array('--config-delete', self::PHPCS_CONFIG_KEY);
            $configMessage = sprintf(
                'PHP CodeSniffer Config <info>%s</info> <comment>delete</comment>',
                self::PHPCS_CONFIG_KEY
            );
        }

        $this->io->write($configMessage);

        $configResult = $this->processBuilder
            ->setArguments($arguments)
            ->getProcess()
            ->mustRun()
            ->getOutput()
        ;

        if ($this->io->isVerbose() && !empty($configResult)) {
            $this->io->write(sprintf('<info>%s</info>', $configResult));
        }
    }

    /**
     * Iterate trough all known paths and check if they are still valid.
     *
     * If path does not exists, is not an directory or isn't readable, the path
     * is removed from the list.
     *
     * @return bool True if changes where made, false otherwise
     */
    private function cleanInstalledPaths()
    {
        $changes = false;
        foreach ($this->installedPaths as $key => $path) {
            // This might be a relative path as well
            $alternativePath = realpath($this->getPHPCodeSnifferInstallPath() . DIRECTORY_SEPARATOR . $path);

            if ((is_dir($path) === false || is_readable($path) === false) &&
                (is_dir($alternativePath) === false || is_readable($alternativePath) === false)
            ) {
                unset($this->installedPaths[$key]);
                $changes = true;
            }
        }
        return $changes;
    }

    /**
     * Check all installed packages (including the root package) against
     * the installed paths from PHP_CodeSniffer and add the missing ones.
     *
     * @return bool True if changes where made, false otherwise
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function updateInstalledPaths()
    {
        $changes = false;

        $searchPaths = array(getcwd());
        $codingStandardPackages = $this->getPHPCodingStandardPackages();
        foreach ($codingStandardPackages as $package) {
            $searchPaths[] = $this->composer->getInstallationManager()->getInstallPath($package);
        }

        $finder = new Finder();
        $finder->files()
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->depth('< 4')
            ->name('ruleset.xml')
            ->in($searchPaths);

        // Only version 3.x and higher has support for having coding standard in the root of the directory.
        if ($this->isPHPCodeSnifferInstalled('>= 3.0.0') !== true) {
            $finder->depth('>= 1');
        }

        // Process each found possible ruleset.
        foreach ($finder as $ruleset) {
            $standardsPath = $ruleset->getPath();

            // Pick the directory above the directory containing the standard, unless this is the project root.
            if ($standardsPath !== getcwd()) {
                $standardsPath = dirname($standardsPath);
            }

            // Use relative paths for local project repositories.
            if ($this->isRunningGlobally() === false) {
                $standardsPath = $this->getRelativePath($standardsPath);
            }

            // De-duplicate and add when directory is not configured.
            if (in_array($standardsPath, $this->installedPaths, true) === false) {
                $this->installedPaths[] = $standardsPath;
                $changes = true;
            }
        }

        return $changes;
    }

    /**
     * Iterates through Composers' local repository looking for valid Coding
     * Standard packages.
     *
     * If the package is the RootPackage (the one the plugin is installed into),
     * the package is ignored for now since it needs a different install path logic.
     *
     * @return array Composer packages containing coding standard(s)
     */
    private function getPHPCodingStandardPackages()
    {
        $codingStandardPackages = array_filter(
            $this->composer->getRepositoryManager()->getLocalRepository()->getPackages(),
            function (PackageInterface $package) {
                if ($package instanceof AliasPackage) {
                    return false;
                }
                return $package->getType() === Plugin::PACKAGE_TYPE;
            }
        );

        if (! $this->composer->getPackage() instanceof RootpackageInterface
            && $this->composer->getPackage()->getType() === self::PACKAGE_TYPE
        ) {
            $codingStandardPackages[] = $this->composer->getPackage();
        }

        return $codingStandardPackages;
    }

    /**
     * Searches for the installed PHP_CodeSniffer Composer package
     *
     * @param null|string|\Composer\Semver\Constraint\ConstraintInterface $versionConstraint to match against
     *
     * @return PackageInterface|null
     */
    private function getPHPCodeSnifferPackage($versionConstraint = null)
    {
        $packages = $this
            ->composer
            ->getRepositoryManager()
            ->getLocalRepository()
            ->findPackages(self::PACKAGE_NAME, $versionConstraint);

        return array_shift($packages);
    }

    /**
     * Returns the path to the PHP_CodeSniffer package installation location
     *
     * @return string
     */
    private function getPHPCodeSnifferInstallPath()
    {
        return $this->composer->getInstallationManager()->getInstallPath($this->getPHPCodeSnifferPackage());
    }

    /**
     * Simple check if PHP_CodeSniffer is installed.
     *
     * @param null|string|\Composer\Semver\Constraint\ConstraintInterface $versionConstraint to match against
     *
     * @return bool Whether PHP_CodeSniffer is installed
     */
    private function isPHPCodeSnifferInstalled($versionConstraint = null)
    {
        return ($this->getPHPCodeSnifferPackage($versionConstraint) !== null);
    }

    /**
     * Test if composer is running "global"
     * This check kinda dirty, but it is the "Composer Way"
     *
     * @return bool Whether Composer is running "globally"
     *
     * @throws \RuntimeException
     */
    private function isRunningGlobally()
    {
        return ($this->composer->getConfig()->get('home') === getcwd());
    }

    /**
     * Returns the relative path to PHP_CodeSniffer from any other absolute path
     *
     * @param string $to Absolute path
     *
     * @return string Relative path
     */
    private function getRelativePath($to)
    {
        $from = $this->getPHPCodeSnifferInstallPath();

        // Some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // Find first non-matching dir
            if ($dir === $to[$depth]) {
                // Ignore this directory
                array_shift($relPath);
            } else {
                // Get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // Add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}
