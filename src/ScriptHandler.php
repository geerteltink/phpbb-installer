<?php

namespace TwentyFirstHall\PhpbbInstaller;

use Composer\Script\Event;

class ScriptHandler
{
    public function postInstall(Event $event)
    {
        $composer = $event->getComposer();
    }

    public function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
    }

    // Test composer scripts with:
    //      composer run-script post-install-cmd
    private function install(Event $event)
    {
        $composer = $event->getComposer();
        $eventName = $event->getName();
        $io = $event->getIO();

        var_dump($eventName);

        // Copy
        //

        // Symlink
        // Install dir is always copied so it can be deleted without deleting the source
    }
}
