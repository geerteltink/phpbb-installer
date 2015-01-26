<?php

namespace TwentyFirstHall\PhpbbInstaller;

use Composer\Script\Event;

class ScriptHandler
{
    private static $permissions = array(
        '/cache' => 0777,
        '/files' => 0777,
        '/images/avatars/upload' => 0777,
        '/store' => 0777,
        '/config.php' => 0640
    );

    private static $resources = array(
        // Complete dirs
        '/adm' => '/adm',
        '/assets' => '/assets',
        '/bin' => '/bin',
        '/cache' => '/cache',
        '/config' => '/config',
        '/download' => '/download',
        '/ext' => '/ext',
        '/files' => '/files',
        '/images' => '/images',
        '/includes' => '/includes',
        '/install' => '/install',
        '/language' => '/language',
        '/phpbb' => '/phpbb',
        '/store' => '/store',
        '/styles' => '/styles',

        // Files
        '/app.php' => '/app.php',
        '/common.php' => '/common.php',
        '/cron.php' => '/cron.php',
        '/faq.php' => '/faq.php',
        '/feed.php' => '/feed.php',
        '/index.php' => '/index.php',
        '/mcp.php' => '/mcp.php',
        '/memberlist.php' => '/memberlist.php',
        '/posting.php' => '/posting.php',
        '/report.php' => '/report.php',
        '/search.php' => '/search.php',
        '/ucp.php' => '/ucp.php',
        '/viewforum.php' => '/viewforum.php',
        '/viewonline.php' => '/viewonline.php',
        '/viewtopic.php' => '/viewtopic.php',
    );

    /**
     * Install phpBB
     *
     * Run ``composer run-script post-install-cmd`` to test the script.
     *
     * @param Event $event
     */
    public static function install(Event $event)
    {
        $eventName = $event->getName();
        $io = $event->getIO();

        $composer = $event->getComposer();
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $installationManager = $composer->getInstallationManager();

        // Get project dir
        $projectDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

        // Get phpbb installation dir
        $topExtra = $composer->getPackage()->getExtra();
        $installationDir = 'phpbb';
        if (!empty($topExtra['phpbb-install-dir'])) {
            $installationDir = $topExtra['phpbb-install-dir'];
        }

        $dest = $projectDir . DIRECTORY_SEPARATOR  . $installationDir;

        $phpbbPackage = false;
        /* @var $package \Composer\Package\PackageInterface */
        foreach ($packages as $package) {
            if ($package->getName() == 'phpbb/phpbb'
                && version_compare($package->getVersion(), '3.1.0') >= 0
            ) {
                $io->write(sprintf('<info>Detected phpBB %s</info>', $package->getVersion()));
                $phpbbPackage = $package;
            }
        }

        if (!$phpbbPackage) {
            $io->write('<error>phpBB is not installed!</error>');
            return;
        }

        if (!is_dir($dest)) {
            if (!mkdir($dest, 0644, true)) {
                $io->write(sprintf('<error>Failed to create destination: </error>', $dest));
                return;
            }
            $io->write(sprintf('<info>Created destination: </info>', $dest));
        }

        // Get phpBB vendor dir
        $src = $installationManager->getInstallPath($phpbbPackage);

        // Copy resources
        $io->write('<info>Copying resources</info>', false);
        foreach (self::$resources as $resource => $destination) {
            self::xcopy($src . $resource, $dest . $destination, 0644);
            $io->write('.', false);
        }
        $io->write(' <info>Done!</info>');

        // Set permissions
        $io->write('<info>Setting permissions</info>', false);
        foreach (self::$permissions as $resource => $permission) {
            if (!file_exists($dest . $resource)) {
                $io->write('<comment>.</comment>', false);
            } elseif (chmod($dest . $resource, $permission)) {
                $io->write('.', false);
            } else {
                $io->write('<error>F</error>', false);
            }
        }
        $io->write(' <info>Done!</info>');

        // Setup .htaccess
        $autoloader = $projectDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        $content = <<<EOF
SetEnv PHPBB_NO_COMPOSER_AUTOLOAD true
SetEnv PHPBB_AUTOLOAD $autoloader

EOF;
        $io->write('<info>Patching .htaccess</info>', false);
        $content .= file_get_contents($src . DIRECTORY_SEPARATOR . '.htaccess');
        if (false !== file_put_contents($dest . DIRECTORY_SEPARATOR . '.htaccess', $content)) {
            $io->write('.', false);
        } else {
            $io->write('<error>F</error>', false);
        }
        $io->write(' <info>Done!</info>');

        // Ready
        $io->write(sprintf(
            '<comment>Please open %s and delete %s afterwards.</comment>',
            'http://your-site.com/install/database_update.php',
            $dest . DIRECTORY_SEPARATOR . 'install'
        ));
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @param       string $source Source path
     * @param       string $dest Destination path
     * @param int|string $permissions New folder creation permissions
     *
     * @return bool Returns true on success, false on failure
     */
    private static function xcopy($source, $dest, $permissions = 0644)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }
}
