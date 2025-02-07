<?php
include_once 'control/constantes.php';
    session_start();
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        // session started more than 30 minutes ago
        session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
        $_SESSION['CREATED'] = time();  // update creation time
    }

    $titulo = isset($_GET['pag']) ? $_GET['pag']." - ".Ambiente::getSystemName() : Ambiente::getSystemName();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="manifest" href="manifest.json">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="msapplication-starturl" content="/dac/">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- As meta tags acima *devem* vir em primeiro lugar dentro do `head`; qualquer outro conteúdo deve vir *após* essas tags -->
    <title><?php echo $titulo ?></title>

    <link rel="icon" sizes="192x192" href="img/favicon.png">
    <!-- Bootstrap -->
    <link href="bootstrap3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <!-- estiliza o seletor link href="bootstrap3.3.7/select/css/bootstrap-select.min.css" rel="stylesheet" -->
    <link href="css/cssPersonalizado.css" rel="stylesheet">
    <!-- HTML5 shim e Respond.js para suporte no IE8 de elementos HTML5 e media queries -->
    <!-- ALERTA: Respond.js não funciona se você visualizar uma página file:// -->
    <!--[if lt IE 9]>
    <script src="js/ieComp/html5shiv.min.js"></script>
    <script src="js/ieComp/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery (obrigatório para plugins JavaScript do Bootstrap) -->
    <script src="bootstrap3.3.7/jquery.min.js" ></script>
    <!-- Inclui todos os plugins compilados do bootstrap (abaixo) -->
    <script src="bootstrap3.3.7/js/bootstrap.min.js"></script>
    <!-- Icones Personalizados - Font Awesome -->
    <link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- estiliza o seletor script src="bootstrap3.3.7/select/js/bootstrap-select.min.js"></script -->
    <script src="js/jsonParser.min.js"></script>
    <script src="js/grade_horario.js"></script>
    <!-- Aqui recebemos as msg do Sistema -->
    <script type="application/x-javascript">
        <?php Ambiente::getUiMens(); ?>
    </script>
    <!--Tabela timetable.js-->
    <link rel="stylesheet" href="timetable.js/styles/plugin.css">
    <script src="timetable.js/js/timetable.min.js"></script>
</head>
<?php
//aqui recebemos por get a pagina de conteudo escolhida
$opcao = isset($_GET['pag']) ? $_GET['pag'] : 'Login';

if(isset($_SESSION['LOGADO']) and $_SESSION['LOGADO'] == true){
    //pessoa logada tem essas opcoes de pagina
    switch ($opcao) {
        //A string do case se torna o Titulo da pagina
        case 'Login'://se a pessoa ja esta logada nao precisa de login novamente
        case 'DashBoard':
            if($_SESSION['NIVEL'] != ALUNO)
            include "view/dashboard.html";
            else include "view/aluno/aluno_dashboard.html";
            break;
        //Infra
        case 'Infraestrutura':
            include "view/infraestrutura/gerenciar_infraestrutura.html";
            break;
        case 'Cad.Predio':
            include "view/infraestrutura/cadPredio.html";
            break;
        case 'Cad.Sala':
            include "view/infraestrutura/cadSala.html";
            break;
        case 'Edit.Predio':
            include "view/infraestrutura/editPredio.html";
            break;
        case 'Edit.Sala':
            include "view/infraestrutura/editSala.html";
            break;
        //Oficina
        case 'Oficinas':
            include "view/oficina/gerenciar_oficina.html";
            break;
        case 'Cad.Oficina':
            include "view/oficina/cadOficina.html";
            break;
        case 'Edit.Oficina':
            include "view/oficina/edtOficina.html";
            break;
        //Turma
        case 'Turmas':
            include "view/turma/gerenciar_turma.html";
            break;
        case 'Cad.Turma':
            include "view/turma/cadTurma.html";
            break;
        case 'Edit.Turma':
            include "view/turma/editTurma.html";
            break;
        case 'Trocar.Periodo':
            include "view/turma/trocarPeriodo.html";
            break;
        //Usuario
        case 'Usuarios':
            include "view/usuario/gerenciar_usuario.html";
            break;
        case 'Cad.Pessoa':
            include "view/usuario/cadPessoa.html";
            break;
        case 'Info.Pessoa':
            include "view/usuario/infoPessoa.html";
            break;
        case 'Meus-Dados':
            include "view/usuario/meusDados.html";
            break;
        //Alunos
        case 'Alunos':
            include "view/aluno/gerenciar_aluno.html";
            break;
        case 'Cad.Aluno':
            include "view/aluno/inserir_aluno_em_turma.html";
            break;
        case 'Presença':
            include "view/aluno/presenca.html";
            break;
        //Relatorios
        case 'Relatorios':
            include "view/relatorio/relatorio.html";
            break;
        default:
            include "view/404.html";
            break;
    }
}else{
    //Pessoa nao logada tem essas opcoes de pagina
    switch ($opcao) {
        case 'Login':
            include "view/login.html";
            break;
        case 'Cad.Candidato':
            include "view/usuario/cadCandidato.html";
            break;
        case 'unsupported':
	        include "view/unsupported.html";
            break;
        default:
            header("Location: ?pag=Login");
            break;
    }
}

?>
</html>
