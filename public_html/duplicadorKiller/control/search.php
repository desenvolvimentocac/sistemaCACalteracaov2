<?php
if ( session_status() !== PHP_SESSION_ACTIVE ) {
    session_start();
}

/** @noinspection PhpIncludeInspection */
include_once '../model/DatabaseOpenHelper.php';

class search{
    private $db;

    function __construct(){
        $this->db = new Database();
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getDuplicados(){
        $clausulaWhere = "numero_documento IN (SELECT B.numero_documento FROM documento B GROUP BY B.numero_documento HAVING COUNT(*) > 1)";
        return $this->db->select('pessoa_id,numero_documento',"documento",$clausulaWhere,null,"numero_documento");
    }

    /**
     * @param $pessoaId
     * @return string
     * @throws Exception
     */
    private function estaEmTurma($pessoaId){
        $numTurmas = json_decode($this->db->select("count(id_aluno) as n","aluno_turma","pessoa_id = ?",array($pessoaId)));
        $numTurmas = $numTurmas[0]->n;
        return $numTurmas;
    }

    /**
     * @param $pessoaId
     * @return string
     * @throws Exception
     */
    private function getNome($pessoaId){
        $pessoa = json_decode($this->db->select("nome,sobrenome","pessoa","id_pessoa = ?",array($pessoaId)));
        $pessoa = $pessoa[0]->nome." ".$pessoa[0]->sobrenome;
        return $pessoa;
    }

    /**
     * @param $pessoaID
     * @return integer
     * @throws Exception
     */
    private function temLogin($pessoaID){
        $login = json_decode($this->db->select("count(*) as n","login","pessoa_id = ?",array($pessoaID)));
        return $login[0]->n;
    }

    private function traduzBool($bol){
        if($bol > 0) return "sim";
        return "não";
    }

    /**
     * @throws Exception
     */
    public function getDataFromDuplicados(){
        $duplic = json_decode($this->getDuplicados());
        $stFmt = array();
        $pessoaId = 0;
        $tam = sizeof($duplic);
        for($i=0;$i<$tam;$i++){
            $pessoaId = $duplic[$i]->pessoa_id;
            $stFmt[$i] = "<tr>
                        <td> {$pessoaId} </td>
                        <td> {$duplic[$i]->numero_documento} </td>
                        <td> {$this->getNome($pessoaId)} </td>
                        <td> {$this->traduzBool( $this->estaEmTurma($pessoaId))} </td>
                        <td> {$this->traduzBool($this->temLogin($pessoaId))} </td>";
            //aqui desmarcamos se vc possui um idenficador que e sequencia de outro mas nao a sequencia 2 vezes
            //isso serve para um usuario que nao se cadastrou em nenhuma oficina mas tem varias contas
            //sem isso ele fica sem cadastro algum
            if($this->estaEmTurma($pessoaId) > 0)
                $stFmt[$i] .= "<td><input type=\"checkbox\" name=\"pessoa_id[]\" value=\"{$pessoaId}\" disabled> </td>";
            else if($this->temLogin($pessoaId) != 0)
                $stFmt[$i] .= "<td><input type=\"checkbox\" name=\"pessoa_id[]\" value=\"{$pessoaId}\"> </td>";
            else
                $stFmt[$i] .= "<td><input type=\"checkbox\" name=\"pessoa_id[]\" value=\"{$pessoaId}\" checked='checked'> </td>";

            $stFmt[$i] .= "</tr>";
        }
        return array_values($stFmt);
    }
}
?>