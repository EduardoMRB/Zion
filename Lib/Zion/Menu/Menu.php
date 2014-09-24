<?/** * @author Pablo Vanni - pablovanni@gmail.com * @since 01/06/2006 * Última Atualização: 28/05/2007 * Última Atualização: 16/03/2010 * Última Atualização: 10/02/2011 Passou a Trabalhar com Vetores - Corrigidos Erros de Acesso - Muitooo Mais Rápida * Autualizada Por: Pablo Vanni - pablovanni@gmail.com * @name Gera um menu para acesso aos módulos do sistema * @version 2.0 * @package Framework */class Menu extends MenuBD{    private $css;    private $html;    private $arrayGrupo = array();    private $arrayModulo = array();    private $arrayPermissao = array();    private $gruposSelecionados = array();    private $modulosSemReferencia = array();    private $modulosComReferencia = array();    private $modulosReferentes = array();    private $arrayPacote = array();    public function __construct()    {        parent::MenuBd();        $this->css = new MenuCss();        $this->html = "";    }    public function setHtml($valor)    {        $this->html.= $valor;    }    public function getHtml()    {        return $this->html;    }    public function geraMenu($usuarioCod = "")    {        if (!is_numeric($usuarioCod))            $usuarioCod = $_SESSION['UsuarioCod'];        $this->arrayGrupo = $this->con->execTodosArray(parent::gruposDiponiveisSql());        $this->arrayModulo = $this->con->execTodosArray(parent::modulosDiponiveisSql(), null, "ModuloCod");        $this->arrayPermissao = $this->con->execTodosArray(parent::usuarioPermissaoModuloSql($usuarioCod), "ModuloCod");        $this->processaDetalhes();        $ajusteLinha = (count($this->gruposSelecionados) * 119);        $this->setHtml("\n<div id=\"menu\" style=\"width:" . $ajusteLinha . "px; \">\n");        foreach ($this->arrayGrupo as $dadosGrupo) {            if (!in_array($dadosGrupo['GrupoCod'], $this->gruposSelecionados)) {                continue;            }            $this->arrayPacote[$dadosGrupo['GrupoCod']] = $dadosGrupo['Pacote'];            $this->setHtml($this->css->iniciaGrupo($dadosGrupo['GrupoDesc']));            $this->geraModulos($dadosGrupo['GrupoCod']);            $this->setHtml($this->css->finalizaGrupo());        }        $this->setHtml("</div>\n");        return $this->getHtml();    }    private function processaDetalhes()    {        $TodosModulos = array();        foreach ($this->arrayModulo as $dadosModulo) {            $TodosModulos[$dadosModulo['ModuloCod']] = $dadosModulo['ModuloCod'];        }        $this->processaPermissoes($TodosModulos);        foreach ($this->arrayModulo as $dadosModulo) {            if (in_array($dadosModulo['ModuloCod'], $this->arrayPermissao)) {                $this->gruposSelecionados[$dadosModulo['GrupoCod']] = $dadosModulo['GrupoCod'];            }            if (empty($dadosModulo['ModuloReferente'])) {                $this->modulosSemReferencia[$dadosModulo['GrupoCod']][$dadosModulo['ModuloCod']] = $dadosModulo['ModuloCod'];            } else {                $this->modulosComReferencia[$dadosModulo['ModuloCod']] = $dadosModulo['ModuloReferente'];                $this->modulosReferentes[$dadosModulo['ModuloReferente']][] = $dadosModulo['ModuloCod'];            }        }    }    private function processaPermissoes($TodosModulos)    {        foreach ($TodosModulos as $moduloCod) {            if (!empty($this->arrayModulo[$moduloCod]['ModuloReferente']) and in_array($moduloCod, $this->arrayPermissao)) {                $this->analisaPermissaoRecursivo($moduloCod);            }        }        $this->arrayPermissao = array_unique($this->arrayPermissao);    }    private function analisaPermissaoRecursivo($moduloCod, $buffer = array())    {        $buffer[$moduloCod] = $moduloCod;        if (empty($this->arrayModulo[$moduloCod]['ModuloReferente'])) {            $this->arrayPermissao = array_merge($this->arrayPermissao, $buffer);        } else {            $this->analisaPermissaoRecursivo($this->arrayModulo[$moduloCod]['ModuloReferente'], $buffer);        }    }    private function geraModulos($grupoCod)    {        $nModulos = count($this->modulosSemReferencia[$grupoCod]);        //Inicia Módulo        $this->setHtml($this->css->iniciaModulo());        if ($nModulos > 0) {            foreach ($this->modulosSemReferencia[$grupoCod] as $moduloCod) {                $this->geraModulo($moduloCod);            }        }        //Finaliza Módulo        $this->setHtml($this->css->finalizaModulo());    }    private function geraModulo($moduloCod)    {        //Se módulo Possui Sub Modulo        if (in_array($moduloCod, $this->modulosComReferencia)) {            return $this->geraSubModulo($moduloCod);        }        $dadosModulo = $this->arrayModulo[$moduloCod];        if (in_array($moduloCod, $this->arrayPermissao) and $dadosModulo['VisivelMenu'] == 'S') {            $this->setHtml($this->css->populaModulo($dadosModulo['ModuloNome'], $dadosModulo['ModuloDesc'], $dadosModulo['NomeMenu'], $this->arrayPacote[$dadosModulo['GrupoCod']], $dadosModulo['ModuloBase']));        }    }    private function geraSubModulo($moduloCod)    {        $dadosSubModulo = $this->arrayModulo[$moduloCod];        $nModulos = count($this->modulosReferentes[$moduloCod]);        if ($nModulos > 0 and in_array($moduloCod, $this->arrayPermissao)) {            $this->setHtml($this->css->iniciaSubModulo($dadosSubModulo['ModuloDesc'], $dadosSubModulo['NomeMenu']));            foreach ($this->modulosReferentes[$moduloCod] as $referenciaCod) {                if (in_array($referenciaCod, $this->modulosComReferencia)) {                    $this->geraSubModulo($referenciaCod);                } else {                    $this->geraModulo($referenciaCod);                }            }            //Finaliza Sub Módulo            $this->setHtml($this->css->finalizaSubModulo());        }    }}