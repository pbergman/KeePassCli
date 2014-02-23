<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class CacheClear extends Command implements ApplicationInterface
{

    /** @var \KeePass\Application  */
    protected $application;

    /**
     * @param \KeePass\Application $application
     */
    public function setKeePassApplication(\KeePass\Application $application)
    {
        $this->application = $application;
    }

    protected function configure()
    {
        $this
            ->setName('cache:clear:all')
            ->setDescription('Clears all cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->application->get('entity_controller')->removeCache(true,true);
        $output->writeln("<info>Cache cleared</info>");
    }
}
