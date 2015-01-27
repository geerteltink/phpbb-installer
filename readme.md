# Composer phpBB installer

This is a composer script which installs phpBB to a given web root. The phpbb files are copied from its vendor dir. Existing files are overwritten. So make sure you don't hack the phpBB source files but use [extensions](https://wiki.phpbb.com/Category:Extensions) and [child themes](https://wiki.phpbb.com/Template_Inheritance_Changes_in_3.1).

Although this is tested on Windows 8.1 and [vagrant-phpbb](https://github.com/twentyfirsthall/vagrant-phpbb), **use at your own risk!!!** Make backups and preferable use a [test server](https://github.com/twentyfirsthall/vagrant-phpbb) before messing up your production server.

To let phpBB know where the vendor lib is located, 2 settings are added to ``.htaccess``. So don't hack into this file.

## Getting Started

1. In your composer.json project file require phpbb-installer.

        "require": {
            "twentyfirsthall/phpbb-installer": "dev-master",
            "phpbb/phpbb": "3.1.*",
            "composer/installers": "~1.0"
        }

2. Add the installer scripts.

        "scripts": {
            "post-update-cmd": "TwentyFirstHall\\PhpbbInstaller\\ScriptHandler::install",
            "post-install-cmd": "TwentyFirstHall\\PhpbbInstaller\\ScriptHandler::install"
        },

3. Configure the ``php-install-dir`` and the ``installer-paths`` for phpBB extensions, styles and languages.

        "extra": {
            "phpbb-install-dir"                 : "public",
            "installer-paths": {
                "public/ext/{$vendor}/{$name}/" : ["type:phpbb-extension"],
                "public/styles/{$name}/"        : ["type:phpbb-style"],
                "public/language/{$name}/"      : ["type:phpbb-language"]
            }
        }

4. Add phpBB extensions and themes.

        "require": {
            ...
            "xtreamwayz/activity": "dev-master",
            "xtreamwayz/portal": "dev-master",
            "xtreamwayz/tools": "dev-master"
        },
        "require-dev": {
            "nicofuma/webprofiler": "~1.0",
        }

Run ``composer install`` or ``composer update``.

A full working example can be viewed in the [vagrant-phpbb](https://github.com/twentyfirsthall/vagrant-phpbb) project.
