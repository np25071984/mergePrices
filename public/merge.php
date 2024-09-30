<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

include "../vendor/autoload.php";

define("OUTPUT_FOLE_NAME", "output.xlsx",);
define("FORMAT_CURRENCY_RUB_INTEGER", '#,##0_-'); // '#,##0_-[$руб]'

function validateFile1(Spreadsheet $spreadsheet): bool
{
    $sheet = $spreadsheet->getActiveSheet();
    if ($sheet->getCell('A1')->getValue() !== "Артикул") {
        return false;
    }
    if ($sheet->getCell('B1')->getValue() !== "Наименование") {
        return false;
    }
    if ($sheet->getCell('C1')->getValue() !== "Прайс") {
        return false;
    }
    if ($sheet->getCell('D1')->getValue() !== "Заказ") {
        return false;
    }
    return true;
}

function validateFile2(Spreadsheet $spreadsheet): bool
{
    $sheet = $spreadsheet->getActiveSheet();
    if ($sheet->getCell('D1')->getValue() !== "Прайс-лист") {
        return false;
    }
    if ($sheet->getCell('D6')->getValue() !== "Бренд") {
        return false;
    }
    if ($sheet->getCell('E6')->getValue() !== "Артикул") {
        return false;
    }
    if ($sheet->getCell('G6')->getValue() !== "Номенклатура") {
        return false;
    }
    if ($sheet->getCell('H6')->getValue() !== "Цена") {
        return false;
    }
    if ($sheet->getCell('J6')->getValue() !== "Заказ") {
        return false;
    }
    if ($sheet->getCell('K6')->getValue() !== "Сумма") {
        return false;
    }
    return true;
}

function redirectWithError(string $errorMsg) {
    session_start();
    $_SESSION["error_msg"] = $errorMsg;
    header("Location: index.php");
    exit;
}

if (!isset($_FILES['file1']) || !isset($_FILES['file2'])) {
    redirectWithError("Оба файла должны быть загружены!");
}

$file1 = "../files/" . basename($_FILES['file1']['tmp_name']);
if (!move_uploaded_file($_FILES['file1']['tmp_name'], $file1)) {
    redirectWithError("Не удалось загрузить " . $_FILES['file1']["name"] . " файл");
}

$file2 = "../files/" . basename($_FILES['file2']['tmp_name']);
if (!move_uploaded_file($_FILES['file2']['tmp_name'], $file2)) {
    redirectWithError("Не удалось загрузить " . $_FILES['file2']["name"] . " файл");
}

if (file_exists(OUTPUT_FOLE_NAME)) {
    unlink(OUTPUT_FOLE_NAME);
}

$reader = IOFactory::createReader("Xlsx");

$spreadsheet = $reader->load($file1);
if (!validateFile1($spreadsheet)) {
    redirectWithError("Файл " . $file1 . " имеет неправильный формат или был изменен");
}

$file1Data = [];
$currentBrand = null;
$rows = $spreadsheet->getActiveSheet()->toArray();
foreach ($rows as $i => $r) {
    if (in_array($r[0], ["", "Артикул"])) {
        $currentBrand = $r[1];
        continue;
    }
    $articl = str_replace([";"], [""], $r[0]);
    $name = str_replace([";"], [""], $r[1]);
    $price = str_replace([","], [""], $r[2]);
    $file1Data[$currentBrand][] = [$articl, $name, $price];
}

$spreadsheet = $reader->load($file2);
if (!validateFile2($spreadsheet)) {
    redirectWithError("Файл " . $file2 . " имеет неправильный формат или был изменен");
}

$file2Data = [];
$rows = $spreadsheet->getActiveSheet()->toArray();
foreach ($rows as $r) {
    if (in_array($r[0], ["", "GUID"])) {
        continue;
    }
    $brand = str_replace([";"], [""], $r[3]);
    $articl = str_replace([";"], [""], $r[4]);
    $name = str_replace([";"], [""], $r[6]);
    $price = str_replace(",", "", $r[7]); // remove number formattiong
    $price = ceil($price * 1.07);
    $file2Data[$brand][] = [$articl, $name, $price];
}

$result = $file1Data + $file2Data;
ksort($result);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle(mb_substr("Прайс", 0, Worksheet::SHEET_TITLE_MAXIMUM_LENGTH, 'utf-8'));
$sheet->getStyle("A:A")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("C:C")->getNumberFormat()->setFormatCode(FORMAT_CURRENCY_RUB_INTEGER);

$sheet->getColumnDimension('A')->setWidth(16.5);
$sheet->getColumnDimension('B')->setWidth(89);
$sheet->getColumnDimension('C')->setWidth(22.5);
$sheet->getColumnDimension('D')->setWidth(22.5);
$sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue("A1", "Артикул");
$sheet->getStyle("B1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue("B1", "Наименование");
$sheet->getStyle("C1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue("C1", "Цена");
$sheet->getStyle("D1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue("D1", "Заказ");

$currentLine = 2;
foreach ($result as $brand => $items) {
    $sheet->mergeCells("A{$currentLine}:D{$currentLine}");
    $sheet->getStyle("A{$currentLine}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A{$currentLine}")->applyFromArray(['font' => [
        'bold' => true,
    ]]);
    $sheet->setCellValue("A{$currentLine}", $brand);
    $currentLine++;
    foreach ($items as $item) {
        $sheet->setCellValue("A{$currentLine}", $item[0]);
        $sheet->setCellValue("B{$currentLine}", $item[1]);
        $sheet->setCellValue("C{$currentLine}", $item[2]);
        $currentLine++;
    }
}
$writer = new Xlsx($spreadsheet);

$writer->save(OUTPUT_FOLE_NAME);

unlink($file1);
unlink($file2);

header('Location: ' . OUTPUT_FOLE_NAME);