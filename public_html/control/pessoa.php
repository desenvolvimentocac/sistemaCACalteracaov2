<?php
/**
 * Created by Filipe
 * Date: 28/04/18
 * Time: 15:39
 */
include_once '../model/DatabaseOpenHelper.php';
include_once 'constantes.php';
include_once 'mensagem.php';

class pessoa{
    private $db;

    //Dados basicos
    private $nome,$sobrenome,$nascimento,$nv;
    //Documentos
    private $docNumber,$docType;
    //contato
    private $respTel,$respTelType,$email;
    //Endereco
    private $rua,$numero,$complemento,$bairro,$cidade,$estado;
    //ruralino
    private $matricula,$curso,$bolsista,$ruralino;
    //Caso menor
    private $responsavelID,$parentesco = INVALIDO;//inicializamos como invalido
    //login
    private $user,$senha;

    public function __construct(){
        $this->db = new Database();
    }

//------------Recebe Dados do Form -------------------------------------------------------------------------------------

    private function receiveAccessLevel(){
        if (isset($_SESSION['NIVEL']) and $_SESSION['NIVEL'] == ADMINISTRADOR) {//se não for adm o nivel é automaticamente aluno
            $this->nv = isset($_POST['nv_acesso']) ? $_POST['nv_acesso'] : ALUNO;//se der erro fica como visitante
        } else {
            $this->nv = ALUNO;
        }
    }

    private function receiveDadosBasicos(){
        $this->nome = isset($_POST['nome']) ? $_POST['nome'] : INVALIDO;
        $this->sobrenome = isset($_POST['sobrenome']) ? $_POST['sobrenome'] : INVALIDO;
        $this->nascimento = isset($_POST['nascimento']) ? $_POST['nascimento'] : INVALIDO;
    }

    private function receiveEndereco(){
        $this->rua = isset($_POST['rua']) ? $_POST['rua'] : INVALIDO;
        $this->numero = isset($_POST['numero']) ? $_POST['numero'] : INVALIDO;
        $this->complemento = isset($_POST['complemento']) ? $_POST['complemento'] : INVALIDO;
        $this->bairro = isset($_POST['bairro']) ? $_POST['bairro'] : INVALIDO;
        $this->cidade = isset($_POST['cidade']) ? $_POST['cidade'] : INVALIDO;
        $this->estado = isset($_POST['estado']) ? $_POST['estado'] : INVALIDO;
    }

    private function receiveDocumento(){
        $this->docType = isset($_POST['doc_type']) ? $_POST['doc_type'] : INVALIDO;
        $this->docNumber = isset($_POST['doc_number']) ? $_POST['doc_number'] : INVALIDO;
    }

    private function receiveContato(){
        $this->respTel = isset($_POST['resp_tel']) ? $_POST['resp_tel'] : INVALIDO;
        $this->respTelType = isset($_POST['resp_tel_type']) ? $_POST['resp_tel_type'] : INVALIDO;
        $this->email = isset($_POST['email']) ? $_POST['email'] : INVALIDO;
    }

    private function receiveRuralino(){
        $this->matricula = isset($_POST['matricula']) ? $_POST['matricula'] : INVALIDO;
        $this->curso = isset($_POST['curso']) ? $_POST['curso'] : INVALIDO;
        $this->bolsista = isset($_POST['bolsista']) ? $_POST['bolsista'] : NAO;
        //somente o ADM pode setar essa informação
    }

    private function receiveLogin(){
        $this->user = isset($_POST['usuario']) ? $_POST['usuario'] : INVALIDO;
        $this->senha = isset($_POST['senha']) ? $_POST['senha'] : INVALIDO;
    }

//---------------------------------------------Procedimento de cadastrar pessoa-----------------------------------------

    /**
     * Busca se existe um nome de usuario parecido com os ja cadastrado
     * @param $nomeDeUsuario
     * @return string
     * @throws Exception
     */
    public function verificaUsuarioDuplicado($nomeDeUsuario){
        return $this->db->select("count(*) as usuario","login","usuario like ?",array('%'.$nomeDeUsuario.'%'));
    }

    /**
     * um usuário pode cadastrar varios dependentes (menores de idade)
     * @throws Exception
     */
    public function setPessoa(){
        $this->receiveAccessLevel();
        $this->ruralino = isset($_POST['ruralino']) ? SIM : NAO;
        //------------------------Controle-----------------------------------------------
        if($this->cadastroIsBroken()){
            $this->redireciona();
            return;
        }
        //---------------------------------------------------------------------------------

        $this->responsavelID = $this->insertDadosBasicos($this->nome,$this->sobrenome,$this->nv,NAO,$this->ruralino,$this->nascimento);//recuperamos o ID do adulto cadastrado
        if($this->responsavelID == null || $this->responsavelID == INVALIDO){
            new mensagem(INSERT_ERRO,"Erro ao cadastrar");
            $this->redireciona();
            return;
        }
        $this->insertContato();
        $this->insertDocumento();
        $this->insertEndereco();
        if ($this->ruralino == SIM) $this->insertRuralino();
        $this->insertLogin();
        $this->insertMenor();

        //Se chegou até aqui então é sucesso
        new mensagem(SUCESSO,$this->nome." cadastrado com sucesso");
        $this->redireciona();
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function cadastroIsBroken(){
        //receber numero do documento, se for repetido não deixa cadastrar
        //Recebendo dados
        $this->receiveDadosBasicos();
        $this->receiveLogin();
        $this->receiveContato();
        $this->receiveDocumento();
        $this->receiveEndereco();

        //Verificando dados
        if($this->nome == INVALIDO || $this->sobrenome == INVALIDO){
            new mensagem(ERRO,"Erro na conexão, Dados Inválidos recebidos");
            return true;
        }

        if($this->user == INVALIDO){
            new mensagem(ERRO,"Erro na conexão, Dados Inválidos recebidos");
            return true;
        }
        if($this->ruralino == SIM){
            $this->receiveRuralino();
            if($this->matricula == INVALIDO || $this->curso == INVALIDO){
                new mensagem(ERRO,"Erro na conexão, Dados Inválidos recebidos");
                return true;
            }

        }

        if($this->rua == INVALIDO){
            new mensagem(ERRO,"Erro na conexão, Dados Inválidos recebidos");
            return true;
        }
        //ultima verificação "Pessoa se cadastrando 2 vezes ??"
        $documentos = json_decode($this->db->select("numero_documento","documento"));
        for ($i=0;$i<sizeof($documentos);$i++){
            if($documentos[$i]->numero_documento == $this->docNumber){
                new mensagem(ERRO,"Cadastro já existente no sistema, caso precise recuperar seu acesso entre em contato com o CAC");
                return true;
            }
        }
        return false;
    }

    /**
     * @throws Exception
     * @return bool Inserido ou nao no banco
     */
    function insertMenor(){
        $verificador = false;
        //verificando se o responsavel id esta habilidato a ter dependentes
        $ismenor = $this->db->select("menor_idade","pessoa","id_pessoa = ?",array($this->responsavelID));
        $ismenor = $ismenor[0]->menor_idade;
        if (isset($_POST['qtd_menor']) and $_POST['qtd_menor'] > 0 and $ismenor != SIM) {
            $this->parentesco = isset($_POST['parentesco']) ? $_POST['parentesco'] : INVALIDO;
            for ($i = 0; $i < $_POST['qtd_menor']; $i++) {
                $nomeAtual = 'nome_menor' . ($i + 1);
                $sobrenomeAtual = 'sobrenome_menor' . ($i + 1);
                $nascimentoAtual = 'nascimento_menor' . ($i + 1);
                //-----------recuperando dados
                $nomeMenor = isset($_POST[$nomeAtual]) ? $_POST[$nomeAtual] : INVALIDO;
                $sobrenomeMenor = isset($_POST[$sobrenomeAtual]) ? $_POST[$sobrenomeAtual] : INVALIDO;
                $nascimentoMenor = isset($_POST[$nascimentoAtual]) ? $_POST[$nascimentoAtual] : INVALIDO;

                $menorID = $this->insertDadosBasicos($nomeMenor,$sobrenomeMenor,ALUNO,SIM,NAO,$nascimentoMenor);

                $verificador = $this->insertRelacaoDependente($menorID);
                if($verificador == false) return false;//se nao inseriu corretamente ja sai do loop
            }
        }
        return $verificador;
    }

//---------------------------------------------Insere dados no banco----------------------------------------------------

    /**
     * @param $nome
     * @param $sobrenome
     * @param $nv
     * @param $isMenor
     * @param $ruralino
     * @param $nascimento
     * @return bool| integer - Retorna o ID do dado inserido ou falso se der erro
     * @throws Exception
     */
    private function insertDadosBasicos($nome,$sobrenome,$nv,$isMenor,$ruralino,$nascimento){
        $params = array($nome,$sobrenome,$nv,$isMenor,$ruralino,$nascimento);
        $this->db->insert("nome,sobrenome,nv_acesso,menor_idade,ruralino,data_nascimento","pessoa",$params);
        return $this->db->getLastId();
    }

    /**
     * @throws Exception
     */
    private function insertDocumento(){
        $params = array($this->responsavelID,$this->docNumber,$this->docType);
        $this->db->insert("pessoa_id,numero_documento,tipo_documento","documento",$params);
    }
	/**
     * @throws Exception
     */
    private function insertContato(){
    	$params = array($this->responsavelID,$this->respTel,$this->respTelType);
    	$this->db->insert("pessoa_id,contato,tipo_contato","contato",$params);
    	if($this->email!= INVALIDO){//se email foi setado então insere um contato
            $params = array($this->responsavelID,$this->email,5);
            $this->db->insert("pessoa_id,contato,tipo_contato","contato",$params);
        }
    }

    /**
     * @throws Exception
     */
    private function insertEndereco(){
        $params = array($this->responsavelID,$this->rua,$this->numero,$this->complemento,$this->bairro,$this->cidade,$this->estado);
        $this->db->insert("pessoa_id,rua,numero,complemento,bairro,cidade,estado","endereco",$params);
    }

    /**
     * @throws Exception
     */
    private function insertRuralino(){
        $params = array($this->responsavelID,$this->matricula,$this->curso,NAO);
        return $this->db->insert("pessoa_id,matricula,curso,bolsista","ruralino",$params);
    }

    /**
     * @return void - Retorna o ID do dado inserido ou falso se der erro
     * @throws Exception
     */
    private function insertLogin(){
        if($this->user == INVALIDO){
            new mensagem(ERRO,"Erro na conexão, Dados Inválidos recebidos");
            $this->redirecionaPagAnterior();
        }
        $params = array($this->responsavelID,$this->user,$this->make_hash($this->senha));
        $this->db->insert("pessoa_id,usuario,senha","login",$params);
    }

    /**
     * @param $depAtualID - O dependente Atual
     * @return bool
     * @throws Exception
     */
    private function insertRelacaoDependente($depAtualID){
        $paramns = array($depAtualID,$this->responsavelID,$this->parentesco);
        return $this->db->insert("pessoa_id,responsavel_id,responsavel_parentesco","menor_idade",$paramns);
    }

    /**
     * @param $str string - senha a ser Encriptada
     * @return bool|string - senha encriptada
     */
    private function make_hash($str){
        return password_hash($str, PASSWORD_BCRYPT);

    }

//--------------------------------------RECUPERA DO BANCO---------------------------------------------------------------
    private function hasPermission($pessoaId){
        //ou pessoa id = dependente id da pessoa
        $dependentes = json_decode($this->getDependentes($_SESSION['ID']));

        if($pessoaId == $_SESSION['ID'] or $_SESSION['NIVEL'] == ADMINISTRADOR){
            return true;
        }else{
            //verificando dependentes
            for($i=0;$i<sizeof($dependentes);$i++){
                if($pessoaId === $dependentes[$i]->id_pessoa) return true;//se for dependente entao tem permissao
            }
            new mensagem(ERRO,"Permissão Insuficiente para a operação requerida");
            $this->redirecionaPagAnterior();
            return false;
        }
    }

    public function hasSelectPermission(){
        if(isset($_SESSION['NIVEL']) and $_SESSION['NIVEL'] == ADMINISTRADOR) return;
        else $this->redireciona();
    }

    /**
     * Retorna o numero de registros dado um filtro
     * @throws Exception
     */
    public function getPageNumber(){
        $nv = isset($_GET['nivel']) ? $_GET['nivel'] : 'selectTodos';
        $pageNumber = "";
        switch ($nv){
            case 'todos':
                $pageNumber = $this->db->select("count(*) as total","pessoa",null,null,"nome",ASC);
                break;
            case 'candidato':
                $pageNumber = $this->db->select("count(*) as total","pessoa","nv_acesso >= ?",array(3),"nome",ASC);
                break;
            case 'professor':
                $pageNumber = $this->db->select("count(*) as total","pessoa","nv_acesso <= ?",array(2),"nome",ASC);
                break;
            case 'administrador':
                $pageNumber = $this->db->select("count(*) as total","pessoa","nv_acesso = ?",array(1),"nome",ASC);
                break;
        }
        return $pageNumber;
    }

    /**
     * Obtem todos os regitros utilizando um filtro de nome
     * @return string JSON
     * @throws Exception
     */
    public function getUsuarios(){
        $nivel = isset($_GET['nivel']) ? $_GET['nivel'] : 'todos';
        $primeiroNome = '%';
        $ultimoNome = '%';
        //IMPORTANTE verificar TAMANHO da string para nao gerar sobrecarga
        if (isset($_GET['nome']) && strlen($_GET['nome'])>0) {
            $nome = $_GET['nome'];
            $partes = explode(" ",$nome);

            $primeiroNome = '%'.$partes[0].'%';

            $ultimoNome = "";
            if(sizeof($partes)>1) {
                if(sizeof($partes)>2){unset($partes[0]);unset($partes[1]);} else unset($partes[0]);//se tem mais de 2 nomes descartamos os 2 primeiros pois pode ser nome composto
                $ultimoNome = implode(" ",$partes);
            }
            $ultimoNome = '%'.$ultimoNome.'%';

            $pagna = null;
            $registros = null;
            $base = null;
        } else {
            $pagna = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
            if($pagna == 1) $base = 1;//deve ser 1 para nao retornar todos os registros
            else $base = REGISTROS*$pagna;
        }

        //-------------------------QUERY-------------------------------
        $jsonUser = "";
        $projecao = "id_pessoa,nome,sobrenome,nv_acesso,menor_idade,ruralino,data_nascimento,excluido";
        switch ($nivel){
            case 'todos':
                //Obtemos todos os Candidatos com left Join em Maior idade
                $jsonUser = $this->db->select($projecao,"pessoa","nome like ? and sobrenome like ?",array($primeiroNome,$ultimoNome),"nome",ASC,REGISTROS,$base);
                break;
            case 'candidato':
                //Obtemos todos os Candidatos com left Join em Maior idade
                $jsonUser = $this->db->select($projecao,"pessoa","nv_acesso >= ? and nome like ? and sobrenome like ?",array(3,$primeiroNome,$ultimoNome),"nome",ASC,REGISTROS,$base);
                break;
            case 'professor':
                if($_SESSION['NIVEL'] == ADMINISTRADOR){
                    //Obtemos todos os professores com left Join em Maior idade
                    $jsonUser = $this->db->select($projecao,"pessoa","nv_acesso <= ? and nome like ? and sobrenome like ?",array(2,$primeiroNome,$ultimoNome),"nome",ASC);

                }else{
                    $jsonUser = $this->db->select("id_pessoa,nome,sobrenome","pessoa","id_pessoa = ?",array($_SESSION['ID']));
                }
                break;
            case 'administrador':
                if($_SESSION['NIVEL'] == ADMINISTRADOR)
                $jsonUser = $this->db->select($projecao, "pessoa", "nv_acesso = ?", array(1));
                break;
        }

        return $jsonUser;
    }

    /**
     * Retorna os dados de uma pessoa dado seu Identificador
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getPessoaById($identificador){
        if($this->hasPermission($identificador)){
            return $this->db->select("nome,sobrenome,menor_idade,ruralino,data_nascimento,excluido","pessoa","id_pessoa = ?",array($identificador));
        }else{
            new mensagem(ERRO,"Voce não tem permissão para isso");
            return '';
        }

    }

    /**
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getLoginUser($identificador){
        if($this->hasPermission($identificador)){
            return $this->db->select("usuario","login","pessoa_id = ?",array($identificador));
        }else{
            return '';
        }
    }

    /**
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getRuralinoByPessoaId($identificador){
        return $this->db->select("matricula,curso,bolsista","ruralino","pessoa_id = ?",array($identificador));
    }

    /**
     * @param $identificador
     * @return string
     * @throws Exception
     */
    public function getResponsavelByMenorId($identificador){
        $projection = "responsavel_parentesco as parentesco,nome,sobrenome,menor_idade.responsavel_id";
        $whereClause = "menor_idade.pessoa_id = ? and responsavel_id = pessoa.id_pessoa";
        return $this->db->select($projection,"menor_idade,pessoa",$whereClause,array($identificador));
    }

    /**
     * @param $pessoaId
     * @return string
     * @throws Exception
     */
    public function getTelefone($pessoaId){
        return $this->db->select("id_contato,contato,tipo_contato as tipo","contato","pessoa_id = ?",array($pessoaId));
    }

    /**
     * @param $pessoaId
     * @return string
     * @throws Exception
     */
    public function getEndereco($pessoaId){
        return $this->db->select("rua,numero,complemento,bairro,cidade,estado","endereco","pessoa_id = ?",array($pessoaId));
    }

    /**
     * @param $pessoaId
     * @return string
     * @throws Exception
     */
    public function getDocumento($pessoaId){
        return $this->db->select("numero_documento,tipo_documento","documento","pessoa_id = ?",array($pessoaId));
    }

    /**
     * @param $responsavelId
     * @return string
     */
    public function getDependentes($responsavelId){
        try {
            return $this->db->select("id_pessoa,nome,sobrenome,responsavel_parentesco", "menor_idade,pessoa", "responsavel_id = ? and pessoa_id = id_pessoa and excluido = 0", array($responsavelId));
        } catch (Exception $e) {
            return "";
        }
    }

//---------------------------------------------UPDATE-------------------------------------------------------------------

    /**
     * Essa funcao adiciona um dependente para um usuario definido
     * @param $respId
     * @throws Exception
     */
    public function addDependente($respId){
        if($this->hasPermission($respId)){
            $this->responsavelID = $respId;//aqui colocamos o usuario passado como responsavel
            if($this->insertMenor()){
                new mensagem(SUCESSO,"Dependente(s) inserido(s) com sucesso");

            }else{
                new mensagem(INSERT_ERRO,"Erro ao inserir dependente");
            }
        }else{
            new mensagem(ERRO,"Sem permissão para alterar");
        }

        $this->redirecionaPagAnterior();
    }
    /**
     * @param $pessoaId
     * @throws Exception
     */
    public function updateDadosBasicos($pessoaId){
        $this->receiveAccessLevel();
        if($this->hasPermission($pessoaId) == false)return;
        $this->receiveDadosBasicos();

        if($_SESSION['NIVEL'] == ADMINISTRADOR){
            $colunms = array("nome","sobrenome","nv_acesso","data_nascimento");
            $params =array($this->nome,$this->sobrenome,$this->nv,$this->nascimento);
        }else{//se nao for adm não mexemos no nivel de acesso
            $colunms = array("nome","sobrenome","data_nascimento");
            $params =array($this->nome,$this->sobrenome,$this->nascimento);
        }

        if($this->db->update($colunms,"pessoa",$params,"id_pessoa = ?",array($pessoaId))){
            new mensagem(SUCESSO,"Cadastro Atualizado");
        }else{
            new mensagem(INSERT_ERRO,"Não foi possivel atualizar");
        }

        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId
     * @throws Exception
     */
    public function updateContato($pessoaId){
        $this->receiveContato();
        //Buscando id de update
        $telId = isset($_POST['resp_tel_id']) ? $_POST['resp_tel_id'] : INVALIDO;
        $emailId = isset($_POST['resp_email_id']) ? $_POST['resp_email_id'] : INVALIDO;
        if($this->hasPermission($pessoaId) == false)return;
        $columns = array("contato","tipo_contato");
        //atualizando telefone
        if($telId != INVALIDO){
            $params = array($this->respTel,$this->respTelType);

            if($this->db->update($columns,"contato",$params,"id_contato = ?",array($telId))){
                new mensagem(SUCESSO,"Contato atualizado");
            }else{
                new mensagem(INSERT_ERRO,"Erro ao atualizar Telefone");
                $this->redirecionaPagAnterior();
                return;
            }
        }
        //atualizando Email
        if($emailId != INVALIDO){
            $params = array($this->email,5);
            if($this->db->update($columns,"contato",$params,"id_contato = ?",array($emailId))){
                new mensagem(SUCESSO,"Contato atualizado");
            }else{
                new mensagem(INSERT_ERRO,"Erro ao atualizar Email");
            }
        }


        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId
     * @throws Exception
     */
    public function updateEndereco($pessoaId){
        $this->receiveEndereco();
        if($this->hasPermission($pessoaId) == false)return;
        $columns = array("rua","numero","complemento","bairro","cidade","estado");
        $params = array($this->rua,$this->numero,$this->complemento,$this->bairro,$this->cidade,$this->estado);

        if($this->db->update($columns,"endereco",$params,"pessoa_id = ?",array($pessoaId))){
            new mensagem(SUCESSO,"Cadastro Atualizado com Sucesso");
        }else{
            new mensagem(INSERT_ERRO,"Erro ao atualizar endereço");
        }
        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId
     * @throws Exception
     */
    public function updateDocument($pessoaId){
        $this->receiveDocumento();
        if($this->hasPermission($pessoaId) == false)return;
        $columns = array("numero_documento","tipo_documento");
        $params = array($this->docNumber,$this->docType);

        if($this->db->update($columns,"documento",$params,"pessoa_id = ?",array($pessoaId))){
            new mensagem(SUCESSO,"Documento atualizado");
        }else{
            new mensagem(INSERT_ERRO,"Não foi possivel atualizar");
        }
        $this->redirecionaPagAnterior();
    }

    /**
     * Atualiza a informação de quem foi cadastrado como não ruralino
     * @param $pessoaId
     * @throws Exception
     */
    public function ruralino($pessoaId){
        $this->responsavelID = $pessoaId;
        if($this->db->update(array("ruralino"),"pessoa",array(SIM),"id_pessoa = ?",array($pessoaId))){
            $this->receiveRuralino();
            if($this->insertRuralino()){
                new mensagem(SUCESSO,"Cadastro atualizado, Curso e Matricula inseridos");
            }else{
                new mensagem(INSERT_ERRO,"Erro ao inserir Curso e Matricula");
            }
        }else{
            new mensagem(ERRO,"Problema ao atualizar registro do aluno");
        }
        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId
     * @throws Exception
     */
    public function updateRuralino($pessoaId){
        if($this->hasPermission($pessoaId)){
            $this->receiveRuralino();
            if($_SESSION['NIVEL'] == ADMINISTRADOR){
                $columns = array("matricula","curso","bolsista");
                $params = array($this->matricula,$this->curso,$this->bolsista);
            }else{//se não for ADM não pode alterar status de bolsista
                $columns = array("matricula","curso");
                $params = array($this->matricula,$this->curso);
            }

            if($this->db->update($columns,"ruralino",$params,"pessoa_id = ?",array($pessoaId))){
                new mensagem(SUCESSO,"Cadastro atualizado");
            }else{
                new mensagem(INSERT_ERRO,"Problema ao atualizar");
            }
        }else{
            new mensagem(ERRO,"Permissão insuficiente");
        }
        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId
     * @throws Exception
     */
    public function updateSenha($pessoaId){
        if($this->hasPermission($pessoaId)){
            $this->receiveLogin();
            if($this->db->update(array("senha"),"login",array($this->make_hash($this->senha)),"pessoa_id = ?",array($pessoaId))){
                new mensagem(SUCESSO,"Senha Atualizada");
            }else{
                new mensagem(INSERT_ERRO,"Erro ao Atualizar");
            }
        }else{
            new mensagem(ERRO,"Permissão insuficiente");
        }
        $this->redirecionaPagAnterior();
    }

    //---------------------------------------------REMOVE---------------------------------------------------------------

    /**
     * @param $pesssoaId
     * @throws Exception
     */
    public function deleteDependente($pesssoaId){
        //para manter um historio somente setamos a pessoa como excluida
        if($this->db->update(array("excluido"),"pessoa",array(SIM),"id_pessoa = ?",array($pesssoaId))){
            new mensagem(SUCESSO,"Dependente Removido com sucesso");
        }else{
            new mensagem(INSERT_ERRO,"Problema ao remover dependente");
        }
        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId integer
     * @param $excuir integer
     * @throws Exception
     */
    public function gerenciaConta($pessoaId,$excuir){
        if($this->hasPermission($pessoaId)){

            if($this->db->update(array("excluido"),"pessoa",array($excuir),"id_pessoa = ?",array($pessoaId))){

                if($excuir == SIM)
                    new mensagem(SUCESSO,"Conta desativada com sucesso");
                else
                    new mensagem(SUCESSO,"Conta Reativada");

            }else{
                new mensagem(INSERT_ERRO,"Erro ao alterar registros");
            }

        }else{
            new mensagem(ERRO,"Permissao Insuficiente");
        }
        $this->redireciona();
    }
    //---------------------------------------------REDIRECT-------------------------------------------------------------
    private function redireciona(){header("Location: ../index.php?pag=Login");}

    private function redirecionaPagAnterior(){
        if (isset($_SERVER['HTTP_REFERER']))
            header("Location: " . $_SERVER['HTTP_REFERER'] . "");
        else
            header("Location: ../index.php?pag=DashBoard");
    }

}