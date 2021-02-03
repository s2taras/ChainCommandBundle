<?php

namespace App\ChainCommandBundle\EventSubscriber;

use App\ChainCommandBundle\Exception\MemberException;
use App\ChainCommandBundle\Service\ChainCommandService;
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
     * @var Application
     */
    protected $application;

    /**
     * @var ChainCommandService
     */
    protected $commandService;

    /**
     * CommandSubscriber constructor
     * @param LoggerInterface $logger
     * @param ChainCommandService $commandService
     */
    public function __construct(LoggerInterface $logger, ChainCommandService $commandService)
    {
        $this->logger = $logger;
        $this->commandService = $commandService;
    }

    /**
     * Set application if it doesn't exist
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        if ($this->application == null) {
            $this->application = $application;
        }
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

        if ($this->commandService->isChild($commandName)) {
            throw new MemberException($commandName);
        }

        if ($this->commandService->isParent($commandName)) {
            $this->logger->info(sprintf(self::PARENT_COMMAND_REGISTER_LOG, $commandName));

            foreach ($this->commandService->getChildren($commandName) as $childCommand) {
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

        if ($this->commandService->isParent($commandName)) {
            $this->logger->info(sprintf(self::PARENT_COMMAND_EXECUTION_CHILDREN_LOG, $commandName));

            foreach ($this->commandService->getChildren($commandName) as $childrenCommand) {
                $child = $this->application->get($childrenCommand);
                $child->run($event->getInput(), $event->getOutput());
            }

            $this->logger->info(sprintf(self::PARENT_COMMAND_EXECUTION_END_LOG, $commandName));
        }
    }
}
