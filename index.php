<?php

date_default_timezone_set('Asia/Tokyo');
require_once __DIR__ . '/vendor/autoload.php';

use DOMWrap\Document;
use GuzzleHttp\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
define('CREDENTIAL_PATH', $_ENV["SERVICE_KEY_JSON"]);
define('SPREADSHEET_ID', $_ENV["SPREADSHEET_ID"]);
putenv("GOOGLE_APPLICATION_CREDENTIALS=" . dirname(__FILE__) . '/' . CREDENTIAL_PATH);

define('BASE_OSAKA_URL', $_ENV["BASE_OSAKA_URL"]);
define('BASE_OSAKA_PAGING_URL', $_ENV["BASE_OSAKA_PAGING_URL"]);

$baseOsakaAreaUrl = BASE_OSAKA_URL;
$baseOsakaAreaPagingUrl = BASE_OSAKA_PAGING_URL;
$titleSelector = 'h3.card-info__heading-area__title';
$annualIncomeSelector = 'ul.job-offer-meta-tags > li:nth-child(1)';
$companyNameSelector = 'h3.card-info__detail-area__box__title';
$totalPagesSelector = 'div.pagers > a:nth-last-child(2)';

$client = new Client;
$doc = new Document;
$response = $client->get($baseOsakaAreaUrl);
$html = (string) $response->getBody();
$node = $doc->html($html);
(int) $totalPages = $node->find($totalPagesSelector)->text() + 1;

$googleClient = new Google_Client();
$googleClient->setApplicationName('test');
$googleClient->useApplicationDefaultCredentials();
$googleClient->addScope(Google_Service_Sheets::SPREADSHEETS);

$titlesByPages = [];
for ($i=1; $i < $totalPages; $i++)
{
  $url = sprintf($baseOsakaAreaPagingUrl, $i);

  $response = $client->get($url);
  $html = (string) $response->getBody();
  $nodes = $doc->find($titleSelector);

  $nodes->each(function ($node) use (&$titlesByPages) {
    $titlesByPages[] = $node->nodeValue;
  });
  // $annualIncomes[] = $node->find($annualIncomeSelector);
  // $companyNames[] = $node->find($companyNameSelector);
  
  // foreach ($titles as $index => $title)
  // {
  //   $array[$index]['title'] = $title->text();
  // }
  
  // foreach ($annualIncomes as $index => $annualIncome)
  // {
  //   $array[$index]['annualIncome'] = $annualIncome->text();
  // }
  
  // foreach ($companyNames as $index => $companyName)
  // {
  //   $array[$index]['companyName'] = $companyName->text();
  // }
}
var_dump($titlesByPages);
for($i=1; $i < 50; $i++)
{
  // echo $item['companyName'];
  // echo $item['annualIncome'];
  // echo $item['title'], PHP_EOL;
  // echo '=============================', PHP_EOL;

  $service = new Google_Service_Sheets($googleClient);

  $value = new Google_Service_Sheets_ValueRange();
  $value->setValues(['values' => $titlesByPages[$i]]);
  $rowNum = $i + 1;

  $response = $service->spreadsheets_values->update(SPREADSHEET_ID, 'シート1!A' . $rowNum, $value, ['valueInputOption' => 'USER_ENTERED']);
}


