<?php
/**
 * Classe para gerenciar avaliações de oficinas
 */
include_once '../model/DatabaseOpenHelper.php';
include_once 'constantes.php';
include_once 'mensagem.php';

class avaliacao {
    private $db;
    private $nota, $comentario, $id_pessoa, $id_oficina;

    public function __construct() {
        $this->db = new Database();
    }

    private function getAvaliacaoData() {
        $this->nota = isset($_POST['nota']) ? $_POST['nota'] : INVALIDO;
        $this->comentario = isset($_POST['comentario']) ? $_POST['comentario'] : "";
        $this->id_pessoa = isset($_POST['id_pessoa']) ? $_POST['id_pessoa'] : INVALIDO;
        $this->id_oficina = isset($_POST['id_oficina']) ? $_POST['id_oficina'] : INVALIDO;
    }

 public function setAvaliacao() {
    $this->getAvaliacaoData();

    $params = array($this->id_pessoa, $this->id_oficina, $this->nota, $this->comentario);
    
    try {
        if ($this->db->insert("id_pessoa,id_oficina,nota,comentario", "avaliacao", $params)) {
            return ["success" => "Avaliação registrada com sucesso."];
        } else {
            return ["error" => "Erro ao registrar avaliação."];
        }
    } catch (Exception $e) {
        return ["error" => "Erro: " . $e->getMessage()];
    }
}


public function getAvaliacoesByProfessor($id_professor) {
    $projection = 
        "DISTINCT avaliacao.id_avaliacao,
         avaliacao.nota,
         avaliacao.comentario,
         avaliacao.data_avaliacao,
         oficina.nome as nome_oficina";
        
    $table = "avaliacao";
    $joins = " INNER JOIN oficina ON oficina.id_oficina = avaliacao.id_oficina" .
             " INNER JOIN turma ON turma.oficina_id = oficina.id_oficina";
    
    $whereClause = "turma.professor = ? AND turma.is_ativo = 1";
    $whereArgs = array($id_professor);
    
    try {
        return $this->db->select($projection, $table . $joins, $whereClause, $whereArgs);
    } catch (Exception $e) {
        error_log("Erro em getAvaliacoesByProfessor: " . $e->getMessage());
        return json_encode(array());
    }
}
    private function redireciona() {
        header("Location: ../index.php?pag=Avaliacoes");
    }
}