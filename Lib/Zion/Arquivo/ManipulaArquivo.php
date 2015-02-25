<?php/** * *    Sappiens Framework *    Copyright (C) 2014, BRA Consultoria * *    Website do autor: www.braconsultoria.com.br/sappiens *    Email do autor: sappiens@braconsultoria.com.br * *    Website do projeto, equipe e documentação: www.sappiens.com.br *    *    Este programa é software livre; você pode redistribuí-lo e/ou *    modificá-lo sob os termos da Licença Pública Geral GNU, conforme *    publicada pela Free Software Foundation, versão 2. * *    Este programa é distribuído na expectativa de ser útil, mas SEM *    QUALQUER GARANTIA; sem mesmo a garantia implícita de *    COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM *    PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais *    detalhes. *  *    Você deve ter recebido uma cópia da Licença Pública Geral GNU *    junto com este programa; se não, escreva para a Free Software *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA *    02111-1307, USA. * *    Cópias da licença disponíveis em /Sappiens/_doc/licenca * */namespace Zion\Arquivo;class ManipulaArquivo extends ManipulaDiretorio{    /**     * Recupera a extenssão de um arquivo atraves da do seu nome     * @param string $arquivo     * @return array     */    public function extenssaoArquivo($arquivo)    {        if(\substr_count($arquivo, '.') < 1){            return '';        }                $vetExt = \explode(".", $arquivo);        return $vetExt[\count($vetExt) - 1];    }    /**     * Faz o upload de um arquivo     * @param string $origem - caminho de origem do arquivo     * @param string $destino - caminho de destino do arquivo     * @throws \Exception     */    public function uploadArquivo($origem, $destino)    {        $postMax = \ini_get("post_max_size");        $upMax = \ini_get("upload_max_filesize");        //Menor Tamanho de Configuração        $tMax = $postMax > $upMax ? $upMax : $postMax;        //Verifica a integridade do arquivo        if (!$this->arquivoExiste($origem)) {            throw new \Exception("O Arquivo não foi carregado, certifique-se que o tamanho do arquivo não tenha ultrapassado " . $tMax . " pois, este tamanho é o maximo permitido pelo seu servidor.");        }        //Verifica se a pasta permite gravação        if (!$this->permiteEscrita(\dirname($destino))) {            throw new \Exception("A pasta onde você esta tentando gravar o arquivo não tem permissão de escrita, contate o administrador do sistema.");        }        //Verifica se o arquivo ja existe        if ($this->arquivoExiste($destino)) {            //Se sim verifica se tem permissão para substitui-lo            if (!$this->permiteEscrita($destino)) {                throw new \Exception("Este arquivo já existe e você não tem permissão para substituí-lo.");            }        }        //Upload        if (!\move_uploaded_file($origem, $destino)) {            throw new \Exception("Não foi possivel fazer o upload!" . $destino);        }    }    public function substituir($origem, $localizarSubstituir)    {        $link = \fopen($origem, "r");        $buffer = '';        while (!\feof($link)) {            $linha = \fgets($link, 4096);            if (\is_array($localizarSubstituir)) {                foreach ($localizarSubstituir as $chave => $valor) {                    $linha = \str_replace($chave, $valor, $linha);                }            }            $buffer .= $linha;        }        return $buffer;    }    public function gravaArquivo($origem, $conteudo)    {        if (\is_file($origem)) {            throw new \Exception("O Arquivo já existe:\n" . $origem);        }        //Gravando o Novo Arquivo        $link = \fopen($origem, "a");        \fwrite($link, $conteudo);        if (!\fclose($link)) {            throw new \Exception("Não foi possivel gerar o arquivo:\n" . $origem);        }    }    public function localizarESubstituirRecursivo($origem, $localizarSubstituir, $ext = [], $especificos = [])    {        $iterator = new \DirectoryIterator($origem);        foreach ($iterator as $info) {            if ($info->isFile()) {                if ($ext and ! \in_array($this->extenssaoArquivo($info->getFilename()), $ext)) {                    continue;                }                if ($especificos and ! \in_array($info->getFilename(), $especificos)) {                    continue;                }                $origemAtual = $origem . \DIRECTORY_SEPARATOR . $info->getFilename();                $conteudo = $this->substituir($origemAtual, $localizarSubstituir);                $this->removeArquivo($origemAtual);                $this->gravaArquivo($origemAtual, $conteudo);            } elseif (!$info->isDot()) {                $this->localizarESubstituirRecursivo($origem . DIRECTORY_SEPARATOR . $info->getFilename(), $localizarSubstituir, $ext);            }        }    }}