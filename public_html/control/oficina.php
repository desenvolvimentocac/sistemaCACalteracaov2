<?php
/**
 * Created by PhpStorm.
 * User: filipe
 * Date: 29/04/18
 * Time: 15:24
 */

include_once '../model/DatabaseOpenHelper.php';
include_once 'constantes.php';
include_once 'mensagem.php';

class oficina{
    private $db;
    private $nome,$preRequisito;
    public function __construct(){
        $this->db = new Database();
    }

    private function getOficinaData(){
        $this->nome = isset($_POST['nome']) ? $_POST['nome'] : INVALIDO;
        $this->nome = ucwords($this->nome);//deixando maiscula
        $this->preRequisito = isset($_POST['pre_requisito']) ? $_POST['pre_requisito'] : INVALIDO;
    }

    public function setOficina(){
        $this->getOficinaData();
        $params = array($this->nome,$this->preRequisito);
        try {
            if($this->db->insert("nome,pre_requisito", "oficina", $params)){
                new mensagem(SUCESSO,$this->nome." cadastrado com sucesso.");
            }else{
                new mensagem(INSERT_ERRO,"Erro ao inserir: ".$this->nome);
            }

        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
        }
        $this->redireciona();
    }

    public function updateOficina($identificador){
        $this->getOficinaData();
        $columns = array("nome","pre_requisito");
        $params = array($this->nome,$this->preRequisito);
        try {
            if($this->db->update($columns,"oficina",$params,"id_oficina = ?",array($identificador))){
                new mensagem(SUCESSO,$this->nome." atualizado com sucesso.");
            }else{
                new mensagem(INSERT_ERRO,"Erro ao atualizar: ".$this->nome);
            }

        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
        }
        $this->redireciona();
    }

    public function getOficina(){
        $json = "";
        try {
            $json = $this->db->select("*", "oficina",null,null,"nome",ASC);
        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
        }
        return $json;
    }
    public function getOficinaById($identificador){
        $json = "";
        try {
            $json = $this->db->select("*", "oficina","id_oficina = ?",array($identificador));
        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
        }
        return $json;
    }

    private function redireciona(){
        //depois de inserir redirecionamos para a pagina de infra
        header("Location: ../index.php?pag=Oficinas");
    }
}