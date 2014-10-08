<?/** * @author Pablo Vanni - pablovanni@gmail.com * @since 01/06/2006 * Última Atualização: 28/05/2007 * Autualizada Por: Pablo Vanni - pablovanni@gmail.com * @name Metodos de interação com a base de dados * @version 2.0 * @package Framework */ namespace Zion\Menu;abstract class MenuSql{    protected $con;    public function __construct()    {        $this->con = \Zion\Banco\Conexao::conectar();    }    /**     * 	Retorna os Grupos disponíveis para determinado usuário     * 	Utilizado para gerar tamanho do menu     * 	@return Quantidade de Grupos     */    protected function gruposDiponiveisUsuario($UsuarioCod)    {        $Sql = "SELECT a.GrupoModuloCod                 FROM _grupo_modulo a INNER JOIN  _modulo b ON (a.ModuloCod = b.ModuloCod)                     INNER JOIN _acao_modulo c ON (b.ModuloCod = c.ModuloCod)                      INNER JOIN _permissao d ON (c.AcaoModuloCod = d.AcaoModuloCod)                WHERE d.UsuarioCod = $UsuarioCod LIMIT 1";        return $this->con->execNLinhas($Sql);    }    /**     * 	Retorna os grupos disponiveis no sistema     * 	@return ResultSet     */    protected function gruposDiponiveis()    {        $Sql = "SELECT 	 GrupoModuloCod, GrupoModuloDesc, GrupoModuloPacote				FROM	 _grupo_modulo  				ORDER BY GrupoModuloPosicao ASC";        return $this->con->executar($Sql);    }    protected function gruposDiponiveisSql()    {        $Sql = "SELECT 	 GrupoModuloCod, GrupoModuloDesc, GrupoModuloPacote				FROM	 _grupo_modulo				ORDER BY GrupoModuloPosicao ASC";        return $Sql;    }    protected function modulosDiponiveisSql()    {        $Sql = "SELECT 	 ModuloCod, GrupoModuloCod, ModuloCodReferente, ModuloNome,                          ModuloDesc, ModuloNomeMenu, ModuloBase, ModuloVisivelMenu  				FROM	 _modulo                WHERE    1                 ORDER BY ModuloPosicao ASC";        return $Sql;    }    protected function usuarioPermissaoModuloSql($UsuarioCod)    {        $Sql = "SELECT DISTINCT(ModuloCod) as ModuloCod                FROM   _acao_modulo a INNER JOIN _permissao b ON (a.AcaoModuloCod = b.AcaoModuloCod)                WHERE  b.TipoPermissaoCod IS NOT NULL                AND    b.UsuarioCod = $UsuarioCod";        return $Sql;    }    protected function dadosModulo($ModuloCod, $Visivel = true)    {        $Visibilidade = $Visivel == false ? "" : " AND VisivelMenu = 'S' ";        $Sql = "SELECT a.ModuloCod, a.GrupoCod, a.ModuloNome,                       a.ModuloDesc, a.NomeMenu, a.ModuloBase,                       b.Pacote                FROM   _modulos a, _grupomodulo b                WHERE  a.GrupoCod = b.GrupoCod                       $Visibilidade AND                       a.ModuloCod = $ModuloCod";        return $this->con->execLinha($Sql);    }    /**     * 	Retorna os grupos referentes     * 	@return ResultSet     */    protected function modulosReferentes($Referencia, $Visivel = true)    {        $Visibilidade = $Visivel == false ? "" : " AND VisivelMenu = 'S' ";        $Sql = "SELECT 	 ModuloCod, NomeMenu				FROM	 _modulos 				WHERE 	 ModuloReferente = " . $Referencia . "                                           $Visibilidade				ORDER BY Posicao ASC";        return $this->con->executar($Sql);    }    /**     * 	Retorna os m�dulos disponiveis no sistema para cada grupo     * 	@param GrupoCod String - C�digo do Grupo     * 	@param Mostrar String  - T -> Todos, V ->Visiveis no menu     * 	@return ResultSet     */    protected function modulosGrupoSemReferencia($GrupoCod, $Mostrar = "V")    {        $condicaoMostrar = ($Mostrar == "V") ? " AND a.VisivelMenu = 'S' " : "";        $Sql = "SELECT a.ModuloCod				FROM   _modulos a, _grupomodulo b 				WHERE  a.GrupoCod = b.GrupoCod             $condicaoMostrar					   AND a.grupocod= " . $GrupoCod . "					   AND a.ModuloReferente = 0 				ORDER BY a.Posicao ASC";        return $this->con->executar($Sql);    }    protected function existeSubModulo($ModuloCod, $Mostrar = "V")    {        $condicaoMostrar = ($Mostrar == "V") ? " VisivelMenu = 'S' AND" : "";        $Sql = "SELECT ModuloCod FROM _modulos WHERE $condicaoMostrar ModuloReferente = $ModuloCod ";        return ($this->con->execNLinhas($Sql) > 0) ? true : false;    }    /**     * 	Retorna o SQL para o n�mero de permiss�es ativas para um grupo inteiro     * 	@param GrupoCod String - C�digo do Grupo     * 	@return String     */    protected function sqlPermissaoGrupo($GrupoCod)    {        $Sql = "SELECT count(a.GrupoCod) Total				FROM   _grupomodulo a, _modulos b, 					   _opcoes_modulo c, _usuarios d,					   _tipo_permissao e   				WHERE  a.GrupoCod         = b.GrupoCod					   AND b.ModuloCod    = c.ModuloCod  					   AND e.UsuarioCod   = d.UsuarioCod 					   AND c.OpcoesModuloCod = e.OpcoesModuloCod 					   AND d.UsuarioCod   = " . $_SESSION['UsuarioCod'] . " 					   AND a.GrupoCod     = " . $GrupoCod . "					   AND e.Permissao    = 'S'";        return $Sql;    }    /**     * 	Retorna o n�mero de permiss�es ativas para um grupo inteiro     * 	@param GrupoCod String - C�digo do Grupo     * 	@return Inteiro     */    protected function permissaoGrupo($GrupoCod)    {        $TotalDiretos = $this->con->execRLinha($this->sqlPermissaoGrupo($GrupoCod));        $TotalGeral = $TotalDiretos; // + $contRef;        return ((int) $TotalGeral);    }    /**     * 	Retorna o n�mero Permiss�es ativas para um m�dulo especifico     * 	@param GrupoCod String - C�digo do Grupo     * 	@param ModuloCod String - C�digo do M�dulo     * 	@return Inteiro     */    protected function ocorrenciasModulo($GrupoCod, $ModuloCod)    {        //Verifica o numero de ocorrencias para este grupo        $Sql = "SELECT count(a.GrupoCod) Total				FROM   _grupomodulo a, _modulos b, 					   _opcoes_modulo c, _usuarios d,					   _tipo_permissao e   				WHERE  a.GrupoCod         = b.GrupoCod					   AND b.ModuloCod    = c.ModuloCod  					   AND e.UsuarioCod   = d.UsuarioCod 					   AND c.OpcoesModuloCod = e.OpcoesModuloCod					   AND b.ModuloCod    = " . $ModuloCod . " 					   AND d.UsuarioCod   = " . $_SESSION['UsuarioCod'] . " 					   AND a.GrupoCod     = " . $GrupoCod . "					   AND e.Permissao    = 'S'";        $LinhaTotal = $this->con->execRLinha($Sql);        return $LinhaTotal['Total'];    }    /**     * @abstract Retorna se existe algum submenu filho do modulo que o usuario tenha permissao     * @author Yuri Gauer Marques     */    protected function checaPermissaoMenuPai($ModuloCod)    {        $Sql = "SELECT a.ModuloCod Total FROM _modulos a, _opcoes_modulo b, _tipo_permissao c                WHERE a.ModuloReferente = " . $ModuloCod . " AND                a.ModuloCod = b.ModuloCod AND                b.OpcoesModuloCod = c.OpcoesModuloCod AND                c.UsuarioCod = " . $_SESSION['UsuarioCod'] . "  AND                c.Permissao = 'S' LIMIT 1";        $Ocorrencias = $this->con->executar($Sql);        $NumOcorrencias = $this->con->nLinhas($Ocorrencias);        return $NumOcorrencias;    }}?>