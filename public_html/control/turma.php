<?php
/**
 * Created by Filipe
 * Date: 03/05/18
 * Time: 17:02
 */

include_once '../model/DatabaseOpenHelper.php';
include_once 'constantes.php';
include_once 'mensagem.php';

class turma{
    private $db;
    private $oficina,$hinic,$hfim,$vagas,$prof,$sala,$turmaId;
    private $seg,$ter,$qua,$qui,$sex;

    public function __construct(){
        $this->db = new Database();
    }

    /**
     * @throws Exception
     */
    public function setPeriodo(){
        $tempoAtual = self::getTempoStatic($this->db);
        $ano = isset($_GET['ano']) ? $_GET['ano'] : INVALIDO;
        $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : INVALIDO;

        if($ano > $tempoAtual->ano){//Ano setado e maior que ano atual - ok
            $this->avancaPeriodo($ano,$periodo);
        }elseif ($periodo > $tempoAtual->periodo){//Periodo maior que periodo atual - ok
            $this->avancaPeriodo($ano,$periodo);
        }else{
            echo "Retrocedendo periodo =( isso não pode.";
        }
        $this->redireciona();
    }

    /**
     * @param $ano
     * @param $periodo
     * @throws Exception
     */
    private function avancaPeriodo($ano, $periodo){
        if($this->db->insert('ano,periodo','tempo',array($ano,$periodo))){
            new mensagem(SUCESSO,"Uhulll Avançamos mais um periodo");
        }else{
            new mensagem(INSERT_ERRO,"Não foi possivel avançar o periodo");
        }
    }

    /**
     * Essa envia o periodo atual como Objeto PHP
     * @param Database $db
     * @return object
     * @throws Exception
     */
    public static function getTempoStatic(Database $db){
        $tempo = json_decode($db->select("id_tempo,ano,periodo","tempo"));
        $tempo = $tempo[sizeof($tempo)-1];//tempo ultimo adicionado
        return $tempo;
    }

    /**
     * Essa envia somente o periodo atual em JSON
     * @return string JSON
     * @throws Exception
     */
    public function getTempo(){
        return json_encode(self::getTempoStatic($this->db),JSON_UNESCAPED_UNICODE);
    }

    /**
     * Essa funcao serve para enviar todos os periodos anteriores
     * @return string
     * @throws Exception
     */
    public function getTempoHistorico(){
        return $this->db->select("id_tempo,ano,periodo","tempo");
    }

    /**
     * @throws Exception
     */
    public function setTempo(){
        $ano = isset($_POST['ano']) ? $_POST['ano'] : INVALIDO;
        $periodo = isset($_POST['periodo']) ? $_POST['perido'] : INVALIDO;
        $columns = ("ano,periodo");
        $params = array($ano,$periodo);

        if($this->db->insert($columns,"tempo",$params)){
            new mensagem(SUCESSO,"Novo período criado");
        }else{
            new mensagem(INSERT_ERRO,"Erro ao inserir novo periodo");
        }

    }

    /**
     * @throws Exception
     */
    public function setTurma(){
        $this->getCommonData();
        //verificando disponibilidade
        if($this->temConflitoDeHorario()){
            new mensagem(ERRO,"<h3>Conflito de Horários com outra turma!!</h3>");
            header("Location: " . $_SERVER['HTTP_REFERER'] . "");
            return;
        }
        //inserindo turma
        $this->insertTurma();
        $this->turmaId = $this->db->getLastId();
        //recuperando os horarios definidos
        $this->insertHorario();
        //redirecionando para pagina inicial
        $this->redireciona();
    }

    /**
     * @param bool $updateId
     * @return boolean
     * @throws Exception
     */
    public function temConflitoDeHorario($updateId = false){
        $inicio = strtotime($this->hinic);
        $fim = strtotime($this->hfim);

        $tempo = $this->getTempoStatic($this->db);

        $projecao = "segunda,terca,quarta,quinta,sexta";
        $table = "turma,horario_turma_sala";
        $whereClause = "id_turma = turma_id and is_ativo = 1 and tempo_id = ?".
            "and sala_id=? and ((inicio BETWEEN ? and ?) or (fim BETWEEN ? and ?))";
        if($updateId != false) $whereClause .="and id_turma <> ".$updateId;//se for update excluimos o horario da atividade atual

        $whereArgs = array(
            $tempo->id_tempo,
            $this->sala,
            date('H:i:s',$inicio),date('H:i:s',$fim-60),// H maiusculo para hora formato 24h
            date('H:i:s',$inicio+60),date('H:i:s',$fim));
        $possiveisConflitos = json_decode($this->db->select($projecao,$table,$whereClause,$whereArgs),JSON_UNESCAPED_UNICODE);
        foreach ($possiveisConflitos as $conflito){//verifica em todas as tuplas do mesmo horario se o dia tambem coincide
            if($conflito['segunda'] && $this->seg) return true;
            if($conflito['terca'] && $this->ter) return true;
            if($conflito['quarta'] && $this->qua == 1) return true;
            if($conflito['quinta'] && $this->qui == 1) return true;
            if($conflito['sexta'] && $this->sex == 1) return true;
        }
        return false;//se nao encontrou nenhum dia conflitante entao conflito é falso
    }

    /**
     * CommonData são os dados que são utilizados no insert e no update
     */
    private function getCommonData(){
        $this->oficina = $_POST['oficina_id'];
        $this->prof = $_POST['prof_id'];
        $this->vagas = $_POST['vagas'];
        $this->hinic = $_POST['horario_inic'];
        $this->hfim = $_POST['horario_fim'];

        $this->sala = isset($_POST['sala_id']) ? $_POST['sala_id'] : INVALIDO;
        //horario
        $this->seg = isset($_POST['seg']) ? 1 : 0;
        $this->ter = isset($_POST['ter']) ? 1 : 0;
        $this->qua = isset($_POST['qua']) ? 1 : 0;
        $this->qui = isset($_POST['qui']) ? 1 : 0;
        $this->sex = isset($_POST['sex']) ? 1 : 0;
    }

    /**
     * Popula a Tabela de Turmas
     * @throws Exception
     */
    private function insertTurma(){
        $date = date('Y-m-d');
        //-------------------criacao do nome----------------------------------------------------------------------------
        $tumasCadastradas = json_decode($this->getTurmaByOficinaId($this->oficina));
        $numTurma = sizeof($tumasCadastradas)+1;//aqui setamos um numero para a turma de acordo com as tumas ja cadastradas para uma determinada oficina
        //$nomeOficina = strtoupper((json_decode($this->getOficinaById($this->oficina)))[0]->nome);

        $nome_turma = "T0".$numTurma."-".date('Y');//nome automatico da turma
        //-------------buscando o ultimo registro de tempo--------------------------------------------
        $tempo = $this->getTempoStatic($this->db);
        //--------------------------------------------------------------------------------------------------------------
        $criacao_turma = $date."";
        $is_ativo = SIM;
        $columns = "criacao_turma,oficina_id,num_vagas,nome_turma,professor,is_ativo,tempo_id";
        $params = array($criacao_turma,$this->oficina,$this->vagas,$nome_turma,$this->prof,$is_ativo,$tempo->id_tempo);
        try {
            if($this->db->insert($columns, "turma", $params)){
                new mensagem(SUCESSO,"Turma cadastrada com sucesso em: ".$tempo->ano." - ".$tempo->periodo);
            }
        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
        }
    }

    /**
     * Popula a tabela horario_turma_sala
     * @throws Exception
     */
    private function insertHorario(){
        if ($this->sala != INVALIDO) {
            $ano = date('Y') . "";
            $columns = "ano,sala_id,segunda,terca,quarta,quinta,sexta,inicio,fim,turma_id";
            $params = array($ano, $this->sala, $this->seg, $this->ter, $this->qua, $this->qui, $this->sex, $this->hinic, $this->hfim, $this->turmaId);
            if ($this->db->insert($columns, "horario_turma_sala", $params)) {
            } else {
                new mensagem(INSERT_ERRO, "Erro ao cadastrar");
            }
        }
    }


    /**
     * @param $oficinaId Integer
     * @return string
     * @throws Exception
     */
    private function getTurmaByOficinaId($oficinaId){
        $projection = "id_turma,oficina_id,num_vagas,nome_turma,professor,is_ativo";
        return $this->db->select($projection,"turma","oficina_id = ? and is_ativo = ?",array($oficinaId,1));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTurmasAtivas(){
        $projection =
            "id_turma,predio.nome as predio,criacao_turma as criacao,oficina.nome as oficina,oficina.pre_requisito,num_vagas as vagas,nome_turma as turma,".
            //selecionando vagas disponiveis
            "(SELECT count(*) as n from aluno_turma where aluno_turma.turma_id = id_turma and lista_espera = 0 and trancado = 0) as ocupadas,".
            "pessoa.nome as professor,sala.nome as sala,segunda,terca,quarta,quinta,sexta,TIME_FORMAT(inicio, '%H:%ih') AS inicio,TIME_FORMAT(fim, '%H:%ih') AS fim";
        $table ="(pessoa,oficina,turma,sala,predio)";
        $joinClause = " LEFT JOIN horario_turma_sala ON id_turma = turma_id";

        if(isset($_SESSION['NIVEL']) and $_SESSION['NIVEL'] == PROFESSOR){
            $whereClause = "professor=id_pessoa and id_oficina=oficina_id and sala_id = id_sala and predio_id = id_predio and turma.is_ativo = ? and turma.tempo_id = ? and professor =".$_SESSION['ID'];
        }else{
            $whereClause = "professor=id_pessoa and id_oficina=oficina_id and sala_id = id_sala and predio_id = id_predio and turma.is_ativo = ? and turma.tempo_id = ?";
        }
        $tempo = self::getTempoStatic($this->db);
        $whereArgs = array(SIM,$tempo->id_tempo);//Ativo = Sim,tempo atual
        return $this->db->select($projection,$table.$joinClause , $whereClause,$whereArgs,"oficina.nome ASC,nome_turma",ASC);
    }

    /**
     * @param $tempoId
     * @return string
     * @throws Exception
     */
    public function getTurmas($tempoId){
        //aqui mostramos todas as turmas ativas ou nao
        $projection = "turma.is_ativo,oficina.nome as oficina,segunda,terca,quarta,quinta,sexta,TIME_FORMAT(inicio, '%H:%ih') AS inicio,TIME_FORMAT(fim, '%H:%ih') AS fim,sala.nome as sala,pessoa.nome as professor,turma.num_vagas as vagas,turma.id_turma,turma.nome_turma as turma";
        $table ="oficina,horario_turma_sala,sala,pessoa,turma";

        if($_SESSION['NIVEL'] == ADMINISTRADOR){
            $whereClause = "turma.oficina_id = oficina.id_oficina and turma.professor = pessoa.id_pessoa and turma.id_turma = horario_turma_sala.turma_id AND horario_turma_sala.sala_id = sala.id_sala AND turma.tempo_id = ?";
        }else{
            $whereClause = "turma.oficina_id = oficina.id_oficina and turma.professor = pessoa.id_pessoa and turma.id_turma = horario_turma_sala.turma_id AND horario_turma_sala.sala_id = sala.id_sala AND turma.tempo_id = ? and turma.professor = ".$_SESSION['ID'];
        }
        return $this->db->select($projection,$table, $whereClause,array($tempoId),"oficina.nome",ASC);
    }

    /**
     * Aqui recuperamos as turmas alocadas em Determinada Sala
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getHorariosBySalaId($identificador){
        //buscando periodo atual
        $tempo = self::getTempoStatic($this->db);
        $projection = "oficina.nome as oficina,segunda,terca,quarta,quinta,sexta,TIME_FORMAT(inicio, '%H:%ih') AS inicio,TIME_FORMAT(fim, '%H:%ih') AS fim,turma.nome_turma as turma";
        try {
            return $this->db->select($projection, "horario_turma_sala,turma,oficina", "sala_id = ? and turma_id = id_turma and oficina_id=id_oficina and  turma.is_ativo = 1 and turma.tempo_id = ?", array($identificador,$tempo->id_tempo),"inicio");
        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
            return "";
        }
    }


    /**
     * @param $turmaId
     * @return string
     * @throws Exception
     */
    public function getTurmaById($turmaId){
        $columns =
            "turma.num_vagas,turma.nome_turma,turma.professor,turma.is_ativo,turma.oficina_id,"./* turma */
            //selecionando vagas disponiveis
            "(SELECT count(*) as n from aluno_turma where aluno_turma.turma_id = id_turma and lista_espera = 0 and trancado = 0) as ocupadas,".
            "ano,segunda,terca,quarta,quinta,sexta,inicio,fim,"./* horario_turma_sala */
            "horario_turma_sala.sala_id,"./* sala */
            "sala.predio_id,"./* Predio */
            "oficina.nome as oficina,oficina.pre_requisito as requisito";/* Oficina */
        $whereClause =
            "turma.id_turma = horario_turma_sala.turma_id ".
            " and horario_turma_sala.sala_id = sala.id_sala ".
            " and turma.oficina_id = oficina.id_oficina".
            "  and id_turma = ?";
        return $this->db->select($columns,"turma,horario_turma_sala,sala,oficina",$whereClause,array($turmaId));
    }

    /**
     * @param $turmaId
     * @throws Exception
     */
    public function updateTurma($turmaId){
        //recebendo dados
        $this->getCommonData();
        $ativo = isset($_POST['rb']) ? $_POST['rb'] : SIM;//por padrao recebe ativo

        //verificando disponibilidade e se vai continuar ativo caso seja update de desativar turma nao gera conflitos
        if($ativo == SIM && $this->temConflitoDeHorario($turmaId)){
            new mensagem(ERRO,"<h3>Conflito de Horarios com outra turma!!</h3>");
            header("Location: " . $_SERVER['HTTP_REFERER'] . "");
            return;
        }
        //atualiza turma
        $turmaColumns = array("num_vagas","professor","is_ativo");
        $turmaParams = array($this->vagas,$this->prof,$ativo);
        if($this->db->update($turmaColumns,"turma",$turmaParams,"id_turma = ?",array($turmaId))){
            //Somente vai tentar atualizar os horarios se a tuma for atualizada
            $horarioColumns = array("sala_id","segunda","terca","quarta","quinta","sexta","inicio","fim");
            $horarioParams = array($this->sala,$this->seg,$this->ter,$this->qua,$this->qui,$this->sex,$this->hinic,$this->hfim);
            if($ativo == SIM){//so deve atualizar horario de turmas ativas
                if($this->db->update($horarioColumns,"horario_turma_sala",$horarioParams,"turma_id = ?",array($turmaId))){
                    new mensagem(SUCESSO,"Turma e Horarios Atualizados");
                    $this->andaListaEspera($turmaId);//aqui verificamos se turma precisa andar
                }else{
                    new mensagem(INSERT_ERRO,"Erro ao atualizar horario da turma");
                }
            }else{
                new mensagem(SUCESSO,"Turma desativada com sucesso");
            }

        }else{
            new mensagem(INSERT_ERRO,"Não foi possivel atualizar");
        }
        $this->redireciona();
    }

    /**
     * verifica vagas disponiveis e numero de alunos em determinada turma fazendo a lista de espera aumentar ou diminuir
     * @param $turmaId
     * @throws Exception
     */
    private function andaListaEspera($turmaId){
        $vagas = json_decode($this->getVagasDisponiveis($turmaId));
        $vagasDisponiveis = ($vagas[0]->vagas - $vagas[0]->ocupadas);
        if ($vagasDisponiveis > 0) {
            //iteramos sobre a lista de espera tirando alunos de la
            $alunoSelecionado = json_decode($this->getAlunoListaEspera($turmaId));
            $columns = array("lista_espera");
            $set = array(NAO);
            for ($i = 0; ($i < $vagasDisponiveis && $i < sizeof($alunoSelecionado)); $i++) {
                $whereArgs = array($alunoSelecionado[$i]->id_aluno);
                $this->db->update($columns, "aluno_turma", $set, "id_aluno = ?", $whereArgs);
            }
        }else if($vagasDisponiveis < 0){
            //iteramos sobre a lista de espera colocando alunos de volta nela
            $alunoSelecionado = json_decode($this->getAlunosId($turmaId));
            $columns = array("lista_espera");
            $set = array(SIM);
            for ($i = 0;($i < (-$vagasDisponiveis) && $i < sizeof($alunoSelecionado)); $i++) {
                $whereArgs = array($alunoSelecionado[$i]->id_aluno);
                $this->db->update($columns, "aluno_turma", $set, "id_aluno = ?", $whereArgs);
            }
        }
    }
    /**
     * Retorna as vagas disponiveis e ocupadas em uma turma
     * @param $turmaId
     * @return string
     * @throws Exception
     */
    private function getVagasDisponiveis($turmaId){
        $projection = "turma.id_turma,num_vagas as vagas,".
            "(SELECT count(*) as n from aluno_turma where aluno_turma.turma_id = id_turma and lista_espera = 0 and trancado = 0) as ocupadas";
        return $this->db->select($projection,"turma","id_turma = ?",array($turmaId));
    }
    /**
     * Retorna todos os alunos em lista de espera
     * @param $turmaId
     * @return string
     * @throws Exception
     */
    private function getAlunoListaEspera($turmaId){
        $columns = "id_aluno,pessoa_id,nome,sobrenome";
        $whereClause = "pessoa_id = id_pessoa and lista_espera = ? and turma_id = ?";
        return $this->db->select($columns,"aluno_turma,pessoa",$whereClause,array(SIM,$turmaId),"id_aluno");
    }

    /**
     * Retorna o id de todos os alunos de uma turma que nao estao na lista de espera
     * pelo maior Id primeiro
     * @param $turmaId
     * @return string
     * @throws Exception
     */
    private function getAlunosId($turmaId){
        $tempo = turma::getTempoStatic($this->db);
        $columns = "aluno_turma.id_aluno";
        $whereClause = "aluno_turma.turma_id = turma.id_turma and turma.tempo_id = ? and turma.id_turma = ? and aluno_turma.lista_espera = 0 and aluno_turma.trancado = 0";
        return $this->db->select($columns,"turma,aluno_turma",$whereClause,array($tempo->id_tempo,$turmaId),"aluno_turma.id_aluno",DESC);
    }


    private function redireciona(){
        //depois de inserir redirecionamos para a pagina de infra
        header("Location: ../index.php?pag=DashBoard");
    }
}