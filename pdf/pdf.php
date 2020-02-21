<?php
/*******************************************************************************
** Software:  Based on FPDF example script "Table with MultiCells"
** Author:    Olivier Plathey
** Website:   http://www.fpdf.org/en/script/script3.php
*/
define('FPDF_FONTPATH', $site_info['pdf_path'] . 'font/');
include_once($site_info['pdf_path'] . 'fpdf.php');
class PDF extends FPDF
{
  var $widths;
  var $aligns;
  var $DefaultFontFamily;
  var $DefaultFontStyle;
  var $DefaultFontSizePt;
  var $HeaderRowData = array();
  function PDF($font = '', $style = '', $size = 0)
  {
    parent::FPDF();
    $this->SetDefaultFont($font, $style, $size);
    $this->AliasNbPages('{NUM_PAGES}');
  } //function PDF($font = '', $style = '', $size = 0)
  function SetDefaultFont($font, $style, $size)
  {
    $this->SetFont($font, $style, $size);
    $this->DefaultFontFamily = $this->FontFamily;
    $this->DefaultFontStyle = $this->FontStyle;
    $this->DefaultFontSizePt = $this->FontSizePt;
  } //function SetDefaultFont($font, $style, $size)
  function RestoreDefaultFont()
  {
    $this->SetFont($this->DefaultFontFamily, $this->DefaultFontStyle, $this->DefaultFontSizePt);
  } //function RestoreDefaultFont()
  function SetWidths($w)
  {
    $this->widths = $w;
  } //function SetWidths($w)
  function SetAligns($a)
  {
    $this->aligns = $a;
  } //function SetAligns($a)
  function Row($data, $align = '', $font = '', $style = '', $size = 0, $fill = 0)
  {
    $this->SetFont($font, $style, $size);
    $nb = 0;
    for ($i = 0; $i < count($data); $i++)
      $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
    $h = 5 * $nb;
    $this->CheckPageBreak($h);
    for ($i = 0; $i < count($data); $i++)
    {
      $w = $this->widths[$i];
      $a = strlen($align) ? $align : (isset($this->aligns[$i]) ? $this->aligns[$i] : 'L');
      $x = $this->GetX();
      $y = $this->GetY();
      $this->Rect($x, $y, $w, $h);
      $this->MultiCell($w, 5, $data[$i], 0, $a, $fill);
      $this->SetXY($x + $w, $y);
    } //for ($i = 0; $i < count($data); $i++)
    $this->Ln($h);
    $this->RestoreDefaultFont();
  } //function Row($data, $align = '', $font = '', $style = '', $size = 0, $fill = 0)
  function SetHeaderRowData($data = array())
  {
    if (is_array($data))
      $this->HeaderRowData = $data;
  } //function SetHeaderRowData($data = array())
  function HeaderRow($data = '')
  {
    if (!is_array($data))
      $data = $this->HeaderRowData;
    $this->Row($data, 'C', '', 'B', $this->FontSizePt + 2, 0);
  } //function HeaderRow($data = '')
  function Header()
  {
    $this->SetFont('', '', 8);
    $this->Cell(10, 10, $this->subject);
    $this->Cell(0, 10, date('d-M-Y g:i a'), 0, 0, 'R');
    $this->Ln();
    $this->RestoreDefaultFont();
  } //function Header()
  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('', '', 8);
    $this->Cell(0, 10, 'Page '.$this->PageNo().' of {NUM_PAGES}', 0, 0, 'C');
    $this->RestoreDefaultFont();
  } //function Footer()
  function Heading($text)
  {
    $this->SetFont('', 'B', 20);
    $this->Cell(0, 10, $text, 1, 0, 'C');
    $this->Ln(20);
    $this->RestoreDefaultFont();
  } //function Heading($text)
  function CheckPageBreak($h)
  {
    if ($this->GetY() + $h > $this->PageBreakTrigger)
    {
      $this->AddPage($this->CurOrientation);
      $this->HeaderRow();
    } //if ($this->GetY() + $h > $this->PageBreakTrigger)
  } //function CheckPageBreak($h)
  function NbLines($w, $txt)
  {
    $cw =& $this->CurrentFont['cw'];
    if ($w == 0)
      $w = $this->w - $this->rMargin - $this->x;
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $s = str_replace("\r", '', $txt);
    $nb = strlen($s);
    if ($nb > 0 and $s[$nb - 1] == "\n")
      $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while ($i < $nb)
    {
      $c = $s[$i];
      if ($c == "\n")
      {
        $i++;
        $sep = -1;
        $j = $i;
        $l = 0;
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
          if ($i == $j)
            $i++;
        } //if ($sep == -1)
        else
          $i = $sep + 1;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
      } //if ($l > $wmax)
      else
        $i++;
    } //while ($i < $nb)
    return $nl;
  } //function NbLines($w, $txt)
} //class PDF extends FPDF
?>
