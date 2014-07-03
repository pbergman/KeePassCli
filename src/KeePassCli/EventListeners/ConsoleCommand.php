<?php
/**
* @author    Philip Bergman <pbergman@live.nl>
* @copyright Philip Bergman
*/

namespace KeePassCli\EventListeners;

use \PBergman\KeePass\Application;
use \KeePassCli\SHMHelper;
use \Symfony\Component\Console\Event\ConsoleCommandEvent;
use \Symfony\Component\Console\Helper\DialogHelper;
use \Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommand implements EventListenerInterface
{
    public function listenerAction(ConsoleCommandEvent $event)
    {

        /** @var \KeePassCli\Commands\ApplicationInterface $command */
        $command    = $event->getCommand();
        /** @var  \ReflectionClass $refCommand */
        $refCommand = new \ReflectionClass($command);
        /** @var DialogHelper $dialog */
        $dialog     = $event->getCommand()->getHelperSet()->get('dialog');
        /** @var OutputInterface $output */
        $output     = $event->getOutput();

        if ($refCommand->isSubclassOf('\KeePassCli\Commands\ApplicationInterface')) {

            $shm     = new SHMHelper();
            $keePass = new Application(
                function() use ($shm, $dialog, $output){

                    if (false === $pwd = $shm->get()) {

                        $pwd = $dialog->askHiddenResponse($output, 'KeePass database password: ', true);

                        $shm->save($pwd);
                    }

                    return $pwd;
                }
            );

            $command->setKeePassApplication($keePass);

        }

        /**
         * Add a pre command if exists, looks for same class as running command
         * in this namespace that extends PreCommandListenerInterface.
         *
         * So for example ListCommand is running will check if __NAMESPACE__\ListCommand
         * exists and extends __NAMESPACE__\PreCommandListenerInterface, if so
         * listenerAction will be triggered of that class
         */

        $preCommandClass = sprintf('%s\%s', __NAMESPACE__,  $refCommand->getShortName());

        if (class_exists($preCommandClass)) {

            $refPreCommandClass = new \ReflectionClass($preCommandClass);

            if ($refPreCommandClass->isSubclassOf(sprintf('%s\PreCommandListenerInterface', __NAMESPACE__))) {

                call_user_func_array(array($preCommandClass,'listenerAction'), array(&$event));

            }
        }

    }
}