<?php

namespace Sirius;

require './vendor/autoload.php';

use Sirius\utils\JsonParser;

class CommandManager
{
    public const HELP_ARGUMENT = 'help';
    private array $commandList = [];
    private array $argumentsList = [];
    private array $optionsList = [];
    private Kernel $kernel;
    protected ?Container $container = null;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->kernel->bootApp();
    }
    public function main($command, $arguments)
    {
        $this->initialize($command, $arguments);
    }

    protected function getKernel(): Kernel
    {
        return $this->kernel;
    }

    private function initialize(string $command, array $arguments): void
    {
        $this->container = $this->kernel->getContainer();
        $this->addCommands();
        $this->addParams($arguments);
        $this->runCommand($command);
    }

    private function addCommands(): void
    {
        $commands = JsonParser::parseFile(__DIR__.'/commands/commands.json');

        if (file_exists(ROOT_DIR. '/config/commands.json')) {
            $userCommands = JsonParser::parseFile(ROOT_DIR . '/config/commands.json');
            foreach ($userCommands as $name => $command) {
                if (isset($commands[$name])) {
                    continue;
                }

                $commands[$name] = $command;
            }
        }

        foreach ($commands as $command) {
            $this->commandList[$command['name']] = new $command['class'];
        }
    }

    private function addParams(array $arguments): void
    {
        foreach ($arguments as $arg) {
            if (preg_match("#(.+?)=(.+)#", $arg, $parsedArgs)) {
                $this->argumentsList[$parsedArgs[1]] = $parsedArgs[2];
            } elseif (preg_match("#--(.+)#", $arg, $parsedArgs)) {
                $this->optionsList[$parsedArgs[1]] = true;
            } else {
                echo 'Wrong argument or option : ' . $arg . PHP_EOL;
                exit();
            }
        }
    }

    private function runCommand(string $command)
    {
        if (self::HELP_ARGUMENT === $command) {
            $this->sendHelp();
        }

        $foundCommand = null;
        foreach ($this->commandList as $currentCommand) {
            if ($command === $currentCommand->getName() || $command === $currentCommand->getAlias()) {
                $foundCommand = $currentCommand;
                break;
            }
        }

        if (null === $foundCommand) {
            echo 'Command not found' . PHP_EOL;
            exit();
        }

        foreach ($this->argumentsList as $argument => $value) {
            if (!$foundCommand->hasArgument($argument)) {
                echo 'This argument does not exist : ' . $argument . PHP_EOL;
                exit();
            }
        }

        foreach ($this->optionsList as $option => $value) {
            if (!$foundCommand->hasOption($option)) {
                echo 'This option does not exist : ' . $option . PHP_EOL;
                exit();
            }
        }

        $foundCommand->setParemValues($this->argumentsList, $this->optionsList);
        $foundCommand->run($this->argumentsList);
    }

    private function sendHelp()
    {
        echo 'The available commands are :' . PHP_EOL . '----------------------------' . PHP_EOL;

        foreach ($this->commandList as $command) {
            echo $command->getName() . ' :' . PHP_EOL;
            echo 'alias : ' . ($command->getAlias()??'') . PHP_EOL;
            echo 'description : ' . ($command->getDescription()??'') . PHP_EOL;
            if (!empty($arguments = $command->getArguments())) {
                echo 'arguments :' . PHP_EOL;
                foreach ($arguments as $argument) {
                    echo '- ' . $argument['name'];
                    echo  (' : ' . $argument['description']?? null) . PHP_EOL ;
                }
            }

            if (!empty($options = $command->getOptions())) {
                echo 'options :' . PHP_EOL;
                foreach ($options as $option) {
                    echo '- ' . $option['name'];
                    echo  (' : ' . $option['description']?? null) . PHP_EOL ;
                }
            }


            echo PHP_EOL;
        }

        exit();
    }
}