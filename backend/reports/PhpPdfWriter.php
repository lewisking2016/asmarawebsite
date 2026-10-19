<?php
/**
 * PhpPdfWriter — minimal pure-PHP PDF generator (no external deps).
 *
 * Emits valid PDF 1.4 documents using only the built-in Helvetica/Times
 * core fonts. Supports:
 *  - A4 / Letter / Legal page formats
 *  - Full-bleed coloured rectangles (for cover backgrounds, accent bars)
 *  - Multi-line text with word-wrap
 *  - Headings, body, captions
 *  - Tables with header row, alternating row zebra, borders
 *  - Page header + footer with auto page numbering
 *
 * This is intentionally tiny — it only knows what the Asmara Reports
 * module needs. It is *not* a general-purpose PDF library.
 */
declare(strict_types=1);

namespace Asmara\Reports;

class PhpPdfWriter
{
    // Page sizes in PDF points (1/72 inch). A4: 595 x 842.
    const PAGE_SIZES = [
        'A4'     => [595.28, 841.89],
        'Letter' => [612.00, 792.00],
        'Legal'  => [612.00, 1008.00],
    ];

    // Built-in core font names (PDF standard 14)
    const FONT_HELV = 'Helvetica';
    const FONT_HELV_B = 'Helvetica-Bold';
    const FONT_HELV_O = 'Helvetica-Oblique';
    const FONT_TIMES = 'Times-Roman';
    const FONT_TIMES_B = 'Times-Bold';

    private string $pageSize = 'A4';
    private array $page = [595.28, 841.89];
    private array $margins = [54, 54, 54, 60]; // top, right, bottom, left
    private string $outputPath = '';
    private array $objects = []; // pdf object bodies keyed by id
    private int $objectCount = 0;

    private float $cursorY = 0;        // current y in points from top
    private float $cursorX = 0;
    private int $pageNumber = 0;
    private array $pages = [];         // accumulated page streams
    private string $currentPage = '';
    private array $pageFontState = []; // track default font/size per page for header

    /** Public accessor for subclasses and tests that need to inject raw operators. */
    public function appendRaw(string $pdfOperators): void
    {
        $this->currentPage .= $pdfOperators;
    }

    private string $defaultFont = self::FONT_HELV;
    private float $defaultSize = 11;

    private ?string $pageHeader = null;
    private ?string $pageFooter = null;
    private string $pageHeaderRight = '';

    /** Initialise writer and open output file. */
    public function __construct(string $pageSize = 'A4', string $outputPath = '')
    {
        if (!isset(self::PAGE_SIZES[$pageSize])) {
            throw new \InvalidArgumentException("Unknown page size: $pageSize");
        }
        $this->pageSize = $pageSize;
        $this->page = self::PAGE_SIZES[$pageSize];
        $this->outputPath = $outputPath;
        $this->resetCursor();
    }

    public function setMargins(float $top, float $right, float $bottom, float $left): void
    {
        $this->margins = [$top, $right, $bottom, $left];
        $this->resetCursor();
    }

    public function setPageHeader(string $left, string $right): void
    {
        $this->pageHeader = $left;
        $this->pageHeaderRight = $right;
    }

    public function setPageFooter(string $text): void
    {
        $this->pageFooter = $text;
    }

    /** Begin a new page. If a page was already open, emit it first. */
    public function newPage(): void
    {
        $this->finalizePage();
        $this->pageNumber++;
        $this->currentPage = '';
        $this->pageFontState = [$this->defaultFont, $this->defaultSize];
        $this->resetCursor();
    }

    /** Get the current page number (1-indexed). */
    public function getPageNumber(): int
    {
        return $this->pageNumber + 1; // 0 -> "page 1" when cover is implicit
    }

    /** Full-bleed filled rectangle (no border). Color is [r,g,b] 0..1. */
    public function fillRect(float $x, float $y, float $w, float $h, array $rgb): void
    {
        [$r, $g, $b] = $this->clampRgb($rgb);
        $this->currentPage .= sprintf(
            "%F %F %F rg %F %F %F %F re f\n",
            $r, $g, $b, $x, $y, $w, $h
        );
    }

    /** Filled rounded-corner badge approximation (rect + corners). */
    public function fillRoundedRect(float $x, float $y, float $w, float $h, float $radius, array $rgb): void
    {
        [$r, $g, $b] = $this->clampRgb($rgb);
        // Build a simple rounded rect path
        $k = 0.5522847498; // bezier circle constant
        $this->currentPage .= sprintf("%F %F %F rg\n", $r, $g, $b);
        $this->currentPage .= sprintf("%F %F m\n", $x + $radius, $y);
        $this->currentPage .= sprintf("%F %F l\n", $x + $w - $radius, $y);
        $this->currentPage .= sprintf("%F %F %F %F %F %F c\n",
            $x + $w - $radius + $k * $radius, $y,
            $x + $w, $y + $radius - $k * $radius,
            $x + $w, $y + $radius);
        $this->currentPage .= sprintf("%F %F l\n", $x + $w, $y + $h - $radius);
        $this->currentPage .= sprintf("%F %F %F %F %F %F c\n",
            $x + $w, $y + $h - $radius + $k * $radius,
            $x + $w - $radius + $k * $radius, $y + $h,
            $x + $w - $radius, $y + $h);
        $this->currentPage .= sprintf("%F %F l\n", $x + $radius, $y + $h);
        $this->currentPage .= sprintf("%F %F %F %F %F %F c\n",
            $x + $radius - $k * $radius, $y + $h,
            $x, $y + $h - $radius + $k * $radius,
            $x, $y + $h - $radius);
        $this->currentPage .= sprintf("%F %F l\n", $x, $y + $radius);
        $this->currentPage .= sprintf("%F %F %F %F %F %F c\n",
            $x, $y + $radius - $k * $radius,
            $x + $radius - $k * $radius, $y,
            $x + $radius, $y);
        $this->currentPage .= "f\n";
    }

    /** Stroked (outline only) rectangle. */
    public function strokeRect(float $x, float $y, float $w, float $h, float $lineWidth, array $rgb): void
    {
        [$r, $g, $b] = $this->clampRgb($rgb);
        $this->currentPage .= sprintf(
            "%F %F %F RG %F w %F %F %F %F re S\n",
            $r, $g, $b, $lineWidth, $x, $y, $w, $h
        );
    }

    /** Set the active font and size. */
    public function setFont(string $font, float $size): void
    {
        $this->defaultFont = $font;
        $this->defaultSize = $size;
        $this->pageFontState = [$font, $size];
    }

    public function setTextColor(array $rgb): void
    {
        [$r, $g, $b] = $this->clampRgb($rgb);
        $this->currentPage .= sprintf("%F %F %F rg\n", $r, $g, $b);
    }

    /** Draw a single text line at (x, y) — y is measured from top. */
    public function drawText(string $text, float $x, float $y, ?string $font = null, ?float $size = null, ?array $color = null): void
    {
        $font = $font ?? $this->defaultFont;
        $size = $size ?? $this->defaultSize;
        if ($color) {
            [$r, $g, $b] = $this->clampRgb($color);
            $this->currentPage .= sprintf("%F %F %F rg\n", $r, $g, $b);
        }
        $this->currentPage .= sprintf("/%s %F Tf\n", $font, $size);
        // Convert (x, y-from-top) to PDF coordinate (y-from-bottom)
        $pdfY = $this->page[1] - $y - $size;
        $this->currentPage .= sprintf("BT %F %F Td (%s) Tj ET\n", $x, $pdfY, $this->escape($text));
    }

    /** Draw centred text within [x, y, w] (y is baseline from top). */
    public function drawCenteredText(string $text, float $x, float $y, float $w, ?string $font = null, ?float $size = null, ?array $color = null): void
    {
        $font = $font ?? $this->defaultFont;
        $size = $size ?? $this->defaultSize;
        $strW = $this->stringWidth($text, $font, $size);
        $this->drawText($text, $x + ($w - $strW) / 2, $y, $font, $size, $color);
    }

    /** Draw right-aligned text within [x, y, w]. */
    public function drawRightText(string $text, float $x, float $y, float $w, ?string $font = null, ?float $size = null, ?array $color = null): void
    {
        $font = $font ?? $this->defaultFont;
        $size = $size ?? $this->defaultSize;
        $strW = $this->stringWidth($text, $font, $size);
        $this->drawText($text, $x + $w - $strW, $y, $font, $size, $color);
    }

    /**
     * Write a wrapped paragraph at the current cursor. Returns true if it
     * fits on the current page, false if a page break was required.
     */
    public function paragraph(string $text, ?string $font = null, ?float $size = null, ?float $lineHeight = null, ?array $color = null): void
    {
        $font = $font ?? $this->defaultFont;
        $size = $size ?? $this->defaultSize;
        $lineHeight = $lineHeight ?? ($size * 1.4);
        $color = $color ?? [0.082, 0.071, 0.102]; // ink
        [$r, $g, $b] = $this->clampRgb($color);

        $maxWidth = $this->page[0] - $this->margins[1] - $this->margins[3];
        $lines = $this->wrap($text, $font, $size, $maxWidth);
        foreach ($lines as $line) {
            if ($this->cursorY + $lineHeight > $this->page[1] - $this->margins[2]) {
                $this->newPage();
            }
            $this->currentPage .= sprintf("%F %F %F rg\n", $r, $g, $b);
            $this->currentPage .= sprintf("/%s %F Tf\n", $font, $size);
            $pdfY = $this->page[1] - $this->cursorY - $size;
            $this->currentPage .= sprintf("BT %F %F Td (%s) Tj ET\n", $this->margins[3], $pdfY, $this->escape($line));
            $this->cursorY += $lineHeight;
        }
        $this->cursorY += $lineHeight * 0.25; // paragraph spacing
    }

    /** Heading 1 — Playfair-ish (we use Times-Bold as a fallback) with rose accent bar. */
    public function h1(string $text, array $barColor = [0.929, 0.090, 0.294]): void
    {
        // accent bar (3pt tall, 36pt wide)
        $this->fillRect($this->margins[3], $this->cursorY, 36, 3, $barColor);
        $this->cursorY += 14;
        if ($this->cursorY + 28 > $this->page[1] - $this->margins[2]) {
            $this->newPage();
        }
        $this->drawText($text, $this->margins[3], $this->cursorY, self::FONT_TIMES_B, 22, [0.082, 0.071, 0.102]);
        $this->cursorY += 32;
    }

    public function h2(string $text): void
    {
        if ($this->cursorY + 20 > $this->page[1] - $this->margins[2]) {
            $this->newPage();
        }
        $this->drawText($text, $this->margins[3], $this->cursorY, self::FONT_HELV_B, 14, [0.082, 0.071, 0.102]);
        $this->cursorY += 22;
    }

    public function h3(string $text): void
    {
        if ($this->cursorY + 16 > $this->page[1] - $this->margins[2]) {
            $this->newPage();
        }
        $this->drawText($text, $this->margins[3], $this->cursorY, self::FONT_HELV_B, 12, [0.082, 0.071, 0.102]);
        $this->cursorY += 18;
    }

    /** Move cursor down by N points. */
    public function spacer(float $points): void
    {
        $this->cursorY += $points;
    }

    /**
     * Draw a data table. Rows is an array of arrays. First row is header.
     * `colWidths` is fractions summing to 1; null means auto.
     */
    public function table(array $rows, ?array $colWidths = null, array $opts = []): void
    {
        if (empty($rows)) return;
        $font = $opts['font'] ?? self::FONT_HELV;
        $size = $opts['size'] ?? 10;
        $headerColor = $opts['header_bg'] ?? [0.984, 0.902, 0.925]; // rose_tint
        $borderColor = $opts['border'] ?? [0.851, 0.812, 0.769];
        $zebra = $opts['zebra'] ?? [1.0, 1.0, 1.0];

        $cols = count($rows[0]);
        $totalW = $this->page[0] - $this->margins[1] - $this->margins[3];
        if ($colWidths === null) {
            $colWidths = array_fill(0, $cols, 1.0 / $cols);
        }
        $colWidths = array_map(fn($w) => $w * $totalW, $colWidths);

        $rowH = $opts['row_height'] ?? ($size * 2.0);
        $headerH = $opts['header_height'] ?? ($size * 2.4);
        $padX = 6;

        // Header
        if ($this->cursorY + $headerH > $this->page[1] - $this->margins[2]) {
            $this->newPage();
        }
        $this->fillRect($this->margins[3], $this->cursorY, $totalW, $headerH, $headerColor);
        $x = $this->margins[3];
        $yText = $this->cursorY + ($headerH - $size) / 2 - 1;
        foreach ($rows[0] as $i => $cell) {
            $this->drawText((string)$cell, $x + $padX, $yText, $font . '', $size, [0.929, 0.090, 0.294]);
            // underline rule
            $this->fillRect($x, $this->cursorY + $headerH - 1, $colWidths[$i], 1, [0.929, 0.090, 0.294]);
            $x += $colWidths[$i];
        }
        $this->cursorY += $headerH;

        // Body
        for ($r = 1; $r < count($rows); $r++) {
            if ($this->cursorY + $rowH > $this->page[1] - $this->margins[2]) {
                $this->newPage();
            }
            // zebra
            if (($r % 2) === 1) {
                $this->fillRect($this->margins[3], $this->cursorY, $totalW, $rowH, $zebra);
            }
            $x = $this->margins[3];
            $yText = $this->cursorY + ($rowH - $size) / 2 - 1;
            foreach ($rows[$r] as $i => $cell) {
                $this->drawText((string)$cell, $x + $padX, $yText, $font, $size, [0.165, 0.149, 0.184]);
                $x += $colWidths[$i];
            }
            // cell border bottom
            $this->fillRect($this->margins[3], $this->cursorY + $rowH - 0.4, $totalW, 0.4, $borderColor);
            $this->cursorY += $rowH;
        }
        $this->cursorY += 8; // table spacing
    }

    /**
     * Draw a key/value list (used for the cover meta block + summary stats).
     */
    public function kvTable(array $rows, float $keyWidthPct = 0.4, float $rowH = 18, ?array $keyColor = null, ?array $valColor = null): void
    {
        $keyColor = $keyColor ?? [0.333, 0.310, 0.369];
        $valColor = $valColor ?? [0.082, 0.071, 0.102];
        $totalW = $this->page[0] - $this->margins[1] - $this->margins[3];
        $keyW = $totalW * $keyWidthPct;
        $valW = $totalW - $keyW;

        foreach ($rows as [$k, $v]) {
            if ($this->cursorY + $rowH > $this->page[1] - $this->margins[2]) {
                $this->newPage();
            }
            $this->drawText((string)$k, $this->margins[3], $this->cursorY + 3, self::FONT_HELV, 10, $keyColor);
            $this->drawText((string)$v, $this->margins[3] + $keyW, $this->cursorY + 3, self::FONT_HELV_B, 10, $valColor);
            // faint divider
            $this->fillRect($this->margins[3], $this->cursorY + $rowH - 0.4, $totalW, 0.3, [0.925, 0.890, 0.855]);
            $this->cursorY += $rowH;
        }
        $this->cursorY += 6;
    }

    /**
     * Finalise the document. Returns the path to the written PDF.
     */
    public function close(): string
    {
        $this->finalizePage();
        if ($this->outputPath === '') {
            throw new \RuntimeException('Output path not set');
        }
        $bytes = $this->build();
        if (file_put_contents($this->outputPath, $bytes) === false) {
            throw new \RuntimeException("Could not write PDF to {$this->outputPath}");
        }
        return $this->outputPath;
    }

    /**
     * High-level: build an Asmara Reports PDF from a data dict and write
     * it to disk. This is the entry point the export endpoint calls when
     * the Python fallback is needed.
     *
     * Expected $data keys:
     *   title, subtitle, company, company_tagline, stamp, doc_id,
     *   meta (array of [key,value]), header_right, page_footer, cover_footer,
     *   summary_rows (array of [key,value]),
     *   timeline (assoc array label => count, last 30 days),
     *   branches (array of [name, count, guests]),
     *   utilisation (array of [name, booked, capacity, pct]),
     *   inquiries (assoc total/new/replied/rate).
     */
    public static function buildReport(string $outputPath, array $data): string
    {
        $pdf = new self('A4', $outputPath);
        $pdf->setPageHeader($data['company'] ?? 'Asmara Restaurant', $data['header_right'] ?? '');
        $pdf->setPageFooter($data['page_footer'] ?? '');
        self::renderCover($pdf, $data);
        self::renderSummary($pdf, $data);
        self::renderTimeline($pdf, $data);
        self::renderBranches($pdf, $data);
        self::renderUtilisation($pdf, $data);
        self::renderInquiries($pdf, $data);
        self::renderNotes($pdf, $data);
        return $pdf->close();
    }

    // ---- High-level section renderers ----------------------------------

    private static function renderCover(self $pdf, array $d): void
    {
        // The cover is full-bleed rose. We don't draw the page header/footer
        // bar on the cover by leaving them unset for this page.
        // Since setPageHeader() sets them globally, we instead just override
        // the cursor to draw cover content.
        $rose      = [0.929, 0.090, 0.294];
        $roseHover = [0.812, 0.055, 0.239];
        $cream     = [0.984, 0.973, 0.961];
        $soft      = [1.000, 0.851, 0.882];
        $dim       = [0.910, 0.710, 0.749];
        $ink       = [0.082, 0.071, 0.102];

        // Full-bleed rose
        $pdf->fillRect(0, 0, 595.28, 841.89, $rose);
        // Top decoration triangle (rose-hover)
        $pdf->appendRaw("0.812 0.055 0.239 rg\n0 841.89 l 0 621.89 l 120 841.89 l f\n");

        // Cream badge
        $pdf->fillRoundedRect(62, 695, 62, 62, 9, $cream);
        $badge = strtoupper(substr($d['company'] ?? 'A', 0, 1));
        $pdf->drawCenteredText($badge, 62, 718, 62, self::FONT_HELV_B, 28, $rose);

        // Company + tagline
        $pdf->drawText($d['company'] ?? 'Asmara Restaurant', 62, 657, self::FONT_HELV_B, 15, [1,1,1]);
        $pdf->drawText($d['company_tagline'] ?? '', 62, 641, self::FONT_HELV, 10.5, $soft);

        // Title (wrapped)
        $title = $d['title'] ?? 'Reports';
        $maxW = 595.28 - 62 - 62;
        $lines = self::wrapStatic($title, self::FONT_HELV_B, 27, $maxW);
        $y = 549;
        foreach ($lines as $ln) {
            $pdf->drawText($ln, 62, $y, self::FONT_HELV_B, 27, [1,1,1]);
            $y -= 32;
        }
        $y -= 8;

        // Subtitle
        if (!empty($d['subtitle'])) {
            $subLines = self::wrapStatic($d['subtitle'], self::FONT_HELV, 11, $maxW);
            foreach ($subLines as $ln) {
                $pdf->drawText($ln, 62, $y, self::FONT_HELV, 11, $soft);
                $y -= 14;
            }
            $y -= 6;
        }

        // Cream accent bar
        $pdf->fillRect(62, $y - 4, 114, 5, $cream);

        // Meta block
        $y -= 24;
        foreach (($d['meta'] ?? []) as [$k, $v]) {
            $pdf->drawText($k, 62, $y, self::FONT_HELV, 9.5, $dim);
            $pdf->drawText($v, 168, $y, self::FONT_HELV, 9.5, [1,1,1]);
            $y -= 23;
        }

        // Stamp
        $stamp = $d['stamp'] ?? 'DRAFT';
        $sw = 26 + 6.4 * strlen($stamp);
        $pdf->strokeRect(62, $y - 34, $sw, 24, 0.8, $cream);
        $pdf->drawCenteredText($stamp, 62, $y - 25.5, $sw, self::FONT_HELV_B, 8.5, $cream);

        // Cover-specific footer text at bottom
        $coverFoot = $d['cover_footer'] ?? '';
        if ($coverFoot !== '') {
            $footLines = self::wrapStatic($coverFoot, self::FONT_HELV, 7.5, 595.28 - 124);
            $fy = 72;
            foreach ($footLines as $ln) {
                $pdf->drawText($ln, 62, $fy, self::FONT_HELV, 7.5, $soft);
                $fy -= 10;
            }
        }
    }

    private static function renderSummary(self $pdf, array $d): void
    {
        $pdf->newPage();
        $pdf->h1('1. Summary');
        $pdf->paragraph(
            'This document summarises bookings, customer inquiries, menu coverage and '
            . 'branch utilisation across all Asmara Restaurant locations. Numbers are '
            . 'drawn from the live database and reflect the state at the time of generation.'
        );
        $pdf->h2('Key metrics');
        $pdf->kvTable($d['summary_rows'] ?? []);
    }

    private static function renderTimeline(self $pdf, array $d): void
    {
        $timeline = $d['timeline'] ?? [];
        if (empty($timeline)) return;
        $pdf->newPage();
        $pdf->h1('2. Bookings last 30 days');

        $rows = [['Day', 'Count', 'Visual']];
        $values = array_values($timeline);
        $max = max(1, max($values));
        $i = 0;
        foreach ($timeline as $label => $v) {
            $bar = str_repeat('|', (int)ceil(($v / $max) * 24));
            $rows[] = [(string)$label, (string)$v, $bar];
            $i++;
        }
        $pdf->table($rows, [0.10, 0.15, 0.75], ['size' => 9, 'row_height' => 14]);
        $pdf->spacer(6);
        $pdf->h2('Status breakdown');
        $pdf->paragraph(
            'Booking status visualisation requires the reportlab renderer. The PHP '
            . 'fallback shows the numeric breakdown in section 1.'
        );
    }

    private static function renderBranches(self $pdf, array $d): void
    {
        $branches = $d['branches'] ?? [];
        if (empty($branches)) return;
        $pdf->newPage();
        $pdf->h1('3. Bookings by branch');
        $rows = [['Branch', 'Bookings', 'Guests']];
        foreach ($branches as $b) {
            $rows[] = [
                $b['name'] ?? ('Branch #' . ($b['id'] ?? '?')),
                (string)($b['count'] ?? 0),
                (string)($b['guests'] ?? 0),
            ];
        }
        $pdf->table($rows, [0.5, 0.25, 0.25], ['size' => 10]);
    }

    private static function renderUtilisation(self $pdf, array $d): void
    {
        $util = $d['utilisation'] ?? [];
        if (empty($util)) return;
        $pdf->newPage();
        $pdf->h1('4. Branch capacity utilisation');
        $rows = [['Branch', 'Booked', 'Capacity', 'Utilisation']];
        foreach ($util as $u) {
            $rows[] = [
                $u['name'] ?? '?',
                (string)($u['booked'] ?? 0),
                (string)($u['capacity'] ?? 0),
                (($u['pct'] ?? 0)) . '%',
            ];
        }
        $pdf->table($rows, [0.45, 0.18, 0.18, 0.19], ['size' => 10]);
    }

    private static function renderInquiries(self $pdf, array $d): void
    {
        $inq = $d['inquiries'] ?? null;
        if (!$inq) return;
        $pdf->newPage();
        $pdf->h1('5. Inquiry funnel');
        $pdf->kvTable([
            ['Total inquiries received', (string)($inq['total'] ?? 0)],
            ['New inquiries awaiting reply', (string)($inq['new'] ?? 0)],
            ['Replied by reporter', (string)($inq['replied'] ?? 0)],
            ['Response rate', ($inq['rate'] ?? 0) . '%'],
        ]);
    }

    private static function renderNotes(self $pdf, array $d): void
    {
        $pdf->newPage();
        $pdf->h1('6. Notes for management');
        $pdf->paragraph(
            'Operational capacity: review branch utilisation numbers and rebalance '
            . 'staffing where any branch exceeds 80% booked seats.'
        );
        $pdf->spacer(4);
        $pdf->paragraph(
            'Customer funnel: the response rate figure should trend toward 95%+ '
            . 'within the same week; anything persistently lower than 70% needs '
            . 'a reply policy review.'
        );
        $pdf->spacer(4);
        $pdf->paragraph(
            'Booking completion: if completion-rate drops below 60%, audit '
            . 'no-show policies and the booking confirmation reminder cadence.'
        );
    }

    /** Static word-wrap helper used during cover rendering. */
    private static function wrapStatic(string $text, string $font, float $size, float $maxWidth): array
    {
        // Delegate to a temporary instance so we can reuse the wrap logic.
        $tmp = new self('A4', '');
        return $tmp->wrap($text, $font, $size, $maxWidth);
    }

    public function output(): string
    {
        $this->finalizePage();
        return $this->build();
    }

    // -----------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------

    private function resetCursor(): void
    {
        $this->cursorY = $this->margins[0];
        $this->cursorX = $this->margins[3];
    }

    private function finalizePage(): void
    {
        if ($this->currentPage === '' && !$this->pages) return;
        // Draw page header + footer if set (skip on cover page when number=1 and no header set)
        $pageStream = '';
        if ($this->pageHeader !== null) {
            $pageStream .= $this->pageHeaderBar();
        }
        if ($this->pageFooter !== null) {
            $pageStream .= $this->pageFooterBar();
        }
        $pageStream .= $this->currentPage;
        $this->pages[] = $pageStream;
    }

    private function pageHeaderBar(): string
    {
        $s = '';
        $y = $this->margins[0] - 18;
        $left = $this->margins[3];
        $right = $this->page[0] - $this->margins[1];
        $s .= sprintf("BT %F %F Td (%s) Tj ET\n", $left, $this->page[1] - $y - 8, $this->escape($this->pageHeader));
        // Use a small Tj inline by setting font first
        $rightText = $this->pageHeaderRight;
        $strW = $this->stringWidth($rightText, self::FONT_HELV, 8);
        $s .= sprintf("/%s 8 Tf\nBT %F %F Td (%s) Tj ET\n", self::FONT_HELV, $right - $strW, $this->page[1] - $y - 8, $this->escape($rightText));
        // divider
        $s .= sprintf("%F %F %F RG 0.5 w %F %F m %F %F l S\n",
            0.929, 0.090, 0.294,
            $left, $this->page[1] - $y - 2, $right, $this->page[1] - $y - 2);
        return $s;
    }

    private function pageFooterBar(): string
    {
        $s = '';
        $left = $this->margins[3];
        $right = $this->page[0] - $this->margins[1];
        $y = $this->page[1] - $this->margins[2] + 12;
        // divider
        $s .= sprintf("%F %F %F RG 0.5 w %F %F m %F %F l S\n",
            0.929, 0.090, 0.294,
            $left, $this->page[1] - $y + 14, $right, $this->page[1] - $y + 14);
        $s .= sprintf("/%s 7.5 Tf BT %F %F Td (%s) Tj ET\n", self::FONT_HELV, $left, $this->page[1] - $y - 4, $this->escape($this->pageFooter));
        $pageStr = "Page " . ($this->pageNumber + 1);
        $strW = $this->stringWidth($pageStr, self::FONT_HELV, 7.5);
        $s .= sprintf("/%s 7.5 Tf BT %F %F Td (%s) Tj ET\n", self::FONT_HELV, $right - $strW, $this->page[1] - $y - 4, $this->escape($pageStr));
        return $s;
    }

    private function clampRgb(array $rgb): array
    {
        return [max(0, min(1, $rgb[0])), max(0, min(1, $rgb[1])), max(0, min(1, $rgb[2]))];
    }

    /** Escape a string for inclusion in a PDF text literal. */
    private function escape(string $s): string
    {
        // Replace () and \ with their escapes
        $s = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
        // Collapse control chars and high-bit noise
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '?', $s) ?? $s;
    }

    /** Approximate string width in points for a core font. */
    private function stringWidth(string $s, string $font, float $size): float
    {
        // Use a character-class-based approximation rather than the real AFM widths.
        // This is good enough for layout decisions in our small document.
        $width = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = $s[$i];
            $o = ord($c);
            if ($o < 0x80) {
                if ($c === ' ') $width += 0.28;
                elseif (ctype_upper($c)) $width += 0.66;
                elseif (ctype_lower($c)) $width += 0.56;
                elseif (ctype_digit($c) || in_array($c, ['0','1','2','3','4','5','6','7','8','9'])) $width += 0.56;
                elseif (in_array($c, ['i','l','I','.',',',';',':','!','\'','`'])) $width += 0.28;
                else $width += 0.6;
            } else {
                // multi-byte char (UTF-8)
                if (($o & 0xE0) === 0xC0) { $width += 0.6; $i += 1; }
                elseif (($o & 0xF0) === 0xE0) { $width += 0.9; $i += 2; }
                elseif (($o & 0xF8) === 0xF0) { $width += 1.2; $i += 3; }
                else $width += 0.6;
            }
        }
        // Bold variants ~10% wider
        if (str_contains($font, 'Bold')) $width *= 1.1;
        return $width * $size;
    }

    /** Word-wrap text to fit maxWidth using the given font/size. */
    private function wrap(string $text, string $font, float $size, float $maxWidth): array
    {
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
        $words = explode(' ', $text);
        $lines = [];
        $line = '';
        foreach ($words as $w) {
            $candidate = $line === '' ? $w : ($line . ' ' . $w);
            if ($this->stringWidth($candidate, $font, $size) <= $maxWidth) {
                $line = $candidate;
            } else {
                if ($line !== '') $lines[] = $line;
                // hard wrap very long single words
                while ($this->stringWidth($w, $font, $size) > $maxWidth && strlen($w) > 1) {
                    $lines[] = substr($w, 0, 4);
                    $w = substr($w, 4);
                }
                $line = $w;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines;
    }

    /** Build the final PDF byte stream. */
    private function build(): string
    {
        // ID assignment plan (must be monotonically increasing in write order
        // so the xref table lines up with the file bytes):
        //   1: Catalog
        //   2: Pages (parent of all Page objects)
        //   3..3+N-1: Per-page Page objects (N = page count)
        //   3+N..3+2N-1: Per-page Content streams
        //   after that: Font dictionaries
        //
        // We build a list of (id, body) tuples, write them all in order, and
        // record each one's byte offset for the xref table.
        $entries = []; // each: ['id' => int, 'body' => string]

        $pageCount = count($this->pages);
        $pageIds   = range(3, 3 + $pageCount - 1);
        $contentIds = range(3 + $pageCount, 3 + 2 * $pageCount - 1);

        // Pre-assign font IDs sequentially after the content streams
        $fontHelvId   = 3 + 2 * $pageCount;
        $fontHelvBId  = $fontHelvId   + 1;
        $fontHelvOId  = $fontHelvBId  + 1;
        $fontTimesBId = $fontHelvOId  + 1;

        // Catalog (id 1) + Pages (id 2)
        $entries[] = ['id' => 1, 'body' => '<< /Type /Catalog /Pages 2 0 R >>'];
        $pageRefs = [];
        foreach ($pageIds as $pid) $pageRefs[] = "$pid 0 R";
        $entries[] = ['id' => 2, 'body' => '<< /Type /Pages /Kids [ ' . implode(' ', $pageRefs) . " ] /Count $pageCount >>"];

        // Page objects
        foreach ($pageIds as $i => $pid) {
            $cid = $contentIds[$i];
            $entries[] = [
                'id' => $pid,
                'body' => sprintf(
                    "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %F %F] "
                    . "/Resources << /Font << /%s %d 0 R /%s %d 0 R /%s %d 0 R /%s %d 0 R >> >> "
                    . "/Contents %d 0 R >>",
                    $this->page[0], $this->page[1],
                    self::FONT_HELV,    $fontHelvId,
                    self::FONT_HELV_B,  $fontHelvBId,
                    self::FONT_HELV_O,  $fontHelvOId,
                    self::FONT_TIMES_B, $fontTimesBId,
                    $cid
                ),
            ];
        }

        // Font dictionaries (must precede content streams since the content
        // stream references them by name — but order doesn't matter for that,
        // what matters is xref ordering which is enforced by sequential write)
        $entries[] = ['id' => $fontHelvId,   'body' => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>'];
        $entries[] = ['id' => $fontHelvBId,  'body' => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>'];
        $entries[] = ['id' => $fontHelvOId,  'body' => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>'];
        $entries[] = ['id' => $fontTimesBId, 'body' => '<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold /Encoding /WinAnsiEncoding >>'];

        // Content streams (must come AFTER Page objects so that when pypdf
        // reads sequentially it sees them in document order; PDF doesn't
        // actually require this, but some readers are picky).
        foreach ($contentIds as $i => $cid) {
            $stream = $this->pages[$i] ?? '';
            // Use the explicit length token so any reader knows where the
            // stream ends. Add a defensive trailing newline in case the
            // stream doesn't end with one.
            if (substr($stream, -1) !== "\n") $stream .= "\n";
            $entries[] = [
                'id' => $cid,
                'body' => "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream",
            ];
        }

        // Now write the file in order, tracking byte offsets.
        $out = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];
        foreach ($entries as $e) {
            $offsets[$e['id']] = strlen($out);
            $out .= $e['id'] . " 0 obj\n" . $e['body'] . "\nendobj\n";
        }

        $maxId = max(array_keys($offsets));

        // xref
        $xrefOffset = strlen($out);
        $out .= "xref\n";
        $out .= "0 " . ($maxId + 1) . "\n";
        $out .= "0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) {
            $off = $offsets[$id] ?? 0;
            $out .= str_pad((string)$off, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $out .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\n";
        $out .= "startxref\n$xrefOffset\n%%EOF\n";
        return $out;
    }
}