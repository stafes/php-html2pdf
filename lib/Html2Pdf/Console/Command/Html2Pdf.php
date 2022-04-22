<?php
/**
 * Html2PdfCommand.
 */

namespace Html2Pdf\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Mpdf\Mpdf;


class Html2Pdf extends Command
{
    const APP_NAME = 'html2pdf';
    const COMMAND_NAME = 'run';
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
     * Instance of mPdf.
     * @var \Mpdf\Mpdf
     */
    private $mpdf;

    /**
     * Configuration method.
     *
     * @see Symfony\Component\Console\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName(sprintf('%s:%s', self::APP_NAME, self::COMMAND_NAME))
            ->setDescription('Html2Pdf.')
            ->addOption(
                'option',
                'o',
                InputOption::VALUE_OPTIONAL,
                'mPDF Options, --option="format=B5,orientation=L"'
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
        try {
            $options = $this->parseOption($input->getOption('option'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['option' => $input->getOption('option')]);
            return self::ERROR_CODE;
        }

        // Initialize Pdf.
        $this->initPdf($options);

        // Generate and Save PDF.
        $html = file_get_contents('php://stdin');
        $this->generatePdf($html);
        $this->savePdf();

        $this->logger->info('Success.');
    }

    /**
     * Generate Pdf.
     * @param string $html
     */
    private function generatePdf($html)
    {
        $this->logger->info('Generate PDF.');
        $this->mpdf->WriteHTML($html);
    }

    /**
     * Save Pdf.
     */
    private function savePdf()
    {
        $this->logger->info('Save PDF.');

        echo $this->mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
    }

    /**
     * Parse Command Line Options.
     * @param string $optionString
     * @return array Parameters
     * @throws \Exception
     */
    private function parseOption($optionString)
    {
        if (empty($option)) {
            return [];
        }

        $items = explode(',', $optionString);

        $params = [];
        foreach ($items as $item) {
            $row = explode('=', $item);
            if (count($row) !== 2) {
                throw new \Exception('Option Parse Error.');
            }
            $params[$row[0]] = $row[1];
        }
        return $params;
    }

    /**
     * initialize Pdf.
     * @param array $options
     */
    private function initPdf($options)
    {
        $this->logger->info('Initialize and Setup mpdf.');
        $mpdfConfig = array_merge($this->config['mpdf']['default_config'], $options);

        $mpdfConfig['fontDir'] = (array)(new \Mpdf\Config\ConfigVariables)->getDefaults()['fontDir'];
        $mpdfConfig['fontDir'][] = $this->config['mpdf']['fontDir'];

        $this->mpdf = new Mpdf($mpdfConfig);

        foreach($this->config['mpdf']['fonts'] as $k => $v){
            $this->mpdf->fontdata[$k] = $v;
            $this->mpdf->available_unifonts[] = $k;
            $this->mpdf->default_available_fonts[] = $k;
            $this->mpdf->BMPonly[] = $k;
        }
        $this->mpdf->SetDefaultFont($mpdfConfig['default_font']);
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