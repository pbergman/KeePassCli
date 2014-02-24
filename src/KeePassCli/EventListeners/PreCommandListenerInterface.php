<?php
/**
* @author    Philip Bergman <pbergman@live.nl>
* @copyright Philip Bergman
*/

namespace KeePassCli\EventListeners;

interface CommandListenerInterface
{
    public function listenerAction(\Symfony\Component\Console\Event\ConsoleCommandEvent $event);
}