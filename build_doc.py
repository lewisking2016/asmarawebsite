#!/usr/bin/env python3
"""Asmara-branded PDF generator (adapted from the Tabio house style).

Usage:
    python build_doc.py content.md config.json output.pdf

content.md   Markdown body (## and ### headings, paragraphs, - bullets,
             1. numbered lists, | pipe tables |, **bold**, `code`).
             Do NOT put a top-level '# Title' in it: the title lives on the cover.
config.json  Cover metadata and brand colours.
"""
import json, os, re, sys, textwrap
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import ParagraphStyle
from reportlab.platypus import (BaseDocTemplate, PageTemplate, Frame, Paragraph,
                                Spacer, Table, TableStyle, PageBreak)
from reportlab.pdfgen import canvas
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

md_path, cfg_path, out_path = sys.argv[1:4]
CFG = json.load(open(cfg_path, encoding="utf-8-sig"))

# ---- Asmara palette (sampled from the live admin design system) ----
ROSE       = colors.HexColor(CFG.get("rose",       "#ed174b"))   # primary accent
ROSE_HOVER = colors.HexColor(CFG.get("rose_hover", "#cf0e3d"))   # hover / dark variant
ROSE_TINT  = colors.HexColor(CFG.get("rose_tint",  "#fbe6ec"))   # rose wash on light rows
CREAM      = colors.HexColor(CFG.get("cream",      "#fbf8f5"))   # surface background
CREAM_2    = colors.HexColor(CFG.get("cream_2",    "#f5efe8"))   # soft chip fill
LINE       = colors.HexColor("#ece3da")   # border
LINE_2     = colors.HexColor("#d9cfc4")   # border strong
INK        = colors.HexColor("#15121a")   # primary text
INK_2      = colors.HexColor("#2a2630")   # primary text strong
INK_3      = colors.HexColor("#554f5e")   # secondary text
INK_4      = colors.HexColor("#8b8597")   # muted text
# Cover-only soft/dim text variants on rose background
SOFT       = colors.HexColor("#FFD9E1")   # cover body text on rose
DIM        = colors.HexColor("#E8B5BF")   # cover labels on rose
ACCENT     = colors.HexColor(CFG.get("accent", "#fbf8f5"))     # cream-on-rose highlight
# Aliases so make_table still works
LIGHT  = ROSE_TINT
BORDER = LINE_2

# ---- fonts: Carlito (Calibri-like) -> DejaVu -> Helvetica ----
FONT, BOLD = "Helvetica", "Helvetica-Bold"
for reg, b, i, bi in [
    ("/usr/share/fonts/truetype/crosextra/Carlito-Regular.ttf",
     "/usr/share/fonts/truetype/crosextra/Carlito-Bold.ttf",
     "/usr/share/fonts/truetype/crosextra/Carlito-Italic.ttf",
     "/usr/share/fonts/truetype/crosextra/Carlito-BoldItalic.ttf"),
    ("C:/Windows/Fonts/Carlito-Regular.ttf",
     "C:/Windows/Fonts/Carlito-Bold.ttf",
     "C:/Windows/Fonts/Carlito-Italic.ttf",
     "C:/Windows/Fonts/Carlito-BoldItalic.ttf"),
    ("C:/Windows/Fonts/calibri.ttf",
     "C:/Windows/Fonts/calibrib.ttf",
     "C:/Windows/Fonts/calibrii.ttf",
     "C:/Windows/Fonts/calibriz.ttf"),
    ("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
     "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
     "/usr/share/fonts/truetype/dejavu/DejaVuSans-Oblique.ttf",
     "/usr/share/fonts/truetype/dejavu/DejaVuSans-BoldOblique.ttf"),
]:
    if all(os.path.exists(p) for p in (reg, b, i, bi)):
        pdfmetrics.registerFont(TTFont("Body", reg))
        pdfmetrics.registerFont(TTFont("Body-Bold", b))
        pdfmetrics.registerFont(TTFont("Body-Italic", i))
        pdfmetrics.registerFont(TTFont("Body-BoldItalic", bi))
        pdfmetrics.registerFontFamily("Body", normal="Body", bold="Body-Bold",
                                      italic="Body-Italic", boldItalic="Body-BoldItalic")
        FONT, BOLD = "Body", "Body-Bold"
        break

W, H = A4
TITLE = CFG["title"]
HEADER_RIGHT = CFG.get("header_right", "%s \u00b7 %s" % (CFG.get("doc_id", ""), TITLE)).strip(" \u00b7")
FOOT_PAGE = CFG.get("page_footer", "Confidential \u2014 for the named recipient only. "
                                   "Not for onward distribution without permission.")
FOOT_COVER = CFG.get("cover_footer", "Prepared by the Asmara Restaurant admin team. " + FOOT_PAGE)


def draw_cover(c):
    # Full-bleed rose cover
    c.setFillColor(ROSE)
    c.rect(0, 0, W, H, stroke=0, fill=1)

    # Asmara rose soft top decoration (subtle rose gradient corner)
    c.saveState()
    c.setFillColor(ROSE_HOVER)
    p = c.beginPath()
    p.moveTo(0, H)
    p.lineTo(0, H - 220)
    p.lineTo(120, H)
    p.close()
    c.drawPath(p, stroke=0, fill=1)
    c.restoreState()

    # Cream rounded square badge
    badge_size = 62
    c.setFillColor(colors.HexColor("#fbf8f5"))
    c.roundRect(62, H - 146, badge_size, badge_size, 9, stroke=0, fill=1)
    c.setFillColor(ROSE)
    c.setFont(BOLD, 19)
    c.drawCentredString(62 + badge_size / 2, H - 123, CFG.get("badge_text", "A"))

    # Company name + tagline
    c.setFillColor(colors.white)
    c.setFont(BOLD, 15)
    c.drawString(62, H - 184, CFG.get("company", "Asmara Restaurant"))
    c.setFont(FONT, 10.5)
    c.setFillColor(SOFT)
    c.drawString(62, H - 200, CFG.get("company_tagline", "Authentic Eritrean & East African Dining"))

    # Document title
    c.setFillColor(colors.white)
    c.setFont(BOLD, 27)
    lines = textwrap.wrap(TITLE, 28)[:3]
    y = H - 292
    for ln in lines:
        c.drawString(62, y, ln)
        y -= 32
    # Subtitle
    c.setFont(FONT, 11)
    c.setFillColor(SOFT)
    c.drawString(62, y - 4, CFG.get("subtitle", ""))
    # Cream accent bar
    bar_y = y - 36
    c.setFillColor(colors.HexColor("#fbf8f5"))
    c.rect(62, bar_y, 114, 5, stroke=0, fill=1)

    # Metadata block
    y = bar_y - 34
    for k, v in CFG["meta"]:
        c.setFillColor(DIM)
        c.setFont(FONT, 9.5)
        c.drawString(62, y, k)
        c.setFillColor(colors.white)
        c.drawString(168, y, v)
        y -= 23

    # Cover stamp
    stamp = CFG.get("stamp", "DRAFT")
    sw = 26 + 6.4 * len(stamp)
    c.setStrokeColor(colors.HexColor("#fbf8f5"))
    c.setLineWidth(0.8)
    c.rect(62, y - 34, sw, 24, stroke=1, fill=0)
    c.setFillColor(colors.HexColor("#fbf8f5"))
    c.setFont(BOLD, 8.5)
    c.drawCentredString(62 + sw / 2, y - 25.5, stamp)

    # Footer divider + cover footer text
    c.setStrokeColor(colors.HexColor("#c43259"))
    c.setLineWidth(0.6)
    c.line(62, 87, W - 62, 87)
    c.setFillColor(SOFT)
    c.setFont(FONT, 7.5)
    for n, ln in enumerate(textwrap.wrap(FOOT_COVER, 112)[:2]):
        c.drawString(62, 72 - 10 * n, ln)


class NumberedCanvas(canvas.Canvas):
    def __init__(self, *a, **k):
        super().__init__(*a, **k)
        self._saved = []

    def showPage(self):
        self._saved.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        total = len(self._saved)
        for i, st in enumerate(self._saved):
            self.__dict__.update(st)
            if i == 0:
                draw_cover(self)
            else:
                # Header
                self.setFillColor(ROSE)
                self.setFont(BOLD, 8)
                self.drawString(50, H - 34, CFG.get("company", "Asmara Restaurant"))
                self.setFillColor(INK_3)
                self.setFont(FONT, 8)
                self.drawRightString(W - 50, H - 34, HEADER_RIGHT)
                self.setStrokeColor(BORDER)
                self.setLineWidth(0.5)
                self.line(50, H - 40, W - 50, H - 40)
                self.line(50, 44, W - 50, 44)
                # Footer
                self.setFillColor(INK_3)
                self.setFont(FONT, 7.5)
                self.drawString(50, 32, FOOT_PAGE)
                self.drawRightString(W - 50, 32, "Page %d of %d" % (i + 1, total))
            super().showPage()
        super().save()


body = ParagraphStyle("body", fontName=FONT, fontSize=9.3, leading=13.2,
                      textColor=INK, spaceAfter=5.5)
h2 = ParagraphStyle("h2", parent=body, fontName=BOLD, fontSize=15, leading=19,
                    textColor=ROSE, spaceBefore=14, spaceAfter=6, keepWithNext=1)
h3 = ParagraphStyle("h3", parent=body, fontName=BOLD, fontSize=11, leading=14,
                    textColor=ROSE, spaceBefore=9, spaceAfter=4, keepWithNext=1)
bul = ParagraphStyle("bul", parent=body, leftIndent=14, bulletIndent=3, spaceAfter=3)
cell = ParagraphStyle("cell", parent=body, fontSize=7.8, leading=10.2, spaceAfter=0)
cellh = ParagraphStyle("cellh", parent=cell, fontName=BOLD, textColor=ROSE)


def inline(t):
    t = t.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    t = re.sub(r"`([^`]+)`", lambda m: '<font face="Courier" size="8.3">%s</font>' % m.group(1), t)
    t = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", t)
    t = re.sub(r"(?<!\*)\*([^*\s][^*]*?)\*(?!\*)", r"<i>\1</i>", t)
    return t


def split_row(line):
    return [c.strip() for c in line.strip().strip("|").split("|")]


def make_table(rows):
    header, data = rows[0], rows[1:]
    ncol = len(header)
    avail = W - 100
    lens, minw = [], []
    for ci in range(ncol):
        vals = [header[ci]] + [r[ci] if ci < len(r) else "" for r in data]
        avg = sum(len(v) for v in vals) / len(vals)
        lens.append(max(7, min(avg, 90)) ** 0.8)
        lw = max((len(w) for v in vals for w in re.sub(r"[*`]", "", v).split()), default=4)
        minw.append(min(lw * 4.1 + 11, 90))
    tot = sum(lens)
    widths = [max(minw[i], avail * lens[i] / tot) for i in range(ncol)]
    s = sum(widths)
    widths = [w * avail / s for w in widths]
    for _ in range(3):
        short = [i for i in range(ncol) if widths[i] < minw[i]]
        if not short:
            break
        deficit = sum(minw[i] - widths[i] for i in short)
        for i in short:
            widths[i] = minw[i]
        rest = [i for i in range(ncol) if i not in short]
        rs = sum(widths[i] for i in rest)
        for i in rest:
            widths[i] -= deficit * widths[i] / rs
    tdata = [[Paragraph(inline(h), cellh) for h in header]]
    for r in data:
        r = r + [""] * (ncol - len(r))
        tdata.append([Paragraph(inline(x), cell) for x in r[:ncol]])
    t = Table(tdata, colWidths=widths, repeatRows=1)
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), LIGHT),
        ("LINEBELOW", (0, 0), (-1, 0), 1, ROSE),
        ("GRID", (0, 0), (-1, -1), 0.4, BORDER),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 4),
        ("RIGHTPADDING", (0, 0), (-1, -1), 4),
        ("TOPPADDING", (0, 0), (-1, -1), 3),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1),
         [colors.white, colors.HexColor("#fdf6f8")]),
    ]))
    return [t, Spacer(1, 7)]


def parse(md):
    out, lines, i = [], md.split("\n"), 0
    while i < len(lines):
        ln = lines[i]
        if not ln.strip():
            i += 1
            continue
        if ln.startswith("---"):
            i += 1
            continue
        if ln.startswith("## "):
            out.append(Paragraph(inline(ln[3:]), h2))
            i += 1
            continue
        if ln.startswith("### "):
            out.append(Paragraph(inline(ln[4:]), h3))
            i += 1
            continue
        if ln.startswith("|"):
            rows = []
            while i < len(lines) and lines[i].startswith("|"):
                if not re.match(r"^\|[\s\-|:]+\|$", lines[i]):
                    rows.append(split_row(lines[i]))
                i += 1
            out += make_table(rows)
            continue
        if ln.startswith("- "):
            while i < len(lines) and lines[i].startswith("- "):
                out.append(Paragraph(inline(lines[i][2:]), bul, bulletText="\u2022"))
                i += 1
            out.append(Spacer(1, 3))
            continue
        if re.match(r"^\d+\. ", ln):
            while i < len(lines) and re.match(r"^\d+\. ", lines[i]):
                mm = re.match(r"^(\d+)\. (.*)", lines[i])
                out.append(Paragraph(inline(mm.group(2)), bul,
                                     bulletText=mm.group(1) + "."))
                i += 1
            out.append(Spacer(1, 3))
            continue
        para = [ln]
        i += 1
        while i < len(lines) and lines[i].strip() and not re.match(r"^(#|\||- |\d+\. |---)", lines[i]):
            para.append(lines[i])
            i += 1
        out.append(Paragraph(inline(" ".join(para)), body))
    return out


md = open(md_path, encoding="utf-8-sig").read()
os.makedirs(os.path.dirname(os.path.abspath(out_path)), exist_ok=True)
doc = BaseDocTemplate(out_path, pagesize=A4,
                      leftMargin=50, rightMargin=50, topMargin=58, bottomMargin=58,
                      title=TITLE, author=CFG.get("company", "Asmara Restaurant"))
frame = Frame(50, 54, W - 100, H - 108, id="f",
              leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0)
doc.addPageTemplates([PageTemplate(id="p", frames=[frame])])
doc.build([PageBreak()] + parse(md), canvasmaker=NumberedCanvas)
print("built", out_path)