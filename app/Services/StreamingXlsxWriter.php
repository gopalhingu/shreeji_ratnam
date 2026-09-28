<?php

namespace App\Services;

use App\Support\ExcelColumn;
use RuntimeException;
use ZipArchive;

/**
 * Writes an xlsx file row by row so a large diamond list never sits in memory
 * as a PhpSpreadsheet workbook.
 */
class StreamingXlsxWriter
{
    const STYLE_DEFAULT = 0;
    const STYLE_BOLD = 1;
    const STYLE_YELLOW = 2;
    const STYLE_BOLD_YELLOW = 3;

    private $rowsPath;
    private $rowsHandle;
    private $rowIndex = 0;
    private $maxColumn = 1;
    private $widths = [];

    public function __construct()
    {
        $this->rowsPath = tempnam(sys_get_temp_dir(), 'xlsx_rows_');
        if ($this->rowsPath === false) {
            throw new RuntimeException('Unable to create a temporary Excel file.');
        }
        $this->rowsHandle = fopen($this->rowsPath, 'wb');
        if ($this->rowsHandle === false) {
            throw new RuntimeException('Unable to create a temporary Excel file.');
        }
    }

    public function __destruct()
    {
        if (is_resource($this->rowsHandle)) {
            fclose($this->rowsHandle);
        }
        if ($this->rowsPath && is_file($this->rowsPath)) {
            @unlink($this->rowsPath);
        }
    }

    /**
     * @param array<int, mixed> $values 0-based cell values
     * @param array<int, int> $yellowColumns 1-based column indexes
     * @param int|null $boldUntil 1-based last column that should be bold; null bolds every emitted cell
     */
    public function addRow(array $values, $bold, array $yellowColumns, $trackWidth, $boldUntil = null)
    {
        $this->rowIndex++;
        $width = count($values);
        foreach ($yellowColumns as $column) {
            if ((int) $column > $width) {
                $width = (int) $column;
            }
        }
        if ($width > $this->maxColumn) {
            $this->maxColumn = $width;
        }

        $xml = '<row r="' . $this->rowIndex . '">';
        for ($column = 1; $column <= $width; $column++) {
            $value = array_key_exists($column - 1, $values) ? $values[$column - 1] : null;
            $isYellow = in_array($column, $yellowColumns, true);
            $isBold = $bold && ($boldUntil === null || $column <= $boldUntil);
            if ($trackWidth) {
                $length = is_scalar($value) ? strlen((string) $value) : 0;
                if (!isset($this->widths[$column]) || $length > $this->widths[$column]) {
                    $this->widths[$column] = $length;
                }
            }
            if (($value === null || $value === '') && !$isYellow && !$isBold) {
                continue;
            }

            $style = self::STYLE_DEFAULT;
            if ($isBold && $isYellow) {
                $style = self::STYLE_BOLD_YELLOW;
            } elseif ($isBold) {
                $style = self::STYLE_BOLD;
            } elseif ($isYellow) {
                $style = self::STYLE_YELLOW;
            }

            $xml .= $this->cellXml($column, $this->rowIndex, $value, $style);
        }
        $xml .= '</row>';
        fwrite($this->rowsHandle, $xml);
    }

    public function save($targetPath)
    {
        if (!is_resource($this->rowsHandle)) {
            throw new RuntimeException('The Excel file has already been saved.');
        }
        fflush($this->rowsHandle);
        fclose($this->rowsHandle);
        $this->rowsHandle = null;

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive is required to create Excel files.');
        }

        $zip = new ZipArchive();
        if (is_file($targetPath)) {
            unlink($targetPath);
        }
        if ($zip->open($targetPath, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Unable to create the Excel file.');
        }

        $sheetPath = $this->writeSheetFile();
        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFile($sheetPath, 'xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($sheetPath);
    }

    private function writeSheetFile()
    {
        $sheetPath = tempnam(sys_get_temp_dir(), 'xlsx_sheet_');
        if ($sheetPath === false) {
            throw new RuntimeException('Unable to create a temporary Excel file.');
        }
        $sheet = fopen($sheetPath, 'wb');
        if ($sheet === false) {
            throw new RuntimeException('Unable to create a temporary Excel file.');
        }

        $lastColumn = ExcelColumn::letter($this->maxColumn);
        $lastRow = max(1, $this->rowIndex);
        fwrite($sheet, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
        fwrite($sheet, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
        fwrite($sheet, '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>');
        fwrite($sheet, '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A2" sqref="A2"/></sheetView></sheetViews>');
        fwrite($sheet, '<sheetFormatPr defaultRowHeight="15"/>');
        fwrite($sheet, '<cols>');
        for ($column = 1; $column <= $this->maxColumn; $column++) {
            $length = isset($this->widths[$column]) ? $this->widths[$column] : 8;
            $width = $length + 2;
            fwrite($sheet, '<col min="' . $column . '" max="' . $column . '" width="' . $width . '" customWidth="1"/>');
        }
        fwrite($sheet, '</cols><sheetData>');
        $rows = fopen($this->rowsPath, 'rb');
        if ($rows !== false) {
            stream_copy_to_stream($rows, $sheet);
            fclose($rows);
        }
        fwrite($sheet, '</sheetData></worksheet>');
        fclose($sheet);

        return $sheetPath;
    }

    private function cellXml($column, $row, $value, $style)
    {
        $reference = ExcelColumn::letter($column) . $row;
        $styleAttribute = $style > 0 ? ' s="' . $style . '"' : '';
        if ($value === null || $value === '') {
            return '<c r="' . $reference . '"' . $styleAttribute . '/>';
        }
        if (is_int($value) || (is_float($value) && is_finite($value)) || (is_string($value) && $value !== '' && is_numeric($value))) {
            return '<c r="' . $reference . '"' . $styleAttribute . '><v>' . $this->xmlText($value) . '</v></c>';
        }

        return '<c r="' . $reference . '" t="inlineStr"' . $styleAttribute . '><is><t xml:space="preserve">' . $this->xmlText($value) . '</t></is></c>';
    }

    private function xmlText($value)
    {
        $text = (string) $value;
        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $text);
        if ($text === null) {
            $text = '';
        }

        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypes()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Worksheet" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFF00"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="0" fillId="2" borderId="0" xfId="0" applyFill="1"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }
}
