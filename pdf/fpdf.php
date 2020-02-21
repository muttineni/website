<?php
/*******************************************************************************
** Software:  FPDF
** Version:   1.53 (2004-12-31)
** Author:    Olivier Plathey
** Website:   http://www.fpdf.org/
*/
if (!class_exists('FPDF'))
{
  define('FPDF_VERSION', '1.53');
  class FPDF
  {
    var $page;
    var $n;
    var $offsets;
    var $buffer;
    var $pages;
    var $state;
    var $compress;
    var $DefOrientation;
    var $CurOrientation;
    var $OrientationChanges;
    var $k;
    var $fwPt, $fhPt;
    var $fw, $fh;
    var $wPt, $hPt;
    var $w, $h;
    var $lMargin;
    var $tMargin;
    var $rMargin;
    var $bMargin;
    var $cMargin;
    var $x, $y;
    var $lasth;
    var $LineWidth;
    var $CoreFonts;
    var $fonts;
    var $FontFiles;
    var $diffs;
    var $images;
    var $PageLinks;
    var $links;
    var $FontFamily;
    var $FontStyle;
    var $underline;
    var $CurrentFont;
    var $FontSizePt;
    var $FontSize;
    var $DrawColor;
    var $FillColor;
    var $TextColor;
    var $ColorFlag;
    var $ws;
    var $AutoPageBreak;
    var $PageBreakTrigger;
    var $InFooter;
    var $ZoomMode;
    var $LayoutMode;
    var $title;
    var $subject;
    var $author;
    var $keywords;
    var $creator;
    var $AliasNbPages;
    var $PDFVersion;
    function FPDF($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
      $this->_dochecks();
      $this->page = 0;
      $this->n = 2;
      $this->buffer = '';
      $this->pages = array();
      $this->OrientationChanges = array();
      $this->state = 0;
      $this->fonts = array();
      $this->FontFiles = array();
      $this->diffs = array();
      $this->images = array();
      $this->links = array();
      $this->InFooter = false;
      $this->lasth = 0;
      $this->FontFamily = '';
      $this->FontStyle = '';
      $this->FontSizePt = 12;
      $this->underline = false;
      $this->DrawColor = '0 G';
      $this->FillColor = '0 g';
      $this->TextColor = '0 g';
      $this->ColorFlag = false;
      $this->ws = 0;
      $this->CoreFonts = array('courier' => 'Courier', 'courierB' => 'Courier-Bold', 'courierI' => 'Courier-Oblique', 'courierBI' => 'Courier-BoldOblique', 'helvetica' => 'Helvetica', 'helveticaB' => 'Helvetica-Bold', 'helveticaI' => 'Helvetica-Oblique', 'helveticaBI' => 'Helvetica-BoldOblique', 'times' => 'Times-Roman', 'timesB' => 'Times-Bold', 'timesI' => 'Times-Italic', 'timesBI' => 'Times-BoldItalic', 'symbol' => 'Symbol', 'zapfdingbats' => 'ZapfDingbats');
      if ($unit == 'pt')
        $this->k = 1;
      elseif ($unit == 'mm')
        $this->k = 72 / 25.4;
      elseif ($unit == 'cm')
        $this->k = 72 / 2.54;
      elseif ($unit == 'in')
        $this->k = 72;
      else
        $this->Error('Incorrect unit: ' . $unit);
      if (is_string($format))
      {
        $format = strtolower($format);
        if ($format == 'a3')
          $format = array(841.89, 1190.55);
        elseif ($format == 'a4')
          $format = array(595.28, 841.89);
        elseif ($format == 'a5')
          $format = array(420.94, 595.28);
        elseif ($format == 'letter')
          $format = array(612, 792);
        elseif ($format == 'legal')
          $format = array(612, 1008);
        else
          $this->Error('Unknown page format: ' . $format);
        $this->fwPt = $format[0];
        $this->fhPt = $format[1];
      } //if (is_string($format))
      else
      {
        $this->fwPt = $format[0] * $this->k;
        $this->fhPt = $format[1] * $this->k;
      } //else
      $this->fw = $this->fwPt / $this->k;
      $this->fh = $this->fhPt / $this->k;
      $orientation = strtolower($orientation);
      if ($orientation == 'p' or $orientation == 'portrait')
      {
        $this->DefOrientation = 'P';
        $this->wPt = $this->fwPt;
        $this->hPt = $this->fhPt;
      } //if ($orientation == 'p' or $orientation == 'portrait')
      elseif ($orientation == 'l' or $orientation == 'landscape')
      {
        $this->DefOrientation = 'L';
        $this->wPt = $this->fhPt;
        $this->hPt = $this->fwPt;
      } //elseif ($orientation == 'l' or $orientation == 'landscape')
      else
        $this->Error('Incorrect orientation: ' . $orientation);
      $this->CurOrientation = $this->DefOrientation;
      $this->w = $this->wPt / $this->k;
      $this->h = $this->hPt / $this->k;
      $margin = 28.35 / $this->k;
      $this->SetMargins($margin, $margin);
      $this->cMargin = $margin / 10;
      $this->LineWidth = .567 / $this->k;
      $this->SetAutoPageBreak(true, 2 * $margin);
      $this->SetDisplayMode('fullwidth');
      $this->SetCompression(true);
      $this->PDFVersion = '1.3';
    } //function FPDF($orientation = 'P', $unit = 'mm', $format = 'A4')
    function SetMargins($left, $top, $right = -1)
    {
      $this->lMargin = $left;
      $this->tMargin = $top;
      if ($right == -1)
        $right = $left;
      $this->rMargin = $right;
    } //function SetMargins($left, $top, $right = -1)
    function SetLeftMargin($margin)
    {
      $this->lMargin = $margin;
      if ($this->page > 0 and $this->x < $margin)
        $this->x = $margin;
    } //function SetLeftMargin($margin)
    function SetTopMargin($margin)
    {
      $this->tMargin = $margin;
    } //function SetTopMargin($margin)
    function SetRightMargin($margin)
    {
      $this->rMargin = $margin;
    } //function SetRightMargin($margin)
    function SetAutoPageBreak($auto, $margin = 0)
    {
      $this->AutoPageBreak = $auto;
      $this->bMargin = $margin;
      $this->PageBreakTrigger = $this->h - $margin;
    } //function SetAutoPageBreak($auto, $margin = 0)
    function SetDisplayMode($zoom, $layout = 'continuous')
    {
      if ($zoom == 'fullpage' or $zoom == 'fullwidth' or $zoom == 'real' or $zoom == 'default' or !is_string($zoom))
        $this->ZoomMode = $zoom;
      else
        $this->Error('Incorrect zoom display mode: ' . $zoom);
      if ($layout == 'single' or $layout == 'continuous' or $layout == 'two' or $layout == 'default')
        $this->LayoutMode = $layout;
      else
        $this->Error('Incorrect layout display mode: ' . $layout);
    } //function SetDisplayMode($zoom, $layout = 'continuous')
    function SetCompression($compress)
    {
      if (function_exists('gzcompress'))
        $this->compress = $compress;
      else
        $this->compress = false;
    } //function SetCompression($compress)
    function SetTitle($title)
    {
      $this->title = $title;
    } //function SetTitle($title)
    function SetSubject($subject)
    {
      $this->subject = $subject;
    } //function SetSubject($subject)
    function SetAuthor($author)
    {
      $this->author = $author;
    } //function SetAuthor($author)
    function SetKeywords($keywords)
    {
      $this->keywords = $keywords;
    } //function SetKeywords($keywords)
    function SetCreator($creator)
    {
      $this->creator = $creator;
    } //function SetCreator($creator)
    function AliasNbPages($alias = '{nb}')
    {
      $this->AliasNbPages = $alias;
    } //function AliasNbPages($alias = '{nb}')
    function Error($msg)
    {
      die('<B>FPDF error: </B>' . $msg);
    } //function Error($msg)
    function Open()
    {
      $this->state = 1;
    } //function Open()
    function Close()
    {
      if ($this->state == 3)
        return;
      if ($this->page == 0)
        $this->AddPage();
      $this->InFooter = true;
      $this->Footer();
      $this->InFooter = false;
      $this->_endpage();
      $this->_enddoc();
    } //function Close()
    function AddPage($orientation = '')
    {
      if ($this->state == 0)
        $this->Open();
      $family = $this->FontFamily;
      $style = $this->FontStyle . ($this->underline ? 'U' : '');
      $size = $this->FontSizePt;
      $lw = $this->LineWidth;
      $dc = $this->DrawColor;
      $fc = $this->FillColor;
      $tc = $this->TextColor;
      $cf = $this->ColorFlag;
      if ($this->page > 0)
      {
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        $this->_endpage();
      } //if ($this->page > 0)
      $this->_beginpage($orientation);
      $this->_out('2 J');
      $this->LineWidth = $lw;
      $this->_out(sprintf('%.2f w', $lw * $this->k));
      if ($family)
        $this->SetFont($family, $style, $size);
      $this->DrawColor = $dc;
      if ($dc != '0 G')
        $this->_out($dc);
      $this->FillColor = $fc;
      if ($fc != '0 g')
        $this->_out($fc);
      $this->TextColor = $tc;
      $this->ColorFlag = $cf;
      $this->Header();
      if ($this->LineWidth != $lw)
      {
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2f w', $lw * $this->k));
      } //if ($this->LineWidth != $lw)
      if ($family)
        $this->SetFont($family, $style, $size);
      if ($this->DrawColor != $dc)
      {
        $this->DrawColor = $dc;
        $this->_out($dc);
      } //if ($this->DrawColor != $dc)
      if ($this->FillColor != $fc)
      {
        $this->FillColor = $fc;
        $this->_out($fc);
      } //if ($this->FillColor != $fc)
      $this->TextColor = $tc;
      $this->ColorFlag = $cf;
    } //function AddPage($orientation = '')
    function Header()
    {
    } //function Header()
    function Footer()
    {
    } //function Footer()
    function PageNo()
    {
      return $this->page;
    } //function PageNo()
    function SetDrawColor($r, $g = -1, $b = -1)
    {
      if (($r == 0 and $g == 0 and $b == 0) or $g == -1)
        $this->DrawColor = sprintf('%.3f G', $r / 255);
      else
        $this->DrawColor = sprintf('%.3f %.3f %.3f RG', $r / 255, $g / 255, $b / 255);
      if ($this->page > 0)
        $this->_out($this->DrawColor);
    } //function SetDrawColor($r, $g = -1, $b = -1)
    function SetFillColor($r, $g = -1, $b = -1)
    {
      if (($r == 0 and $g == 0 and $b == 0) or $g == -1)
        $this->FillColor = sprintf('%.3f g', $r / 255);
      else
        $this->FillColor = sprintf('%.3f %.3f %.3f rg', $r / 255, $g / 255, $b / 255);
      $this->ColorFlag = ($this->FillColor != $this->TextColor);
      if ($this->page > 0)
        $this->_out($this->FillColor);
    } //function SetFillColor($r, $g = -1, $b = -1)
    function SetTextColor($r, $g = -1, $b = -1)
    {
      if (($r == 0 and $g == 0 and $b == 0) or $g == -1)
        $this->TextColor = sprintf('%.3f g', $r / 255);
      else
        $this->TextColor = sprintf('%.3f %.3f %.3f rg', $r / 255, $g / 255, $b / 255);
      $this->ColorFlag = ($this->FillColor != $this->TextColor);
    } //function SetTextColor($r, $g = -1, $b = -1)
    function GetStringWidth($s)
    {
      $s = (string)$s;
      $cw =& $this->CurrentFont['cw'];
      $w = 0;
      $l = strlen($s);
      for ($i = 0; $i < $l; $i++)
        $w += $cw[$s{$i}];
      return $w * $this->FontSize / 1000;
    } //function GetStringWidth($s)
    function SetLineWidth($width)
    {
      $this->LineWidth = $width;
      if ($this->page > 0)
        $this->_out(sprintf('%.2f w', $width * $this->k));
    } //function SetLineWidth($width)
    function Line($x1, $y1, $x2, $y2)
    {
      $this->_out(sprintf('%.2f %.2f m %.2f %.2f l S', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k));
    } //function Line($x1, $y1, $x2, $y2)
    function Rect($x, $y, $w, $h, $style = '')
    {
      if ($style == 'F')
        $op = 'f';
      elseif ($style == 'FD' or $style == 'DF')
        $op = 'B';
      else
        $op = 'S';
      $this->_out(sprintf('%.2f %.2f %.2f %.2f re %s', $x * $this->k, ($this->h - $y) * $this->k, $w * $this->k, -$h * $this->k, $op));
    } //function Rect($x, $y, $w, $h, $style = '')
    function AddFont($family, $style = '', $file = '')
    {
      $family = strtolower($family);
      if ($file == '')
        $file = str_replace(' ', '', $family) . strtolower($style) . '.php';
      if ($family == 'arial')
        $family = 'helvetica';
      $style = strtoupper($style);
      if ($style == 'IB')
        $style = 'BI';
      $fontkey = $family . $style;
      if (isset($this->fonts[$fontkey]))
        $this->Error('Font already added: ' . $family . ' ' . $style);
      include($this->_getfontpath() . $file);
      if (!isset($name))
        $this->Error('Could not include font definition file');
      $i = count($this->fonts) + 1;
      $this->fonts[$fontkey] = array('i' => $i, 'type' => $type, 'name' => $name, 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw, 'enc' => $enc, 'file' => $file);
      if ($diff)
      {
        $d = 0;
        $nb = count($this->diffs);
        for ($i = 1; $i <= $nb; $i++)
        {
          if ($this->diffs[$i] == $diff)
          {
            $d = $i;
            break;
          } //if ($this->diffs[$i] == $diff)
        } //for ($i = 1; $i <= $nb; $i++)
        if ($d == 0)
        {
          $d = $nb + 1;
          $this->diffs[$d] = $diff;
        } //if ($d == 0)
        $this->fonts[$fontkey]['diff'] = $d;
      } //if ($diff)
      if ($file)
      {
        if ($type == 'TrueType')
          $this->FontFiles[$file] = array('length1' => $originalsize);
        else
          $this->FontFiles[$file] = array('length1' => $size1, 'length2' => $size2);
      } //if ($file)
    } //function AddFont($family, $style = '', $file = '')
    function SetFont($family, $style = '', $size = 0)
    {
      global $fpdf_charwidths;
      $family = strtolower($family);
      if ($family == '')
        $family = $this->FontFamily;
      if ($family == 'arial')
        $family = 'helvetica';
      elseif ($family == 'symbol' or $family == 'zapfdingbats')
        $style = '';
      $style = strtoupper($style);
      if (strpos($style, 'U') !== false)
      {
        $this->underline = true;
        $style = str_replace('U', '', $style);
      } //if (strpos($style, 'U') !== false)
      else
        $this->underline = false;
      if ($style == 'IB')
        $style = 'BI';
      if ($size == 0)
        $size = $this->FontSizePt;
      if ($this->FontFamily == $family and $this->FontStyle == $style and $this->FontSizePt == $size)
        return;
      $fontkey = $family . $style;
      if (!isset($this->fonts[$fontkey]))
      {
        if (isset($this->CoreFonts[$fontkey]))
        {
          if (!isset($fpdf_charwidths[$fontkey]))
          {
            $file = $family;
            if ($family == 'times' or $family == 'helvetica')
              $file .= strtolower($style);
            include($this->_getfontpath() . $file . '.php');
            if (!isset($fpdf_charwidths[$fontkey]))
              $this->Error('Could not include font metric file');
          } //if (!isset($fpdf_charwidths[$fontkey]))
          $i = count($this->fonts) + 1;
          $this->fonts[$fontkey] = array('i' => $i, 'type' => 'core', 'name' => $this->CoreFonts[$fontkey], 'up' => -100, 'ut' => 50, 'cw' => $fpdf_charwidths[$fontkey]);
        } //if (isset($this->CoreFonts[$fontkey]))
        else
          $this->Error('Undefined font: ' . $family . ' ' . $style);
      } //if (!isset($this->fonts[$fontkey]))
      $this->FontFamily = $family;
      $this->FontStyle = $style;
      $this->FontSizePt = $size;
      $this->FontSize = $size / $this->k;
      $this->CurrentFont =& $this->fonts[$fontkey];
      if ($this->page > 0)
        $this->_out(sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    } //function SetFont($family, $style = '', $size = 0)
    function SetFontSize($size)
    {
      if ($this->FontSizePt == $size)
        return;
      $this->FontSizePt = $size;
      $this->FontSize = $size / $this->k;
      if ($this->page > 0)
        $this->_out(sprintf('BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    } //function SetFontSize($size)
    function AddLink()
    {
      $n = count($this->links) + 1;
      $this->links[$n] = array(0, 0);
      return $n;
    } //function AddLink()
    function SetLink($link, $y = 0, $page = -1)
    {
      if ($y == -1)
        $y = $this->y;
      if ($page == -1)
        $page = $this->page;
      $this->links[$link] = array($page, $y);
    } //function SetLink($link, $y = 0, $page = -1)
    function link($x, $y, $w, $h, $link)
    {
      $this->PageLinks[$this->page][] = array($x * $this->k, $this->hPt - $y * $this->k, $w * $this->k, $h * $this->k, $link);
    } //function Link($x, $y, $w, $h, $link)
    function Text($x, $y, $txt)
    {
      $s = sprintf('BT %.2f %.2f Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
      if ($this->underline and $txt != '')
        $s .= ' ' . $this->_dounderline($x, $y, $txt);
      if ($this->ColorFlag)
        $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
      $this->_out($s);
    } //function Text($x, $y, $txt)
    function AcceptPageBreak()
    {
      return $this->AutoPageBreak;
    } //function AcceptPageBreak()
    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '')
    {
      $k = $this->k;
      if ($this->y + $h > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
      {
        $x = $this->x;
        $ws = $this->ws;
        if ($ws > 0)
        {
          $this->ws = 0;
          $this->_out('0 Tw');
        } //if ($ws > 0)
        $this->AddPage($this->CurOrientation);
        $this->x = $x;
        if ($ws > 0)
        {
          $this->ws = $ws;
          $this->_out(sprintf('%.3f Tw', $ws * $k));
        } //if ($ws > 0)
      } //if ($this->y + $h > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
      if ($w == 0)
        $w = $this->w - $this->rMargin - $this->x;
      $s = '';
      if ($fill == 1 or $border == 1)
      {
        if ($fill == 1)
          $op = ($border == 1) ? 'B' : 'f';
        else
          $op = 'S';
        $s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
      } //if ($fill == 1 or $border == 1)
      if (is_string($border))
      {
        $x = $this->x;
        $y = $this->y;
        if (strpos($border, 'L') !== false)
          $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
        if (strpos($border, 'T') !== false)
          $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
        if (strpos($border, 'R') !== false)
          $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
        if (strpos($border, 'B') !== false)
          $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
      } //if (is_string($border))
      if ($txt !== '')
      {
        if ($align == 'R')
          $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
        elseif ($align == 'C')
          $dx = ($w - $this->GetStringWidth($txt)) / 2;
        else
          $dx = $this->cMargin;
        if ($this->ColorFlag)
          $s .= 'q ' . $this->TextColor . ' ';
        $txt2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
        $s .= sprintf('BT %.2f %.2f Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $txt2);
        if ($this->underline)
          $s .= ' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
        if ($this->ColorFlag)
          $s .= ' Q';
        if ($link)
          $this->link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
      } //if ($txt !== '')
      if ($s)
        $this->_out($s);
      $this->lasth = $h;
      if ($ln > 0)
      {
        $this->y += $h;
        if ($ln == 1)
          $this->x = $this->lMargin;
      } //if ($ln > 0)
      else
        $this->x += $w;
    } //function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '')
    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = 0)
    {
      $cw =& $this->CurrentFont['cw'];
      if ($w == 0)
        $w = $this->w - $this->rMargin - $this->x;
      $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
      $s = str_replace("\r", '', $txt);
      $nb = strlen($s);
      if ($nb > 0 and $s[$nb - 1] == "\n")
        $nb--;
      $b = 0;
      if ($border)
      {
        if ($border == 1)
        {
          $border = 'LTRB';
          $b = 'LRT';
          $b2 = 'LR';
        } //if ($border == 1)
        else
        {
          $b2 = '';
          if (strpos($border, 'L') !== false)
            $b2 .= 'L';
          if (strpos($border, 'R') !== false)
            $b2 .= 'R';
          $b = (strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
        } //else
      } //if ($border)
      $sep = -1;
      $i = 0;
      $j = 0;
      $l = 0;
      $ns = 0;
      $nl = 1;
      while ($i < $nb)
      {
        $c = $s{$i};
        if ($c == "\n")
        {
          if ($this->ws > 0)
          {
            $this->ws = 0;
            $this->_out('0 Tw');
          } //if ($this->ws > 0)
          $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
          $i++;
          $sep = -1;
          $j = $i;
          $l = 0;
          $ns = 0;
          $nl++;
          if ($border and $nl == 2)
            $b = $b2;
          continue;
        } //if ($c == "\n")
        if ($c == ' ')
        {
          $sep = $i;
          $ls = $l;
          $ns++;
        } //if ($c == ' ')
        $l += $cw[$c];
        if ($l > $wmax)
        {
          if ($sep == -1)
          {
            if ($i == $j)
              $i++;
            if ($this->ws > 0)
            {
              $this->ws = 0;
              $this->_out('0 Tw');
            } //if ($this->ws > 0)
            $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
          } //if ($sep == -1)
          else
          {
            if ($align == 'J')
            {
              $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
              $this->_out(sprintf('%.3f Tw', $this->ws * $this->k));
            } //if ($align == 'J')
            $this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill);
            $i = $sep + 1;
          } //else
          $sep = -1;
          $j = $i;
          $l = 0;
          $ns = 0;
          $nl++;
          if ($border and $nl == 2)
            $b = $b2;
        } //if ($l > $wmax)
        else
          $i++;
      } //while ($i < $nb)
      if ($this->ws > 0)
      {
        $this->ws = 0;
        $this->_out('0 Tw');
      } //if ($this->ws > 0)
      if ($border and strpos($border, 'B') !== false)
        $b .= 'B';
      $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
      $this->x = $this->lMargin;
    } //function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = 0)
    function Write($h, $txt, $link = '')
    {
      $cw =& $this->CurrentFont['cw'];
      $w = $this->w - $this->rMargin - $this->x;
      $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
      $s = str_replace("\r", '', $txt);
      $nb = strlen($s);
      $sep = -1;
      $i = 0;
      $j = 0;
      $l = 0;
      $nl = 1;
      while ($i < $nb)
      {
        $c = $s{$i};
        if ($c == "\n")
        {
          $this->Cell($w, $h, substr($s, $j, $i - $j), 0, 2, '', 0, $link);
          $i++;
          $sep = -1;
          $j = $i;
          $l = 0;
          if ($nl == 1)
          {
            $this->x = $this->lMargin;
            $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
          } //if ($nl == 1)
          $nl++;
          continue;
        } //if ($c == "\n")
        if ($c == ' ')
          $sep = $i;
        $l += $cw[$c];
        if ($l > $wmax)
        {
          if ($sep == -1)
          {
            if ($this->x > $this->lMargin)
            {
              $this->x = $this->lMargin;
              $this->y += $h;
              $w = $this->w - $this->rMargin - $this->x;
              $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
              $i++;
              $nl++;
              continue;
            } //if ($this->x > $this->lMargin)
            if ($i == $j)
              $i++;
            $this->Cell($w, $h, substr($s, $j, $i - $j), 0, 2, '', 0, $link);
          } //if ($sep == -1)
          else
          {
            $this->Cell($w, $h, substr($s, $j, $sep - $j), 0, 2, '', 0, $link);
            $i = $sep + 1;
          } //else
          $sep = -1;
          $j = $i;
          $l = 0;
          if ($nl == 1)
          {
            $this->x = $this->lMargin;
            $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
          } //if ($nl == 1)
          $nl++;
        } //if ($l > $wmax)
        else
          $i++;
      } //while ($i < $nb)
      if ($i != $j)
        $this->Cell($l / 1000 * $this->FontSize, $h, substr($s, $j), 0, 0, '', 0, $link);
    } //function Write($h, $txt, $link = '')
    function Image($file, $x, $y, $w = 0, $h = 0, $type = '', $link = '')
    {
      if (!isset($this->images[$file]))
      {
        if ($type == '')
        {
          $pos = strrpos($file, '.');
          if (!$pos)
            $this->Error('Image file has no extension and no type was specified: ' . $file);
          $type = substr($file, $pos + 1);
        } //if ($type == '')
        $type = strtolower($type);
        // $mqr = get_magic_quotes_runtime(); comment by bob
        // set_magic_quotes_runtime(0); comment by bob
        if ($type == 'jpg' or $type == 'jpeg')
          $info = $this->_parsejpg($file);
        elseif ($type == 'png')
          $info = $this->_parsepng($file);
        else
        {
          $mtd = '_parse' . $type;
          if (!method_exists($this, $mtd))
            $this->Error('Unsupported image type: ' . $type);
          $info = $this->$mtd($file);
        } //else
        // set_magic_quotes_runtime($mqr); comment by bob
        $info['i'] = count($this->images) + 1;
        $this->images[$file] = $info;
      } //if (!isset($this->images[$file]))
      else
        $info = $this->images[$file];
      if ($w == 0 and $h == 0)
      {
        $w = $info['w'] / $this->k;
        $h = $info['h'] / $this->k;
      } //if ($w == 0 and $h == 0)
      if ($w == 0)
        $w = $h * $info['w'] / $info['h'];
      if ($h == 0)
        $h = $w * $info['h'] / $info['w'];
      $this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
      if ($link)
        $this->link($x, $y, $w, $h, $link);
    } //function Image($file, $x, $y, $w = 0, $h = 0, $type = '', $link = '')
    function Ln($h = '')
    {
      $this->x = $this->lMargin;
      if (is_string($h))
        $this->y += $this->lasth;
      else
        $this->y += $h;
    } //function Ln($h = '')
    function GetX()
    {
      return $this->x;
    } //function GetX()
    function SetX($x)
    {
      if ($x >= 0)
        $this->x = $x;
      else
        $this->x = $this->w + $x;
    } //function SetX($x)
    function GetY()
    {
      return $this->y;
    } //function GetY()
    function SetY($y)
    {
      $this->x = $this->lMargin;
      if ($y >= 0)
        $this->y = $y;
      else
        $this->y = $this->h + $y;
    } //function SetY($y)
    function SetXY($x, $y)
    {
      $this->SetY($y);
      $this->SetX($x);
    } //function SetXY($x, $y)
    function GetPageWidth()
    {
      return $this->w - $this->rMargin - $this->lMargin;
    } //function GetPageWidth()
    function Output($name = '', $dest = '')
    {
      if ($this->state < 3)
        $this->Close();
      if (is_bool($dest))
        $dest = $dest ? 'D' : 'F';
      $dest = strtoupper($dest);
      if ($dest == '')
      {
        if ($name == '')
        {
          $name = 'doc.pdf';
          $dest = 'I';
        } //if ($name == '')
        else
          $dest = 'F';
      } //if ($dest == '')
      switch ($dest)
      {
        case 'I':
          if (ob_get_contents())
            $this->Error('Some data has already been output, can\'t send PDF file');
          if (php_sapi_name() != 'cli')
          {
            header('Content-Type: application/pdf');
            if (headers_sent())
              $this->Error('Some data has already been output to browser, can\'t send PDF file');
            header('Content-Length: ' . strlen($this->buffer));
            header('Content-disposition: inline; filename="' . $name . '"');
          } //if (php_sapi_name() != 'cli')
          echo $this->buffer;
          break;
        case 'D':
          if (ob_get_contents())
            $this->Error('Some data has already been output, can\'t send PDF file');
          if (isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
            header('Content-Type: application/force-download');
          else
            header('Content-Type: application/octet-stream');
          if (headers_sent())
            $this->Error('Some data has already been output to browser, can\'t send PDF file');
          header('Content-Length: ' . strlen($this->buffer));
          header('Content-disposition: attachment; filename="' . $name . '"');
          echo $this->buffer;
          break;
        case 'F':
          $f = fopen($name, 'wb');
          if (!$f)
            $this->Error('Unable to create output file: ' . $name);
          fwrite($f, $this->buffer, strlen($this->buffer));
          fclose($f);
          break;
        case 'S':
          return $this->buffer;
        default:
          $this->Error('Incorrect output destination: ' . $dest);
      } //switch ($dest)
      return '';
    } //function Output($name = '', $dest = '')
    function _dochecks()
    {
      if (1.1 == 1)
        $this->Error('Don\'t alter the locale before including class file');
      if (sprintf('%.1f', 1.0) != '1.0')
        setlocale(LC_NUMERIC, 'C');
    } //function _dochecks()
    function _getfontpath()
    {
      if (!defined('FPDF_FONTPATH') and is_dir(dirname(__FILE__) . '/font'))
        define('FPDF_FONTPATH', dirname(__FILE__) . '/font/');
      return defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
    } //function _getfontpath()
    function _putpages()
    {
      $nb = $this->page;
      if (!empty($this->AliasNbPages))
      {
        for ($n = 1; $n <= $nb; $n++)
          $this->pages[$n] = str_replace($this->AliasNbPages, $nb, $this->pages[$n]);
      } //if (!empty($this->AliasNbPages))
      if ($this->DefOrientation == 'P')
      {
        $wPt = $this->fwPt;
        $hPt = $this->fhPt;
      } //if ($this->DefOrientation == 'P')
      else
      {
        $wPt = $this->fhPt;
        $hPt = $this->fwPt;
      } //else
      $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
      for ($n = 1; $n <= $nb; $n++)
      {
        $this->_newobj();
        $this->_out('<</Type /Page');
        $this->_out('/Parent 1 0 R');
        if (isset($this->OrientationChanges[$n]))
          $this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]', $hPt, $wPt));
        $this->_out('/Resources 2 0 R');
        if (isset($this->PageLinks[$n]))
        {
          $annots = '/Annots [';
          foreach ($this->PageLinks[$n] as $pl)
          {
            $rect = sprintf('%.2f %.2f %.2f %.2f', $pl[0], $pl[1], $pl[0] + $pl[2], $pl[1] - $pl[3]);
            $annots .= '<</Type /Annot /Subtype /Link /Rect [' . $rect . '] /Border [0 0 0] ';
            if (is_string($pl[4]))
              $annots .= '/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
            else
            {
              $l = $this->links[$pl[4]];
              $h = isset($this->OrientationChanges[$l[0]]) ? $wPt : $hPt;
              $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]>>', 1 + 2 * $l[0], $h - $l[1] * $this->k);
            } //else
          } //foreach ($this->PageLinks[$n] as $pl)
          $this->_out($annots . ']');
        } //if (isset($this->PageLinks[$n]))
        $this->_out('/Contents ' . ($this->n + 1) . ' 0 R>>');
        $this->_out('endobj');
        $p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
        $this->_newobj();
        $this->_out('<<' . $filter . '/Length ' . strlen($p) . '>>');
        $this->_putstream($p);
        $this->_out('endobj');
      } //for ($n = 1; $n <= $nb; $n++)
      $this->offsets[1] = strlen($this->buffer);
      $this->_out('1 0 obj');
      $this->_out('<</Type /Pages');
      $kids = '/Kids [';
      for ($i = 0; $i < $nb; $i++)
        $kids .= (3 + 2 * $i) . ' 0 R ';
      $this->_out($kids . ']');
      $this->_out('/Count ' . $nb);
      $this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]', $wPt, $hPt));
      $this->_out('>>');
      $this->_out('endobj');
    } //function _putpages()
    function _putfonts()
    {
      $nf = $this->n;
      foreach ($this->diffs as $diff)
      {
        $this->_newobj();
        $this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [' . $diff . ']>>');
        $this->_out('endobj');
      } //foreach ($this->diffs as $diff)
      // $mqr = get_magic_quotes_runtime(); comment by bob
      // set_magic_quotes_runtime(0); comment by bob
      foreach ($this->FontFiles as $file => $info)
      {
        $this->_newobj();
        $this->FontFiles[$file]['n'] = $this->n;
        $font = '';
        $f = fopen($this->_getfontpath() . $file, 'rb', 1);
        if (!$f)
          $this->Error('Font file not found');
        while (!feof($f))
          $font .= fread($f, 8192);
        fclose($f);
        $compressed = (substr($file, -2) == '.z');
        if (!$compressed and isset($info['length2']))
        {
          $header = (ord($font{0}) == 128);
          if ($header)
          {
            $font = substr($font, 6);
          } //if ($header)
          if ($header and ord($font{$info['length1']}) == 128)
          {
            $font = substr($font, 0, $info['length1']) . substr($font, $info['length1'] + 6);
          } //if ($header and ord($font{$info['length1']}) == 128)
        } //if (!$compressed and isset($info['length2']))
        $this->_out('<</Length ' . strlen($font));
        if ($compressed)
          $this->_out('/Filter /FlateDecode');
        $this->_out('/Length1 ' . $info['length1']);
        if (isset($info['length2']))
          $this->_out('/Length2 ' . $info['length2'] . ' /Length3 0');
        $this->_out('>>');
        $this->_putstream($font);
        $this->_out('endobj');
      } //foreach ($this->FontFiles as $file => $info)
      // set_magic_quotes_runtime($mqr); comment by bob
      foreach ($this->fonts as $k => $font)
      {
        $this->fonts[$k]['n'] = $this->n + 1;
        $type = $font['type'];
        $name = $font['name'];
        if ($type == 'core')
        {
          $this->_newobj();
          $this->_out('<</Type /Font');
          $this->_out('/BaseFont /' . $name);
          $this->_out('/Subtype /Type1');
          if ($name != 'Symbol' and $name != 'ZapfDingbats')
            $this->_out('/Encoding /WinAnsiEncoding');
          $this->_out('>>');
          $this->_out('endobj');
        } //if ($type == 'core')
        elseif ($type == 'Type1' or $type == 'TrueType')
        {
          $this->_newobj();
          $this->_out('<</Type /Font');
          $this->_out('/BaseFont /' . $name);
          $this->_out('/Subtype /' . $type);
          $this->_out('/FirstChar 32 /LastChar 255');
          $this->_out('/Widths ' . ($this->n + 1) . ' 0 R');
          $this->_out('/FontDescriptor ' . ($this->n + 2) . ' 0 R');
          if ($font['enc'])
          {
            if (isset($font['diff']))
              $this->_out('/Encoding ' . ($nf + $font['diff']) . ' 0 R');
            else
              $this->_out('/Encoding /WinAnsiEncoding');
          } //if ($font['enc'])
          $this->_out('>>');
          $this->_out('endobj');
          $this->_newobj();
          $cw =& $font['cw'];
          $s = '[';
          for ($i = 32; $i <= 255; $i++)
            $s .= $cw[chr($i)] . ' ';
          $this->_out($s . ']');
          $this->_out('endobj');
          $this->_newobj();
          $s = '<</Type /FontDescriptor /FontName /' . $name;
          foreach ($font['desc'] as $k => $v)
            $s .= ' /' . $k . ' ' . $v;
          $file = $font['file'];
          if ($file)
            $s .= ' /FontFile' . ($type == 'Type1' ? '' : '2') . ' ' . $this->FontFiles[$file]['n'] . ' 0 R';
          $this->_out($s . '>>');
          $this->_out('endobj');
        } //elseif ($type == 'Type1' or $type == 'TrueType')
        else
        {
          $mtd = '_put' . strtolower($type);
          if (!method_exists($this, $mtd))
            $this->Error('Unsupported font type: ' . $type);
          $this->$mtd($font);
        } //else
      } //foreach ($this->fonts as $k => $font)
    } //function _putfonts()
    function _putimages()
    {
      $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
      reset($this->images);
      while (list($file, $info) = each($this->images))
      {
        $this->_newobj();
        $this->images[$file]['n'] = $this->n;
        $this->_out('<</Type /XObject');
        $this->_out('/Subtype /Image');
        $this->_out('/Width ' . $info['w']);
        $this->_out('/Height ' . $info['h']);
        if ($info['cs'] == 'Indexed')
          $this->_out('/ColorSpace [/Indexed /DeviceRGB ' . (strlen($info['pal']) / 3 - 1) . ' ' . ($this->n + 1) . ' 0 R]');
        else
        {
          $this->_out('/ColorSpace /' . $info['cs']);
          if ($info['cs'] == 'DeviceCMYK')
            $this->_out('/Decode [1 0 1 0 1 0 1 0]');
        } //else
        $this->_out('/BitsPerComponent ' . $info['bpc']);
        if (isset($info['f']))
          $this->_out('/Filter /' . $info['f']);
        if (isset($info['parms']))
          $this->_out($info['parms']);
        if (isset($info['trns']) and is_array($info['trns']))
        {
          $trns = '';
          for ($i = 0; $i < count($info['trns']); $i++)
            $trns .= $info['trns'][$i] . ' ' . $info['trns'][$i] . ' ';
          $this->_out('/Mask [' . $trns . ']');
        } //if (isset($info['trns']) and is_array($info['trns']))
        $this->_out('/Length ' . strlen($info['data']) . '>>');
        $this->_putstream($info['data']);
        unset($this->images[$file]['data']);
        $this->_out('endobj');
        if ($info['cs'] == 'Indexed')
        {
          $this->_newobj();
          $pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
          $this->_out('<<' . $filter . '/Length ' . strlen($pal) . '>>');
          $this->_putstream($pal);
          $this->_out('endobj');
        } //if ($info['cs'] == 'Indexed')
      } //while (list($file, $info) = each($this->images))
    } //function _putimages()
    function _putxobjectdict()
    {
      foreach ($this->images as $image)
        $this->_out('/I' . $image['i'] . ' ' . $image['n'] . ' 0 R');
    } //function _putxobjectdict()
    function _putresourcedict()
    {
      $this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
      $this->_out('/Font <<');
      foreach ($this->fonts as $font)
        $this->_out('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
      $this->_out('>>');
      $this->_out('/XObject <<');
      $this->_putxobjectdict();
      $this->_out('>>');
    } //function _putresourcedict()
    function _putresources()
    {
      $this->_putfonts();
      $this->_putimages();
      $this->offsets[2] = strlen($this->buffer);
      $this->_out('2 0 obj');
      $this->_out('<<');
      $this->_putresourcedict();
      $this->_out('>>');
      $this->_out('endobj');
    } //function _putresources()
    function _putinfo()
    {
      $this->_out('/Producer ' . $this->_textstring('FPDF ' . FPDF_VERSION));
      if (!empty($this->title))
        $this->_out('/Title ' . $this->_textstring($this->title));
      if (!empty($this->subject))
        $this->_out('/Subject ' . $this->_textstring($this->subject));
      if (!empty($this->author))
        $this->_out('/Author ' . $this->_textstring($this->author));
      if (!empty($this->keywords))
        $this->_out('/Keywords ' . $this->_textstring($this->keywords));
      if (!empty($this->creator))
        $this->_out('/Creator ' . $this->_textstring($this->creator));
      $this->_out('/CreationDate ' . $this->_textstring('D:' . date('YmdHis')));
    } //function _putinfo()
    function _putcatalog()
    {
      $this->_out('/Type /Catalog');
      $this->_out('/Pages 1 0 R');
      if ($this->ZoomMode == 'fullpage')
        $this->_out('/OpenAction [3 0 R /Fit]');
      elseif ($this->ZoomMode == 'fullwidth')
        $this->_out('/OpenAction [3 0 R /FitH null]');
      elseif ($this->ZoomMode == 'real')
        $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
      elseif (!is_string($this->ZoomMode))
        $this->_out('/OpenAction [3 0 R /XYZ null null ' . ($this->ZoomMode / 100) . ']');
      if ($this->LayoutMode == 'single')
        $this->_out('/PageLayout /SinglePage');
      elseif ($this->LayoutMode == 'continuous')
        $this->_out('/PageLayout /OneColumn');
      elseif ($this->LayoutMode == 'two')
        $this->_out('/PageLayout /TwoColumnLeft');
    } //function _putcatalog()
    function _putheader()
    {
      $this->_out('%PDF-' . $this->PDFVersion);
    } //function _putheader()
    function _puttrailer()
    {
      $this->_out('/Size ' . ($this->n + 1));
      $this->_out('/Root ' . $this->n . ' 0 R');
      $this->_out('/Info ' . ($this->n - 1) . ' 0 R');
    } //function _puttrailer()
    function _enddoc()
    {
      $this->_putheader();
      $this->_putpages();
      $this->_putresources();
      $this->_newobj();
      $this->_out('<<');
      $this->_putinfo();
      $this->_out('>>');
      $this->_out('endobj');
      $this->_newobj();
      $this->_out('<<');
      $this->_putcatalog();
      $this->_out('>>');
      $this->_out('endobj');
      $o = strlen($this->buffer);
      $this->_out('xref');
      $this->_out('0 ' . ($this->n + 1));
      $this->_out('0000000000 65535 f ');
      for ($i = 1; $i <= $this->n; $i++)
        $this->_out(sprintf('%010d 00000 n ', $this->offsets[$i]));
      $this->_out('trailer');
      $this->_out('<<');
      $this->_puttrailer();
      $this->_out('>>');
      $this->_out('startxref');
      $this->_out($o);
      $this->_out('%%EOF');
      $this->state = 3;
    } //function _enddoc()
    function _beginpage($orientation)
    {
      $this->page++;
      $this->pages[$this->page] = '';
      $this->state = 2;
      $this->x = $this->lMargin;
      $this->y = $this->tMargin;
      $this->FontFamily = '';
      if (!$orientation)
        $orientation = $this->DefOrientation;
      else
      {
        $orientation = strtoupper($orientation{0});
        if ($orientation != $this->DefOrientation)
          $this->OrientationChanges[$this->page] = true;
      } //else
      if ($orientation != $this->CurOrientation)
      {
        if ($orientation == 'P')
        {
          $this->wPt = $this->fwPt;
          $this->hPt = $this->fhPt;
          $this->w = $this->fw;
          $this->h = $this->fh;
        } //if ($orientation == 'P')
        else
        {
          $this->wPt = $this->fhPt;
          $this->hPt = $this->fwPt;
          $this->w = $this->fh;
          $this->h = $this->fw;
        } //else
        $this->PageBreakTrigger = $this->h - $this->bMargin;
        $this->CurOrientation = $orientation;
      } //if ($orientation != $this->CurOrientation)
    } //function _beginpage($orientation)
    function _endpage()
    {
      $this->state = 1;
    } //function _endpage()
    function _newobj()
    {
      $this->n++;
      $this->offsets[$this->n] = strlen($this->buffer);
      $this->_out($this->n . ' 0 obj');
    } //function _newobj()
    function _dounderline($x, $y, $txt)
    {
      $up = $this->CurrentFont['up'];
      $ut = $this->CurrentFont['ut'];
      $w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ');
      return sprintf('%.2f %.2f %.2f %.2f re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$ut / 1000 * $this->FontSizePt);
    } //function _dounderline($x, $y, $txt)
    function _parsejpg($file)
    {
      $a = getimagesize($file);
      if (!$a)
        $this->Error('Missing or incorrect image file: ' . $file);
      if ($a[2] != 2)
        $this->Error('Not a JPEG file: ' . $file);
      if (!isset($a['channels']) or $a['channels'] == 3)
        $colspace = 'DeviceRGB';
      elseif ($a['channels'] == 4)
        $colspace = 'DeviceCMYK';
      else
        $colspace = 'DeviceGray';
      $bpc = isset($a['bits']) ? $a['bits'] : 8;
      $f = fopen($file, 'rb');
      $data = '';
      while (!feof($f))
        $data .= fread($f, 4096);
      fclose($f);
      return array('w' => $a[0], 'h' => $a[1], 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'DCTDecode', 'data' => $data);
    } //function _parsejpg($file)
    function _parsepng($file)
    {
      $f = fopen($file, 'rb');
      if (!$f)
        $this->Error('Can\'t open image file: ' . $file);
      if (fread($f, 8) != chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10))
        $this->Error('Not a PNG file: ' . $file);
      fread($f, 4);
      if (fread($f, 4) != 'IHDR')
        $this->Error('Incorrect PNG file: ' . $file);
      $w = $this->_freadint($f);
      $h = $this->_freadint($f);
      $bpc = ord(fread($f, 1));
      if ($bpc > 8)
        $this->Error('16-bit depth not supported: ' . $file);
      $ct = ord(fread($f, 1));
      if ($ct == 0)
        $colspace = 'DeviceGray';
      elseif ($ct == 2)
        $colspace = 'DeviceRGB';
      elseif ($ct == 3)
        $colspace = 'Indexed';
      else
        $this->Error('Alpha channel not supported: ' . $file);
      if (ord(fread($f, 1)) != 0)
        $this->Error('Unknown compression method: ' . $file);
      if (ord(fread($f, 1)) != 0)
        $this->Error('Unknown filter method: ' . $file);
      if (ord(fread($f, 1)) != 0)
        $this->Error('Interlacing not supported: ' . $file);
      fread($f, 4);
      $parms = '/DecodeParms <</Predictor 15 /Colors ' . ($ct == 2 ? 3 : 1) . ' /BitsPerComponent ' . $bpc . ' /Columns ' . $w . '>>';
      $pal = '';
      $trns = '';
      $data = '';
      do
      {
        $n = $this->_freadint($f);
        $type = fread($f, 4);
        if ($type == 'PLTE')
        {
          $pal = fread($f, $n);
          fread($f, 4);
        } //if ($type == 'PLTE')
        elseif ($type == 'tRNS')
        {
          $t = fread($f, $n);
          if ($ct == 0)
            $trns = array(ord(substr($t, 1, 1)));
          elseif ($ct == 2)
            $trns = array(ord(substr($t, 1, 1)), ord(substr($t, 3, 1)), ord(substr($t, 5, 1)));
          else
          {
            $pos = strpos($t, chr(0));
            if ($pos !== false)
              $trns = array($pos);
          } //else
          fread($f, 4);
        } //elseif ($type == 'tRNS')
        elseif ($type == 'IDAT')
        {
          $data .= fread($f, $n);
          fread($f, 4);
        } //elseif ($type == 'IDAT')
        elseif ($type == 'IEND')
          break;
        else
          fread($f, $n + 4);
      } //do
      while ($n);
      if ($colspace == 'Indexed' and empty($pal))
        $this->Error('Missing palette in ' . $file);
      fclose($f);
      return array('w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'parms' => $parms, 'pal' => $pal, 'trns' => $trns, 'data' => $data);
    } //function _parsepng($file)
    function _freadint($f)
    {
      $a = unpack('Ni', fread($f, 4));
      return $a['i'];
    } //function _freadint($f)
    function _textstring($s)
    {
      return '(' . $this->_escape($s) . ')';
    } //function _textstring($s)
    function _escape($s)
    {
      return str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $s)));
    } //function _escape($s)
    function _putstream($s)
    {
      $this->_out('stream');
      $this->_out($s);
      $this->_out('endstream');
    } //function _putstream($s)
    function _out($s)
    {
      if ($this->state == 2)
        $this->pages[$this->page] .= $s . "\n";
      else
        $this->buffer .= $s . "\n";
    } //function _out($s)
  } //class FPDF
  if (isset($_SERVER['HTTP_USER_AGENT']) and $_SERVER['HTTP_USER_AGENT'] == 'contype')
  {
    header('Content-Type: application/pdf');
    exit;
  } //if (isset($_SERVER['HTTP_USER_AGENT']) and $_SERVER['HTTP_USER_AGENT'] == 'contype')
} //if (!class_exists('FPDF'))
?>
