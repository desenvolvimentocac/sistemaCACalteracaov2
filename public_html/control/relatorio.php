<?php
/**
 * Created by Filipe
 * Date: 07/06/18
 * Time: 11:28
 */
include_once '../model/DatabaseOpenHelper.php';
class relatorio{
 private $db;

 public function __construct(){
     $this->db = new Database();
 }

    //o total de alunos é obtdo atraves da soma dos alunos por oficina no front-end diminuindo processamento
    /**
     * @param $tempoId
     * @return string
     * @throws Exception
     */
    public function getAlunosPorOficina($tempoId){
        $projection = "oficina.nome as oficina,turma.nome_turma as turma,count(aluno_turma.id_aluno) as alunos,".
            //lista de espera
            "(SELECT count(aluno_turma.id_aluno) from aluno_turma WHERE aluno_turma.turma_id = turma.id_turma and aluno_turma.lista_espera = 1) as espera";
        $table = "turma,aluno_turma,oficina";
        $whereClause = "turma.tempo_id = ? AND turma.id_turma = aluno_turma.turma_id and aluno_turma.lista_espera = 0 and aluno_turma.trancado = 0 and turma.oficina_id = oficina.id_oficina";
        $groupBy = " GROUP BY id_turma";
        return $this->db->select($projection,$table,$whereClause.$groupBy,array($tempoId));
    }

    /**
     * obtem o Historico de um aluno especifico
     * @return string
     * @throws Exception
     */
    public function getAlunosHistorico($nome){
        if (isset($nome) && strlen($nome) > 0) {
            $partes = explode(" ", $nome);
            $primeiroNome = '%' . $partes[0] . '%';
            $ultimoNome = "";
            if (sizeof($partes) > 1){
                unset($partes[0]);
                $ultimoNome = implode(" ", $partes);
            }
            $ultimoNome = '%' . $ultimoNome . '%';

            $projecao = "pessoa.id_pessoa,pessoa.nome,pessoa.sobrenome,oficina.nome as atividade,turma.nome_turma as turma,tempo.ano,tempo.periodo";
            $tabela = "pessoa,aluno_turma,turma,oficina,tempo";
            $whereClause = "
                        pessoa.excluido = 0 AND
                        pessoa.id_pessoa = aluno_turma.pessoa_id AND
                        aluno_turma.lista_espera = 0 AND
                        aluno_turma.trancado = 0 AND
                        aluno_turma.turma_id = turma.id_turma AND
                        turma.oficina_id = oficina.id_oficina AND
                        turma.tempo_id = tempo.id_tempo AND
                        turma.is_ativo = 1 AND
                        pessoa.nome LIKE ? AND
                        pessoa.sobrenome LIKE ?
        ";
            $whereArgs = array($primeiroNome, $ultimoNome);
            return $this->db->select($projecao, $tabela, $whereClause, $whereArgs);
        }
        return "";
    }

    /**
     * @throws Exception
     */
    public function getAlunosCadastrados(){
            //alunos da rural
            $ruralino = json_decode($this->db->select("count(*) as n","ruralino"));
            //crianças
            $crianca = json_decode($this->db->select("count(*) as n","menor_idade"));
            //total de cadastros
            $total = json_decode($this->db->select("count(*) as n","pessoa"));
            $json = new stdClass();
            $json->ruralino = $ruralino[0]->n;
            $json->crianca = $crianca[0]->n;
            $json->total = $total[0]->n;

            return json_encode($json,JSON_UNESCAPED_UNICODE);
    }
}