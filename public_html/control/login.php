<?php
/**
 * Created by Filipe Klinger
 * Date: 27/04/18
 * Time: 18:22
 */
include_once '../model/DatabaseOpenHelper.php';
include_once 'constantes.php';
include_once 'mensagem.php';

class login{
    private $db;

    public function __construct(){
        $this->db = new Database();
    }

    /**
     * @throws Exception
     */
    public function verifyUser(){
        //Obtendo dados atraves de POST
        $login = isset($_POST['login']) ? $_POST['login'] : INVALIDO;
        $senha = isset($_POST['senha']) ? $_POST['senha'] : INVALIDO;

        //primeiro buscamos os usuario possiveis
        $usr = json_decode($this->db->select("senha,pessoa_id", "login", "usuario = ?", array($login)));

        if($usr != null and password_verify($senha,$usr[0]->senha)){
            $user = json_decode($this->db->select("id_pessoa,nome,nv_acesso","pessoa","id_pessoa = ?",array($usr[0]->pessoa_id)));
            $_SESSION['LOGADO'] = true;
            $_SESSION['ID'] = $user[0]->id_pessoa;
            $_SESSION['NIVEL'] = $user[0]->nv_acesso;
            $user[0]->nv_acesso = $this->getNVacesso($user[0]->nv_acesso);

            $_SESSION['USER'] = json_encode($user[0],JSON_UNESCAPED_UNICODE);
            $this->getMenu();
            $this->redireciona(true);
        }else{
            $_SESSION['LOGADO'] = false;
            $this->redireciona(false);
        }
    }

    private function getNVacesso($nv){
        switch ($nv){
            case ADMINISTRADOR:
                return Ambiente::getCargoAdm();
            case PROFESSOR:
                return Ambiente::getCargoProf();
            case ALUNO:
                return Ambiente::getCargoAluno();
            default:
                return "Visitante";
        }
    }

    private function redireciona($is_logado){
        if($is_logado){
            header("Location: ../index.php?pag=DashBoard");
        }else{
            new mensagem(ERRO,"Login ou senha Incorretos");
            header("Location: " . $_SERVER['HTTP_REFERER'] . "");//MANDA DE VOLTA PARA O login
        }
    }

    public static function logout(){
        session_destroy();
        header("Location: ../index.php?pag=Login");
    }

    public static function getUser(){
        if(isset($_SESSION['LOGADO']) and $_SESSION['LOGADO'] == true){
            echo $_SESSION['USER'];
        }else{
            header("Location: ../index.php?pag=Login");
        }
    }

    private function getMenu(){
        switch ($_SESSION['NIVEL']){
            case ADMINISTRADOR:
                $_SESSION['MENU'] = Ambiente::getAdmMenu();
                break;
            case PROFESSOR:
                $_SESSION['MENU'] = Ambiente::getProfMenu();
                break;
            case ALUNO:
                $_SESSION['MENU'] = Ambiente::getAlunoMenu();
                break;
            case VISITANTE:
                $_SESSION['MENU'] ='[]';
                break;
            default:
                $_SESSION['MENU'] ='[]';
                break;
        }
    }
}