<?php

namespace Tests\Unit;

use App\Services\DiamondExportBuilder;
use App\Services\DiamondImportService;
use App\Services\DiamondSheetReader;
use App\Services\StreamingXlsxWriter;
use App\Support\ExcelColumn;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class DiamondSpreadsheetTest extends TestCase
{
    public function test_export_totals_stay_on_the_original_columns()
    {
        $keys = [
            'id', 'stock_id', 'growth_type', 'status', 'reference', 'range', 'shape', 'weight',
            'color', 'clarity', 'cut', 'polish', 'symmetry', 'fluorescence_intensity', 'length',
            'width', 'height', 'ratio', 'lab', 'report_date', 'report_number', 'location',
            'discounts', 'live_rap', 'rap_amount', 'price_per_carat', 'total_price',
            'bargaining_price_per_carat', 'bargaining_total_price', 'depth_percentage',
            'table_percentage', 'crown_height', 'crown_angle', 'pavilion_depth', 'pavilion_angle',
            'inscription', 'key_to_symbols', 'white_inclusion', 'black_inclusion', 'open_inclusion',
            'fancy_color', 'fancy_color_intensity', 'fancy_color_overtone', 'girdle_percentage',
            'girdle', 'culet', 'state', 'city', 'cert_url', 'video_url', 'image_url', 'treatment',
            'country', 'cert_comment', 'cop', 'category', 'images', 'shade', 'milky', 'eye_clean',
            'h_and_a', 'created_at', 'updated_at',
        ];
        $columns = array_combine($keys, $keys);

        $authenticated = new DiamondExportBuilder($columns, true);
        $header = $authenticated->header(true);
        $row = $authenticated->mapRow([
            'weight' => '1.50',
            'total_price' => '300.00',
            'stock_id' => 'S1',
            'price_per_carat' => '200.00',
        ]);
        $this->assertSame('1.50', $row[ExcelColumn::index('H') - 1]);
        $this->assertSame('200.00', $row[ExcelColumn::index('Z') - 1]);
        $this->assertSame('300.00', $row[ExcelColumn::index('AA') - 1]);
        $totals = $authenticated->totalsRow($header);
        $this->assertEquals(1.5, $totals[ExcelColumn::index('H') - 1]);
        $this->assertEquals(200, $totals[ExcelColumn::index('Z') - 1]);
        $this->assertEquals(300, $totals[ExcelColumn::index('AA') - 1]);

        $guest = new DiamondExportBuilder($columns, false);
        $guestHeader = $guest->header(true);
        $guestRow = $guest->mapRow([
            'weight' => '2',
            'price_per_carat' => '90',
            'total_price' => '180',
            'reference' => 'hidden',
            'bargaining_price_per_carat' => 'hidden',
        ]);
        $this->assertSame('2', $guestRow[ExcelColumn::index('G') - 1]);
        $this->assertSame('90', $guestRow[ExcelColumn::index('Y') - 1]);
        $this->assertSame('180', $guestRow[ExcelColumn::index('Z') - 1]);
        $this->assertNotContains('hidden', $guestRow);
        $guestTotals = $guest->totalsRow($guestHeader);
        $this->assertEquals(2, $guestTotals[ExcelColumn::index('G') - 1]);
        $this->assertEquals(90, $guestTotals[ExcelColumn::index('Y') - 1]);
        $this->assertEquals(180, $guestTotals[ExcelColumn::index('Z') - 1]);
    }

    public function test_import_formulas_match_the_existing_calculations()
    {
        $service = new DiamondImportService();
        $row = $service->normalizeRow([
            'report_date' => '',
            'ratio' => '',
            'length' => '6',
            'width' => '4',
            'rap_amount' => '',
            'weight' => '2',
            'live_rap' => '100',
            'price_per_carat' => '',
            'discounts' => '-10',
            'total_price' => '',
            'bargaining_price_per_carat' => '',
            'bargaining_total_price' => '',
        ]);

        $this->assertSame(date('Y-m-d'), $row['report_date']);
        $this->assertSame('1.50', $row['ratio']);
        $this->assertSame('200.00', $row['rap_amount']);
        $this->assertSame('90.00', $row['price_per_carat']);
        $this->assertSame('180.00', $row['total_price']);
        $this->assertSame('0.00', $row['bargaining_price_per_carat']);
        $this->assertSame('0.00', $row['bargaining_total_price']);

        $header = $service->buildHeader(['Serial', 'Stock Id', 'Depth %', 'Report #', 'H & A']);
        $this->assertSame(['serial', 'stock_id', 'depth_percentage', 'report_number', 'h_and_a'], $header);
        $trimmed = $service->trimHeaderCells(['Serial', 'Stock Id', '', null]);
        $this->assertSame(['Serial', 'Stock Id'], $trimmed);
        $this->assertSame('stock_id', $service->formatColumn(" Stock  Id "));
        $this->assertSame(
            ['stock_id', 'weight'],
            $service->buildHeader(['Stock Id', 'Weight'])
        );
        $this->assertSame(
            ['stock_id', 'stock_id_2', 'weight'],
            $service->buildHeader(['Stock Id', 'Stock Id', 'Weight'])
        );
    }

    public function test_row_issues_flag_missing_duplicate_and_oversized_values()
    {
        $service = new DiamondImportService();
        $header = $service->buildHeader(['Stock Id', 'Weight', 'Cert Comment']);
        $limits = [
            'weight' => ['kind' => 'decimal', 'precision' => 8, 'scale' => 2],
            'cert_comment' => ['kind' => 'string', 'length' => 5],
        ];
        $seen = [];

        $blank = $service->rowIssues($header, [null, null, null], $limits, $seen);
        $this->assertTrue($blank['blank']);

        $first = $service->rowIssues($header, ['A1', '1.20', 'note'], $limits, $seen);
        $this->assertSame([], $first['errors']);
        $seen[$first['stock_key']] = true;

        $missing = $service->rowIssues($header, ['', '1.20', 'note'], $limits, $seen);
        $this->assertContains('Stock id is required.', $missing['errors']);

        $duplicate = $service->rowIssues($header, ['A1', '1.50', 'note'], $limits, $seen);
        $this->assertContains('Duplicate stock id "A1".', $duplicate['errors']);

        $huge = $service->rowIssues($header, ['A2', '123456789', 'note'], $limits, $seen);
        $this->assertContains('Weight is too large.', $huge['errors']);

        $long = $service->rowIssues($header, ['A3', '1.20', 'too long'], $limits, $seen);
        $this->assertContains('Cert Comment is longer than 5 characters.', $long['errors']);
    }

    public function test_header_requires_a_stock_id_column()
    {
        $this->expectException(\RuntimeException::class);
        (new DiamondImportService())->buildHeader(['Weight', 'Color']);
    }

    public function test_xlsx_round_trip_reads_only_real_rows()
    {
        $path = tempnam(sys_get_temp_dir(), 'diamond_round_');
        @unlink($path);
        $path .= '.xlsx';

        $writer = new StreamingXlsxWriter();
        $writer->addRow(['id', 'Stock Id', 'Weight'], true, [3], true, 3);
        $writer->addRow(['1', 'ABC123', '1.25'], false, [3], true, null);
        $writer->save($path);

        $rows = iterator_to_array((new DiamondSheetReader())->rows($path));
        @unlink($path);

        $this->assertCount(2, $rows);
        $this->assertSame('Stock Id', $rows[0]['cells'][1]);
        $this->assertSame('ABC123', $rows[1]['cells'][1]);
        $this->assertSame('1.25', $rows[1]['cells'][2]);
    }

    public function test_inflated_worksheet_dimension_does_not_allocate_every_excel_row()
    {
        $path = $this->bloatedWorkbook();
        $before = memory_get_usage(true);
        $rows = iterator_to_array((new DiamondSheetReader())->rows($path));
        $used = memory_get_usage(true) - $before;
        @unlink($path);

        $this->assertCount(2, $rows);
        $this->assertCount(2, $rows[0]['cells']);
        $this->assertSame('Stock Id', $rows[0]['cells'][1]);
        $this->assertSame('STONE-1', $rows[1]['cells'][1]);
        $this->assertLessThan(32 * 1024 * 1024, $used);
    }

    public function test_general_numbers_drop_binary_float_noise_and_ignore_columns_past_the_header()
    {
        $path = tempnam(sys_get_temp_dir(), 'floats_');
        @unlink($path);
        $path .= '.xlsx';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:XFD8090"/>'
            . '<sheetData>'
            . '<row r="1"><c r="A1" t="inlineStr"><is><t>id</t></is></c><c r="B1" t="inlineStr"><is><t>Weight</t></is></c><c r="C1" t="inlineStr"><is><t>Depth %</t></is></c></row>'
            . '<row r="2"><c r="A2"><v>1</v></c><c r="B2"><v>0.55000000000000004</v></c><c r="C2"><v>65.599999999999994</v></c><c r="XFD2"><v>1603222786.7001922</v></c></row>'
            . '</sheetData></worksheet>';

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        $rows = iterator_to_array((new DiamondSheetReader())->rows($path));
        @unlink($path);

        $this->assertCount(3, $rows[0]['cells']);
        $this->assertSame('0.55', $rows[1]['cells'][1]);
        $this->assertSame('65.6', $rows[1]['cells'][2]);
    }

    private function bloatedWorkbook()
    {
        $path = tempnam(sys_get_temp_dir(), 'bloated_');
        @unlink($path);
        $path .= '.xlsx';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:XFD1048576"/>'
            . '<sheetData>'
            . '<row r="1"><c r="A1" t="inlineStr"><is><t>id</t></is></c><c r="B1" t="inlineStr"><is><t>Stock Id</t></is></c></row>'
            . '<row r="2"><c r="A2"><v>1</v></c><c r="B2" t="inlineStr"><is><t>STONE-1</t></is></c></row>'
            . '</sheetData></worksheet>';

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        return $path;
    }
}
