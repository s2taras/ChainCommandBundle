<?php

namespace App\ChainCommandBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command for simple using
 *
 * Class BaseCommand
 * @package App\ChainCommandBundle\Command
 */
class BaseCommand extends Command
{
    const COMMAND = 'base:hello';
    const MESSAGE = 'Say hello from Base';
    const DESCRIPTION = 'Hello from Base!';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * FooCommand constructor
     * @param LoggerInterface $logger
     * @param string|null $name
     * @throws LogicException
     */
    public function __construct(LoggerInterface $logger, string $name = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(static::COMMAND)
            ->setDescription(static::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info(static::MESSAGE);
        $output->writeln(static::MESSAGE);

        return Command::SUCCESS;
    }
}
