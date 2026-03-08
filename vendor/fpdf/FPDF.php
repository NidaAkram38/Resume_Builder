<?php
namespace FPDF;

define('FPDF_VERSION', '1.86');

class FPDF
{
    public $page = 0;
    public $n = 2;
    public $offsets = array();
    public $buffer = '';
    public $pages = array();
    public $state = 0;
    public $compress = false;
    public $k = 2.834645669;
    public $DefPageFormat = 'A4';
    public $CurPageFormat;
    public $h = 297;
    public $w = 210;
    public $wPt = 595.28;
    public $hPt = 841.89;
    public $l = 10;
    public $t = 10;
    public $b = 10;
    public $r = 10;
    public $AutoPageBreak = false;
    public $PageBreakTrigger = 0;
    public $x = 0;
    public $y = 0;
    public $lasth = 0;
    public $LineWidth = 0.2;
    public $fontpath = '';
    public $CoreFonts = array();
    public $fonts = array();
    public $FontFamily = '';
    public $FontStyle = '';
    public $FontSizePt = 12;
    public $FontSize = 0;
    public $DrawColor = '';
    public $FillColor = '';
    public $TextColor = '';
    public $ColorFlag = false;
    public $ws = 0;
    public $underline = false;
    public $in_header = false;
    public $in_footer = false;
    public $alias_nb_pages = '';
    public $CurrentFont = array();
    public $image_scale = 96;
    public $images = array();
    public $links = array();
    public $link_page_height = 28.35;

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->state = 0;
        $this->CoreFonts = array(
            'courier' => true, 'helvetica' => true, 'times' => true,
            'symbol' => true, 'zapfdingbats' => true
        );
        $this->fontpath = __DIR__ . '/font/';
        if(!is_dir($this->fontpath)) {
            $this->fontpath = '';
        }
        $this->fonts['helvetica'] = array('i'=>false, 'b'=>false, 'u'=>false);
        $this->fonts['courier'] = array('i'=>false, 'b'=>false, 'u'=>false);
        $this->fonts['times'] = array('i'=>false, 'b'=>false, 'u'=>false);
        $this->_setorientation($orientation);
        $this->SetUnit($unit);
        $this->SetPageFormat($size);
        $this->state = 1;
        $this->SetDrawColor(0);
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetFont('Helvetica', '', 12);
        $this->SetLineWidth(0.2);
        $this->x = $this->l;
        $this->y = $this->t;
        $this->lasth = 0;
    }

    private function _setorientation($orientation)
    {
        $orientation = strtoupper($orientation);
        if($orientation == 'P')
        {
            $this->DefPageFormat = 'P';
            $this->wPt = 595.28;
            $this->hPt = 841.89;
        }
        elseif($orientation == 'L')
        {
            $this->DefPageFormat = 'L';
            $this->wPt = 841.89;
            $this->hPt = 595.28;
        }
        else
            $this->_error('Incorrect orientation: '.$orientation);
        $this->CurPageFormat = $this->DefPageFormat;
    }

    public function SetUnit($unit)
    {
        $unit = strtolower($unit);
        if($unit == 'pt')
            $this->k = 1;
        elseif($unit == 'mm')
            $this->k = 2.834645669;
        elseif($unit == 'cm')
            $this->k = 28.34645669;
        elseif($unit == 'in')
            $this->k = 72;
        else
            $this->_error('Incorrect unit: '.$unit);
    }

    private function SetPageFormat($format)
    {
        $format = strtoupper($format);
        if($format == 'A3')
            list($this->wPt, $this->hPt) = array(1190.55, 1683.78);
        elseif($format == 'A4')
            list($this->wPt, $this->hPt) = array(595.28, 841.89);
        elseif($format == 'A5')
            list($this->wPt, $this->hPt) = array(419.53, 595.28);
        elseif($format == 'LETTER')
            list($this->wPt, $this->hPt) = array(612, 792);
        elseif($format == 'LEGAL')
            list($this->wPt, $this->hPt) = array(612, 1008);
        else
            $this->_error('Unknown page format: '.$format);

        if($this->CurPageFormat == 'L')
            list($this->wPt, $this->hPt) = array($this->hPt, $this->wPt);
        $this->w = $this->wPt / $this->k;
        $this->h = $this->hPt / $this->k;
        $this->PageBreakTrigger = $this->h - $this->b;
    }

    public function AddPage($orientation='')
    {
        if($this->state == 0)
            $this->_error('No page has been added yet. Use AddPage()');
        $family = $this->FontFamily;
        $style = $this->FontStyle;
        $size = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
        if($this->page > 0)
            $this->_endpage();
        $this->_beginpage($orientation);
        $this->_out('2 J');
        if($lw != $this->LineWidth) {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2f w', $lw * $this->k));
        }
        if($family)
            $this->SetFont($family, $style, $size);
        if($dc != $this->DrawColor) {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if($fc != $this->FillColor) {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        if($tc != $this->TextColor) {
            $this->TextColor = $tc;
            $this->_out($tc);
        }
        $this->ColorFlag = $cf;
    }

    private function _beginpage($orientation)
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->l;
        $this->y = $this->t;
        $this->FontFamily = '';
        if($orientation == '')
            $orientation = $this->DefPageFormat;
        else {
            $orientation = strtoupper($orientation);
            if($orientation != $this->CurPageFormat) {
                if($orientation == 'P')
                {
                    $this->wPt = 595.28;
                    $this->hPt = 841.89;
                    $this->w = $this->wPt / $this->k;
                    $this->h = $this->hPt / $this->k;
                }
                elseif($orientation == 'L')
                {
                    $this->wPt = 841.89;
                    $this->hPt = 595.28;
                    $this->w = $this->wPt / $this->k;
                    $this->h = $this->hPt / $this->k;
                }
                else
                    $this->_error('Incorrect orientation: '.$orientation);
                $this->CurPageFormat = $orientation;
                $this->PageBreakTrigger = $this->h - $this->b;
            }
        }
        $this->_out(sprintf('%.2f %.2f %.2f %.2f re W n', 0, 0, $this->wPt, $this->hPt));
    }

    private function _endpage()
    {
        $this->state = 1;
    }

    public function SetFont($family, $style='', $size=0)
    {
        $family = strtolower($family);
        if($family == '')
            $family = $this->FontFamily;
        if($family == 'arial')
            $family = 'helvetica';
        $style = strtoupper($style);
        if(strpos($style, 'U') !== false) {
            $this->underline = true;
            $style = str_replace('U', '', $style);
        } else
            $this->underline = false;
        if($style == 'IB')
            $style = 'BI';
        if($size == 0)
            $size = $this->FontSizePt;
        if($family == $this->FontFamily && $style == $this->FontStyle && $size == $this->FontSizePt)
            return;
        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey])) {
            if(isset($this->CoreFonts[$family]))
                $this->fonts[$fontkey] = array('type'=>'core', 'name'=>$this->_getfontname($family, $style));
            else
                $this->_error('Undefined font: '.$family.' '.$style);
        }
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $this->CurrentFont = $this->fonts[$fontkey];
        if($this->state == 2)
            $this->_out(sprintf('BT /F%d %.2f Tf ET', sizeof($this->fonts), $this->FontSize * $this->k));
    }

    private function _getfontname($family, $style)
    {
        if($family == 'courier')
            return 'Courier'.($style ? '-'.strtoupper($style) : '');
        elseif($family == 'helvetica')
            return 'Helvetica'.($style ? '-'.strtoupper($style) : '');
        elseif($family == 'times')
            return 'Times-'.($style == 'I' ? 'Italic' : ($style == 'B' ? 'Bold' : ($style == 'BI' ? 'BoldItalic' : 'Roman')));
        else
            return 'Helvetica';
    }

    public function SetFontSize($size)
    {
        if($this->FontSizePt == $size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        if($this->state == 2)
            $this->_out(sprintf('BT /F%d %.2f Tf ET', sizeof($this->fonts), $this->FontSize * $this->k));
    }

    public function SetLineWidth($width)
    {
        $this->LineWidth = $width;
        if($this->state == 2)
            $this->_out(sprintf('%.2f w', $width * $this->k));
    }

    public function SetDrawColor($r, $g=null, $b=null)
    {
        if(($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->DrawColor = sprintf('%.3f G', $r/255);
        else
            $this->DrawColor = sprintf('%.3f %.3f %.3f RG', $r/255, $g/255, $b/255);
        if($this->state == 2)
            $this->_out($this->DrawColor);
    }

    public function SetFillColor($r, $g=null, $b=null)
    {
        if(($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->FillColor = sprintf('%.3f g', $r/255);
        else
            $this->FillColor = sprintf('%.3f %.3f %.3f rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
        if($this->state == 2)
            $this->_out($this->FillColor);
    }

    public function SetTextColor($r, $g=null, $b=null)
    {
        if(($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->TextColor = sprintf('%.3f g', $r/255);
        else
            $this->TextColor = sprintf('%.3f %.3f %.3f rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
        if($this->state == 2)
            $this->_out($this->TextColor);
    }

    public function GetStringWidth($s)
    {
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        if(!$cw) {
            $cw = array();
            for($i = 0; $i <= 255; $i++)
                $cw[$i] = 600;
        }
        $w = 0;
        $l = strlen($s);
        for($i = 0; $i < $l; $i++)
            $w += $cw[ord($s[$i])];
        return $w * $this->FontSize / 1000;
    }

    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $k = $this->k;
        if($this->y + $h > $this->PageBreakTrigger && !$this->in_header && !$this->in_footer && $this->AcceptPageBreak())
        {
            $x = $this->x;
            $ws = $this->ws;
            if($ws > 0)
            {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurPageFormat);
            $this->x = $x;
            if($ws > 0)
            {
                $this->ws = $ws;
                $this->_out(sprintf('%.3f Tw', $ws * $k));
            }
        }
        if($w == 0)
            $w = $this->w - $this->r - $this->x;
        $s = '';
        if($fill || $border == 1)
        {
            if($fill)
                $op = ($border == 1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        if(is_string($border))
        {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border, 'L'))
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
            if(strpos($border, 'T'))
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
            if(strpos($border, 'R'))
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
            if(strpos($border, 'B'))
                $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        }
        if($txt !== '')
        {
            if($align == 'R')
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            elseif($align == 'C')
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            else
                $dx = $this->cMargin;
            if($this->ColorFlag)
                $s .= 'q '.$this->TextColor.' ';
            $s .= sprintf('BT %.2f %.2f Td (%s) Tj ET', ($this->x+$dx)*$k, ($this->h-$this->y-0.5*$h+0.3*$this->FontSize)*$k, $this->_escape($txt));
            if($this->underline)
                $s .= ' '.$this->_dounderline($this->x+$dx, $this->y+0.5*$h+0.3*$this->FontSize, $txt);
            if($this->ColorFlag)
                $s .= ' Q';
            if($link)
                $this->Cell($w, $h, '', 0, 0, '', false, $link);
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln > 0)
        {
            $this->y += $h;
            if($ln > 1)
                $this->x = $this->l;
            else
                $this->x += $w;
        }
        else
            $this->x += $w;
    }

    public function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        if(!isset($this->CurrentFont))
            $this->_error('No font has been set');
        $cw = &$this->CurrentFont['cw'];
        if($w == 0)
            $w = $this->w - $this->r - $this->x;
        $wmax = ($w - 2*$this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $b = (is_string($border)) ? $border : ($border ? '1' : '');
        $b2 = (is_string($border)) ? '' : ($border ? '1' : '');
        $first = 1;
        while($nb > 0)
        {
            $l = 1;
            $ns = 0;
            $nl = 0;
            while($l < $nb)
            {
                $c = $s[$l];
                if($c == "\n")
                {
                    $nl = 1;
                    break;
                }
                if($c == ' ')
                    $ns = $l;
                if(!isset($cw[ord($c)]))
                    $c = '?';
                $l += $cw[ord($c)];
                if($l > $wmax)
                    break;
                $l++;
            }
            if($c == "\n")
                $l = $l;
            else
            {
                if($l == $nb)
                    $l++;
                elseif($ns == 0)
                    $l++;
                else
                    $l = $ns + 1;
            }
            if($nl)
                $l--;
            $s_cut = substr($s, 0, $l);
            $s = substr($s, $l);
            $nb -= $l;
            if($c == "\n")
                $nl = 1;
            $border_to_use = (($first) ? $b : $b2);
            $this->Cell($w, $h, $s_cut, $border_to_use, 1, $align, $fill);
            if($nl)
                $this->SetX($this->l);
            $first = 0;
        }
    }

    public function Ln($h=null)
    {
        $this->x = $this->l;
        if(is_string($h))
            $this->y += $this->lasth;
        else
            $this->y += $h ?? $this->FontSize;
    }

    public function Output($name='', $dest='')
    {
        if($this->state < 3)
            $this->Close();
        $pdf = $this->_endcode();
        if($dest == '')
        {
            if($name == '')
                $name = 'doc.pdf';
            $dest = 'I';
        }
        if($dest == 'I')
        {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="'.$name.'"');
            echo $pdf;
        }
        elseif($dest == 'D')
        {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$name.'"');
            echo $pdf;
        }
        elseif($dest == 'F')
            file_put_contents($name, $pdf);
        elseif($dest == 'S')
            return $pdf;
        return '';
    }

    public function Close()
    {
        if($this->state == 3)
            return;
        if($this->page > 0)
            $this->_endpage();
        $this->state = 3;
    }

    private function _endcode()
    {
        $this->_putpages();
        $this->_putfonts();
        $this->_putresources();
        $loc = strlen($this->buffer);
        $this->_out('startxref');
        $this->_out($loc);
        $this->_out('%%EOF');
        $this->state = 0;
        return $this->buffer;
    }

    private function _putpages()
    {
        $nb = $this->page;
        $filter = $this->compress ? '/Filter /FlateDecode ' : '';
        for($n = 1; $n <= $nb; $n++)
        {
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            $this->_out(sprintf('/Resources <</Font <</F1 %d 0 R>> >> /MediaBox [0 0 %.2f %.2f]', $this->_getobj_idx(sprintf('F%d', sizeof($this->fonts))), $this->wPt, $this->hPt));
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            $p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
            $this->_newobj();
            $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }

        $this->offsets[1] = strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Catalog /Pages 2 0 R>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<</Type /Pages /Kids [');
        for($i = 1; $i <= $nb; $i++)
            $this->_out(($this->n - $nb * 2 + 2 * $i - 1).' 0 R');
        $this->_out('] /Count '.$nb.'>>');
        $this->_out('endobj');
    }

    private function _putfonts()
    {
        foreach($this->fonts as $font)
        {
            if($font['type'] == 'core')
            {
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /'.$font['name']);
                $this->_out('/Encoding /WinAnsiEncoding');
                $this->_out('>>');
                $this->_out('endobj');
            }
        }
    }

    private function _putresources()
    {
        $this->_putfonts();
    }

    private function _newobj()
    {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_out($this->n.' 0 obj');
    }

    private function _out($s)
    {
        if($this->state == 2)
            $this->pages[$this->page] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }

    private function _putstream($s)
    {
        $this->_out('stream');
        $this->_out($s);
        $this->_out('endstream');
    }

    private function _escape($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        return '('.$s.')';
    }

    private function _dounderline($x, $y, $txt)
    {
        $up = $this->CurrentFont['up'] ?? -100;
        $ut = $this->CurrentFont['ut'] ?? 50;
        $w = $this->GetStringWidth($txt);
        return sprintf('%.2f %.2f %.2f %.2f re f', $x*$this->k, ($this->h-($y+$ut/1000*$this->FontSize))*$this->k, $w*$this->k, -$ut/1000*$this->FontSize*$this->k);
    }

    private function _getobj_idx($name)
    {
        return 0;
    }

    public function AcceptPageBreak()
    {
        return $this->AutoPageBreak;
    }

    private function _error($msg)
    {
        error_log('FPDF error: '.$msg);
    }

    public $cMargin = 2.5;
}
?>
