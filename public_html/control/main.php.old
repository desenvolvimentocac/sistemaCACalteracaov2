<?php
/**
 * Created by Filipe klinger
 * Date: 02/05/18
 * Time: 11:46
 * Sistema de Rotas para o controler
 */
include_once 'constantes.php';
include_once 'login.php';
include_once 'infraestrutura.php';
include_once 'oficina.php';
include_once 'pessoa.php';
include_once 'turma.php';
include_once 'aluno.php';
include_once 'relatorio.php';
include_once 'mensagem.php';

class main{
    private $act;

    public function __construct(){
	    //HSTS

	    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		    header('Strict-Transport-Security: max-age=31536000');
	    } else {
		    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
		    die();
	    }

    }

    public function setAction(){
        $this->act = isset($_GET['req']) ? $_GET['req'] : INVALIDO;
        try {
            $this->doAction();
        } catch (Exception $e) {
            new mensagem(ERRO,"Erro: ".$e);
            $this->redireciona();
        }
    }

    /**
     * @throws Exception
     */
    private function doAction(){
        switch ($this->act){
            case "login":
                $login = new login();
                $login->verifyUser();
                break;
                //Infraestrutura
            case "insertPredio":
                $infra = new infraestrutura();
                $infra->setPredio();
                break;
            case "updatePredio":
                $infra = new infraestrutura();
                $infra->updatePredio($_GET['id']);
                break;
            case "insertSala":
                $infra = new infraestrutura();
                $infra->setSala();
                break;
            case "updateSala":
                $infra = new infraestrutura();
                $infra->updateSala($_GET['id']);
                break;
            case "selectPredio":
                $infra = new infraestrutura();
                echo $infra->getPredios();
                break;
            case "selectPredioById":
                $infra = new infraestrutura();
                echo $infra->getPredioById($_GET['id']);
                break;
            case "selectSala":
                $infra = new infraestrutura();
                echo $infra->getSalas();
                break;
            case "selectSalaByPredioId":
                $infra = new infraestrutura();
                echo $infra->getSalaByPredioId($_GET['id']);
                break;
            case "selectSalaById":
                $infra = new infraestrutura();
                echo $infra->getSalaById($_GET['id']);
                break;
                //Pessoa------------------------------------------------------------------------------------------------
            case "verificaUser":
                $pess = new pessoa();
                echo $pess->verificaUsuarioDuplicado($_GET['nome']);
                break;
            case "insertPessoa":
                $pess = new pessoa();
                $pess->setPessoa();
                break;
            case "selectUsuario":
                $pess = new pessoa();
                echo $pess->getUsuarios();
                break;
            case "getPageNumber":
                $pess = new pessoa();
                echo $pess->getPageNumber();
                break;
            case "selectPessoaById":
                $pess = new pessoa();
                echo $pess->getPessoaById($_GET['id']);
                break;
            case "selectLoginUser":
                $pess = new pessoa();
                echo $pess->getLoginUser($_GET['id']);
                break;
            case "selectRuralinoByPessoaId":
                $pess = new pessoa();
                echo $pess->getRuralinoByPessoaId($_GET['id']);
                break;
            case "selectResponsavelByMenorId":
                $pess = new pessoa();
                echo $pess->getResponsavelByMenorId($_GET['id']);
                break;
            case "selectTelefoneByPessoaId":
                $pess = new pessoa();
                echo $pess->getTelefone($_GET['id']);
                break;
            case "selectEndereco":
                $pess = new pessoa();
                echo $pess->getEndereco($_GET['id']);
                break;
            case "selectDocumento":
                $pess = new pessoa();
                echo $pess->getDocumento($_GET['id']);
                break;
            case "selectDependentes":
                $pess = new pessoa();
                echo $pess->getDependentes($_GET['id']);
                break;
            case "updateDadosBasicos":
                $pess = new pessoa();
                $pess->updateDadosBasicos($_GET['id']);
                break;
            case "updateEndereco":
                $pess = new pessoa();
                $pess->updateEndereco($_GET['id']);
                break;
            case "insertRuralino":
                $pess = new pessoa();
                $pess->ruralino($_GET['id']);
                break;
            case "updateRuralino":
                $pess = new pessoa();
                $pess->updateRuralino($_GET['id']);
                break;
            case "updateDoc":
                $pess = new pessoa();
                $pess->updateDocument($_GET['id']);
                break;
            case "updateContato":
                $pess = new pessoa();
                $pess->updateContato($_GET['id']);
                break;
            case "addDependente":
                $pess = new pessoa();
                $pess->addDependente($_GET['id']);
                break;
            case "removeDependente":
                $pess = new pessoa();
                $pess->deleteDependente($_GET['id']);
                break;
            case "updateSenha":
                $pess = new pessoa();
                $pess->updateSenha($_GET['id']);
                break;
            case "desativaConta":
                $pess = new pessoa();
                $pess->gerenciaConta($_GET['id'],SIM);
                break;
            case "ativaConta":
                $pess = new pessoa();
                $pess->gerenciaConta($_GET['id'],NAO);
                break;
            //Oficina---------------------------------------------------------------------------------------------------
            case "insertOficina":
                $ofic = new oficina();
                $ofic->setOficina();
                break;
            case "updateOficina":
                $ofic = new oficina();
                $ofic->updateOficina($_GET['id']);
                break;
            case "selectOficina":
                $ofic = new oficina();
                echo $ofic->getOficina();
                break;
            case "selectOficinaById":
                $ofic = new oficina();
                echo $ofic->getOficinaById($_GET['id']);
                break;
                //TURMA-------------------------------------------------------------------------------------------------
            case "insertTurma":
                $turma = new turma();
                $turma->setTurma();
                break;
            case "selectTurma":
                $turma = new turma();
                echo $turma->getTurmas($_GET['id']);
                break;
            case "updateTurma":
                $turma = new turma();
                $turma->updateTurma($_GET['id']);
                break;
            case "selectTurmaAtiva":
                header("Content-Type: application/json; charset=UTF-8");
                $turma = new turma();
                echo $turma->getTurmasAtivas();
                break;
            case "selectHorario":
                $turma = new turma();
                echo $turma->getHorariosBySalaId($_GET['id']);
                break;
            case "selectTurmaById":
                $turma = new turma();
                echo $turma->getTurmaById($_GET['id']);
                break;
            case "selectPeriodoAtual":
                $turma = new turma();
                echo $turma->getTempo();
                break;
            case "selectTempoHistorico":
                $turma = new turma();
                echo $turma->getTempoHistorico();
                break;
            case "setPeriodo":
                $turma = new turma();
                $turma->setPeriodo();
                break;
                //Aluno-------------------------------------------------------------------------------------------------
            case "insertAluno":
                $aluno = new aluno();
                $aluno->setAluno();
                break;
            case "selectAlunosByTurmaId":
                $aluno = new aluno();
                echo $aluno->getAlunos($_GET['id']);
                break;
            case "selectAlunosListaEspera":
                $aluno = new aluno();
                echo $aluno->getAlunoListaEspera($_GET['id']);
                break;
            case "trancarMatricula":
                $aluno = new aluno();
                $aluno->trancarMatricula($_GET['id']);
                break;
            case "listaPresenca":
                $aluno = new aluno();
                echo $aluno->getListaPresenca($_GET['id']);
                break;
            //Relatorio-------------------------------------------------------------------------------------------------
            case "relAlunosPorTurmaPeriodo":
                $relatorio = new relatorio();
                echo $relatorio->getAlunosPorOficina($_GET['id']);
                break;
            case "selectAlunosHistorico":
                $relatorio = new relatorio();
                echo $relatorio->getAlunosHistorico();
                break;
            case "selectAlunosCadastrados":
                $relatorio = new relatorio();
                echo $relatorio->getAlunosCadastrados();
                break;
            //USUARIO---------------------------------------------------------------------------------------------------
            case "selecUsuarioLogado":
                login::getUser();
                break;
            case "logout":
                login::logout();
                break;
            default:
                    $this->redireciona();
                break;
        }
    }

    private function redireciona(){
        header("Location: ../index.php?pag=Login");
    }

}

$controlador = new main();
$controlador->setAction();