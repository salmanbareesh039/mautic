<?php

namespace Mautic\MarketplaceBundle\Command;

use Mautic\CoreBundle\Helper\ComposerHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommand extends Command
{
    protected static $defaultName = self::NAME;
    public const NAME = 'mautic:marketplace:remove';

    public function __construct(private ComposerHelper $composer, private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('package', InputArgument::REQUIRED, 'The Packagist package of the plugin to remove (e.g. mautic/example-plugin)');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Removing '.$input->getArgument('package').', this might take a while...');
        $packageVendorAndName = $input->getArgument('package');

        // Just checking the package type so that the user doesn't accidentially removes a core package
        if (!in_array($packageVendorAndName, $this->composer->getMauticPluginPackages())) {
            $output->writeln('This package cannot be removed, it must be of type mautic-plugin');

            return \Symfony\Component\Console\Command\Command::FAILURE;
        }

        $removeResult = $this->composer->remove($packageVendorAndName);

        if (0 !== $removeResult->exitCode) {
            $message = 'Error while removing plugin through Composer: '.$removeResult->output;
            $this->logger->error($message);
            $output->writeLn($message);

            return \Symfony\Component\Console\Command\Command::FAILURE;
        }

        $output->writeln($input->getArgument('package').' has successfully been removed.');

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
    protected static $defaultDescription = 'Removes a plugin that is currently installed';
}
