<?namespace Zion\Layout;class Html{    public function abreTagAberta($tag, array $atributos = array(), array $complementos = array())    {        $attr = vsprintf(str_repeat('%s ', count($atributos)), $this->montaAtributos($atributos));        $comp = vsprintf(str_repeat('%s ', count($complementos)), $this->montaComplementos($complementos));        return '<' . $tag . ' ' . $attr . $comp . '>' . "\n";    }    public function abreTagFechada($tag, array $atributos = array(), array $complementos = array())    {        $attr = vsprintf(str_repeat('%s ', count($atributos)), $this->montaAtributos($atributos));        $comp = vsprintf(str_repeat('%s ', count($complementos)), $this->montaComplementos($complementos));        return '<' . $tag . ' ' . $attr . $comp . '>' . "\n";    }        public function entreTags($tag, $texto)    {        return '<'.$tag.'>'.$texto.'</'.$tag.'>' . "\n";    }    public function abreComentario()    {        return '<!-- ';    }    public function fechaComentario()    {        return ' -->' . "\n";    }    private function montaAtributos(array $atributos)    {        $ret = [];        foreach ($atributos as $attr => $valor) {            $ret[] = $attr . '=' . '"' . $valor . '"';        }        return $ret;    }    private function montaComplementos(array $complementos)    {        $ret = [];        foreach ($complementos as $attr => $valor) {            $ret[] = $attr . '=' . $valor;        }        return $ret;    }    public function fechaTag($tag)    {        return '</' . $tag . '>' . "\n";    }}