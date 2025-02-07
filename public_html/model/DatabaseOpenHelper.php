<?php
/**
 * Created by Filipe Klinger.
 * Date: 03/03/18
 * Time: 14:37
 */
const ASC = " ASC ";
const DESC = " DESC ";
class Database{
    /**
     * @var PDO
     */
    private $diretorio;
    private $pdo;

    function __construct(){
        $this->diretorio = dirname(__FILE__);
        try {
            $this->conectar();
        } catch (Exception $e) {
            echo $e;
        }
    }

    /**
     * Getting Database configs
     * @param string $arquivo
     * @throws Exception ErrorOnOpen
     */
    private function conectar($arquivo = 'database.ini')
    {
        if (!$setings = parse_ini_file($arquivo, TRUE)) throw new Exception("ErrorOnOpen");
        $sgbd = $setings['database']['sgbd'];
        $host = $setings['database']['host'];
        $port = $setings['database']['port'];
        $schema = $setings['database']['schema'];
        $username = $setings['database']['username'];
        $password = $setings['database']['password'];

        $this->pdo = new PDO($sgbd . ":host=" . $host . ";port=" . $port . ";dbname=" . $schema, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }

//------------Make Atomic transactions-----------------------
    public function startTransaction(){
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->pdo->beginTransaction();
    }

    public function endTransaction(){
        // commit the transaction
        /** @noinspection PhpUndefinedMethodInspection */
        $this->pdo->commit();
    }

//-----------------SELECT-------------------------------------------------------------------

    /**
     * @param string $columns
     * @param string $table
     * @param string $whereClause
     * @param array $whereArgs
     * @param string $orderBy
     * @param string $sequence
     * @param integer $limit
     * @param integer $offset
     * @return string JsonObject
     * @throws Exception
     */
    public function select($columns, $table, $whereClause = null, $whereArgs = array(null), $orderBy = null,$sequence = ASC, $limit = null,$offset = null)
    {
        //check
        if (empty($columns)) throw new Exception("EmptyColumns", 1);
        if (empty($table)) throw new Exception("EmptyTable", 1);
        //begin
        $query = "SELECT ";

        //Projection
        $query .= $columns;

        //TABLE
        $query .= " FROM " . $table;

        //RESTRICTION
        if ($whereClause != null and is_string($whereClause) and strlen($whereClause) > 0) {
            $query .= " WHERE ";
            $query .= $whereClause;
        }

        //ORDER
        if ($orderBy != null and is_string($orderBy) and strlen($orderBy) > 0) {
            $query .= " ORDER BY " . $this->antiInjection($orderBy)." ".$this->antiInjection($sequence);
        }

        //Paginator
        if ($offset != null and $limit != null) {
            $query .=" LIMIT ". intval($limit). " OFFSET ".intval($offset);
        }
        //Preparing
        $stmt = $this->pdo->prepare($query);
        //Inserting params
        if ($whereArgs != null and sizeof($whereArgs) > 0) {
            for ($i = 0; $i < sizeof($whereArgs); $i++) {
                $whereArgs[$i] = $this->antiInjection($whereArgs[$i]);
                $stmt->bindParam($i + 1, $whereArgs[$i]);

            }
        }

        //Running Query
        try {
            $stmt->execute();
        } catch (Exception $Exception) {
            $err = $Exception->getMessage();
            $arquivo = fopen($this->diretorio."/ErrLogSelect.txt", "a+");
            $err = "[" . date("d/m/Y h:i A") . "]" . " QUERY: " . $query . " ERRO: " . $err . "\n";
            fwrite($arquivo, $err);
            fclose($arquivo);

            $stmt->closeCursor();
            return false;
        }

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return json_encode($data,JSON_UNESCAPED_UNICODE);//return json
    }

//-------------------------INSERT------------------------------------------------

    /**
     * @param $columns
     * @param $table
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function insert($columns, $table, $params = array())
    {
        //check
        if (empty($columns)) throw new Exception("EmptyColumns", 1);
        if (empty($table)) throw new Exception("EmptyTable", 1);
        if (!is_array($params)) throw new Exception("ArrayNotFound", 1);
        if (sizeof($params) < 1) throw new Exception("EmptyParams", 1);

        //Begin
        $query = "INSERT INTO " . $table;
        $query .= " ( " . $columns . " ) VALUES (";

        //Inserting placeholders
        for ($i = 0; $i < sizeof($params); $i++) {
            if ($i < sizeof($params) - 1) {
                $query .= " ?, ";
            } else {
                $query .= " ? ";
            }
        }
        //closing placeholders
        $query .= ") ";

        //Preparing
        $stmt = $this->pdo->prepare($query);

        //Inserting params
        if ($params != null and sizeof($params) > 0) {
            for ($i = 0; $i < sizeof($params); $i++) {
                $params[$i] = $this->antiInjection($params[$i]);
                $stmt->bindParam($i + 1, $params[$i]);
            }
        }

        //Running Query
        try {
            $stmt->execute();
        } catch (Exception $e) {//logamos os erros em arquivo
            $err = $e->getMessage();
            $arquivo = fopen($this->diretorio."/ErrLogInsert.txt", "a+");
            $err = "[" . date("d/m/Y h:i A") . "]" . " QUERY: " . $query . " ERRO: " . $err . "\n";
            fwrite($arquivo, $err);
            fclose($arquivo);

            $stmt->closeCursor();
            return false;
        }

        $stmt->closeCursor();

        return true;
    }
//-----------------UPDATE-------------------------------------------------------------------

    /**
     * @param array $columns
     * @param $table
     * @param array $params
     * @param null $whereClause
     * @param array $whereArgs
     * @return bool
     * @throws Exception
     */
    public function update($columns = array(), $table, $params = array(), $whereClause = null, $whereArgs = array(null))
    {
        //check
        if (!is_array($columns)) throw new Exception("ArrayNotFound", 1);
        if (sizeof($columns) < 1) throw new Exception("EmptyColumns", 1);
        if (empty($table)) throw new Exception("EmptyTable", 1);
        if (!is_array($params)) throw new Exception("ArrayNotFound", 1);
        if (sizeof($params) < 1) throw new Exception("EmptyParams", 1);
        if (!is_array($whereArgs)) throw new Exception("ArrayNotFound", 1);

        $query = "UPDATE " . $table . " SET ";

        //binding VALUES
        for ($i = 0; $i < sizeof($columns); $i++) {
            if ($i < sizeof($columns) - 1) {
                $query .= $columns[$i] . " = ? ,";
            } else {
                $query .= $columns[$i] . " = ? ";
            }
        }

        //RESTRICTION
        if ($whereClause != null and strlen($whereClause) > 0) {
            $query .= " WHERE ";
            $query .= $whereClause;
        }

        //Preparing
        $stmt = $this->pdo->prepare($query);

        //Inserting params
        $i = 0;
        if (sizeof($params) > 0) {
            for ($j = 0; $j < sizeof($params); $j++) {
                //somente os parametros vem do usuario entao testamos
                $params[$j] = $this->antiInjection($params[$j]);
                $stmt->bindParam($i + 1, $params[$j]);
                $i++;
            }
        }
        //Inserting RESTRICTION params
        if (sizeof($whereArgs) > 0) {
            for ($j = 0; $j < sizeof($whereArgs); $j++) {
                $whereArgs[$j] = $this->antiInjection($whereArgs[$j]);
                $stmt->bindParam($i + 1, $whereArgs[$j]);
                $i++;
            }
        }

        //Running Query
        try {
            $stmt->execute();
        } catch (Exception $Exception) {
            $err = $Exception->getMessage();
            $arquivo = fopen($this->diretorio."/ErrLogUpdate.txt", "a+");
            $err = "[" . date("d/m/Y h:i A") . "]" . " QUERY: " . $query . " ERRO: " . $err . "\n";
            fwrite($arquivo, $err);
            fclose($arquivo);

            $stmt->closeCursor();
            return false;
        }

        $stmt->closeCursor();

        return true;
    }
//----DELETE--------------------------------------

    /**
     * @param $table
     * @param null $whereClause
     * @param array $whereArgs
     * @return bool
     * @throws Exception
     */
    public function delete($table, $whereClause = null, $whereArgs = array(null))
    {
        //Check
        if (empty($table)) throw new Exception("EmptyTable", 1);
        if (!is_array($whereArgs)) throw new Exception("ArrayNotFound", 1);

        //Begin
        $query = "DELETE FROM " . $table;


        //RESTRICTION
        if ($whereClause != null and strlen($whereClause) > 0) {
            $query .= " WHERE ";
            $query .= $whereClause;
        }

        //Preparing
        $stmt = $this->pdo->prepare($query);

        //Inserting params
        if (sizeof($whereArgs) > 0) {
            for ($i = 0; $i < sizeof($whereArgs); $i++) {
                $whereArgs[$i] = $this->antiInjection($whereArgs[$i]);
                $stmt->bindParam($i + 1, $whereArgs[$i]);
            }
        }

        //Running query
        try {
            $stmt->execute();
        } catch (Exception $Exception) {
            $err = $Exception->getMessage();
            $arquivo = fopen($this->diretorio."/ErrLogDelete.txt", "a+");
            $err = "[" . date("d/m/Y h:i A") . "]" . " QUERY: " . $query . " ERRO: " . $err . "\n";
            fwrite($arquivo, $err);
            fclose($arquivo);

            $stmt->closeCursor();
            return false;
        }

        $stmt->closeCursor();

        return true;
    }

    /**
     * antiInjection
     * realiza validações dos valores para evitar inserção de
     * codigo malicioso com SQL Injection
     * @param $dados
     * @return string
     * @throws Exception SQLInjectionError
     */
    private function antiInjection($dados)
    {
        $dados = trim($dados);
        $dados = stripslashes($dados);
        $dados = htmlspecialchars($dados);
        $vetDados = str_split($dados);//transforma em um vetor
        for ($i = 0; $i < count($vetDados); $i++) {//percorre caracter a caracter
            if ($vetDados[$i] == ";") {
                throw new Exception("SQLInjectionError", 1);
            }
        }
        return $dados;
    }

//-------------------LastId---------------------------------------------------------------------------------------------
    /**
     * @return false or NumberOfLastId
     */
    public function getLastId()
    {
        $query = "SELECT LAST_INSERT_ID()";

        //Preparing
        $stmt = $this->pdo->prepare($query);

        //Running query
        try {
            $stmt->execute();
        } catch (Exception $Exception) {
            $err = $Exception->getMessage();
            $arquivo = fopen($this->diretorio."/ErrLogLastId.txt", "a+");
            $err = "[" . date("d/m/Y h:i A") . "]" . " QUERY: " . $query . " ERRO: " . $err . "\n";
            fwrite($arquivo, $err);
            fclose($arquivo);

            $stmt->closeCursor();
            return false;
        }

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $data = $data[0]['LAST_INSERT_ID()'];
        return $data;
    }
//-----------------------------------SET---Variable---------------------------------------------------------------------

    /**
     * @param string $name name of variable
     * @param $value mixed value of variable
     * @throws Exception
     */
    public function setVariable($name, $value){
        $value = $this->antiInjection($value);
        $name = $this->antiInjection($name);
        $this->pdo->query("Set @".$name.":=".$value);
    }
}

