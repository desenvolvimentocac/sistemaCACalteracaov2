/*Recuperando as informações*/
var notSupported = ['SamsungBrowser', 'MSIE', 'Trident'];

$(document).ready(function () {
    var b = navigator.userAgent;
    for (var i in notSupported) {

        let aux = new RegExp(notSupported[i]);
        if (b.match(aux) != null) {
            alert("Navegador Não Supportado, Você Será Redirecionado . . . .");
            window.location.replace("view/unsupported.html");
        }
    }
});

function ajaxLoadGET(destino, funcaoParse, corpo, funcaoEncadeada) {
    var body = $(corpo);
    //colocando uma mensagem de load para o usuario
    body.prepend('<div class="loader"></div>');
    //var xhttp = new XMLHttpRequest();//Objeto Ajax
    var xhttp;
    try {
        // Firefox, Opera 8.0+, Safari
        xhttp = new XMLHttpRequest();
    } catch (e) {
        // Internet Explorer
        try {
            xhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                xhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                alert("Esse site não funciona no seu navegador, Use o chrome.");
                return false;
            }
        }
    }
    xhttp.onreadystatechange = function () {//toda vez que mudar estado chama a funcao
        if (xhttp.readyState === 4) {//estado 4 é quando recebe a resposta
            $(".loader").remove();
            body.empty();
            funcaoParse(this.responseText, body, funcaoEncadeada);
        }
    };
    if (xhttp.overrideMimeType) {
        xhttp.overrideMimeType('application/json');
    }
    xhttp.open("GET", destino);
    xhttp.send();

}

/*---------------------------------------------Login---------------------------------*/
function listaMarota(resposta, corpo) {
    var objJson = JSON.parse(resposta);
    var aux;
    if (objJson.length > 0) {
        for (var i in objJson) {
            //aqui obtemos o numero real de vagas disponiveis
            objJson[i].vagas = parseInt(objJson[i].vagas) - parseInt(objJson[i].ocupadas);
            if (objJson[i].vagas < 10) {
                objJson[i].vagas = '&nbsp;&nbsp;' + objJson[i].vagas;
            }
            if (objJson[i].pre_requisito.length < 2) objJson[i].pre_requisito = 'Nenhum';
            var aux = JSON.parse(avaliaData(objJson[i]));

            corpo.append(
                `<div class="container oficinasContainer">
                    <div class="col-md-2 ">
                        <div class="text-center oficinasvagas">${objJson[i].vagas}<br>vagas</div>
                    </div>
                    <div class="col-md-8">
                        <h3 >${objJson[i].oficina} - ${objJson[i].inicio}</h3>
                        <h4>Dias: ${aux}</h4>
                        <p>Local: <span style="text-transform: capitalize">${objJson[i].sala}</span> em  <span style="text-transform: uppercase">${objJson[i].predio}</span></p>
                        <p>Professor: ${objJson[i].professor}</p>
                        <p>Requisito: ${objJson[i].pre_requisito}</p>
                        <hr>
                      </div>
                </div>`
            );
        }
    }
    else {
        corpo.append(
            '<div class="container oficinasContainer">' +
            '<h2 class="text-center"> <span class="glyphicon glyphicon-education"></span> Novas Turmas em Breve</h2>' +
            '</div>'
        );
    }
}

function avaliaData(jsonObject) {
    var dias = new Array();
    if (jsonObject.segunda === '1') {
        dias.push("Seg ");
    }
    if (jsonObject.terca === '1') {
        dias.push("Ter ");
    }
    if (jsonObject.quarta === '1') {
        dias.push("Qua ");
    }
    if (jsonObject.quinta === '1') {
        dias.push("Qui ");
    }
    if (jsonObject.sexta === '1') {
        dias.push("Sex ");
    }
    return JSON.stringify(dias);
}

const now = new Date();
var periodo;
if (now.getMonth() > 6) {
    periodo = 2;
}
else {
    periodo = 1;
}

/*---------------------------------------------DASHBOARD------------------------------*/
function parseUsuario(resposta) {
    var jsonObj = JSON.parse(resposta);
    $('#user').append(jsonObj.nome);
    $('#nv').append(jsonObj.nv_acesso);
}

function parseTurmaHorario(resposta, corpo) {
    var objJson = JSON.parse(resposta);
    for (i in objJson) {
        //aqui obtemos o numero real de vagas disponiveis
        objJson[i].vagas = parseInt(objJson[i].vagas) - parseInt(objJson[i].ocupadas);
        corpo.append(
            '<tr>' +
            '<td class=\'col-md-2\' style="text-transform: capitalize;">' + objJson[i].oficina + '</td>' +
            '<td class=\'col-md-2\'>' + objJson[i].turma + '</td>' +
            '<td class=\'col-md-2\'>' + getDiaSemana(objJson[i]) + '</td>' +
            '<td class=\'col-md-2\'>' + objJson[i].inicio + " as " + objJson[i].fim + '</td>' +
            '<td class=\'col-md-1\' style="text-transform: capitalize;">' + objJson[i].sala + '</td>' +
            '<td class=\'col-md-2\' style="text-transform: capitalize;">' + objJson[i].professor + '</td>' +
            '<td class=\'col-md-1\'>' + objJson[i].vagas + '</td>' +
            '</tr>');
    }
    if (objJson.length < 1) {
        corpo.append(
            '<tr>' +
            '<td class=\'col-md-2\' style="text-transform: capitalize;">Sem turmas disponíveis</td>' +
            '<td class=\'col-md-2\'></td>' +
            '<td class=\'col-md-2\'></td>' +
            '<td class=\'col-md-2\'></td>' +
            '<td class=\'col-md-1\' style="text-transform: capitalize;"></td>' +
            '<td class=\'col-md-2\' style="text-transform: capitalize;"></td>' +
            '<td class=\'col-md-1\'></td>' +
            '</tr>');
    }

}

function getMenu() {
    let menu = JSON.parse(menuPrincipal);
    let menuString = '';

    for (i in menu) {

        menuString += '        <li class="text-center submenu">\n' +
            '                       <a href="#' + menu[i].link + '" data-toggle="collapse" aria-expanded="false">\n' +
            '                           <i class="glyphicon ' + menu[i].icone + '"></i>\n' +
            '                           <br>' + menu[i].nome + '\n' +
            '                       </a>\n' +
            '                       <ul class="collapse list-unstyled" id="' + menu[i].link + '">';

        for (j in menu[i].submenu) {
            menuString += '          <li><a href="' + menu[i].submenu[j].link + '">' + menu[i].submenu[j].nome + '</a></li>';
        }

        menuString += '</ul></li>';
    }

    $('#menu').prepend(menuString);
}

/*---------------------------------------------INFRA----------------------------------*/
function jsonParsePredios(json, corpo) {
    var objJson = JSON.parse(json);
    for (var i in objJson) {
        corpo.append(
            '<tr>' +
            '<td class=\'col-md-5\' style="text-transform: uppercase;"> ' + objJson[i].nome + '</td>' +
            '<td class=\'col-md-5\'> ' + objJson[i].localizacao + '</td>' +
            '<td class=\'col-md-1\'> ' + isAtivo(objJson[i].is_ativo) + '</td>' +
            '<td class=\'col-md-1\'> <a href="?pag=Edit.Predio&id=' + objJson[i].id_predio + '" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></a> </td>' +
            '</tr>');
    }
}

function jsonParseSalas(json, corpo) {
    var objJson = JSON.parse(json);
    for (var i in objJson) {
        corpo.append(
            '<tr>' +
            '<td class=\'col-md-4\' style="text-transform: capitalize;"> ' + objJson[i].sala + '</td>' +
            '<td class=\'col-md-4\' style="text-transform: uppercase;"> ' + objJson[i].predio + '</td>' +
            '<td class=\'col-md-2\'> ' + isAtivo(objJson[i].is_ativo) + '</td>' +
            '<td class=\'col-md-2\'> <a href="?pag=Edit.Sala&id=' + objJson[i].id_sala + '" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></a> </td>' +
            '</tr>');
    }
}

function isAtivo(num) {
    if (num == '1') return "sim";
    else return "não";
}

function jsonParseNomePredios(resposta, corpo) {
    var objJson = JSON.parse(resposta);
    corpo.append('<option value="" disabled selected>Selecione o prédio ao qual a sala pertence</option>');
    for (var i in objJson) {
        if(objJson[i].is_ativo == 0) continue;
        corpo.append(
            '<option value="' + objJson[i].id_predio + '">' + objJson[i].nome + '</option>');
    }
}

/*TODO: SUBSTITUR A FUNÇÃO getDiaSemana*/

function getDiaSemana(objdia) {
    let diasSemana = "";
    if (objdia.segunda === "1") diasSemana = "Segunda";
    if (objdia.terca === "1") {
        if (diasSemana.length > 1) diasSemana += " e ";
        diasSemana += "Terça";
    }
    if (objdia.quarta === "1") {
        if (diasSemana.length > 1) diasSemana += " e ";
        diasSemana += "Quarta";
    }
    if (objdia.quinta === "1") {
        if (diasSemana.length > 1) diasSemana += " e ";
        diasSemana += "Quinta";
    }
    if (objdia.sexta === "1") {
        if (diasSemana.length > 1) diasSemana += " e ";
        diasSemana += "Sexta";
    }
    return diasSemana;
}

function getNVacesso(nv) {
    switch (nv) {
        case '1':
            return CargoAdm;
        case '2':
            return CargoProf;
        case '3':
            return CargoAluno;
        default:
            return "Visitante";
    }
}
function getMsgs() {
    let aviso = $('#avisos');
    let msg = JSON.parse(mensagem);

    switch (msg.tipo) {
        case "erro":
            aviso.append(msg.desc);
            aviso.addClass("alert alert-danger");
            break;
        case "sucesso":
            aviso.append(msg.desc);
            aviso.addClass("alert alert-success");
            break;
        default:
            aviso.removeAttr("class");

    }
}

function getParameterByName(name) {
    let url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    let regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

/* -----------------------------------------TURMAS--------------------------------------------- */

function carregadorCadTurmas() {
    //obtendo oficinas
    ajaxLoadGET('control/main.php?req=selectOficina', jsonParseOficinasTurma, selectOficinaTurma);
    //obtendo predios
    ajaxLoadGET('control/main.php?req=selectPredio', jsonParseNomePredios, selectPredioTurma);
    //obtendo professores
    ajaxLoadGET('control/main.php?req=selectUsuario&nivel=professor', jsonParseProfessorTurma, selectProfTurma);
    //carregando previamente os itens cadastrados no primeiro predio
    SalaFromPredioId();
    disponibilidade();

}

function disponibilidade() {
    var identificador = parseInt(selectSalaTurma.val());
    ajaxLoadGET('control/main.php?req=selectHorario&id=' + identificador, criaGrade, horariosTurma);
}

function jsonParseOficinasTurma(resposta, corpo) {
    corpo.empty();
    var objJson = JSON.parse(resposta);
    for (var i in objJson) {
        corpo.append('<option value="' + objJson[i].id_oficina + '">' + objJson[i].nome + '</option>');
    }
    if (objJson.length < 1) {
        corpo.append('<option value="-1">Nenhuma Oficina cadastrada</option>');
    }
}

function SalaFromPredioId() {
    let identificador = parseInt(selectPredioTurma.val());
    ajaxLoadGET('control/main.php?req=selectSalaByPredioId&id=' + identificador, jsonParseSalasTurma, '#carregandoSalas');
}

function jsonParseSalasTurma(resposta) {
    cp = $('#selectSala');
    cp.empty();
    var objJson = JSON.parse(resposta);
    for (var i in objJson) {
        cp.append('<option value="' + objJson[i].id_sala + '">' + objJson[i].nome + '</option>');
    }
    disponibilidade();
}



function jsonParteHorariosDisponiveis(resposta, corpo) {
    let objJson = JSON.parse(resposta);
    for (let i in objJson) {
        corpo.append(
            '<tr>\n' +
            '                    <th class="col-sm-2 col-md-2">' + objJson[i].inicio + ' ás ' + objJson[i].fim + '</th>\n' +
            '                    <td class="col-sm-2 col-md-2">' + isAtivoX(objJson[i].segunda, objJson[i]) + '</td>\n' +
            '                    <td class="col-sm-2 col-md-2">' + isAtivoX(objJson[i].terca, objJson[i]) + '</td>\n' +
            '                    <td class="col-sm-2 col-md-2">' + isAtivoX(objJson[i].quarta, objJson[i]) + '</td>\n' +
            '                    <td class="col-sm-2 col-md-2">' + isAtivoX(objJson[i].quinta, objJson[i]) + '</td>\n' +
            '                    <td class="col-sm-2 col-md-2">' + isAtivoX(objJson[i].sexta, objJson[i]) + '</td>\n' +
            '                </tr>');
    }
    if (objJson.length < 1) {
        corpo.append(
            '<tr>\n' +
            '                    <th class="col-sm-2 col-md-2">--:-- - --:--</th>\n' +
            '                    <td class="col-sm-2 col-md-2">Nenhuma turma</td>\n' +
            '                    <td class="col-sm-2 col-md-2">cadastrada</td>\n' +
            '                    <td class="col-sm-2 col-md-2">nesta</td>\n' +
            '                    <td class="col-sm-2 col-md-2">sala</td>\n' +
            '                    <td class="col-sm-2 col-md-2"><span class="fa fa-smile-o"></span></td>\n' +
            '                </tr>');
    }
}

function jsonParseProfessorTurma(resposta, corpo) {
    corpo.empty();
    let objJson = JSON.parse(resposta);
    for (let i in objJson) {
        corpo.append('<option value="' + objJson[i].id_pessoa + '">' + objJson[i].nome + '</option>');
    }
}

function isAtivoX(num, obj) {
    if (num === '1') return obj.oficina;
    else return " ";
}

function parsePeriodoText(resposta, corpo) {
    let json = JSON.parse(resposta);
    $('#anoAtual').append(json.ano);
    $('#periodoAtual').append(json.periodo);
}

function parsePeriodosSelect(resposta, corpo, funcaoEncadeada) {
    let objJson = JSON.parse(resposta);
    let opcoes = '';
    for (i in objJson) {
        if (i == objJson.length - 1) opcoes += '<option value="' + objJson[i].id_tempo + '" selected="selected">';
        else opcoes += '<option value="' + objJson[i].id_tempo + '">';
        opcoes += objJson[i].ano + ' - ' + objJson[i].periodo + '</option>';
    }
    corpo.append(opcoes);
    //aqui executamos uma funcao 5ms apos o parse de periodo
    setTimeout(funcaoEncadeada, 5);
}

/*-------------------------ALUNOS---------------------------*/
function parseTurmasAtivas(resposta, corpo) {
    let objJson = JSON.parse(resposta);
    for (let i in objJson) {
        corpo.append(
            '<option value=' + objJson[i].id_turma + ' >' + objJson[i].oficina + " - " + objJson[i].turma + '</option>'
        );
    }
    if (objJson.length < 1) {
        corpo.append(
            '<option value="0" selected="selected" disabled="disabled">Nenhuma Turma Disponível</option>'
        );
    }
    obterAlunos();
}

function obterAlunos() {
    let turma = $('#turma').val();
    if (turma != null) $('#gerarPresenca').attr('href', 'control/main.php?req=listaPresenca&id=' + turma);
    else $('#gerarPresenca').attr('disabled', 'disabled');
    ajaxLoadGET('control/main.php?req=selectAlunosByTurmaId&id=' + turma, parseAlunos, '#alunos');
}

function parseAlunos(resposta, corpo) {
    let objJson = JSON.parse(resposta);
    let listaAlunos = '';
    let listaEspera = '';
    let objEspera = [];
    let listaTrancados = '';
    let num = 0;
    for (let i in objJson) {

        if (objJson[i].trancado === '0' && objJson[i].lista_espera === '0') {
            num++;
            listaAlunos +=
                '<tr>\n' +
                '     <td>'+num+' </td>\n' +
                '     <td style="text-transform: capitalize;">' + objJson[i].nome + " " + objJson[i].sobrenome + '</td>\n' +
                '     <td>' + calculaIdade(objJson[i].data_nascimento) + ' anos</a></td>\n' +
                '     <td><a href="javascript:func()" onclick="confirmacaoTrancarMatricula(' + objJson[i].id_aluno + ')" class="btn btn-primary trancar">Trancar Matricula</a></td>\n' +
                '</tr>';
        } else if (objJson[i].trancado === '1') {
            listaTrancados +=
                '<tr>\n' +
                '     <td> </td>\n' +
                '     <td style="text-transform: capitalize;">' + objJson[i].nome + " " + objJson[i].sobrenome + '</td>\n' +
                '     <td>' + calculaIdade(objJson[i].data_nascimento) + ' anos</a></td>\n' +
                '     <td>Matricula Trancada</a></td>\n' +
                '</tr>';
        } else {
            objEspera.push(objJson[i]);
        }
    }

    //Ordenando por chegada
    objEspera.sort(function (a, b) {
        return a.id_aluno - b.id_aluno;
    });
    // agora inserindo os alunos da espera
    for (let j in objEspera) {
        listaEspera +=
            '<tr>\n' +
            '     <td>' + (parseInt(j) + 1) + '</td>\n' +
            '     <td style="text-transform: capitalize;">' + objEspera[j].nome + " " + objEspera[j].sobrenome + '</td>\n' +
            '     <td>' + calculaIdade(objJson[j].data_nascimento) + ' anos</td>\n' +
            '     <td></a></td>\n' +
            '</tr>';
    }

    let listaVazia =
        '<tr>\n' +
        '                    <td></td>\n' +
        '                    <td  style="font-size: x-large; font-weight: bold"><span class="fa fa-frown-o"></span> NÃO EXISTEM ALUNOS NESTA CATEGORIA</td>\n' +
        '                    <td></td>\n' +
        '                    <td></td>\n' +
        '                </tr>';
    if (listaAlunos.length < 2) listaAlunos = listaVazia;
    if (listaEspera.length < 2) listaEspera = listaVazia;
    if (listaTrancados.length < 2) listaTrancados = listaVazia;
    corpo.append(listaAlunos);
    $('#listaEspera').empty().append(listaEspera);
    $('#matTrancada').empty().append(listaTrancados);
}

/*------------------------------------------------INSERIR-ALUNO-EM-TURMA-------------------------*/
function obterInfoTurma() {
    let idTurma = $('#turma').val();
    ajaxLoadGET('control/main.php?req=selectTurmaById&id=' + idTurma, parseTurmaInfo, '#diaTurma');

    function parseTurmaInfo(resposta) {
        let json = JSON.parse(resposta);
        if (json[0].requisito.length < 2) json[0].requisito = 'Nenhum';
        $('#diaTurma').append(getDiaSemana(json[0]));
        $('#horarioTurma').empty().append(json[0].inicio.slice(0, 5) + 'h ás ' + json[0].fim.slice(0, 5) + 'h');
        $('#preReq').empty().append(json[0].requisito);
        $('.vagas').empty().append(parseInt(json[0].num_vagas) - parseInt(json[0].ocupadas));
    }

    //apos trocar de turma devemos desmarcar os alunos da turma anterior
    Desmarcar();
}

function parseTurmasComVagas(resposta, corpo, funcaoEncadeada) {
    var objJson = JSON.parse(resposta);
    for (i in objJson) {
        corpo.append(
            '<option value=' + objJson[i].id_turma + ' >' + objJson[i].oficina + " - " + objJson[i].turma + '</option>'
        );

    }
    funcaoEncadeada();
}


function getCandidatosByName() {
    let nome = $('#searchNames').val();
    let url = 'control/main.php?req=selectUsuario&nome=' + nome + '&pagina=' + pagina;
    ajaxLoadGET(url, parseCandidatos, '#tcandidatos');
    function parseCandidatos(resposta, corpo) {
        let objJson = JSON.parse(resposta);
        if (objJson.length === 0) {
            corpo.append(
                '<tr>' +
                '<td style="font-size: x-large; font-weight: bold"><span class="fa fa-frown-o" ></span> ALUNO NÃO ENCONTRADO</td>  </td>' +
                '<td class=\'col-md-1\'></td>' +
                '<td class=\'col-md-1\'>Tente pelo nome completo</td>' +
                '<td class=\'col-md-1\'></td>' +
                '</tr>');
        } else {
            for (i in objJson) {
                if (objJson[i].excluido == 1) continue;//removendo usuarios desativados
                corpo.append(
                    '<tr>' +
                    '<td class=\'col-md-5\'> ' + objJson[i].nome + ' ' + objJson[i].sobrenome + '</td>' +
                    '<td class=\'col-md-1\'> ' + getNVacesso(objJson[i].nv_acesso) + '</td>' +
                    '<td class=\'col-md-1\'> ' + calculaIdade(objJson[i].data_nascimento) + '</td>' +
                    '<td class=\'col-md-1\'> <input type="checkbox" name="aluno_id[]" onclick="contagem();" value="' + objJson[i].id_pessoa + '"> </td>' +
                    '</tr>');
            }
        }
    }
}

function Desmarcar() {
    $("input[name='aluno_id[]']").each(function () {
        $(this).removeAttr("checked");
    })
    contagem();
}

function contagem() {
    let checkbox = $('input:checkbox[name^=aluno_id]:checked');
    $('.selecionados').empty().append(checkbox.length);
}

function calculaIdade(nascimento) {
    nascimento = new Date(nascimento);
    let hoje = new Date();
    return Math.floor(Math.ceil(Math.abs(nascimento.getTime() - hoje.getTime()) / (1000 * 3600 * 24)) / 365.25);
}

/*------------------------------------------------USUARIOS------------------------------*/
var usuarioJson;

function jsonParseInfoPessoa(json) {
    let objJson = JSON.parse(json);
    usuarioJson = objJson;
    nome = objJson[0].nome;
    $('.nome').append(nome);
    let controleConta = $('#bloqConta');

    if (objJson[0].excluido == 1) {
        $('#nomeLabel').append(nome + "- Usuário Desativado");
        //adicionando botao de reativar
        controleConta.append('<a href="control/main.php?req=ativaConta&id=' + identificador + '" class="btn btn-primary">Reativar conta</a>');

    } else {
        //botao de desativar
        $('#nomeLabel').append(nome);
        controleConta.append('<a href="control/main.php?req=desativaConta&id=' + identificador + '" class="btn btn-danger">Desativar conta</a>');
    }

    $('#sobrenome').append(objJson[0].sobrenome);
    //formatando data de nascimento objJson[0].data_nascimento
    let nascFormatada = objJson[0].data_nascimento.split('-');
    nascFormatada = nascFormatada[2] + ' / ' + nascFormatada[1] + ' / ' + nascFormatada[0];
    let idade = calculaIdade(objJson[0].data_nascimento);
    $('#nasc').append(nascFormatada+'<br>Idade: '+idade + ' Anos');
    if (objJson[0].menor_idade === "1") {
        loadMenor();
        $('#maiorIdade').attr('hidden',true);
    } else {
        loadContato(identificador);
        loadEnd(identificador);
        loadDocument();
        loadDepententes();
        loadLogin(identificador);
    }
    if (objJson[0].ruralino === "1") {
        loadRuralino();
    } else {
        btnInsertRuralino();
    }
    if (objJson[0].excluido == '0') addBtnEdicaoPessoa();
}

function loadLogin(id) {
    $('#altSenha').removeAttr('hidden');
    ajaxLoadGET('control/main.php?req=selectLoginUser&id=' + id, parseLogin, '.carr');

    function parseLogin(resposta) {
        let objJson = JSON.parse(resposta);
        $('#login').empty().append(objJson[0].usuario);
    }

}

function loadMenor() {
    $('#menorIdade').removeAttr("hidden");
    ajaxLoadGET('control/main.php?req=selectResponsavelByMenorId&id=' + identificador, parseMenor, '.carr');

    function parseMenor(json, corpo) {
        var objJson = JSON.parse(json);
        $('#menorIdade').removeAttr("hidden");
        $('#respnome').append(objJson[0].nome);
        $('#respsobrenome').append(objJson[0].sobrenome);
        $('#respLink').attr('href','index.php?pag=Info.Pessoa&id='+objJson[0].responsavel_id);
        $('#parentesco').append(objJson[0].parentesco);

        loadContato(objJson[0].responsavel_id);
        loadEnd(objJson[0].responsavel_id);
    }
}

function loadRuralino() {
    //adicionamos o botão editar
    $('#ruraLabel').append('&nbsp; <button id="btnDeps" onclick="editaRuralino()" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></button>');
    $('#ruralinoConteudo').removeAttr("hidden");
    ajaxLoadGET('control/main.php?req=selectRuralinoByPessoaId&id=' + identificador, parseRuralino, '.carr');

    function parseRuralino(json, corpo) {
        var objJson = JSON.parse(json);
        $('#matricula').append(objJson[0].matricula);
        $('#curso').append(objJson[0].curso);
        $('#bolsista').append(isAtivo(objJson[0].bolsista));
    }
}

function btnInsertRuralino() {
    //adicionamos o botão Adicionar
    $('#ruraLabel').append('&nbsp; <button id="btnDeps" onclick="insertRuralino()" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></button>');
}

function insertRuralino() {
    let conteudo = $('#ruralinoConteudo');
    conteudo.empty();
    conteudo.removeAttr('hidden');
    conteudo.append(
        '<form action="control/main.php?req=insertRuralino&id=' + identificador + '" method="POST">' +
        '<p>Curso: <input type="text" name="curso" placeholder="Educação Física" required="required"></p>' +
        '<p>Matricula: <input type="number" name="matricula" placeholder="2018180188" ></p>' +
        '<div class="form-group">\n' +
        '                <label class="control-label" >Bolsista do CAC?</label>\n' +
        '                <div class="">\n' +
        '                    <label class="radio-inline">\n' +
        '                        <input type="radio" name="bolsista" value="1">SIM\n' +
        '                    </label>\n' +
        '                    <label class="radio-inline">\n' +
        '                        <input type="radio" name="bolsista" value="0">NÃO\n' +
        '                    </label>\n' +
        '                </div>\n' +
        '            </div>' +
        '<br/><input type="submit" class="btn btn-primary" value="Gravar"/>' +
        '</form>'
    );
}

function editaRuralino() {
    let curso = $('#curso').text();
    let matricula = $('#matricula').text();
    let conteudo = $('#ruralinoConteudo');
    conteudo.empty();
    conteudo.append(
        '<form action="control/main.php?req=updateRuralino&id=' + identificador + '" method="POST">' +
        '<p>Curso: <input type="text" name="curso" value="' + curso + '" required="required"></p>' +
        '<p>Matricula: <input type="number" name="matricula" value="' + matricula + '" ></p>' +
        '<div class="form-group">\n' +
        '                <label class="control-label" >Bolsista do CAC?</label>\n' +
        '                <div class="">\n' +
        '                    <label class="radio-inline">\n' +
        '                        <input type="radio" name="bolsista" value="1">SIM\n' +
        '                    </label>\n' +
        '                    <label class="radio-inline">\n' +
        '                        <input type="radio" name="bolsista" value="0">NÃO\n' +
        '                    </label>\n' +
        '                </div>\n' +
        '            </div>' +
        '<br/><input type="submit" class="btn btn-primary" value="Gravar"/>' +
        '</form>'
    );
}

function loadContato(id) {
    ajaxLoadGET('control/main.php?req=selectTelefoneByPessoaId&id=' + id, parseContato, '#tels');

    function parseContato(json, corpo) {
        jsonContato = json;
        let objJson = JSON.parse(json);
        for (i in objJson) {
            corpo.append('<p>Contato (' + getTelType(objJson[i].tipo) + '): ' + objJson[i].contato + '</p>');
        }
    }

    function getTelType(num) {
        num = parseInt(num);
        switch (num) {
            case 1:
                return "celular";
            case 2:
                return "Whatsapp";
            case 3:
                return "Fixo";
            case 4:
                return "Recados";
            case 5:
                return "Email";
            default:
                return "...";
        }

    }
}

function loadEnd(id) {
    ajaxLoadGET('control/main.php?req=selectEndereco&id=' + id, parseEnd, '.carr');

    function parseEnd(json, corpo) {
        var objJson = JSON.parse(json);
        $('#rua').append(objJson[0].rua);
        $('#num').append(objJson[0].numero);
        $('#complemento').append(objJson[0].complemento);
        $('#bairro').append(objJson[0].bairro);
        $('#cidade').append(objJson[0].cidade);
        $('#estado').append(objJson[0].estado);

    }
}

function loadDocument() {
    ajaxLoadGET('control/main.php?req=selectDocumento&id=' + identificador, parseDocumento, '.carr');
    $('#documentos').removeAttr("hidden");

    function parseDocumento(resposta, corpo) {
        let json = JSON.parse(resposta);
        $('#tipoDoc').append(documenTipo(json[0].tipo_documento));
        $('#numDoc').append(json[0].numero_documento);
    }

    function documenTipo(num) {
        if (num === "1") return "Registro Geral (RG)";
        return "Passaporte";
    }
}

function loadDepententes() {
    ajaxLoadGET('control/main.php?req=selectDependentes&id=' + identificador, parseDependentes, '#dep');
    $('#dependentes').removeAttr("hidden");

    function parseDependentes(resposta, corpo) {
        let json = JSON.parse(resposta);
        for (i in json) {
            corpo.append('<p>Nome: <span class="depNome"> ' + json[i].nome + '</span>&nbsp;<span class="depsobrenome">' + json[i].sobrenome +
                '&nbsp; <a href="?pag=Meus-Dados&id=' + json[i].id_pessoa + '" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></a>' +
                '&nbsp; <a href="control/main.php?req=removeDependente&id=' + json[i].id_pessoa + '" class="btn btn-primary"><span class=\'glyphicon glyphicon-remove\'></span></a>' +
                '</span></p>');
        }
    }
}

/*------------------------------------------------EDITA USUARIO--------------------------*/
function addBtnEdicaoPessoa() {
    $('#dadosBasicos').append('&nbsp; <button id="btNome" onclick="editUsuarioNome()" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></button>');
    $('#contato').append('&nbsp; <button id="btnCont" onclick="editUsuarioContato()" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></button>');
    $('#endereco').append('&nbsp; <button id="btnEnd" onclick="editUsuarioEndereco()" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></button>');
    $('#docLabel').append('&nbsp; <button id="btnDoc" onclick="editUsuarioDocumento()" class="btn btn-primary"><span class=\'glyphicon glyphicon-pencil\'></span></button>');
    $('#dependentes h4').append('&nbsp; <button id="btnDeps" onclick="adicionaDependete()" class="btn btn-primary"><span class=\'glyphicon glyphicon-plus\'></span></button>');
}

function addMenor() {
    var quantidade = $('#qtd_menor').val();
    var divContent = $('#menor_de_idade');

    quantidade++;

    $(function () {
        $('<div class="aluno">' +
            '<h4> Dependente #' + quantidade + ' </h4><hr>\n' +
            '            <div class="form-group col-md-8 col-lg-push-2">\n' +
            '                <label class=" control-label" for="nome_menor' + quantidade + '">Nome</label>\n' +
            '                <div class="">\n' +
            '                    <input id="nome_menor0" name="nome_menor' + quantidade + '" type="text" placeholder="nome menor ' + quantidade + '"  class="form-control input-md" required="">\n' +
            '                </div>\n' +
            '            </div><br/>\n' +
            '\n' +
            '            <!-- Sobrenome do menor 0 -->\n' +
            '            <div class="form-group col-md-8 col-lg-push-2">\n' +
            '                <label class="control-label" for="sobrenome_menor' + quantidade + '">Sobrenome</label>\n' +
            '                <div class="">\n' +
            '                    <input id="sobrenome_menor0" name="sobrenome_menor' + quantidade + '" type="text" placeholder="sobrenome menor ' + quantidade + '"  class="form-control input-md" required="">\n' +
            '                </div>\n' +
            '            </div><br/>\n' +
            '\n' +
            '            <!-- Nascimento do menor ' + quantidade + ' -->\n' +
            '            <div class="form-group col-md-8 col-lg-push-2">\n' +
            '                <label class="control-label" for="nascimento_menor' + quantidade + '">Nascimento</label>\n' +
            '                <div class="">\n' +
            '                    <input id="nascimento_menor0" name="nascimento_menor' + quantidade + '" type="date" placeholder="dd/mm/aaaa"  class="form-control input-md" required="">\n' +
            '                </div>\n' +
            '            </div><br/>' +
            '</div>').appendTo(divContent);
        $('#qtd_menor').remove();

        $('<input type="hidden" name="qtd_menor" value=" ' + quantidade + '" id="qtd_menor">').appendTo(divContent);
    });
}

function editUsuarioNome() {
    $('#btNome').removeAttr("onclick");
    let dadosBasicos = $('#nomeNasc');
    let sobrenome = $('#sobrenome').text();
    let nasc = usuarioJson[0].data_nascimento;
    dadosBasicos.empty();
    // noinspection BadExpressionStatementJS
    dadosBasicos.append(
        `<form action="control/main.php?req=updateDadosBasicos&id=${identificador}" method="POST"><p>Nível de Acesso: <select id="nv_acesso" name="nv_acesso">
                        <option value=4>Visitante</option>
                        <option value=3>${CargoAluno} </option>
                         <option value=2>${CargoProf} </option>
                         <option value=1>${CargoAdm} </option>
                     </select></p><p>Nome: <input type='text' name='nome' value="${nome}" required="required"></p><p>Sobrenome: <input type="text" name="sobrenome" value="${sobrenome}" required="required"></p><p>Data nascimento: <input type="date" name="nascimento" value="${nasc}" required="required"> </p><br/><input type="submit" class="btn btn-primary" value="Gravar"/></form>`);
}

function editUsuarioContato() {
    $('#btnCont').removeAttr("onclick");
    let tels = $('#tels');
    let acm = '<form action="control/main.php?req=updateContato&id=' + identificador + '" method="POST">';
    let tipo = 0;
    tels.empty();
    if (jsonContato !== null) {
        jsonContato = JSON.parse(jsonContato);
            tipo = jsonContato[0].tipo;
            acm += `<p><input type="hidden" name="resp_tel_id" value="${jsonContato[0].id_contato}">Tel: <input type="number" name="resp_tel" value="${jsonContato[0].contato}" required="required">Tipo: <select id="resp_tel_type" name="resp_tel_type">
                        <option value="2" ${verTp(tipo, 2)}>Whatsapp</option>
                        <option value="1" ${verTp(tipo, 1)}>Celular</option>
                        <option value="3" ${verTp(tipo, 3)}>Fixo (residencial)</option>
                        <option value="4" ${verTp(tipo, 4)}>Recados</option>
                        <option value="5" ${verTp(tipo, 5)}>Email</option>
                    </select></p>`;
            if(jsonContato.length >1){
                acm += '<p><input type="hidden" name="resp_email_id" value="'+jsonContato[1].id_contato+'">' +
                    'Email: <input type="email" name="email" value="' + jsonContato[1].contato + '" required="required">' +
                '</p>';
            }
    }
    acm += '<br/><input type="submit" class="btn btn-primary" value="Gravar"/></form>';
    tels.append(acm);

    function verTp(tp, val) {
        if (parseInt(tp) === parseInt(val)) return 'selected';
        return '';
    }
}

function editUsuarioEndereco() {
    $('#btnEnd').removeAttr("onclick");
    let end = $('#end');
    //Obtendo dados atuais
    let rua = $('#rua').text();
    let numero = $('#num').text();
    let complemento = $('#complemento').text();
    let bairro = $('#bairro').text();
    let cidade = $('#cidade').text();
    let estado = $('#estado').text();
    end.empty();
    //Inserindo campos de edicao
    end.append(
        '<form action="control/main.php?req=updateEndereco&id=' + identificador + '" method="POST">' +
        '<p>Rua: <input type=\'text\' name=\'rua\' value="' + rua + '" required="required"></p>' +
        '<p>Numero: <input type="number" name="numero" value="' + numero + '" required="required"></p>' +
        '<p>Complemento: <input type="text" name="complemento" value="' + complemento + '" ></p>' +
        '<p>Bairro: <input type="text" name="bairro" value="' + bairro + '" required="required"></p>' +
        '<p>Cidade: <input type="text" name="cidade" value="' + cidade + '" required="required"></p>' +
        '<p>Estado: <input type="text" name="estado" value="' + estado + '" required="required"></p>' +
        '<br/><input type="submit" class="btn btn-primary" value="Gravar"/>' +
        '</form>');
}

function editUsuarioDocumento() {
    $('#btnDoc').removeAttr("onclick");
    let docs = $('#docs');
    //obtendo dados atuais
    let tipo = $('#tipoDoc').text();
    let numero = $('#numDoc').text();
    docs.empty();

    docs.append(
        '<form action="control/main.php?req=updateDoc&id=' + identificador + '" method="POST">' +
        '<p>Tipo: ' +
        '   <select id="doc_type" name="doc_type">\n' +
        '       <option value="1">Registro geral (RG)</option>\n' +
        '       <option value="2">Passaporte</option>\n' +
        '   </select>' +
        '</p>' +
        '<p>Numero: <input type="number" name="doc_number" value="' + numero + '" required="required"></p>' +
        '<br/><input type="submit" class="btn btn-primary" value="Gravar"/>' +
        '</form>');
}

function adicionaDependete() {
    $('#gravaMenor').attr('type', 'submit');
    $('#label_parentesco').removeAttr('hidden');
    //requisitamos adicionar dependente no id do usuario atual
    $('#formDependentes').attr('action', 'control/main.php?req=addDependente&id=' + identificador);
    addMenor();
}

/*------------------------------VERIFICAÇÃO DE FORMULARIO DE ENTRADA----------------*/
var userDisponivel = false;
var senhaOk = false;

function verificadores() {
    let btn = $('#btn-senha');
    if (userDisponivel && senhaOk) {
        btn.attr('type', 'submit');
    } else {
        alert("Dados Para Acesso a Conta precisam ser corrigidos");
    }
}

function verificaSenha() {
    let erro = $('#error-senha').empty();
    if ($('#senha').val() === $('#repsenha').val()) {
        senhaOk = true;
    } else {
        erro.append('Senhas não conferem');
        senhaOk = false;
    }
}

function verificaUpdateSenha() {
    if (senhaOk) {
        $('#btn-senha').attr('type', 'submit');
    } else {
        alert('Senhas não conferem');
    }
}

function verificaUsuarioDuplicado() {
    let usuario = $('#usuario').val();
    ajaxLoadGET('control/main.php?req=verificaUser&nome=' + usuario, parseUserDuplicado, '#error-user');

    function parseUserDuplicado(resposta) {
        let json = JSON.parse(resposta);
        let msg = $('#error-user').empty();
        if (json[0].usuario != 0) {
            msg.append('Usuário indisponivel');
            userDisponivel = false;
        } else {
            msg.append('Usuário OK');
            userDisponivel = true;
        }
    }
}

/*-------------------------------GERENCIAMENTO DE USUARIOS ----------------------------------*/
function jsonParseUsuarios(resposta, corpo) {
    var objJson = JSON.parse(resposta);
    let string = '';
    for (var i in objJson) {
        string +=
            '<tr>\n';
        if (objJson[i].excluido == '1') {
            string += '     <td class="col-md-4" style="text-transform: capitalize;color: #b92c28;">' + objJson[i].nome + " " + objJson[i].sobrenome + ' - Desativado</td>\n';
        } else {
            string += '     <td class="col-md-4" style="text-transform: capitalize;">' + objJson[i].nome + " " + objJson[i].sobrenome + '</td>\n';
        }
        string += '     <td class="col-md-2">' + getNVacesso(objJson[i].nv_acesso) + '</td>\n' +
            '     <td class="col-md-2">' + isAtivo(objJson[i].menor_idade) + '</td>\n' +
            '     <td class="col-md-2">' + isAtivo(objJson[i].ruralino) + '</td>\n' +
            '<td  class="col-md-2"> <a href="?pag=Info.Pessoa&id=' + objJson[i].id_pessoa + '" class="btn btn-primary"><span class=\'glyphicon glyphicon-eye-open\'></span></a> </td>' +
            '</tr>';
        corpo.empty();
        corpo.append(string);
    }
    if (objJson.length < 1) {
        corpo.append(
            '<tr>\n' +
            '\n' +
            '                    <td style="font-size: x-large; font-weight: bold"><span class="fa fa-frown-o" ></span> NÃO EXISTEM ALUNOS NESTA CATEGORIA</td>\n' +
            '                    <td></td>\n' +
            '                    <td></td>\n' +
            '                    <td></td>\n' +
            '                    <td></td>\n' +
            '                    <td></td>\n' +
            '                </tr>');
    }
}

function slideTo(value) {
    document.querySelector('#' + value).scrollIntoView({
        behavior: "smooth",
        block: "start",
    });
}


/* PAGINADOR */
function trocaPag(pag) {
    if (pag <= totalpaginas && pag > 0) pagina = pag;
    $('#paginic').empty().val(pagina);
    carregaUsuarios();
}

function trocaReq(req) {
    nivel = req;
    pagina = 1;//reseta o numero de paginas
    //recarregando o paginador
    trocaPag(pagina);
    carregaUsuarios();
    ajaxLoadGET('control/main.php?req=getPageNumber&nivel=' + nivel, setPaginador, '#cadastrosRuralino');
}

function carregaUsuarios() {
    ajaxLoadGET('control/main.php?req=selectUsuario&nivel=' + nivel + '&pagina=' + pagina, jsonParseUsuarios, '#usuarios');
}

function pesquisa() {
    $('#bt-pesquisa').attr("disabled", "disabled");//importante para nao envia 2 requisicoes iguais
    let nome = $('#searchName').val();
    ajaxLoadGET('control/main.php?req=selectUsuario&nivel=' + nivel + '&nome=' + nome, jsonParseUsuarios, '#usuarios');
}


function pesquisaCad() {
    $('#bt-cad').attr("disabled", "disabled");//importante para nao envia 2 requisicoes iguais
    getCandidatosByName();
}


function limpaPesquisa(btn,search) {
    $('#'+btn).removeAttr("disabled");
    $('#'+search).val('');
}

function setPaginador(resposta) {
    let objJson = JSON.parse(resposta);
    let registros = objJson[0].total;
    totalpaginas = parseInt(registros / 25);
    if (totalpaginas == 0) totalpaginas = 1;
    $('#paginic').empty().append(pagina);
    $('#pagfim').empty().append(totalpaginas);
    $('#goLast').empty().append('<a href="#" onclick="trocaPag(' + totalpaginas + ')" id="goLast">&raquo;</a>');
}