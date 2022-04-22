<?php
/**
 * GetHtmlCommand.
 */

namespace Html2Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class GetHtml extends Command
{
    const APP_NAME = 'html2pdf';
    const COMMAND_NAME = 'gethtml';
    const ERROR_CODE = 1;

    /**
     * Config.
     * @var array
     */
    private $config;

    /**
     * Instance of logger.
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * Configuration method.
     *
     * @see Symfony\Component\Console\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName(sprintf('%s:%s', self::APP_NAME, self::COMMAND_NAME))
            ->setDescription('Get Html String.')
            ->addOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                'Target Url.'
            )
        ;

        $this->config = require getenv('BATCH_ROOT_DIR').'/config.php';
        $this->initLogger();
    }

    /**
     * Execute Command.
     *
     * @see Symfony\Component\Console\Command::execute()
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get CommandLine Options.
        $url = $input->getOption('url');

        if (empty($url)) {
            $this->logger->error('Too few arguments.', compact('url'));
            return self::ERROR_CODE;
        }

        echo $this->getHtml($url);

        $this->logger->info('Get Html Success.');
    }

    /**
     * Get Html.
     * @param string $url
     * @return string Html
     */
    private function getHtml($url)
    {
        $this->logger->info('Get Html.', compact('url'));

        if (preg_match('/^s3:\/\//', $url)) {
            $client = new \Aws\S3\S3Client($this->config['aws']['s3']['config']);
            $client->registerStreamWrapper();
        }

        return file_get_contents($url);
    }

    /**
     * init Logger.
     */
    private function initLogger()
    {
        $logLevel = strtoupper(getenv('LOG_LEVEL'));
        $streamHandler = new StreamHandler('php://stderr', Logger::toMonologLevel($logLevel));

        $this->logger = new Logger(sprintf('[%s:%s]', self::APP_NAME, self::COMMAND_NAME));
        $this->logger->pushHandler($streamHandler);
    }
}