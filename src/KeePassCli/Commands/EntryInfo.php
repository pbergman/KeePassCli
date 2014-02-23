<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use KeePass\KeePass;
use KeePassCli\AutoCompleteIndex;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;


class EntryInfo extends Command implements ApplicationInterface
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
            ->setName('entries:info')
            ->setDescription('Lists info about selected entry')
            ->addArgument(
                'server',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Will show given server'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $ac = new AutoCompleteIndex(
            $this->application->get('keepass')->getEntityController(),
            'entry'
        );

        $ac->build(array('name','namespace'));


        var_dump($ac->getResultByIndex('lynda.com'));exit;

    }

}