<?php
/**
* @author    Philip Bergman <pbergman@live.nl>
* @copyright Philip Bergman
*/

namespace KeePassCli\EventListeners;

use \KeePass\Application;
use \SharedMemory\Controller;
use \Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleCommand implements EventListenerInterface
{
    public function listenerAction(ConsoleCommandEvent $event){

        /** @var \KeePassCli\Commands\ApplicationInterface $command */
        $command    = $event->getCommand();
        $refCommand = new \ReflectionClass($command);

        if ($refCommand->isSubclassOf('\KeePassCli\Commands\ApplicationInterface')) {

            $shm     = new Controller();
            $keePass = new Application(
                function() use ($shm){
                    if ( false === $pwd = $shm->varGet('kpdbpw')) {
                        if (function_exists('readline')) {
                            $pwd = \readline('KeePass database password: ');
                            $shm->varSet('kpdbpw', $pwd);
                        } else {
                            $pwd = false;
                        }

                    }

                    return $pwd;
                }
            );

            $command->setKeePassApplication($keePass);

        }

    }
}