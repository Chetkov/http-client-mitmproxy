<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Console\Command;

use Chetkov\HttpClientMitmproxy\DefaultFactory;
use Chetkov\HttpClientMitmproxy\Enum\AppMode;
use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\MitmProxyFactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mitm-proxy:start')]
class MitmProxyCommand extends Command
{
    /** @var string */
    protected static $defaultDescription = 'Starts a mitm-proxy client.';

    /**
     * @param DefaultFactory $factory
     */
    public function __construct(
        private MitmProxyFactoryInterface $factory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('app-mode', 'm', InputOption::VALUE_OPTIONAL, 'One of: ' . implode(', ', AppMode::possibles()))
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'One of: ' . implode(', ', Format::possibles([(string) Format::text()])))
            ->addOption('editor', 'e', InputOption::VALUE_OPTIONAL, 'One of: ' . implode(', ', Editor::possibles()))
            ->addOption('temp-dir', 'd', InputOption::VALUE_OPTIONAL, 'Temporary directory')
            ->setHelp('This command allows you to start a proxy-client and change the data sent/received by the backend code')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $tempDir = $input->getOption('temp-dir') ?? MITM_TEMP_DIR;

            $proxyUid = md5(uniqid((string) getmypid(), true));

            $appMode = $input->getOption('app-mode');
            if ($appMode) {
                $appMode = AppMode::fromValue($appMode);
            }

            $format = $input->getOption('format');
            if ($format) {
                $format = Format::fromValue($format);
            }

            $editor = $input->getOption('editor');
            if ($editor) {
                $editor = Editor::fromValue($editor);
            }

            $this->factory
                ->createProxyClient($proxyUid, $tempDir)
                ->start($appMode, $format, $editor);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error((string) $e);
            return Command::FAILURE;
        }
    }
}
