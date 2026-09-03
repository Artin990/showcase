<?php

/**
 * تولید خروجی اکسل حرفه‌ای برای فاکتور با فرمت SpreadsheetML (Excel XML).
 * این فرمت نیازی به هیچ کتابخانه‌ای ندارد و مستقیما توسط Excel باز می‌شود.
 * ویژگی‌ها: Border دور سلول‌ها، ادغام سلول عنوان، عرض ستون تنظیم‌شده، جهت راست‌به‌چپ (RTL)،
 * بدون گرافیک اضافه - فقط داده‌های مرتب و قابل ویرایش.
 */
class ExcelInvoiceExporter
{
    public static function stream(array $invoice, array $items): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $invoice['invoice_number'] . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // BOM برای نمایش صحیح فارسی در اکسل
        echo self::build($invoice, $items);
        exit;
    }

    private static function esc(?string $val): string
    {
        return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
    }

    public static function build(array $invoice, array $items): string
    {
        $colCount = 6;

        // پالت رنگی هماهنگ با قالب انتخابی فاکتور
        $tpl = $invoice['template'] ?? 'classic';
        $pal = self::palette($tpl);

        $xml  = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

        // ---------------- استایل‌ها ----------------
        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Borders/><Font ss:FontName="Tahoma" ss:Size="10"/></Style>';

        $xml .= '<Style ss:ID="title"><Font ss:FontName="Tahoma" ss:Size="16" ss:Bold="1" ss:Color="' . $pal['head'] . '"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';

        $xml .= '<Style ss:ID="metaLabel"><Font ss:FontName="Tahoma" ss:Size="10" ss:Bold="1" ss:Color="#6B7280"/><Alignment ss:Horizontal="Right"/></Style>';
        $xml .= '<Style ss:ID="metaValue"><Font ss:FontName="Tahoma" ss:Size="10" ss:Bold="1"/><Alignment ss:Horizontal="Right"/></Style>';

        $xml .= '<Style ss:ID="header"><Font ss:FontName="Tahoma" ss:Size="10" ss:Bold="1" ss:Color="' . $pal['headText'] . '"/>'
              . '<Interior ss:Color="' . $pal['head'] . '" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/>'
              . '<Borders>' . self::borderAllSides($pal['border']) . '</Borders></Style>';

        $xml .= '<Style ss:ID="cell"><Font ss:FontName="Tahoma" ss:Size="10"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/>'
              . '<Borders>' . self::borderAllSides($pal['border']) . '</Borders></Style>';

        $xml .= '<Style ss:ID="cellRight"><Font ss:FontName="Tahoma" ss:Size="10"/><Alignment ss:Horizontal="Right" ss:Vertical="Center"/>'
              . '<Borders>' . self::borderAllSides($pal['border']) . '</Borders></Style>';

        $xml .= '<Style ss:ID="totalLabel"><Font ss:FontName="Tahoma" ss:Size="11" ss:Bold="1"/>'
              . '<Interior ss:Color="' . $pal['soft'] . '" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/>'
              . '<Borders>' . self::borderAllSides($pal['border']) . '</Borders></Style>';

        $xml .= '<Style ss:ID="totalValue"><Font ss:FontName="Tahoma" ss:Size="12" ss:Bold="1" ss:Color="' . $pal['head'] . '"/>'
              . '<Interior ss:Color="' . $pal['soft'] . '" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/>'
              . '<Borders>' . self::borderAllSides($pal['border']) . '</Borders></Style>';
        $xml .= '</Styles>' . "\n";

        // ---------------- Worksheet ----------------
        $xml .= '<Worksheet ss:Name="فاکتور">';
        $xml .= '<Table ss:DefaultColumnWidth="90">';
        $xml .= '<Column ss:Width="30"/>';   // #
        $xml .= '<Column ss:Width="180"/>';  // نام محصول
        $xml .= '<Column ss:Width="60"/>';   // تعداد
        $xml .= '<Column ss:Width="90"/>';   // قیمت واحد
        $xml .= '<Column ss:Width="80"/>';   // تخفیف
        $xml .= '<Column ss:Width="100"/>';  // مبلغ

        // ردیف عنوان (ادغام‌شده روی همه ستون‌ها)
        $xml .= '<Row ss:Height="34">';
        $xml .= '<Cell ss:StyleID="title" ss:MergeAcross="' . ($colCount - 1) . '"><Data ss:Type="String">فاکتور فروش</Data></Cell>';
        $xml .= '</Row>';

        // ردیف‌های اطلاعات کلی فاکتور (برچسب + مقدار ادغام‌شده)
        $xml .= self::metaRow('شماره فاکتور', (string) $invoice['invoice_number'], $colCount);
        if (!empty($invoice['invoice_date_shamsi'])) {
            $xml .= self::metaRow('تاریخ', (string) $invoice['invoice_date_shamsi'], $colCount);
        }
        if (!empty($invoice['seller_name'])) {
            $xml .= self::metaRow('فروشنده', (string) $invoice['seller_name'], $colCount);
        }
        if (!empty($invoice['customer_name'])) {
            $xml .= self::metaRow('خریدار', (string) $invoice['customer_name'], $colCount);
        }

        // ردیف خالی جداکننده
        $xml .= '<Row></Row>';

        // هدر جدول اقلام
        $xml .= '<Row ss:Height="24">';
        foreach (['#', 'نام محصول', 'تعداد', 'قیمت واحد (ریال)', 'تخفیف (ریال)', 'مبلغ (ریال)'] as $h) {
            $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">' . self::esc($h) . '</Data></Cell>';
        }
        $xml .= '</Row>';

        // ردیف‌های اقلام
        foreach ($items as $i => $item) {
            $xml .= '<Row>';
            $xml .= '<Cell ss:StyleID="cell"><Data ss:Type="Number">' . ($i + 1) . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="cellRight"><Data ss:Type="String">' . self::esc($item['product_name']) . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="cell"><Data ss:Type="Number">' . (int) $item['quantity'] . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="cell"><Data ss:Type="Number">' . (float) $item['unit_price'] . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="cell"><Data ss:Type="Number">' . (float) $item['discount'] . '</Data></Cell>';
            $xml .= '<Cell ss:StyleID="cell"><Data ss:Type="Number">' . (float) $item['row_total'] . '</Data></Cell>';
            $xml .= '</Row>';
        }

        // ردیف جمع کل (ادغام برچسب روی ۵ ستون اول)
        $xml .= '<Row ss:Height="26">';
        $xml .= '<Cell ss:StyleID="totalLabel" ss:MergeAcross="4"><Data ss:Type="String">جمع کل فاکتور</Data></Cell>';
        $xml .= '<Cell ss:StyleID="totalValue"><Data ss:Type="Number">' . (float) $invoice['total_amount'] . '</Data></Cell>';
        $xml .= '</Row>';

        $xml .= '</Table>';

        // تنظیم جهت شیت به راست‌به‌چپ
        $xml .= '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><RTL/><FitToPage/></WorksheetOptions>';

        $xml .= '</Worksheet>';
        $xml .= '</Workbook>';

        return $xml;
    }

    private static function metaRow(string $label, string $value, int $colCount): string
    {
        $xml = '<Row>';
        $xml .= '<Cell ss:StyleID="metaLabel"><Data ss:Type="String">' . self::esc($label) . '</Data></Cell>';
        $xml .= '<Cell ss:StyleID="metaValue" ss:MergeAcross="' . ($colCount - 2) . '"><Data ss:Type="String">' . self::esc($value) . '</Data></Cell>';
        $xml .= '</Row>';
        return $xml;
    }

    /** پالت رنگی هر قالب — هماهنگ با قالب‌های چاپی (tpl-*.css) */
    private static function palette(string $tpl): array
    {
        switch ($tpl) {
            case 'modern':
                return ['head' => '#0D9488', 'headText' => '#FFFFFF', 'soft' => '#CCFBF1', 'border' => '#99F6E4'];
            case 'minimal':
                return ['head' => '#0F172A', 'headText' => '#FFFFFF', 'soft' => '#E2E8F0', 'border' => '#CBD5E1'];
            case 'elegant':
                return ['head' => '#1E293B', 'headText' => '#FDE68A', 'soft' => '#FEF3C7', 'border' => '#FDE68A'];
            case 'corporate':
                return ['head' => '#0F172A', 'headText' => '#5EEAD4', 'soft' => '#CCFBF1', 'border' => '#A7F3D0'];
            case 'classic':
            default:
                return ['head' => '#16324F', 'headText' => '#FFFFFF', 'soft' => '#F3E6C9', 'border' => '#C9C2B2'];
        }
    }

    private static function borderAllSides(string $color = '#C9C2B2'): string
    {
        $side = '<Border ss:Position="%s" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="' . $color . '"/>';
        return sprintf($side, 'Top') . sprintf($side, 'Bottom') . sprintf($side, 'Left') . sprintf($side, 'Right');
    }
}
