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
#use Symfony\Component\Console\Input\InputArgument;
#use Symfony\Component\Console\Input\InputInterface;
#use Symfony\Component\Console\Output\OutputInterface;
#use Symfony\Component\Filesystem\Filesystem;
#use UAParser\Util\Fetcher;

class UAParser_Command_FetchCommand extends Symfony_Component_Console_Command_Command
{
    /** @var string */
    private $defaultYamlFile;

    public function __construct($defaultYamlFile)
    {
        $this->defaultYamlFile = $defaultYamlFile;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ua-parser:fetch')
            ->setDescription('Fetches an updated YAML file for ua-parser.')
            ->addArgument(
                'file',
                Symfony_Component_Console_Input_InputArgument::OPTIONAL,
                '_regexes.yaml output file',
                $this->defaultYamlFile
            )
        ;
    }

    protected function execute(Symfony_Component_Console_Input_InputInterface $input, Symfony_Component_Console_Output_OutputInterface $output)
    {
        $fs = new Symfony_Component_Filesystem_Filesystem();
        $fetcher = new UAParser_Util_Fetcher();
        $fs->dumpFile($input->getArgument('file'), $fetcher->fetch());
    }
}
