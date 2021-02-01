<?php

namespace App\ChainCommandBundle\EventSubscriber;

use App\ChainCommandBundle\Exception\MemberException;
use App\ChainCommandBundle\Helper\ChainTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Command subscriber for handling chains and single commands
 *
 * Class CommandSubscriber
 * @package App\ChainCommandBundle\EventSubscriber
 */
class CommandSubscriber implements EventSubscriberInterface
{
    use ChainTrait;

    const PARENT_COMMAND_REGISTER_LOG = '%s is a master command of a command chain that has registered member commands';
    const PARENT_COMMAND_EXECUTION_START_LOG = 'Executing %s command itself first:';
    const PARENT_COMMAND_EXECUTION_CHILDREN_LOG = 'Executing %s chain members:';
    const PARENT_COMMAND_EXECUTION_END_LOG = 'Execution of %s chain completed.';
    const CHILD_COMMAND_REGISTER_LOG = '%s registered as a member of %s command chain';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array Chains configuration
     */
    protected $chainCommands = [];

    /**
     * @var Application
     */
    protected $application;

    /**
     * CommandSubscriber constructor
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set chain commands
     * @param $chainCommands
     */
    public function setChainCommands($chainCommands)
    {
        $this->chainCommands = $chainCommands;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['beforeCommand', 15],
            ConsoleEvents::TERMINATE => ['afterCommand', 15]
        ];
    }

    /**
     * Validate children commands and execute parent command
     * @param ConsoleCommandEvent $event
     * @throws MemberException
     */
    public function beforeCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $commandName = $command->getName();

        if ($this->isChild($commandName)) {
            throw new MemberException('some:command');
        }

        if ($this->isParent($commandName)) {
            $this->logger->info(sprintf(self::PARENT_COMMAND_REGISTER_LOG, $commandName));

            foreach ($this->getChildren($commandName) as $childCommand) {
                $this->logger->info(sprintf(self::CHILD_COMMAND_REGISTER_LOG, $childCommand, $commandName));
            }

            $this->logger->info(sprintf(self::PARENT_COMMAND_EXECUTION_START_LOG, $commandName));
            $this->application = $command->getApplication();
        }
    }

    /**
     * Execute children commands
     * @param ConsoleTerminateEvent $event
     * @throws CommandNotFoundException
     */
    public function afterCommand(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();
        $commandName = $command->getName();

        if ($this->isParent($commandName)) {
            $this->logger->info(sprintf(self::PARENT_COMMAND_EXECUTION_CHILDREN_LOG, $commandName));

            foreach ($this->getChildren($commandName) as $childrenCommand) {
                $child = $this->application->get($childrenCommand);
                $child->run($event->getInput(), $event->getOutput());
            }

            $this->logger->info(sprintf(self::PARENT_COMMAND_EXECUTION_END_LOG, $commandName));
        }
    }
}
