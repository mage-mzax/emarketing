<?php
/*
 * NOTICE:
 * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.
 */
/**
 * ua-parser
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 *
 * Released under the MIT license
 */
#namespace UAParser\Command;

#use Symfony\Component\Console\Command\Command;
#use Symfony\Component\Console\Input\InputInterface;
#use Symfony\Component\Console\Input\InputOption;
#use Symfony\Component\Console\Output\OutputInterface;
#use UAParser\Util\Converter;
#use UAParser\Util\Fetcher;

class UAParser_Command_UpdateCommand extends Symfony_Component_Console_Command_Command
{
    /** @var string */
    private $resourceDirectory;

    public function __construct($resourceDirectory)
    {
        $this->resourceDirectory = $resourceDirectory;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ua-parser:update')
            ->setDescription('Fetches an updated YAML file for ua-parser and overwrites the current PHP file.')
            ->addOption(
                'no-backup',
                null,
                Symfony_Component_Console_Input_InputOption::VALUE_NONE,
                'Do not backup the previously existing file'
            )
        ;
    }

    protected function execute(Symfony_Component_Console_Input_InputInterface $input, Symfony_Component_Console_Output_OutputInterface $output)
    {
        $fetcher = new UAParser_Util_Fetcher();
        $converter = new UAParser_Util_Converter($this->resourceDirectory);

        $converter->convertString($fetcher->fetch(), !$input->getOption('no-backup'));
    }
}
