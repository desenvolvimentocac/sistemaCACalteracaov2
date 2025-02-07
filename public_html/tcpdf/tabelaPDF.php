<?php
// Include the main TCPDF library (search for installation path).
//require_once('tcpdf_include.php');
require_once('config/tcpdf_config_alt.php');
require_once '../control/constantes.php';
require_once 'tcpdf.php';

// Extend the TCPDF class to create custom Footer
class MYPDF extends TCPDF {
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Página  '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

class PDF{
	private $impressao,$dadosTurma;
	public function __construct($impressaoOBJ,$dadosObj){
	    $this->impressao = $impressaoOBJ;
	    $this->dadosTurma = $dadosObj;
	    $this->inicializa();
    }

    private function inicializa(){
        $pdfTitle = "Lista de Presenca ".$this->dadosTurma->oficina." ".$this->dadosTurma->turma." ".Ambiente::getSystemName();
        $orientation = 'L';//P = portrait L = landscape

        // create new PDF document
        $pdf = new MYPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('CAC System');
        $pdf->SetTitle($pdfTitle);
        $pdf->SetSubject($pdfTitle);
        $pdf->SetKeywords(Ambiente::getSystemName().', PDF, presença,'.Ambiente::getAtividadeName());

        $dataHoje = date('d/m/Y');
        //Cabeçalho
        $header_title = Ambiente::getInstituicaoName();
        $header_logo = "logo.jpg";
        $txt_header = Ambiente::getSystemNameExtenso()."\n".
            "Lista de presença da turma: {$this->dadosTurma->turma}, {$this->dadosTurma->oficina} / ".Ambiente::getCargoProf().": {$this->dadosTurma->professor}\n".
            "Emitido em: {$dataHoje}";

        $pdf->SetHeaderData($header_logo, PDF_HEADER_LOGO_WIDTH, $header_title, $txt_header, array(0,0,0), array(0,0,0));
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

        //margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 12);

        // add a page
        $pdf->AddPage();

        // create HTML content
        $html = "";
            //cabecalho tabela
            $html .= '<table cellspacing="0" cellpadding="1" border="1" align="center">
			<thead>
			    <tr bgcolor="#6f77cf">
					<th><font color="#ffffff">#</font></th>
					<th colspan="4"><font color="#ffffff">Nome:</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
					<th><font color="#ffffff">&nbsp;</font></th>
			    </tr>
			    </thead>';
            for($i=0;$i<sizeof($this->impressao);$i++){
                if(!isset($this->impressao[$i]->pos)) continue;
                if($i%2 == 0) $html .= "<tr bgcolor=\"#ebebe0\" align=\"center\" nobr=\"true\">";
                else $html .= "<tr align=\"center\" nobr=\"true\">";
                $html .="
					<td >".$this->impressao[$i]->pos."</td>
					<td colspan=\"4\">".ucwords($this->impressao[$i]->nome)."</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td> 
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
					<td >&nbsp;</td>
				</tr>";

            }
            $html .="</table>";
            // output the HTML content
            $pdf->writeHTML($html, true, false, false, false, '');
            // ---------------------------------------------------------
            //echo $html;//iprimindo teste

            // close and output PDF document colocar D para Download I para inline
            $pdf->Output($pdfTitle.'.pdf', 'D');
        }


}


//============================================================+
// END OF FILE
//============================================================+

?>
