#!/usr/bin/env php
<?php
require __DIR__ . '/../Vendor/autoload.php';

date_default_timezone_set('Asia/Tokyo');

use Symfony\Component\Console\Application;
use Dotenv\Dotenv;
use Html2Pdf\Console\Command\Html2Pdf as Html2PdfCommand;
use Html2Pdf\Console\Command\GetHtml as GetHtmlCommand;

$rootDir = __DIR__ . '/..';
putenv('BATCH_ROOT_DIR=' . $rootDir);

$dotenv = new Dotenv(getenv('BATCH_ROOT_DIR'));
$dotenv->load();

$application = new Application();
$application->add(new Html2PdfCommand);
$application->add(new GetHtmlCommand);
$result = $application->run();

exit($result);