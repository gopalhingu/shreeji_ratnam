<?php

namespace App\Services;

use App\Support\ExcelColumn;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use RuntimeException;
use XMLReader;
use ZipArchive;

/**
 * Reads diamond workbooks without materialising empty rows.
 * Excel files often claim a used range of A1:XFD1048576; PhpSpreadsheet's
 * toArray() tries to allocate that whole range and exhausts memory.
 */
class DiamondSheetReader
{
    private $spreadsheet;
    private $tempFiles = [];
    private $formats = [];
    private $sharedStrings = [];
    private $date1904 = false;

    public function __destruct()
    {
        $this->releaseSpreadsheet();
        $this->releaseTempFiles();
    }

    public function rows($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'xls') {
            yield from $this->readXls($path);
            return;
        }

        yield from $this->readXlsx($path);
    }

    private function readXlsx($path)
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive is required to read Excel files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the Excel file.');
        }

        try {
            $sheetPath = $this->resolveSheetPath($zip);
            $sheetFile = $this->extractZipEntry($zip, $sheetPath);
            $sharedFile = $this->extractZipEntry($zip, 'xl/sharedStrings.xml', false);
            $stylesXml = $zip->getFromName('xl/styles.xml');
            if ($sharedFile !== null) {
                $this->sharedStrings = $this->readSharedStrings($sharedFile);
            }
            if ($stylesXml !== false) {
                $this->formats = $this->readFormats($stylesXml);
            }
        } finally {
            $zip->close();
        }

        $previousCalendar = Date::getExcelCalendar();
        Date::setExcelCalendar($this->date1904 ? Date::CALENDAR_MAC_1904 : Date::CALENDAR_WINDOWS_1900);
        try {
            $maxColumn = $this->headerWidth($sheetFile);
            if ($maxColumn < 1) {
                return;
            }
            yield from $this->iterateSheet($sheetFile, $maxColumn);
        } finally {
            Date::setExcelCalendar($previousCalendar);
            $this->sharedStrings = [];
            $this->releaseTempFiles();
        }
    }

    private function readXls($path)
    {
        $reader = IOFactory::createReaderForFile($path);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }
        $this->spreadsheet = $reader->load($path);

        try {
            $sheet = $this->spreadsheet->getActiveSheet();
            $grouped = [];
            foreach ($sheet->getCoordinates(false) as $coordinate) {
                if (!preg_match('/^([A-Z]+)(\d+)$/i', $coordinate, $matches)) {
                    continue;
                }
                $column = ExcelColumn::index($matches[1]);
                $row = (int) $matches[2];
                $cell = $sheet->getCell($coordinate);
                try {
                    $value = $cell->getFormattedValue();
                } catch (\Throwable $e) {
                    $value = $cell->getValue();
                }
                if ($value === '') {
                    $value = null;
                }
                if (is_numeric($value)) {
                    $value = $this->cleanGeneralNumber($value);
                }
                $grouped[$row][$column] = $value;
            }
            ksort($grouped);
            $maxColumn = 1;
            if (isset($grouped[1])) {
                foreach ($grouped[1] as $column => $value) {
                    if ($value !== null && $value !== '' && $column > $maxColumn) {
                        $maxColumn = $column;
                    }
                }
            }
            foreach ($grouped as $rowIndex => $columns) {
                $cells = array_fill(0, $maxColumn, null);
                $hasValue = false;
                foreach ($columns as $column => $value) {
                    if ($column < 1 || $column > $maxColumn) {
                        continue;
                    }
                    $cells[$column - 1] = $value;
                    if ($value !== null && $value !== '') {
                        $hasValue = true;
                    }
                }
                if ((int) $rowIndex === 1 || $hasValue) {
                    yield [
                        'index' => (int) $rowIndex,
                        'cells' => $cells,
                    ];
                }
            }
        } finally {
            $this->releaseSpreadsheet();
        }
    }

    private function resolveSheetPath(ZipArchive $zip)
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $relationshipNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $packageNs = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $workbook->registerXPathNamespace('m', $main);

        $properties = $workbook->xpath('//m:workbookPr');
        if ($properties && isset($properties[0]['date1904'])) {
            $flag = strtolower((string) $properties[0]['date1904']);
            $this->date1904 = $flag === '1' || $flag === 'true';
        }

        $sheets = $workbook->xpath('//m:sheet');
        $active = 0;
        $views = $workbook->xpath('//m:workbookView');
        if ($views && isset($views[0]['activeTab'])) {
            $active = (int) $views[0]['activeTab'];
        }
        if (!$sheets || !isset($sheets[$active])) {
            $active = 0;
        }
        if (!$sheets || !isset($sheets[$active])) {
            return 'xl/worksheets/sheet1.xml';
        }

        $sheetId = (string) $sheets[$active]->attributes($relationshipNs)['id'];
        foreach ($rels->children($packageNs)->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $sheetId) {
                $target = ltrim(str_replace('\\', '/', (string) $relationship['Target']), '/');
                if (strpos($target, 'xl/') !== 0) {
                    $target = 'xl/' . ltrim($target, '/');
                }
                if (strpos($target, '../') !== false) {
                    break;
                }

                return $target;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function extractZipEntry(ZipArchive $zip, $name, $required = true)
    {
        $stream = $zip->getStream($name);
        if ($stream === false) {
            if ($required) {
                throw new RuntimeException('The Excel file does not contain a worksheet.');
            }

            return null;
        }

        $temp = tempnam(sys_get_temp_dir(), 'xlsx_part_');
        if ($temp === false) {
            fclose($stream);
            throw new RuntimeException('Unable to read the Excel file.');
        }
        $output = fopen($temp, 'wb');
        stream_copy_to_stream($stream, $output);
        fclose($output);
        fclose($stream);
        $this->tempFiles[] = $temp;

        return $temp;
    }

    private function readSharedStrings($path)
    {
        $strings = [];
        $reader = $this->openXml($path);
        $index = -1;
        $parts = [];
        $inItem = false;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'si') {
                $inItem = true;
                $parts = [];
                $index++;
                if ($reader->isEmptyElement) {
                    $strings[$index] = '';
                    $inItem = false;
                }
                continue;
            }
            if ($inItem && $reader->nodeType === XMLReader::ELEMENT && $reader->localName === 't') {
                $parts[] = $reader->readString();
                continue;
            }
            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'si') {
                $strings[$index] = implode('', $parts);
                $inItem = false;
            }
        }
        $reader->close();

        return $strings;
    }

    private function readFormats($stylesXml)
    {
        $styles = simplexml_load_string($stylesXml);
        if ($styles === false) {
            return [];
        }
        $main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $styles->registerXPathNamespace('m', $main);
        $custom = [];
        $numFormats = $styles->xpath('//m:numFmts/m:numFmt');
        if ($numFormats) {
            foreach ($numFormats as $numFormat) {
                $custom[(int) $numFormat['numFmtId']] = (string) $numFormat['formatCode'];
            }
        }

        $formats = [];
        $cellFormats = $styles->xpath('//m:cellXfs/m:xf');
        if ($cellFormats) {
            foreach ($cellFormats as $index => $cellFormat) {
                $formatId = (int) $cellFormat['numFmtId'];
                if (isset($custom[$formatId])) {
                    $formats[$index] = $custom[$formatId];
                    continue;
                }
                $builtIn = NumberFormat::builtInFormatCode($formatId);
                $formats[$index] = $builtIn !== '' ? $builtIn : 'General';
            }
        }

        return $formats;
    }

    private function headerWidth($sheetFile)
    {
        $reader = $this->openXml($sheetFile);
        $width = 0;
        $nextColumn = 1;
        $inHeader = false;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'row') {
                $rowIndex = (int) $reader->getAttribute('r');
                if ($rowIndex > 1) {
                    break;
                }
                $inHeader = true;
                $nextColumn = 1;
                if ($reader->isEmptyElement) {
                    break;
                }
                continue;
            }
            if (!$inHeader) {
                continue;
            }
            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'row') {
                break;
            }
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'c') {
                continue;
            }
            $parsed = $this->parseCell($reader, $nextColumn);
            $nextColumn = $parsed['column'] + 1;
            if ($parsed['value'] !== null && $parsed['value'] !== '' && $parsed['column'] > $width) {
                $width = $parsed['column'];
            }
        }
        $reader->close();

        return $width;
    }

    private function iterateSheet($sheetFile, $maxColumn)
    {
        $reader = $this->openXml($sheetFile);
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                continue;
            }
            $rowIndex = (int) $reader->getAttribute('r');
            $cells = array_fill(0, $maxColumn, null);
            $hasValue = false;
            $nextColumn = 1;
            if (!$reader->isEmptyElement) {
                while ($reader->read()) {
                    if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'row') {
                        break;
                    }
                    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'c') {
                        continue;
                    }
                    $parsed = $this->parseCell($reader, $nextColumn);
                    $nextColumn = $parsed['column'] + 1;
                    if ($parsed['column'] >= 1 && $parsed['column'] <= $maxColumn) {
                        $cells[$parsed['column'] - 1] = $parsed['value'];
                        if ($parsed['value'] !== null && $parsed['value'] !== '') {
                            $hasValue = true;
                        }
                    }
                }
            }
            if ($rowIndex === 1 || $hasValue) {
                yield [
                    'index' => $rowIndex > 0 ? $rowIndex : 1,
                    'cells' => $cells,
                ];
            }
        }
        $reader->close();
    }

    private function parseCell(XMLReader $reader, $fallbackColumn)
    {
        $type = $reader->getAttribute('t');
        $style = $reader->getAttribute('s');
        $column = $this->columnFromReference($reader->getAttribute('r'));
        if ($column < 1) {
            $column = $fallbackColumn;
        }
        $raw = null;
        $text = [];
        if (!$reader->isEmptyElement) {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->localName === 'c') {
                    break;
                }
                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }
                if ($reader->localName === 'v') {
                    $raw = $reader->readString();
                } elseif ($reader->localName === 't') {
                    $text[] = $reader->readString();
                }
            }
        }

        return [
            'column' => $column,
            'value' => $this->cellValue($type, $style, $raw, $text),
        ];
    }

    private function cellValue($type, $style, $raw, array $text)
    {
        if ($type === 's') {
            $index = (int) $raw;

            return isset($this->sharedStrings[$index]) ? $this->sharedStrings[$index] : '';
        }
        if ($type === 'inlineStr' || $type === 'str') {
            if ($text !== []) {
                return implode('', $text);
            }

            return $raw === null ? null : (string) $raw;
        }
        if ($type === 'b') {
            return ((string) $raw === '1') ? '1' : '0';
        }
        if ($type === 'e' || $raw === null || $raw === '') {
            return null;
        }

        $format = 'General';
        if ($style !== null && isset($this->formats[(int) $style])) {
            $format = $this->formats[(int) $style];
        }
        if (!is_numeric($raw)) {
            return (string) $raw;
        }
        if ($format === '' || strcasecmp($format, 'General') === 0) {
            return $this->cleanGeneralNumber($raw);
        }

        return NumberFormat::toFormattedString($raw + 0, $format);
    }

    /**
     * Excel stores 0.55 as 0.55000000000000004. General format shows the short number.
     */
    private function cleanGeneralNumber($raw)
    {
        $number = (float) $raw;
        if (!is_finite($number)) {
            return (string) $raw;
        }
        $rounded = round($number, 6);
        if (abs($rounded - round($rounded)) < 0.0000001) {
            return sprintf('%.0f', $rounded);
        }
        $text = rtrim(rtrim(sprintf('%.6F', $rounded), '0'), '.');
        if ($text === '' || $text === '-') {
            return '0';
        }

        return $text;
    }

    private function columnFromReference($reference)
    {
        if (!$reference || !preg_match('/^([A-Z]+)/i', $reference, $matches)) {
            return 0;
        }

        return ExcelColumn::index($matches[1]);
    }

    private function openXml($path)
    {
        $reader = new XMLReader();
        if (!$reader->open($path, null, LIBXML_NONET | LIBXML_COMPACT | LIBXML_NOBLANKS)) {
            throw new RuntimeException('Unable to read the Excel worksheet.');
        }

        return $reader;
    }

    private function releaseSpreadsheet()
    {
        if ($this->spreadsheet) {
            $this->spreadsheet->disconnectWorksheets();
            $this->spreadsheet = null;
        }
    }

    private function releaseTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        $this->tempFiles = [];
    }
}
