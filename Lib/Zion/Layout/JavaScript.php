<?php/** * @author Pablo Vanni - pablovanni@gmail.com * @since 15/05/2006 * Última Atualização: 14/10/2014 * Autualizada Por: Pablo Vanni - pablovanni@gmail.com * @name Agrupa as funções de validação em JavaScript */namespace Zion\Layout;class JavaScript{    /**     * JavaScript::srcJS()     *      * @param mixed $src     * @return     */    public function srcJS($src)    {        return '<script src="' . $src . '"></script>';    }    /**     * JavaScript::abreJS()     *      * @return     */    public function abreJS()    {        return '<script type="text/javascript"> ';    }    /**     * JavaScript::fechaJS()     *      * @return     */    public function fechaJS()    {        return ' </script>';    }    /**     * JavaScript::abreFuncao()     *      * @param mixed $nome     * @param mixed $parametros     * @return     */    public function abreFuncao($nome, $parametros)    {        return 'function ' . $nome . '(' . $parametros . '){ ';    }    /**     * JavaScript::fechaFuncao()     *      * @return     */    public function fechaFuncao()    {        return ' } ';    }    /**     * JavaScript::entreJS()     *      * @param mixed $codigo     * @return     */    public function entreJS($codigo)    {        return $this->abreJS() . $codigo . $this->fechaJS();    }    /**     * JavaScript::redireciona()     *      * @param mixed $url     * @return     */    public function redireciona($url)    {        return ' window.location="' . $url . '" ';    }    /**     * JavaScript::abreLoadJQuery()     *      * @return     */    public function abreLoadJQuery()    {        return ' $(document).ready(function() { ';    }    /**     * JavaScript::fechaLoadJQuery()     *      * @return     */    public function fechaLoadJQuery()    {        return ' }); ';    }}