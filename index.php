<?php

date_default_timezone_set('Asia/Tokyo');
require_once __DIR__ . '/vendor/autoload.php';

use DOMWrap\Document;
use GuzzleHttp\Client;

$baseOsakaAreaUrl = 'https://www.green-japan.com/area/27/01';
$baseOsakaAreaPagingUrl = 'https://www.green-japan.com/area/27/01?page=%d';
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

for ($i=1; $i < $totalPages; $i++)
{
  $url = sprintf($baseOsakaAreaPagingUrl, $i);

  $response = $client->get($url);
  $html = (string) $response->getBody();
  $node = $doc->html($html);
  
  $titles = $node->find($titleSelector);
  $annualIncomes = $node->find($annualIncomeSelector);
  $companyNames = $node->find($companyNameSelector);
  
  $array = [];
  
  foreach ($titles as $index => $title)
  {
    $array[$index]['title'] = $title->text();
  }
  
  foreach ($annualIncomes as $index => $annualIncome)
  {
    $array[$index]['annualIncome'] = $annualIncome->text();
  }
  
  foreach ($companyNames as $index => $companyName)
  {
    $array[$index]['companyName'] = $companyName->text();
  }
  
  foreach($array as $item)
  {
    echo $item['companyName'];
    echo $item['annualIncome'];
    echo $item['title'], PHP_EOL;
    echo '=============================', PHP_EOL;
  }

}

