<?php
/**
 * Created by Filipe
 * Date: 28/04/18
 * Time: 18:50
 */

include_once '../model/DatabaseOpenHelper.php';
include_once 'constantes.php';
include_once 'mensagem.php';

class infraestrutura{
    private $db,$nome,$localizacao,$ativo,$predioId;

    public function __construct(){
        $this->db = new Database();
    }

    private function getPredioData(){
        $nome = isset($_POST['nome']) ? $_POST['nome'] : INVALIDO;
        $this->nome = strtoupper($nome);//Deixando maiscula
        $localizacao = isset($_POST['localizacao']) ? $_POST['localizacao'] : INVALIDO;
        $this->localizacao = ucwords($localizacao);
        $this->ativo = isset($_POST['ativo']) ? $_POST['ativo'] : SIM;
    }

    public function setPredio(){
        $this->getPredioData();
        $params = array($this->nome,$this->localizacao);
        if($this->nome != INVALIDO){
            try {
                if($this->db->insert("nome,localizacao", "predio", $params)){
                    new mensagem(SUCESSO,"Predio: ".$this->nome." cadastrado com sucesso!!");
                }else{
                    new mensagem(INSERT_ERRO,"Erro ao inserir");
                }

            } catch (Exception $e) {
                new mensagem(ERRO,"Erro: ".$e);
            }
        }

        $this->redireciona();
    }

    public function updatePredio($identificador){
        $this->getPredioData();
        $params = array($this->nome,$this->localizacao,$this->ativo);

        if($this->nome != INVALIDO){
            try {
                $columns = array("nome","localizacao","is_ativo");
                if($this->db->update($columns,"predio",$params,"id_predio=?",array($identificador))){
                    new mensagem(SUCESSO," Predio: ".$this->nome." atualizado com sucesso!!");
                }else{
                    new mensagem(INSERT_ERRO,"Erro ao atualizar");
                }

            } catch (Exception $e) {
                new mensagem(ERRO,"Erro: ".$e);
            }
        }

        $this->redireciona();
    }

    private function getSalaData(){
        $this->predioId = isset($_POST['predio_id']) ? $_POST['predio_id'] : INVALIDO;;
        $nome = isset($_POST['nome']) ? $_POST['nome'] : INVALIDO;
        $this->nome = ucwords($nome);
        $this->ativo = isset($_POST['ativo']) ? $_POST['ativo'] : SIM;
    }

    public function setSala(){
        $this->getSalaData();
        $params = array($this->predioId,$this->nome,$this->ativo);
        if($this->nome != INVALIDO){
            try {
                if($this->db->insert("predio_id,nome,is_ativo", "sala", $params)){
                    new mensagem(SUCESSO," ".$this->nome." cadastrado com sucesso!!");
                }else{
                    new mensagem(INSERT_ERRO,"Erro ao inserir");
                }

            } catch (Exception $e) {
                new mensagem(ERRO,"Erro: ".$e);
            }
        }
        $this->redireciona();
    }

    public function updateSala($identificador){
        $this->getSalaData();
        $params = array($this->nome,$this->ativo);
        if($this->nome != INVALIDO){
            try {
                $columns = array("nome","is_ativo");
                if($this->db->update($columns,"sala",$params,"id_sala = ?",array($identificador))){
                    new mensagem(SUCESSO," ".$this->nome." atualizado com sucesso!!");
                }else{
                    new mensagem(INSERT_ERRO,"Erro ao atualizar");
                }

            } catch (Exception $e) {
                new mensagem(ERRO,"Erro: ".$e);
            }
        }
        $this->redireciona();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPredios(){
        return $this->db->select("id_predio,nome,localizacao,is_ativo", "predio");

    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSalas(){
        //retornamos as salas agrupadas por predio
        return $this->db->select("id_sala,predio.nome as predio,sala.nome as sala,sala.is_ativo", "predio,sala", "id_predio = predio_id", null, "predio_id");
    }

    /**
     * @param $identificador Integer
     * @return string
     * @throws Exception
     */
    public function getPredioById($identificador){
        return $this->db->select("nome,localizacao,is_ativo","predio","id_predio = ?",array($identificador));
    }
    /**
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getSalaById($identificador){
        //retornamos uma sala especifica
        return $this->db->select("id_sala,nome,is_ativo","sala","id_sala = ?",array($identificador));
    }
    /**
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getSalaByPredioId($identificador){

        //retornamos as salas de um predio especifico
        return $this->db->select("id_sala,nome","sala","predio_id = ? and is_ativo = 1",array($identificador));
    }

    private function redireciona(){
        //depois de inserir redirecionamos para a pagina de infra
        header("Location: ../index.php?pag=Infraestrutura");
    }
}