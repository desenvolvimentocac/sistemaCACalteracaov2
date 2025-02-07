<?php
/**
 * Created by Filipe
 * Date: 23/05/18
 * Time: 19:06
 */
if ( session_status() !== PHP_SESSION_ACTIVE ) {
    session_start();
}
class mensagem{
    private $msg;
    public function __construct($tipo,$msg){
        $this->msg = $msg;
        switch ($tipo){
            case SUCESSO:
                $this->makeSucessMsg();
                break;
            case ERRO:
                $this->makeErrorMsg();
                break;
            case INSERT_ERRO:
                $this->makeInsertionErrorMsg();
                break;
            default:
                $this->msgIncorreta();
                break;
        }
    }

    private function makeSucessMsg(){
        $_SESSION['MSG'] = "{\"tipo\":\"sucesso\",\"desc\":\"".$this->msg."\"}";
    }
    private function makeErrorMsg(){
        $_SESSION['MSG'] = "{\"tipo\":\"erro\",\"desc\":\"".$this->msg."\"}";
    }

    private function makeInsertionErrorMsg(){
        $_SESSION['MSG'] = "{\"tipo\":\"erro\",\"desc\":\"".$this->msg.", erro detalhado gravado no LOG\"}";
    }

    private function msgIncorreta(){
        $_SESSION['MSG'] = "{\"tipo\":\"erro\",\"desc\":\" Mensagem Definida Incorretamente. \"}";
    }
}