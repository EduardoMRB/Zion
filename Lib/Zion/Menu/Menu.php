<?phpnamespace Zion\Menu;class Menu extends MenuSql{    private $urlBase;    private $urlModuloBase;    private $arrayGrupo;    private $arrayModulo;    private $arrayPermissao;    private $gruposSelecionados;    private $modulosSemReferencia;    private $modulosComReferencia;    private $modulosReferentes;    private $arrayPacote;    public function __construct()    {        parent::__construct();        $this->urlBase = \SIS_URL_BASE;        $this->urlModuloBase = \SIS_URL_DEFAULT_BASE;        $this->arrayGrupo = array();        $this->arrayModulo = array();        $this->arrayPermissao = array();        $this->gruposSelecionados = array();        $this->modulosSemReferencia = array();        $this->modulosComReferencia = array();        $this->modulosReferentes = array();        $this->arrayPacote = array();    }    /**     * Menu::geraMenu()     * Monta um JSON com a estrutura de menus, de acordo com as permissões atribuídas ao usuário logado.     *      * @return string JSON com a estrura encontrada. Array ([1] => Array ([Grupo] => Grupo Maior [ModulosGrupo] => Modulos e Submodulos do grupo))     * @example Sempre que um array dentro de ModulosGrupo tiver um outro array na chave Subs, este será um subgrupo podendo ter módulos e outros subgrupos, recursivamente. False otherwise.     */    public function geraMenu($twig = false)    {        if (!isset($_SESSION['usuarioCod'])) {            return \json_encode(array("sucesso" => false, "retorno" => "Usuário não autenticado."));        }        $usuarioCod = $_SESSION['usuarioCod'];        $this->arrayGrupo = $this->con->paraArray(parent::gruposDiponiveisSql());        $this->arrayModulo = $this->con->paraArray(parent::modulosDiponiveisSql(), null, "modulocod");        $this->arrayPermissao = $this->con->paraArray(parent::usuarioPermissaoModuloSql($usuarioCod), "modulocod");        $this->processaDetalhes();        $json = array();        foreach ($this->arrayGrupo as $dadosGrupo) {            if (!\in_array($dadosGrupo['grupocod'], $this->gruposSelecionados)) {                continue;            }            $this->arrayPacote[$dadosGrupo['grupocod']] = $dadosGrupo['grupopacote'];            $grupoModuloCod = $dadosGrupo['grupocod'];            $json[$grupoModuloCod]['grupo'] = $dadosGrupo['gruponome']; //Inicia um grupo.            $json[$grupoModuloCod]['grupoClass'] = $dadosGrupo['grupoclass']; //Inicia um grupo.            $json[$grupoModuloCod]['modulosGrupo'] = $this->geraModulos($dadosGrupo['grupocod']); //Carrega os módulos e subgrupos do grupo recém-iniciado.        }        if ($twig === true) {            return $json;        }        return \json_encode(array('sucesso' => true, 'retorno' => $json));    }    /**     * Menu::processaDetalhes()     *      * @return     */    private function processaDetalhes()    {        $todosModulos = array();        foreach ($this->arrayModulo as $dadosModulo) {            $todosModulos[$dadosModulo['modulocod']] = $dadosModulo['modulocod'];        }        $this->processaPermissoes($todosModulos);        foreach ($this->arrayModulo as $dadosModulo) {            if (\in_array($dadosModulo['modulocod'], $this->arrayPermissao)) {                $this->gruposSelecionados[$dadosModulo['grupocod']] = $dadosModulo['grupocod'];            }            if (empty($dadosModulo['modulocodreferente'])) {                $this->modulosSemReferencia[$dadosModulo['grupocod']][$dadosModulo['modulocod']] = $dadosModulo['modulocod'];            } else {                $this->modulosComReferencia[$dadosModulo['modulocod']] = $dadosModulo['modulocodreferente'];                $this->modulosReferentes[$dadosModulo['modulocodreferente']][] = $dadosModulo['modulocod'];            }        }    }    /**     * Menu::processaPermissoes()     *      * @param mixed $todosModulos     * @return     */    private function processaPermissoes($todosModulos)    {        foreach ($todosModulos as $moduloCod) {            if (!empty($this->arrayModulo[$moduloCod]['modulocodreferente']) and \in_array($moduloCod, $this->arrayPermissao)) {                $this->analisaPermissaoRecursivo($moduloCod);            }        }        $this->arrayPermissao = array_unique($this->arrayPermissao);    }    /**     * Menu::analisaPermissaoRecursivo()     *      * @param mixed $moduloCod     * @param mixed $buffer     * @return     */    private function analisaPermissaoRecursivo($moduloCod, $buffer = array())    {        $buffer[$moduloCod] = $moduloCod;        if (empty($this->arrayModulo[$moduloCod]['modulocodreferente'])) {            $this->arrayPermissao = \array_merge($this->arrayPermissao, $buffer);        } else {            $this->analisaPermissaoRecursivo($this->arrayModulo[$moduloCod]['modulocodreferente'], $buffer);        }    }    /**     * Menu::geraModulos()     *      * @param mixed $grupoCod     * @return     */    private function geraModulos($grupoCod)    {        $nModulos = \count($this->modulosSemReferencia[$grupoCod]);        //Inicia Módulo        $json = array();        if ($nModulos > 0) {            foreach ($this->modulosSemReferencia[$grupoCod] as $moduloCod) {                if (\in_array($moduloCod, $this->arrayPermissao)) {                    \array_push($json, $this->geraModulo($moduloCod));                }            }        }        return $json;    }    /**     * Menu::geraModulo()     *      * @param mixed $moduloCod     * @return     */    private function geraModulo($moduloCod)    {        //Se módulo Possui Sub Modulo        if (\in_array($moduloCod, $this->modulosComReferencia)) {            return $this->geraSubModulo($moduloCod);        }        $dadosModulo = $this->arrayModulo[$moduloCod];        if (\in_array($moduloCod, $this->arrayPermissao) and $dadosModulo['modulovisivelmenu'] == 'S') {            return $this->populaModulo($dadosModulo['modulonome'], $dadosModulo['modulonome'], $dadosModulo['modulonomemenu'], $this->arrayPacote[$dadosModulo['grupocod']], $dadosModulo['modulobase'], $dadosModulo['moduloclass'],$dadosModulo['modulonamespace']);        }    }    /**     * Menu::geraSubModulo()     *      * @param mixed $moduloCod     * @return     */    private function geraSubModulo($moduloCod)    {        $dadosSubModulo = $this->arrayModulo[$moduloCod];        $nModulos = \count($this->modulosReferentes[$moduloCod]);        $json = array();        if ($nModulos > 0 and \in_array($moduloCod, $this->arrayPermissao)) {            $json = $this->iniciaSubModulo($dadosSubModulo['modulonome'], $dadosSubModulo['modulonome'], $dadosSubModulo['modulonomemenu'], $dadosSubModulo['moduloclass']);            foreach ($this->modulosReferentes[$moduloCod] as $referenciaCod) {                if (\in_array($referenciaCod, $this->modulosComReferencia)) {                    $json['subs'][$referenciaCod] = $this->geraModulo($referenciaCod);                } else {                    $json['subs'][$referenciaCod] = $this->geraModulo($referenciaCod);                }            }            return $json;        }    }    /**     * MenuCss::iniciaSubModulo()     *      * @param mixed $moduloDesc     * @param mixed $nomeMenu     * @return     */    private function iniciaSubModulo($moduloNome, $moduloDesc, $nomeMenu, $moduloClass = '')    {        return array("modulo" => $moduloNome, "menu" => $nomeMenu, "menuDesc" => $moduloDesc, "menuUrl" => "#", "moduloClass" => $moduloClass);    }    /**     * MenuCss::populaModulo()     *      * @param mixed $moduloNome     * @param mixed $moduloDesc     * @param mixed $nomeMenu     * @param mixed $pacote     * @param mixed $moduloBase     * @param string $moduloNamespace Namespace do Módulo (opcional)     * @return     */    private function populaModulo($moduloNome, $moduloDesc, $nomeMenu, $pacote, $moduloBase, $moduloClass = '', $moduloNamespace = '')    {        if($moduloNamespace){            $url = $this->urlBase . $moduloNamespace;        }        else {            if ($moduloBase) {                $url = $this->urlModuloBase . $pacote . "/" . $moduloNome . "/";            } else {                $url = $this->urlBase . $pacote . "/" . $moduloNome . "/";            }        }        return array("modulo" => $moduloNome, "menu" => $nomeMenu, "menuDesc" => $moduloDesc, "menuUrl" => $url, "moduloClass" => $moduloClass, "subs" => false);    }}