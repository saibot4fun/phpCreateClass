<?php

/**
 * @author Roberto Tobias Zikert Dalchau
 * @copyright 2018
 */
 // conectado com o banco de dados
set_time_limit(0);
$conexao = mysql_connect("host","user","pass");
$db = mysql_select_db("pass");
// valores padrões de variaveirs
$id_modulo = 1;
$txt_sql = "";
$txt_sql_modulo = "";


// recuperando nomes de tabelas/ ou unica
$sql = mysql_query("SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE (TABLE_SCHEMA = 'databaseName' OR TABLE_SCHEMA = 'tableName') AND TABLE_NAME = 'tableName'
ORDER BY TABLE_NAME ");
while($linha = mysql_fetch_array($sql))
{
    
	// gerando nome de arquivos para o modulo
	$arquivo_modulo = "modulos/" . $linha[0] . "/modulo." . $linha[0] . ".php";
	$arquivo_form 	= "modulos/" . $linha[0] . "/template/tpl.frm." . $linha[0] . ".php";
	$arquivo_lis 	= "modulos/" . $linha[0] . "/template/tpl.lis." . $linha[0] . ".php";
    $arquivo_js_lis = "modulos/" . $linha[0] . "/template/js.lis." . $linha[0] . ".php";
	$arquivo_geral	= "modulos/" . $linha[0] . "/template/tpl.geral." . $linha[0] . ".php";
	$arquivo_js		= "modulos/" . $linha[0] . "/template/js." . $linha[0] . ".php";
	$arquivo_ajax	= "modulos/" . $linha[0] . "/template/ajax." . $linha[0] . ".php";

	//CLASSE
	//, inicio da classe
		$classe  = "";
	$classe .= "<?\n";
	$classe .= " include_once(URL_FILE . \"classes/Conexao.php\");\n";
	$classe .= "class " .str_replace(" ","",ucwords(str_replace("_"," ",$linha[0]))) . " extends Conexao\n";
	$classe .= "{\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
		$classe .= "	private $$row[Field];\n";
	}
	$classe .= '	private $pdo;'."\n";

	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{

		$classe .= "	public function set" . str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))) .'($arg)' . "\n";
		$classe .= "	{\n";
		$classe .= '		$this->' .$row['Field'] . ' = $arg;' . "\n";
		$classe .= "	}\n";
		$classe .= " 	\n";
		$classe .= "	public function get" . str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))) .'()' . "\n";
		$classe .= "	{\n";
		$classe .= '		return $this->' .$row['Field'] . ";\n";
		$classe .= "	}\n";
	}
	$classe .= '	public function setPdo($arg)' . "\n";
	$classe .= "	{\n";
	$classe .= '		$this->pdo = $arg;' . "\n";
	$classe .= "	}\n";
	$classe .= " 	\n";
	$classe .= '	public function getPdo()' . "\n";
	$classe .= "	{\n";
	$classe .= '		return $this->pdo;'."\n";
	$classe .= "	}\n";



    /**
     *
     * METODO DE ADICIONAR
     *
     */

	$classe  .= '	public function Adicionar()' . "\n";
	$classe  .= "	{\n";
    $classe	 .= '		$this->pdo = new Conexao();'."\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
    $campos     = "";
    $valores    = "";
    $bind       = "";
    $x = 0;
	while($row = mysql_fetch_array($query))
	{
		$campos .= "`{$row['Field']}`,";
        $valores .= "?,";
        $tipo = substr($row['Type'],0,3);
        if($tipo == "int") $pdo_p = "PDO::PARAM_INT"; else $pdo_p = "PDO::PARAM_STR";
        $bind .= '      $stmt->bindParam('.$x++.',$this->get' . str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))) .'(),'.$pdo_p.');' . "\n";
        //$classe  .= '		$registro[\''.$row['Field'].'\'] = $this->get' . str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))) .'()' .";\n";
	}
    $campos = substr($campos,0,-1);
    $valores = substr($valores,0,-1);
    $classe  .= '       $sql = \'INSERT INTO '.$linha[0].' ('.$campos.') VALUES ('.$valores.')\';'."\n";
    $classe  .= '       $stmt = $this->pdo->prepare($sql);' . "\n";
    $classe  .= "       $bind";
    $classe  .= '       $rs = $stmt->execute();'. "\n";
	$classe  .= '		return $rs ;' . "\n";
	$classe  .= "	}\n";


     /**
     *
     * METODO DE MODIFICAR
     *
     */


	$classe  .= "	public function Modificar()" . "\n";
	$classe  .= "	{\n";
    $classe	 .= '		$this->pdo = new Conexao();'."\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
    $campos = "";
    $valores = "";
    $bind = "";
    $x = 0;
	while($row = mysql_fetch_array($query))
	{
        $campos .= "`{$row['Field']}` = ?,";
        $valores .= "?,";
        $tipo = substr($row['Type'],0,3);
        if($tipo == "int") $pdo_p = "PDO::PARAM_INT"; else $pdo_p = "PDO::PARAM_STR";
        $bind .= '      $stmt->bindParam('.$x++.',$this->get' . str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))) .'(),'.$pdo_p.');' . "\n";
	}
    $campos = substr($campos,0,-1);

    $classe  .= '       $sql = \'UPDATE '.$linha[0].' SET '.$campos.'  WHERE id = ?\';' ."\n";
    $classe  .= '       $stmt = $this->pdo->prepare($sql);' . "\n";
    $classe  .= "       $bind";
    $classe  .= '       $rs = $stmt->execute();'. "\n";
	$classe  .= '		return $rs ;' . "\n";
	$classe  .= "	}\n";


     /**
     *
     * METODO DE DELETAR
     *
     */

	$classe  .= '	public function Remover($lista)' . "\n";
	$classe  .= "	{\n";
    $classe	 .= '		$this->pdo = new Conexao();'."\n";
    $classe  .= '		$lista = implode(",",$lista);' . "\n";
    $classe  .= '        $sql = "DELETE FROM '.$linha[0].' WHERE id IN({$lista})";'."\n";
    $classe  .= '        $stmt = $this->pdo->prepare($sql);' . "\n";
    $classe  .= '        $rs = $stmt->execute();'. "\n";
	$classe  .= '		return $rs ;' . "\n";
	$classe  .= "	}\n";


     /**
     *
     * METODO LISTAR COM PAGINAÇÃO
     *
     */


    $classe  .="    /**\n";
    $classe  .='    * @var $id_grupoid do grupo do usuário logado para definir permições'."\n";
    $classe  .='    * @var $pagina Numero da pagina Atual'."\n";
    $classe  .='    * @var $numeroRegistros numero de registros a serem mostrados'."\n";
    $classe  .='    * @var $numeroInicioRegistro ponteiro de registro'."\n";
    $classe  .='    * @var $busca valor a ser filtrado na query'."\n";
    $classe  .='    * @todo Método que retorna registros no formato de paginação'."\n";
    $classe  .="    */\n";
    $classe  .='    public function ListarPaginacao($id_grupo,$pagina,$numeroRegistros,$numeroInicioRegistro,$busca = "",$filtro = "",$ordem = "")'."\n";
    $classe  .="    {\n";
    $classe	 .= '		$this->pdo = new Conexao();'."\n";
    $classe  .="        //query que conta numero de registros\n";
    $classe  .='        $sql = "SELECT COUNT(*) as total FROM '.$linha[0].'";'."\n";
    $classe  .="        // prepanrando para executar pdo\n";
    $classe  .='        $stmt = $this->pdo->prepare($sql);'."\n";
    $classe  .="        // executando PDO\n";
    $classe  .='        $stmt->execute();'."\n";
    $classe  .="        //pegando resultado em forma de objeto\n";
    $classe  .='        $rs = $stmt->fetch(PDO::FETCH_OBJ);'."\n";
    $classe  .="        // transformando reusultado em variavel\n";
    $classe  .='        $totalRegistros = $rs->total;'."\n";;

    $classe  .="        //query que busca registroscom LIMIT E OFFSET\n";
    $classe  .='        $sql = \'SELECT * FROM '.$linha[0].'\';'."\n";
    $classe  .="        //se ouver busca adicionado where na query\n";
    $classe  .='        if($busca != "") $sql.= " WHERE titulo LIKE \'%$busca%\'";'."\n";
    $classe  .='        if($filtro != "") $sql .=" ORDER BY $filtro $ordem"; else $sql .=" ORDER BY id DESC";'."\n";
    $classe  .="        // definindo limite de regitros para paginação\n";
    $classe  .='        $sql .= " LIMIT ?,?";'."\n";
    $classe  .="        // preparando para executar PDO\n";
    $classe  .='        $stmt = $this->pdo->prepare($sql);'."\n";
    $classe  .="        // passando paramentros para a query\n";
    $classe  .='        $stmt->bindParam(1,$numeroInicioRegistro,PDO::PARAM_INT);'."\n";
    $classe  .='        $stmt->bindParam(2,$numeroRegistros,PDO::PARAM_INT);'."\n";
    $classe  .='        // executando PDO'."\n";
    $classe  .='        $stmt->execute();'."\n";
    $classe  .="        // loopping do resultado parassando para um array de objetos\n";
    $classe  .='        while($linha = $stmt->fetch(PDO::FETCH_OBJ))'."\n";
    $classe  .="        {\n";
    $classe  .="            // criando instancia do Objeto\n";
    $classe  .="            $".$linha[0]." = new ".str_replace(" ","",ucwords(str_replace("_"," ",$linha[0])))."();\n";
    $classe  .="            // setando valores no Objeto\n";
    $query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
   	while($row = mysql_fetch_array($query))
	{
		$classe  .= '		   $'.$linha[0].'->set'.str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))).'($linha->'.$row['Field'].');' . "\n";
	}
    $classe  .="            // adicionando objeto em array\n";
    $classe  .='            $vetor[] = $'.$linha[0].';'."\n";
    $classe  .="        }\n";
    $classe  .="        // retornando resultados em objeto e total de registro\n";
    $classe  .='	   return array($vetor,$totalRegistros);'."\n";
    $classe  .="}\n";



     /**
     *
     * METODO DE EDITAR
     *
     */


	$classe  .= '	public function Editar()'. "\n";
	$classe  .= '	{'. "\n";
    $classe	 .= '		$this->pdo = new Conexao();'."\n";
    $classe  .="        //query que conta numero de registros\n";
    $classe  .='        $sql = "SELECT * FROM '.$linha[0].' WHERE id = ?";'."\n";
    $classe  .="        // prepanrando para executar pdo\n";
    $classe  .='        $stmt = $this->pdo->prepare($sql);'."\n";
    $classe  .="        //Atribuindo parametro a query\n";
    $classe  .='        $stmt->bindParam(1,$this->id,PDO::PARAM_INT);'."\n";
    $classe  .="        // executando PDO\n";
    $classe  .='        $stmt->execute();'."\n";
    $classe  .="        //pegando resultado em forma de objeto\n";
    $classe  .='        $linha = $stmt->fetch(PDO::FETCH_OBJ);'."\n";

    $classe  .="        $".$linha[0]." = new ".str_replace(" ","",ucwords(str_replace("_"," ",$linha[0])))."();\n";
    $query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
   	while($row = mysql_fetch_array($query))
	{
		$classe  .= '		   $'.$linha[0].'->set'.str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))).'($linha->'.$row['Field'].');' . "\n";
	}

    $classe  .='        return $'.$linha[0].';'."\n";
	$classe  .= '	}'. "\n";


     /**
     *
     * METODO DE ADICIONAR
     *
     */

	$classe .= "}\n";
	$classe .= "?>\n";





	// MODULO

	$modulo = "<?\n include_once(URL_FILE .\"modulos/".$linha[0]."/classe.".$linha[0].".php\");\n";
    $modulo.= "include_once(URL_FILE . \"classes/GerarTabelaJquery.php\");\n";
	$modulo.= "\n $".$linha[0]." = new ".str_replace(" ","",ucwords(str_replace("_"," ",$linha[0])))."();\n";

	$modulo.= "\n\n" . 'switch($app_comando)' . "\n";
	$modulo.= "{\n";
	$modulo.= '		case "frm_adicionar_'.$linha[0].'" :' . "\n";
    $modulo.= '		    $linha = new '.str_replace(" ","",ucwords(str_replace("_"," ",$linha[0]))). "();\n";
	$modulo.= '			$template = "tpl.frm.'.$linha[0].'.php";' . "\n";
	$modulo.= "\n";
	$modulo.= "			break;\n";
	$modulo.= "\n";
	$modulo.= '		case "adicionar_'.$linha[0].'" :' . "\n";
	$modulo.= "\n";
	$modulo.= "		/*Cotetando informações do Formulários*/\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_assoc($query))
	{
		$modulo  .= '			$'.$linha[0].'->set'.str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))).'($_POST[\''.$row['Field'].'\']);' . "\n";
	}
	$modulo.= '			$retorno = $'.$linha[0].'->Adicionar();'."\n";
    $modulo.= '	        if($retorno == 1)' . "\n";
    $modulo.= '	        {' . "\n";
    $modulo.= '	             $msg["codigo"] = 0;' . "\n";
    $modulo.= '	             $msg["mensagem"] = "Operação executada com Sucesso";' . "\n";
    $modulo.= '	        }' . "\n";
    $modulo.= '	        else' . "\n";
    $modulo.= '	        {' . "\n";
    $modulo.= '	             $msg["codigo"] = 1;' . "\n";
    $modulo.= '	             $msg["mensagem"] = "Erro ao Executar Operação";' . "\n";
    $modulo.= '	        }' . "\n";

    $modulo.= '	         echo json_encode($msg);'."\n";
	$modulo.= '			$template = "ajax.'.$linha[0].'.php";' . "\n";
	$modulo.= "\n";
	$modulo.= "			break;\n";
	$modulo.= "\n";
	$modulo.= '		case "frm_atualizar_'.$linha[0].'" :' . "\n";
	$modulo.= "\n";
	$modulo.= '			$'.$linha[0].'->setId($app_codigo);'. "\n";
	$modulo.= '			$linha = $'.$linha[0].'->Editar();'. "\n";
	$modulo.= '			$template = "tpl.frm.'.$linha[0].'.php";' . "\n";
	$modulo.= "\n";
	$modulo.= "			break;\n";
	$modulo.= "\n";
	$modulo.= '		case "atualizar_'.$linha[0].'" :' . "\n";
	$modulo.= "\n";
	$modulo.= "		/*Cotetando informações do Formulários*/\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
		$modulo  .= '			$'.$linha[0].'->set'.str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))).'($_POST[\''.$row['Field'].'\']);' . "\n";
	}
	$modulo.= '			$retorno = $'.$linha[0].'->Modificar();' ."\n";
    $modulo.= '	        if($retorno == 1)' . "\n";
    $modulo.= '	        {' . "\n";
    $modulo.= '	             $msg["codigo"] = 0;' . "\n";
    $modulo.= '	             $msg["mensagem"] = "Operação executada com Sucesso";' . "\n";
    $modulo.= '	        }' . "\n";
    $modulo.= '	        else' . "\n";
    $modulo.= '	        {' . "\n";
    $modulo.= '	             $msg["codigo"] = 1;' . "\n";
    $modulo.= '	             $msg["mensagem"] = "Erro ao Executar Operação";' . "\n";
    $modulo.= '	        }' . "\n";
    $modulo.= '	         echo json_encode($msg);'."\n";
	$modulo.= '			$template = "ajax.'.$linha[0].'.php";' . "\n";

	$modulo.= "\n";
	$modulo.= "			break;\n";
	$modulo.= "\n";
	$modulo.= '		case "listar_'.$linha[0].'" :' . "\n";
	$modulo.= "\n";
	$modulo.= '			$template = "tpl.geral.'.$linha[0].'.php";'."\n";
	$modulo.= "\n";
	$modulo.= "			break;\n";
	$modulo.= "\n";
	$modulo.= '		case "deletar_'.$linha[0].'" :' . "\n";
	$modulo.= "\n";
	$modulo.= '			if(count($_POST[\'registros\']) > 0)'. "\n";
	$modulo.= '			{ ';
	$modulo.= "\n";
	$modulo.= '				$retorno = $'.$linha[0].'->Remover($_POST[\'registros\']);' ."\n";
    $modulo.= '	        if($retorno == 1)' . "\n";
    $modulo.= '	        {' . "\n";
    $modulo.= '	             $msg["codigo"] = 0;' . "\n";
    $modulo.= '	             $msg["mensagem"] = "Operação executada com Sucesso";' . "\n";
    $modulo.= '	        }' . "\n";
    $modulo.= '	        else' . "\n";
    $modulo.= '	        {' . "\n";
    $modulo.= '	             $msg["codigo"] = 1;' . "\n";
    $modulo.= '	             $msg["mensagem"] = "Erro ao Executar Operação";' . "\n";
    $modulo.= '	        }' . "\n";
    $modulo.= '	         echo json_encode($msg);'."\n";
	$modulo.= '			}'."\n";
	$modulo.= '			$template = "ajax.'.$linha[0].'.php";' . "\n";
	$modulo.= "\n";
	$modulo.= "		break;\n";
    $modulo.= '		case "ajax_listar_'.$linha[0].'" :' . "\n";
	$modulo.= "\n";
	$modulo.= '			$template = "tpl.lis.'.$linha[0].'.php";'."\n";
	$modulo.= "\n";
	$modulo.= "			break;\n";
	$modulo.= "\n";
	$modulo.= "}\n";
	$modulo.= "?>";

	//TPL LIS


	$tplLis  = "<?\n";
    $tplLis .= 'include_once("modulos/'.$linha[0].'/template/js.lis.'.$linha[0].'.php");' . "\n";
    $tplLis .= '$busca = strip_tags(trim($_REQUEST["busca"]));' . "\n";
    $tplLis .= '$pagina = $_REQUEST["pagina"];' . "\n";
    $tplLis .= '$filtro = $_REQUEST["filtro"];' . "\n";
    $tplLis .= '($_REQUEST["ordem"] == "desc")? $ordem = "asc": $ordem = "desc";' . "\n";
	$tplLis .= '$busca = $_REQUEST[\'busca\'];' . "\n";
	$tplLis .= '$pagina = $_REQUEST[\'pagina\'];' . "\n";
	$tplLis .= 'if($pagina == "") { $pagina = 0; }' . "\n";
	$tplLis .= '$numeroRegistros = 50;' . "\n";
	$tplLis .= '$numeroInicioRegistro = $pagina * $numeroRegistros;' . "\n";

	$tplLis .= '$listar = $'.$linha[0].'->ListarPaginacao($_SESSION[\'usuario\'][\'id_grupo\'],$pagina,$numeroRegistros,$numeroInicioRegistro,$busca,$filtro,$ordem);' . "\n";

	$tplLis .= '$tabela = new GerarTabelaJquery();' . "\n";
	$tplLis .= '$tabela->buscaAtiva = true;' . "\n";
	$tplLis .= '$tabela->busca = $busca;' . "\n";
	$tplLis .= '$tabela->pagina = $pagina;' . "\n";
	$tplLis .= '$tabela->numeroRegistros = $numeroRegistros;' . "\n";
	$tplLis .= '$tabela->numeroRegistroIncio = $pagina * $numeroRegistros;' . "\n";

	$tplLis .= '$tabela->acaoAdicionar 	= "frm_adicionar_'.$linha[0].'";'. "\n";
	$tplLis .= '$tabela->acaoDeletar 	= "deletar_'.$linha[0].'";'. "\n";
	$tplLis .= '$tabela->acaoModificar 	= "frm_atualizar_'.$linha[0].'";'. "\n";
	$tplLis .= '$tabela->acaoListar 	= "listar_'.$linha[0].'";'. "\n";
	$tplLis .= '$tabela->acaoVizualizar = "visualizar_'.$linha[0].'";'. "\n";
	$tplLis .= '$tabela->modulo 		= $app_modulo;'. "\n";
	$tplLis .= '$tabela->comando 		= $app_comando;'. "\n";
	$tplLis .= '$tabela->codigo 		= $app_codigo;'. "\n";
	$tplLis .= 'if(count($listar[0])> 0)'. "\n";
    $tplLis .= '{'. "\n";
	$tplLis .= '   foreach($listar[0] as $linha)' . "\n";
	$tplLis .= '   {' . "\n";
	$tplLis .= '	  $dados[\'box\'] = $linha->getId();' . "\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
		$tplLis .= '	 $dados[\''.$row['Field'].'\']	= $linha->get'.str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))).'();' . "\n";
	}
	$tplLis .= '	  $dados[\'Alterar\'] = $linha->getId();' . "\n";
	$tplLis .= '	  $todos[]= $dados;' . "\n";
	$tplLis .= '   }' . "\n";
    $tplLis .= '}' . "\n";
	$tplLis .= '$coluna[] = "box"; $ordenacao[0]["nome"] = "";             $ordenacao[0]["tipo"] = $ordem;' . "\n";
	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
    $x = 1;
	while($row = mysql_fetch_array($query))
	{
		$tplLis .= '$coluna[]	= " '.ucwords(str_replace("_"," ",$row['Field'])).'"; $ordenacao['.$x.']["nome"] = "'.$row['Field'].'";             $ordenacao['.$x.']["tipo"] = $ordem;'. "\n";
        $x++;
	}
	$tplLis .= '$coluna[] = "Alterar";$ordenacao[0]["nome"] = "";             $ordenacao[0]["tipo"] = $ordem;' . "\n";

	$tplLis .= '$tabela->nome = "'.$linha[0].'";' . "\n";

	$tplLis .= '$tabela->totalRegistros = $listar[1];' . "\n";
	$tplLis .= '$tabela->dados = $todos;' . "\n";
	$tplLis .= '$tabela->colunas = $coluna;' . "\n";
	$tplLis .= '$tabela->botaoAdicionar = "1";' . "\n";
    $tplLis .= '$tabela->ordenacao = $ordenacao;' . "\n";
    $tplLis .= '$tabela->filtro = $filtro;' . "\n";
    $tplLis .= '$tabela->ordem = $_REQUEST["ordem"];' . "\n";
	$tplLis .= 'echo $tabela->CriarTabela();' . "\n";
	$tplLis .= "?>\n";

    /**
     * GERAL MODULO
     *
     */
    $geral = "";
    $geral .= "<?php\n";
    $geral .= "/**\n";
    $geral .= " * @author Tobias\n";
    $geral .= " * @copyright 2017\n";
    $geral .= " */\n";
    $geral .= "include(\"modulos/".$linha[0]."/template/js.".$linha[0].".php\");\n";
    $geral .= "?>\n";
    $geral .= "<div class=\"titulo-modulo\" ><h3>".ucwords(str_replace(" "," ",$linha[0]))."</h3></div>\n";
    $geral .= "<div id=\"conteudo_grid\" ></div>   \n";
    $geral .= "<div id=\"div_form\"></div>\n";
    $geral .= "<div id=\"dialog-confirm\"></div>\n";


    /**
     *
     * INICIO DO FORMULÁRIO , CHECANDO DADOS
     *
     */
    $formulario = "";
    $formulario .= "<form action=\"#\" method=\"post\" name=\"log\" id=\"log\" class=\"form_ajax\" style=\"width:100%;\">\n";
    $formulario .= '<input type="hidden" value="<?=$linha->getId();?>"name="id" id="id" />' ."\n";
    $formulario .= "<fieldset>\n";
    $formulario .= "<legend>Formulário de Cadastro</legend>\n";
    $query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
        $formulario .= "	<label>".ucwords(str_replace("_"," ",$row['Field'])).":</label>\n";
        $formulario .= '	<input type="text" size="45" class="text ui-widget-content ui-corner-all ui-autocomplete-input" name="'.$row['Field'].'" id="'.$row['Field'].'"  value="<?=$linha->get'.str_replace(" ","",ucwords(str_replace("_"," ",$row['Field']))).'();?>"/><br/>'."\n";
    }
    $formulario .= "</fieldset>\n";
    $formulario .= "</form>\n";




    $js_list = "";
    $js_list .= '<script type="text/javascript">'."\n";
    $js_list .='	$(document).ready(function()'."\n";
    $js_list .='    {'."\n";
    $js_list .='       zebra(\'MYTABLE\',\'odd\');'."\n";
    $js_list .='       '."\n";
    $js_list .='        /**'."\n";
    $js_list .='        CarregarDialog(\'#div_form\');'."\n";
    $js_list .='        * Ação do Botão Adicionar'."\n";
    $js_list .='        *'."\n";
    $js_list .='        */'."\n";
    $js_list .='       $(\'#botao_adicionar\').button().click('."\n";
    $js_list .='        function()'."\n";
    $js_list .='        {'."\n";
    $js_list .='            // div para criar os formularios'."\n";
    $js_list .='            // quando houver ação do click ele vai criar o objeto dialog'."\n";
    $js_list .='           $(\'#div_form\').dialog('."\n";
    $js_list .='           {'."\n";
    $js_list .='                title: \'Adicionar'.ucwords(str_replace("_"," ",$linha[0])).'\', // titulo da janela'."\n";
    $js_list .='                height: 450, // altura da janela'."\n";
    $js_list .='                width: 350, // largura da janela'."\n";
    $js_list .='                modal: true, // abilita MODAL (modal e o escurecimento de tela)'."\n";
    $js_list .='                // mostra botões na janela'."\n";
    $js_list .='                buttons: [{'."\n";
    
    
    
    $js_list .='                    // criando botão salvar'."\n";
    $js_list .='                    text : "Salvar",'."\n";
    $js_list .='                    class : "btn btn-success",'."\n";
    $js_list .='				    click: function() '."\n";
    $js_list .='                    {'."\n";
    $js_list .='                         var resultado = true;'."\n";
    $js_list .='                         var msg_form = \'\''."\n";
   	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
        $js_list .='                         if($(\'#'.$row['Field'].'\').val() == "")'."\n";
        $js_list .='                         {'."\n";
        $js_list .='                            msg_form = msg_form + " '.$row['Field'].' não pode ser em branco.<br>";'."\n";
        $js_list .='                            resultado = false;'."\n";
        $js_list .='                         }'."\n";
	}
    $js_list .='                         if(resultado)'."\n";
    $js_list .='                         { '."\n";
    $js_list .='                             // ao clicar em salvar enviando dados por post via AJAX'."\n";
    $js_list .='                             $.post("index_xml.php?app_modulo='.$linha[0].'&app_comando=adicionar_'.$linha[0].'",'."\n";
    $js_list .='                             { '."\n";
    $js_list .='                                    // defininco parametros que serão apssados junto com o POST'."\n";
    $campos = '';
    $rw = array();
   	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
	   $rw[] =$row['Field'].':$(\'#'.$row['Field'].'\').val()';
    }
    $campos = implode(",",$rw);
    $js_list .='                                    '.$campos."\n";
    $js_list .='                             },'."\n";
    $js_list .='                             // pegando resposta do retorno do post'."\n";
    $js_list .='                             function(response)'."\n";
    $js_list .='                             {'."\n";
    $js_list .='                                var nome_dialog = "Atenção";'."\n";
    $js_list .='                                // gerando mensagens de erro'."\n";
    $js_list .='                                msg = \'<div class="erro_ajax"><h1>NÃO HOUVE NENHUMA MENSAGEM</h1></div>\''."\n";
    $js_list .='                                if(response["codigo"] == 0)'."\n";
    $js_list .='                                {'."\n";
    $js_list .='                                   msg = response["mensagem"];'."\n";
    $js_list .='                                   nome_dialog = "Sucesso!";'."\n";
    $js_list .='                                   Limpar();'."\n";
    $js_list .='                                   $( "#div_form" ).dialog( "close" );'."\n";
    $js_list .='                                }'."\n";
    $js_list .='                                else'."\n";
    $js_list .='                                {'."\n";
    $js_list .='                                   msg = response["mensagem"];'."\n";
    $js_list .='                                }'."\n";
    $js_list .='                                // chamando função para mostrar mesagem no formato padrão'."\n";
    $js_list .='                                alertJquery(msg,nome_dialog);'."\n";
    $js_list .='                              }'."\n";
    $js_list .='                              , "json" // definindo retorno para o formato json'."\n";
    $js_list .='                              );'."\n";
    $js_list .='                              // atualizando grid'."\n";
    $js_list .='                              AtualizarGrid(0,\'\');'."\n";
    $js_list .='                              // fechando janela'."\n";
    $js_list .='                              //$( this ).dialog( "close" );'."\n";
    $js_list .='                        }'."\n";
    $js_list .='                        else'."\n";
    $js_list .='                        {'."\n";
    $js_list .='                            alertJquery(msg_form,"Atenção");'."\n";
    $js_list .='                        }'."\n";
    $js_list .='				    }},'."\n";
    
    
    $js_list .='                    {'."\n";
    $js_list .='                    text: "Limpar",'."\n";
    $js_list .='                    class: "btn btn-warning",'."\n";
    $js_list .='                    click: function() {'."\n";
    $js_list .='                        Limpar();'."\n";
    $js_list .='				    }},'."\n";
    $js_list .='				    {'."\n";
    $js_list .='				    text : "Fechar",'."\n";
    $js_list .='				    class : "btn btn-danger",'."\n";
    $js_list .='                    click: function() {'."\n";
    $js_list .='					   $( this ).dialog( "close" );'."\n";
    $js_list .='				    }'."\n";
    $js_list .='                }],'."\n";
    
    
    
    
    
    
    
    $js_list .='                // definindo fundo da janela'."\n";
    $js_list .='                overlay:'."\n";
    $js_list .='                {'."\n";
    $js_list .='                    opacity: 100,'."\n";
    $js_list .='                    background: "#000000"'."\n";
    $js_list .='                },'."\n";
    $js_list .='                close:function()'."\n";
    $js_list .='                {'."\n";
    $js_list .='                    jQuery(this).dialog("destroy").empty();'."\n";
    $js_list .='                },'."\n";
    $js_list .='                // definindo ação de load do template para a janela'."\n";
    $js_list .='                open: function ()'."\n";
    $js_list .='                {'."\n";
    $js_list .='                    jQuery(this).load(\'index_xml.php?app_modulo='.$linha[0].'&app_comando=frm_adicionar_'.$linha[0].'\');'."\n";
    $js_list .='                    $(".ui-dialog-titlebar-close").addClass("ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only");'."\n";;
    $js_list .='                    $(".ui-dialog-titlebar-close").append("<span class=\'ui-button-icon-primary ui-icon ui-icon-closethick\'></span><span class=\'ui-button-text\'>close</span>");'."\n";;    
    $js_list .='                }'."\n";
    $js_list .='           }'."\n";
    $js_list .='           );            '."\n";
    $js_list .='        });'."\n";
    $js_list .='        /* Fim  da ação para o botão adicionar */'."\n";
    $js_list .='        '."\n";
    $js_list .='        '."\n";
    $js_list .='        $(\'#botoww\').button();'."\n";
    $js_list .='        '."\n";
    $js_list .='        '."\n";
    $js_list .='        //Inicio da função do botão excluir'."\n";
    $js_list .='        $(\'#botao_deletar\').button().click(function()'."\n";
    $js_list .='        {'."\n";
    $js_list .='            var checked = $("input[name=\'lista[]\']:checked").length;'."\n";
    $js_list .='            if(checked > 0)'."\n";
    $js_list .='            {'."\n";
    $js_list .='                var values = new Array();'."\n";
    $js_list .='                $.each($("input[name=\'lista[]\']:checked"), function() {'."\n";
    $js_list .='                    values.push($(this).val());'."\n";
    $js_list .='                });'."\n";
    $js_list .='                confirmJquery(\'Deseja realmente remover este(s) registros(s)?<br>ID´s (\'+values+\')\',\'Alerta do Sistema\',values,ExcluirRegistros);'."\n";
    $js_list .='            }'."\n";
    $js_list .='            else'."\n";
    $js_list .='            {'."\n";
    $js_list .='                alertJquery(\'Por favor, selecione itens para serem excluidos\');'."\n";
    $js_list .='            }'."\n";
    $js_list .='        });'."\n";
    $js_list .='         function Limpar()'."\n";
    $js_list .='         {'."\n";
   	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
	   $js_list .='              $(\'#'.$row['Field'].'\').val(\'\');'."\n";
    }
    $js_list .='         }'."\n";
    $js_list .='         function ExcluirRegistros(dados)'."\n";
    $js_list .='         {'."\n";
    $js_list .='             $.post("index_xml.php?app_modulo='.$linha[0].'&app_comando=deletar_'.$linha[0].'",'."\n";
    $js_list .='             { '."\n";
    $js_list .='                    registros:dados'."\n";
    $js_list .='             },'."\n";
    $js_list .='             function(response)'."\n";
    $js_list .='             {'."\n";
       $js_list .='               var nome_dialog = "Atenção";'."\n";
    $js_list .='                  // gerando mensagens de erro'."\n";
    $js_list .='                   msg = \'<div class="erro_ajax"><h1>NÃO HOUVE NENHUMA MENSAGEM</h1></div>\''."\n";
    $js_list .='                   if(response["codigo"] == 0)'."\n";
    $js_list .='                   {'."\n";
    $js_list .='                       msg = response["mensagem"];'."\n";
    $js_list .='                       nome_dialog = "Sucesso!";'."\n";
    $js_list .='                       $( "#div_form" ).dialog( "close" );'."\n";
    $js_list .='                   }'."\n";
    $js_list .='                   else'."\n";
    $js_list .='                   {'."\n";
    $js_list .='                      msg = response["mensagem"];'."\n";
    $js_list .='                   }'."\n";
    $js_list .='                   // chamando função para mostrar mesagem no formato padrão'."\n";
    $js_list .='                    alertJquery(msg,nome_dialog);'."\n";
    $js_list .='             }'."\n";
    $js_list .='             , "json"'."\n";
    $js_list .='             );'."\n";
    $js_list .='             AtualizarGrid(0,\'\');'."\n";
    $js_list .='            '."\n";
    $js_list .='        }'."\n";
    $js_list .='        //fim fo botão excluir'."\n";
    $js_list .='        '."\n";
    $js_list .='        '."\n";
    $js_list .='        '."\n";
    $js_list .='        /* funcção de alteração de registros*/'."\n";
    $js_list .='    });'."\n";
    $js_list .='        '."\n";
    $js_list .='        function AlterarRegistro(id_registro)'."\n";
    $js_list .='        {'."\n";
    $js_list .='            CarregarDialog(\'#div_form\');'."\n";
    $js_list .='           $(\'#div_form\').dialog('."\n";
    $js_list .='           {'."\n";
    $js_list .='                title: \'Atualizar '.ucwords(str_replace("_"," ",$linha[0])).'\','."\n";
    $js_list .='                height: 450,'."\n";
    $js_list .='                width: 350,'."\n";
    $js_list .='                modal: true,'."\n";
    $js_list .='                buttons: [{'."\n";
    $js_list .='               text: "Salvar",'."\n";
    $js_list .='               class: "btn btn-success",'."\n";
    $js_list .='				    click: function() '."\n";
    $js_list .='                    {'."\n";
    $js_list .='                         var resultado = true;'."\n";
    $js_list .='                         var msg_form = \'\''."\n";
    $query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
        $js_list .='                         if($(\'#'.$row['Field'].'\').val() == "")'."\n";
        $js_list .='                         {'."\n";
        $js_list .='                            msg_form = msg_form + " '.$row['Field'].' não pode ser em branco.<br>";'."\n";
        $js_list .='                            resultado = false;'."\n";
        $js_list .='                         }'."\n";
	}
    $js_list .='                         if(resultado)'."\n";
    $js_list .='                         { '."\n";
    $js_list .='                             //alert(dados);'."\n";
    $js_list .='                             $.post("index_xml.php?app_modulo='.$linha[0].'&app_comando=atualizar_'.$linha[0].'",'."\n";
    $js_list .='                             { '."\n";
    $campos = '';
    $rw = array();
   	$query = mysql_query("SHOW COLUMNS FROM {$linha[0]}");
	while($row = mysql_fetch_array($query))
	{
	   $rw[] =$row['Field'].':$(\'#'.$row['Field'].'\').val()';
    }
    $campos = implode(",",$rw);
    $js_list .='                                    '.$campos."\n";
    $js_list .='                             },'."\n";
    $js_list .='                             function(response)'."\n";
    $js_list .='                             {'."\n";
    $js_list .='                                var nome_dialog = "Atenção";'."\n";
    $js_list .='                                // gerando mensagens de erro'."\n";
    $js_list .='                                msg = \'<div class="erro_ajax"><h1>NÃO HOUVE NENHUMA MENSAGEM</h1></div>\''."\n";
    $js_list .='                                if(response["codigo"] == 0)'."\n";
    $js_list .='                                {'."\n";
    $js_list .='                                   msg = response["mensagem"];'."\n";
    $js_list .='                                   nome_dialog = "Sucesso!";'."\n";
    $js_list .='                                   $( "#div_form" ).dialog( "close" );'."\n";
    $js_list .='                                }'."\n";
    $js_list .='                                else'."\n";
    $js_list .='                                {'."\n";
    $js_list .='                                   msg = response["mensagem"];'."\n";
    $js_list .='                                }'."\n";
    $js_list .='                                // chamando função para mostrar mesagem no formato padrão'."\n";
    $js_list .='                                alertJquery(msg,nome_dialog);'."\n";
    $js_list .='                             }'."\n";
    $js_list .='                             , "json"'."\n";
    $js_list .='                             );'."\n";
    $js_list .='                             AtualizarGrid(0,\'\');'."\n";
    $js_list .='                             $( this ).dialog( "close" );'."\n";
    $js_list .='                        }'."\n";
    $js_list .='                        else'."\n";
    $js_list .='                        {'."\n";
    $js_list .='                            alertJquery(msg_form,"Atenção");'."\n";
    $js_list .='                        }'."\n";
    
    $js_list .='				    }},'."\n";
    $js_list .='				    {'."\n";
    $js_list .='				    text: "Fechar",'."\n";
    $js_list .='				    class: "btn btn-danger",'."\n";
    $js_list .='                    click: function() {'."\n";
    $js_list .='					   $( this ).dialog( "close" );'."\n";
    $js_list .='				    }'."\n";
    $js_list .='                    '."\n";
    $js_list .='                }],'."\n";
    
    
    $js_list .='                overlay:'."\n";
    $js_list .='                {'."\n";
    $js_list .='                    opacity: 100,'."\n";
    $js_list .='                    background: "#000000"'."\n";
    $js_list .='                },'."\n";
    $js_list .='                close:function()'."\n";
    $js_list .='                {'."\n";
    $js_list .='                    jQuery(this).dialog("destroy").empty();'."\n";
    $js_list .='                },'."\n";
    $js_list .='                open: function ()'."\n";
    $js_list .='                {'."\n";
    $js_list .='                    jQuery(this).load(\'index_xml.php?app_modulo='.$linha[0].'&app_comando=frm_atualizar_'.$linha[0].'&app_codigo=\'+id_registro);'."\n";
    $js_list .='                    $(".ui-dialog-titlebar-close").addClass("ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only");'."\n";
    $js_list .='                    $(".ui-dialog-titlebar-close").append("<span class=\'ui-button-icon-primary ui-icon ui-icon-closethick\'></span><span class=\'ui-button-text\'>close</span>");'."\n";    
   
    $js_list .='                }'."\n";
    $js_list .='           });'."\n";
    $js_list .='        }'."\n";
    $js_list .='        '."\n";
    $js_list .='        '."\n";
    $js_list .='</script>'."\n";







	$txt_sql_modulo .= "INSERT INTO modulo set nome = '$linha[0]' ,dir = '$linha[0]' ,status=0; \n";

	$txt_sql .=" INSERT INTO comando (nome,acao,modulo,status) SELECT 'Adicionar $linha[0]','frm_adicionar_$linha[0]|adicionar_$linha[0]',id,'0' FROM modulo WHERE dir LIKE '%{$linha[0]}%';\n";
	$txt_sql .=" INSERT INTO comando (nome,acao,modulo,status) SELECT 'Atualizar $linha[0]','frm_atualizar_$linha[0]|atualizar_$linha[0]',id,'0' FROM modulo WHERE dir LIKE '%{$linha[0]}%';\n";
	$txt_sql .=" INSERT INTO comando (nome,acao,modulo,status) SELECT 'Listar $linha[0]','listar_$linha[0]|visualizar_$linha[0]|ajax_listar_$linha[0]',id,'0' FROM modulo WHERE dir LIKE '%{$linha[0]}%';\n";
	$txt_sql .=" INSERT INTO comando (nome,acao,modulo,status) SELECT 'Deletar $linha[0]','deletar_$linha[0]',id,'0' FROM modulo WHERE dir LIKE '%{$linha[0]}%';\n";

	$js = "\n";

    $js .= '<?php'."\n";

    $js .= '/**'."\n";
    $js .= '* @author Tobias'."\n";
    $js .= '* @copyright 2017'."\n";
    $js .= '*/'."\n";

    $js .= '//include_once("includes/js.includes.php");'."\n";
    $js .= '?>'."\n";
    $js .= '<script type="text/javascript">'."\n";
    $js .= '	$(document).ready(function()'."\n";
    $js .= '    {'."\n";
    $js .= '       CarregarPadrao("#conteudo_central");'."\n";
    $js .= '       AtualizarGrid(0,"");'."\n";
    $js .= '   '."\n";
    $js .= '    });'."\n";
    $js .= '    function AtualizarGrid(pagina,busca,filtro,ordem)'."\n";
    $js .= '    {'."\n";
    $js .= '        //alert(filtro);'."\n";
    $js .= '        if(filtro == "" || filtro === undefined)  filtro = ""; '."\n";
    $js .= '        if(ordem == "" || ordem  === undefined)  ordem = "";'."\n";
    $js .= '        '."\n";
    $js .= '        //alert(filtro + '-' + ordem );'."\n";
    $js .= '        $("#conteudo_grid").load("index_xml.php?app_comando=ajax_listar_'.$linha[0].'&app_modulo='.$linha[0].'&app_codigo&pagina="+pagina+"&busca="+$.trim(busca)+"&filtro="+filtro+"&ordem="+ordem);'."\n";
    $js .= '        '."\n";
    $js .= '    }'."\n";
    $js .= '</script>'."\n";

    $ajax  = '<?' . "\n";
    $ajax .= 'switch($app_comando)' . "\n";
    $ajax .= "{ \n";
    $ajax .= '      case "":' ."\n";
    $ajax .= "      \n";
    $ajax .= "      break;\n";
    $ajax .= "}\n";
    $ajax .= "?>";

    $ajax = "";


	if(!file_exists("modulos_gerador/" . $linha[0])) mkdir("modulos_gerador/" . $linha[0],0777);
	if(!file_exists("modulos_gerador/" . $linha[0]. "/template")) mkdir("modulos_gerador/" . $linha[0] . "/template",0777);

	if(file_exists("modulos_gerador/" . $linha[0]. "/classe." . $linha[0]. ".php"))
		unlink("modulos_gerador/" . $linha[0]. "/classe" . $linha[0]. ".php");



	if(file_exists($arquivo_modulo))
		unlink($arquivo_modulo);

	if(file_exists($arquivo_form))
		unlink($arquivo_form);

	if(file_exists($arquivo_lis))
		unlink($arquivo_lis);


	if(file_exists($arquivo_js))
		unlink($arquivo_js);

	if(file_exists($arquivo_ajax))
		unlink($arquivo_ajax);

    if(file_exists($arquivo_js_lis))
        unlink($arquivo_js_lis);

    if(file_exists($arquivo_geral))
        unlink($arquivo_geral);



	$ponteiro = fopen ("modulos/" . $linha[0]. "/classe." . $linha[0]. ".php", "w");
	fwrite($ponteiro, $classe);
	fclose ($ponteiro);

	$ponteiro = fopen ($arquivo_modulo, "w");
	fwrite($ponteiro, $modulo);
	fclose ($ponteiro);

	$ponteiro = fopen ($arquivo_form, "w");
	fwrite($ponteiro, $formulario);
	fclose ($ponteiro);

	$ponteiro = fopen ($arquivo_lis, "w");
	fwrite($ponteiro, $tplLis);
	fclose ($ponteiro);

   	$ponteiro = fopen ($arquivo_js_lis, "w");
	fwrite($ponteiro, $js_list);
	fclose ($ponteiro);
   

	$ponteiro = fopen ($arquivo_geral, "w");
	fwrite($ponteiro, $geral);
	fclose ($ponteiro);

	$ponteiro = fopen ($arquivo_js, "w");
	fwrite($ponteiro, $js);
	fclose ($ponteiro);

	$ponteiro = fopen ($arquivo_ajax, "w");
	fwrite($ponteiro, $ajax);
	fclose ($ponteiro);


	unset($classe);
	$id_modulo ++;
}
$ponteiro = fopen ("sql_comando.sql", "w");
fwrite($ponteiro, $txt_sql);
fclose ($ponteiro);

$ponteiro = fopen ("sql_modulo.sql", "w");
fwrite($ponteiro, $txt_sql_modulo);
fclose ($ponteiro);




?>
