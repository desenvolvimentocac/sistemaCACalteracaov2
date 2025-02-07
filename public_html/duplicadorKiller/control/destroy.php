<?php
if ( session_status() !== PHP_SESSION_ACTIVE ) {
    session_start();
}
/**
 * Created by Filipe
 * Date: 20/09/18
 * Time: 20:41
 */

/** @noinspection PhpIncludeInspection */
include_once '../../model/DatabaseOpenHelper.php';

class destroy{
    private $db;
    function __construct(){
        $this->db = new Database();
    }

    public function eliminaDuplicados(){
        $registroEliminado = "";
        foreach ($_POST['pessoa_id'] as $id){
            try {
                $registroEliminado .= $this->eliminador($id) . "<br/>";
            } catch (Exception $e) {
                $registroEliminado .= $e."<br/>";
            }
        }
        $_SESSION['MENSAG'] = $registroEliminado." =O";
        echo $registroEliminado;
        $this->redirecionaPagAnterior();
    }

    /**
     * @param $pessoaId
     * @return string
     * @throws Exception
     */
    private function eliminador($pessoaId){
        //START TRANSACTION;

        //contato
        //documento
        //endereco
        //login
        //menor_idade
        //pessoa
        //ruralino
        //aluno_turma

        //COMMIT;

        /** @noinspection PhpUnreachableStatementInspection */
        $this->db->startTransaction();
            $this->db->delete("contato","pessoa_id=?",array($pessoaId));
            $this->db->delete("documento","pessoa_id=?",array($pessoaId));
            $this->db->delete("endereco","pessoa_id=?",array($pessoaId));
            $this->db->delete("login","pessoa_id=?",array($pessoaId));
            $this->db->delete("menor_idade","pessoa_id=?",array($pessoaId));
            $this->db->delete("ruralino","pessoa_id=?",array($pessoaId));
            $this->db->delete("aluno_turma","pessoa_id=?",array($pessoaId));
            //por ultimo pessoa para nao dar erro de foreignKey
            $this->db->delete("pessoa","id_pessoa=?",array($pessoaId));
        $this->db->endTransaction();

        return "Eliminado {$pessoaId}";
    }

    private function redirecionaPagAnterior(){
        if (isset($_SERVER['HTTP_REFERER']))
            header("Location: " . $_SERVER['HTTP_REFERER'] . "");
        else
            header("Location: ../index.php?pag=DashBoard");
    }
}

$dest = new destroy();
$dest->eliminaDuplicados();
