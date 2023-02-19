<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class MitmProxyCommand extends Command implements SignalableCommandInterface
{
    /**
     * @param array<int> $signals
     * @param \Closure|null $signalsHandler
     */
    public function __construct(
        protected array $signals = [\SIGINT, \SIGTERM],
        protected ?\Closure $signalsHandler = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'config',
            shortcut: 'c',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Path to config file',
            default: MITM_CONFIG_DIR . '/config.php'
        );
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedSignals(): array
    {
        return $this->signals;
    }

    /**
     * @inheritDoc
     */
    public function handleSignal(int $signal): void
    {
        if (!$this->signalsHandler && in_array($signal, $this->signals, true)) {
            exit(0);
        }

        $this->signalsHandler->call($this);
    }
}
