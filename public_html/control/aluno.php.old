<?php
/**
 * Created by Filipe
 * Date: 26/05/18
 * Time: 19:05
 */
include_once '../model/DatabaseOpenHelper.php';
include_once 'mensagem.php';
include_once 'constantes.php';
include_once '../tcpdf/tabelaPDF.php';
class aluno{
    private $db;
    private $sucessInsercao,$failInsercao,$listaEsperaInsercao,$idTurma;
    private $numSucess,$numEspera,$numFail,$numErro;

    public function __construct(){
        $this->db = new Database();
    }

    /**
     * @throws Exception
     */
    public function setAluno(){
        //inicializando contadores
        $this->numSucess=$this->numEspera=$this->numFail=$this->numErro=0;
        //recuperando a turma
        $this->idTurma = isset($_POST['turma']) ? $_POST['turma'] : INVALIDO;
        $this->sucessInsercao = "Status dos Candidatos: <br/>";
        foreach ($_POST['aluno_id'] as $id){
            $this->parseCandidato($id);
        }
        //montando mensagem
        $msg = "";
        if($this->numSucess == 1) $msg.=$this->numSucess." aluno inserido com sucesso na turma<br/>"; elseif($this->numSucess >1) $msg.=$this->numSucess." alunos inseridos com sucesso na turma<br/>";
        if($this->numEspera == 1) $msg.=$this->numEspera." aluno inserido na lista de espera<br/>"; elseif($this->numEspera > 1) $msg.=$this->numEspera." alunos inseridos na lista de espera<br/>";
        if($this->numFail == 1) $msg.=$this->numFail." aluno já está na turma ou participa de ".Ambiente::getMaxOficinas()." ".Ambiente::getAtividadeName()."s<br/>"; elseif($this->numFail > 1) $msg.=$this->numFail." alunos já estão na turma ou participam de ".Ambiente::getMaxOficinas()." ".Ambiente::getAtividadeName()."s<br/>";
        if($this->numErro == 1) $msg.=$this->numErro." aluno não pôde ser inserido, log de erro gerado<br/>";if($this->numErro > 1) $msg.=$this->numErro." alunos não puderam ser inseridos, log de erro gerado<br/>";
        //form vazio
        if($this->numSucess == 0 && $this->numEspera == 0 && $this->numFail == 0 && $this->numErro == 0){$this->numErro = 1;$msg="Nenhum dado recebido, use o botão de busca";}
        //verificando se temos mais erros ou sucesso
        if(($this->numFail+$this->numErro) <= $this->numSucess){
            new mensagem(SUCESSO,$msg);
        }else{
            new mensagem(ERRO,$msg);
        }

        $this->redireciona();
    }

    /**
     * @param $idPessoa
     * @throws Exception
     */
    private function parseCandidato($idPessoa){
        //verificando se o aluno já esta cadastrado em X oficinas do periodo atual
         $estaParticipando = $this->getParticipacaoPeriodoAtual($idPessoa);

        if($estaParticipando < Ambiente::getMaxOficinas()){
            //buscando se o aluno já esta cadasrado nessa turma
            $jaNaTurma = json_decode($this->db->select("count(*) as n","aluno_turma","turma_id = ? and pessoa_id = ?",array($this->idTurma,$idPessoa)));
            $jaNaTurma = $jaNaTurma[0]->n;
            if($jaNaTurma == null or $jaNaTurma == NAO){
                //obtendo num de vagas cadastradas na turma
                $turma = json_decode($this->getVagasDisponiveis($this->idTurma));
                $turma = $turma[0];
                //inserindo aluno em turma
                $mens = "";
                if(($turma->ocupadas) < ($turma->vagas)){
                    //pessoa dentro do numero de vagas
                    $params = array($this->idTurma,$idPessoa,NAO);
                    $this->sucessInsercao = 0;
                }else{
                    //pessoas na lista de espera
                    $params = array($this->idTurma,$idPessoa,SIM);
                    $this->sucessInsercao = 1;
                }
                //tentando inserir o aluno na turma
                if($this->db->insert("turma_id,pessoa_id,lista_espera","aluno_turma",$params)){
                    //inserido com sucesso
                    if($this->sucessInsercao == 0) $this->numSucess++;
                    else $this->numEspera++;

                }else{
                    //Não foi possivel inserir
                    $this->numErro++;
                }
            }else{
                //Já está nessa turma
                $this->numFail++;
            }
        }else{
            //já esta participando de X Oficinas
            $this->numFail++;
        }
    }

    /**
     * aqui conseguimos obter a parcicipacao do aluno em turmas do periodo atual
     * @param $idPessoa
     * @return object
     * @throws Exception
     */
    private function getParticipacaoPeriodoAtual($idPessoa){
        $tempoAtual = turma::getTempoStatic($this->db);
        $whereCLause = "id_turma = turma_id and turma.tempo_id = ? and pessoa_id = ?";
        $estaParticipando = json_decode($this->db->select("count(*) as n","aluno_turma,turma",$whereCLause,array($tempoAtual->id_tempo,$idPessoa)));
        $estaParticipando = $estaParticipando[0]->n;
        return $estaParticipando;
    }

    /**
     * recupera o nome e sobrenome de uma pessoa pelo id passado
     * @param $idPessoa
     * @return string com o nome e sobrenome
     * @throws Exception
     */
    private function getNome($idPessoa){
        //recuperando nome do aluno
        $nomeCandidato = json_decode($this->db->select("nome,sobrenome","pessoa","id_pessoa = ?",array($idPessoa)));
        $nomeCandidato = $nomeCandidato[0]->nome.' '.$nomeCandidato[0]->sobrenome;
        return $nomeCandidato;
    }

    /**
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
     * Buscando os alunos de turma especifica
     * @param $turmaId Integer - Id da turma
     * @return string JSON
     * @throws Exception
     */
    public function getAlunos($turmaId){
        $tempo = turma::getTempoStatic($this->db);
        $columns = "aluno_turma.id_aluno,pessoa.nome,pessoa.sobrenome,pessoa.data_nascimento,turma.nome_turma as turma,lista_espera,aluno_turma.trancado";
        $whereClause = "aluno_turma.turma_id = turma.id_turma and aluno_turma.pessoa_id = pessoa.id_pessoa and turma.tempo_id = ? and turma.id_turma = ?";
        return $this->db->select($columns,"pessoa,turma,aluno_turma",$whereClause,array($tempo->id_tempo,$turmaId),"pessoa.nome",ASC);
    }

    /**
     * @param $turmaId
     * @return string
     * @throws Exception
     */
    public function getAlunoListaEspera($turmaId){
        $columns = "id_aluno,pessoa_id,nome,sobrenome";
        $whereClause = "pessoa_id = id_pessoa and lista_espera = ? and turma_id = ?";
        return $this->db->select($columns,"aluno_turma,pessoa",$whereClause,array(SIM,$turmaId),"id_aluno");
    }

    /**
     * @param $alunoId
     * @throws Exception
     */
    public function trancarMatricula($alunoId){
        $mensagenAcumulada = "";
        //trancamos a matricula do aluno informado
        if($this->db->update(array("trancado"),"aluno_turma",array(SIM),"id_aluno = ?",array($alunoId))){
            $mensagenAcumulada .= "Matricula trancada";
            //se trancamento deu certo vamos verificar os alunos na lista de espera

            //buscando a turma do aluno
            $turma = json_decode($this->db->select("turma_id","aluno_turma","id_aluno = ?",array($alunoId)));

            $turma = $turma[0]->turma_id;
            $vagas = json_decode($this->getVagasDisponiveis($turma));
            if(($vagas[0]->vagas - $vagas->ocupadas) > 0)
            $alunoSelecionado = json_decode($this->getAlunoListaEspera($turma));
            else $alunoSelecionado = 0 ;
            //Verificamos se existe lista de Espera
            if(sizeof($alunoSelecionado) > 0){
                $alunoSelecionado = $alunoSelecionado[0];

                //colocamos o primeiro aluno da lista de espera na turma
                if($this->db->update(array("lista_espera"),"aluno_turma",array(NAO),"id_aluno = ?",array($alunoSelecionado->id_aluno))){
                    $mensagenAcumulada.= ", Candidato ".$alunoSelecionado->nome." ".$alunoSelecionado->sobrenome." que estava na lista de espera foi Inserido na Turma";
                    new mensagem(SUCESSO,$mensagenAcumulada);

                }else{
                    new mensagem(ERRO,"Matricula trancada mas lista de espera não pôde andar");
                }
            }else{
                new mensagem(SUCESSO,$mensagenAcumulada);
            }

        }else{
            new mensagem(INSERT_ERRO,"Não foi possível trancar a matrícula");
        }

       $this->redirecionaPagAnterior();
    }

    /**
     * aqui geramos uma lista de presença com os alunos ativos na turma
     * @param $turmaId Integer - identificador da turma
     * @throws Exception
     */
    public function getListaPresenca($turmaId){
        if($turmaId != null and $turmaId != INVALIDO){
            $alunos = json_decode($this->getAlunos($turmaId));
            $Objpresenca = array();
            $posAluno = 0;
            for($i=0;$i< sizeof($alunos);$i++){
                //se cair nessa condicao pulamos para prox iteracao
                if(($alunos[$i]->lista_espera == 1) || ($alunos[$i]->trancado == 1)){continue;}

                $Objpresenca[$posAluno] = new stdClass();
                $Objpresenca[$posAluno]->pos = $posAluno+1;
                $Objpresenca[$posAluno]->nome = $alunos[$i]->nome." ".$alunos[$i]->sobrenome;
                $posAluno++;
            }
            //aqui recuperamos os dados do professor, nome da oficina e nome da turma
            $oficinaAtual = json_decode($this->db->select("oficina.nome as oficina,nome_turma as turma,pessoa.nome as professor,pessoa.sobrenome","oficina,turma,pessoa","oficina_id = id_oficina and id_turma = ? and pessoa.id_pessoa = turma.professor",array($turmaId)));
            $oficinaAtual[0]->professor = $oficinaAtual[0]->professor." ".$oficinaAtual[0]->sobrenome;
            new pdf($Objpresenca,$oficinaAtual[0]);
        }else{
            $this->redirecionaPagAnterior();
        }
    }

    /**
     * Esse historico e para o aluno obter as oficinas que ele ja participou
     */
    public function meuHistorico(){

    }

    /**
     * para o aluno obter as oficinas que ele está matriculado atualmente
     * e tambem as oficinas de seus dependentes se houver
     * @throws exception
     */
    public function minhasOficinas(){
        //$_SESSION['ID']
        $oficinas = json_decode($this->oficinasAtuais($_SESSION['ID']));
        //colocando todas as oficinas em um unico objeto aluno
        $ofParent = array();
        for($j=0;$j<sizeof($oficinas);$j++){
            $nome = $oficinas[$j]->nome;
            unset($oficinas[$j]->nome);
            array_push($ofParent,
                (array) $oficinas[$j]);

            unset($oficinas[$j]);
            $oficinas[$j] = new stdClass();
            $oficinas[$j]->nome = $nome;
        }
        $oficinas[0]->oficinas = $ofParent;

        //buscando dependentes
        $dependentes = json_decode($this->dependentesId($_SESSION['ID']));
        if(sizeof($dependentes)>0){
            //obtendo as oficinas atuais do tal dependente
            for($i=0;$i < sizeof($dependentes);$i++){
                $oficDep = json_decode($this->oficinasAtuais($dependentes[$i]->id_pessoa));
                unset($dependentes[$i]->id_pessoa);//removendo id por seguranca
                $dependentes[$i]->oficinas = $oficDep;
                array_push($oficinas,$dependentes[$i]);
            }
        }
        return json_encode($oficinas,JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtem o Id e nome dos dependentes
     * @param $responsavelId
     * @return string
     * @throws Exception
     */
    private function dependentesId($responsavelId){
        $projecao = "pessoa.id_pessoa,pessoa.nome,pessoa.sobrenome";
        $tabela = "pessoa,menor_idade";
        $whereClause =
            "pessoa.id_pessoa = menor_idade.pessoa_id AND
            menor_idade.responsavel_id = ?";
        $whereArgs = array($responsavelId);
        return $this->db->select($projecao,$tabela,$whereClause,$whereArgs);
    }

    /**
     * obtem as oficinas que um aluno esta cursando atualmente
     * @param $id_pessoa
     * @return string
     * @throws Exception
     */
    private function oficinasAtuais($id_pessoa){
        $projecao = "pessoa.nome,turma.nome_turma as turma,oficina.nome as oficina,DATE_FORMAT(hts.inicio, \"%H:%ih\") as inicio,DATE_FORMAT(hts.fim, \"%H:%ih\") as fim,hts.segunda,hts.terca,hts.quarta,hts.quinta,hts.sexta,sala.nome as sala,p2.nome as professor";
        $tabela = "pessoa,aluno_turma,oficina,turma,horario_turma_sala as hts,sala,pessoa as p2";
        $whereClause =
            "pessoa.id_pessoa=aluno_turma.pessoa_id AND
            aluno_turma.turma_id = turma.id_turma AND
            turma.oficina_id = oficina.id_oficina AND
            turma.id_turma = hts.turma_id AND
            turma.tempo_id = (SELECT MAX(tempo.id_tempo) as id FROM tempo) AND
            hts.sala_id = sala.id_sala AND
            turma.professor = p2.id_pessoa AND
            pessoa.id_pessoa = ?";
        $whereArgs = array($id_pessoa);
        return $this->db->select($projecao,$tabela,$whereClause,$whereArgs);

    }

    private function redireciona(){header("Location: ../index.php?pag=Cad.Aluno");}
    private function redirecionaPagAnterior(){header("Location: " . $_SERVER['HTTP_REFERER'] . "");}
}