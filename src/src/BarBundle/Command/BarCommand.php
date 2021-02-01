<?php

namespace App\BarBundle\Command;

use App\ChainCommandBundle\Command\BaseCommand;

/**
 * Class for demonstrate command bar:hi
 *
 * Class BarCommand
 * @package App\BarBundle\Command
 */
class BarCommand extends BaseCommand
{
    const COMMAND = 'bar:hi';
    const MESSAGE = 'Hi from Bar!';
    const DESCRIPTION = 'Say hi from Bar';
}
