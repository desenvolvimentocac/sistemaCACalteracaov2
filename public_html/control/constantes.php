<?php
/**
 * Constantes para a aplicação
 * Created by Filipe
 * Date: 28/04/18
 * Time: 17:02
 */
const SIM = 1;
const NAO = 0;
const INVALIDO = -1;

//niveis de acesso
const ADMINISTRADOR = 1;
const PROFESSOR = 2;
const ALUNO = 3;
const VISITANTE = 4;

//mensagens
const SUCESSO = 200;
const ERRO = 500;
const INSERT_ERRO = 501;

//paginador
const REGISTROS = 25;


/* Variaveis de Ambiente */
class Ambiente{
    //Nome do sistema
    private static $nomeSistema = "Sistema CAC";
    private static $nomeSistemaExtenso = "Sistema do Centro de Arte e Cultura - CAC";
    private static $nomeInstituicao = "Universidade Federal Rural do Rio de Janeiro";
    //cargos
    private static $admn = "Administrador";//Administrador
    private static $prof = "Oficineiro";//Oficineiro
    private static $alun = "Aluno";//Aluno
    //nome da atividade
    private static $atividade = "Oficina";//escreva somente no singular
    //Quantidade maxima de atividades que um aluno pode cursar num mesmo periodo
    private static $maxAtiv = 2;

    //Metodos-----------------------------------------------------------------------------------------------------------
    public static function getSystemName(){return self::$nomeSistema;}
    public static function getSystemNameExtenso(){return self::$nomeSistemaExtenso;}
    public static function getInstituicaoName(){return self::$nomeInstituicao;}
    public static function getAdmMenu(){return '[{"nome":"Infra","link":"InfraSubmenu","icone":"glyphicon-dashboard","submenu":[{"nome":"Gerenciar","link":"?pag=Infraestrutura"},{"nome":"Novo Predio","link":"?pag=Cad.Predio"},{"nome":"Nova Sala","link":"?pag=Cad.Sala"}]},{"nome":"'.self::$atividade.'s","link":"OficinaSubmenu","icone":"glyphicon-knight","submenu":[{"nome":"Gerenciar","link":"?pag=Oficinas"},{"nome":"Nova '.self::$atividade.'","link":"?pag=Cad.Oficina"}]},{"nome":"Usuarios","link":"UsuarioSubmenu","icone":"glyphicon-user","submenu":[{"nome":"Gerenciar","link":"?pag=Usuarios"},{"nome":"Novo Usuário","link":"?pag=Cad.Pessoa"}]},{"nome":"Turmas","link":"TurmaSubmenu","icone":"glyphicon-bell","submenu":[{"nome":"Mudar Período","link":"?pag=Trocar.Periodo"},{"nome":"Gerenciar Turmas","link":"?pag=Turmas"},{"nome":"Nova Turma","link":"?pag=Cad.Turma"}]},{"nome":"Alunos","link":"AlunoSubmenu","icone":"glyphicon-education","submenu":[{"nome":"Gerenciar","link":"?pag=Alunos"},{"nome":"Cad. Aluno em Turma","link":"?pag=Cad.Aluno"}]},{"nome":"Relatórios","link":"RelatorioSubmenu","icone":"glyphicon-print","submenu":[{"nome":"Gerar","link":"?pag=Relatorios"}]}]';}
    public static function getProfMenu(){return '[{"nome":"Minha conta","link":"ContaSubmenu","icone":"glyphicon-user","submenu":[{"nome":"Meus Dados","link":"?pag=Meus-Dados&id='.$_SESSION['ID'].'"}]},{"nome":"Minhas Turmas","link":"TurmasSubmenu","icone":"glyphicon-bell","submenu":[{"nome":"ver","link":"?pag=Turmas"},{"nome":"Nova Turma","link":"?pag=Cad.Turma"}]},{"nome":"Alunos","link":"AlunosSubmenu","icone":"glyphicon-education","submenu":[{"nome":"Gerenciar","link":"?pag=Presença"}]}]';}
    public static function getAlunoMenu(){return '[{"nome":"Minha conta","link":"ContaSubmenu","icone":"glyphicon-user","submenu":[{"nome":"Meus Dados","link":"?pag=Meus-Dados&id='.$_SESSION['ID'].'"}]}]';}
    public static function getCargoAdm(){return self::$admn;}
    public static function getCargoProf(){return self::$prof;}
    public static function getCargoAluno(){return self::$alun;}
    public static function getAtividadeName(){return self::$atividade;}
    public static function getMaxOficinas(){ return self::$maxAtiv;}
    //transformando em JS para enviar a Ui
    public static function getUiMens(){
        $msg = isset($_SESSION['MSG']) ? $_SESSION['MSG'] : '{"tipo":" ","desc":" "}';
        echo "var mensagem = '".$msg."';";
        unset($_SESSION['MSG']);
        $menu = isset($_SESSION['MENU']) ? $_SESSION['MENU'] : '{"nome":" ","link":" "}';
        echo "const menuPrincipal = '".$menu."';";
        echo "
            const SystemName = \"".self::$nomeSistema."\";
            const InstituicaoName = \"".self::$nomeInstituicao."\";
            const SystemNameExtenso = \"".self::$nomeSistemaExtenso."\";
            const CargoAdm = \"".self::$admn."\";
            const CargoProf = \"".self::$prof."\";
            const CargoAluno = \"".self::$alun."\";
            const AtividadeName = \"".self::$atividade."\";
        ";

    }
}
