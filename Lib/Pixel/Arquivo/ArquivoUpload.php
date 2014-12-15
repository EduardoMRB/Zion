<?phpnamespace Pixel\Arquivo;class ArquivoUpload extends \Zion\Arquivo\ManipulaImagem{    public function sisUpload(\Pixel\Form\FormUpload $config)    {        $con = \Zion\Banco\Conexao::conectar();        if ($config->getDisabled() === true) {            return;        }        $uploadCodReferencia = $config->getCodigoReferencia();        $nomeCampo = \str_replace('[]', '', $config->getNome());        $totalDoForm = $this->contaArquivos($nomeCampo);        $upsPermitido = \ini_get("max_file_uploads");        //Maximo de uploads ultrapassado - Existem servidorem que não retornan esse parametro        if ($totalDoForm > $upsPermitido and ! empty($upsPermitido)) {            throw new \Exception("Seu servidor permite envio de no maximo " . $upsPermitido . " arquivos simultaneos, remova alguns arquivos e tente novamente.");        }        $ano = \date('Y');        $mes = \date('m');        $dia = \date('d');        $this->criaDiretorioStorage($ano, $mes, $dia);        $moduloCod = $con->execRLinha("SELECT moduloCod FROM _modulo WHERE moduloNome = '" . MODULO . "'");        if ($uploadCodReferencia) {            $uploadNomeCampo = $nomeCampo;            $totalDoBanco = $con->execRLinha("SELECT COUNT(uploadCod) as total FROM _upload WHERE moduloCod = $moduloCod AND uploadCodReferencia = $uploadCodReferencia AND uploadNomeCampo = '$uploadNomeCampo'");            $removidos = \filter_input(\INPUT_POST, 'sisUR' . $nomeCampo, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY);            $totalRemovidos = \count($removidos);            $totalArquivos = $totalDoForm + ($totalDoBanco - $totalRemovidos);            if (\is_array($removidos)) {                foreach ($removidos as $codigoARemover) {                    $this->removerArquivos($config, $uploadCodReferencia, $codigoARemover);                }            }        } else {            $totalArquivos = $totalDoForm;        }        $minimoArquivos = $config->getMinimoArquivos();        $maximoArquivos = $config->getMaximoArquivos();        if (!empty($minimoArquivos) and $totalArquivos < $minimoArquivos) {            throw new \Exception("Você deve selecionar no minimo " . $minimoArquivos . " arquivo" . (($minimoArquivos > 1) ? "s." : "."));        }        if (!empty($maximoArquivos) and $totalArquivos > $maximoArquivos) {            throw new \Exception("$totalArquivos Você deve selecionar no maximo " . $maximoArquivos . " arquivo" . (($maximoArquivos > 1) ? "s." : "."));        }        $tamanhoMaximo = $config->getTamanhoMaximoEmBytes();        $extensaoPermitida = $config->getExtensoesPermitidas();        $extensaoNaoPermitida = $config->getExtensoesNaoPermitidas();        $alturaMaxima = $config->getAlturaMaxima();        $larguraMaxima = $config->getLarguraMaxima();        $tratarComo = $config->getTratarComo();        if ($totalDoForm > 0) {            foreach (\array_keys($_FILES[$nomeCampo]['name']) as $posicao) {                $nomeOriginal = $_FILES[$nomeCampo]['name'][$posicao];                $uploadMime = $_FILES[$nomeCampo]['type'][$posicao];                $tamanhoEmBytes = $_FILES[$nomeCampo]['size'][$posicao];                $origem = $_FILES[$nomeCampo]['tmp_name'][$posicao];                $extensao = \strtolower($this->extenssaoArquivo($nomeOriginal));                if (\is_array($extensaoPermitida)) {                    $extensaoPermitida = \array_map(\strtolower, $extensaoPermitida);                    if (!\in_array($extensao, $extensaoPermitida)) {                        throw new \Exception("Extensão para o arquivo " . $nomeOriginal . " não é permitida. Use somente:" . \implode(", ", $extensaoPermitida));                    }                }                if (\is_array($extensaoNaoPermitida)) {                    $extensaoNaoPermitida = \array_map(\strtolower, $extensaoNaoPermitida);                    if (\in_array($extensao, $extensaoNaoPermitida)) {                        throw new \Exception("Extensão para o arquivo " . $nomeOriginal . " não é permitida.");                    }                }                if ($tamanhoMaximo and $tamanhoEmBytes > $tamanhoMaximo) {                    throw new \Exception("O tamanho maximo de arquivo permitido é " . (($tamanhoMaximo / 1048576 * 100000) / 100000) . "MB");                }                //Grava na Tabela                            $con->executar("INSERT INTO _upload (moduloCod, uploadCodReferencia, uploadNomeCampo, uploadNomeOriginal, uploadDataCadastro, uploadMime)                 VALUES ($moduloCod, $uploadCodReferencia,'$nomeCampo','$nomeOriginal','" . ($ano . '-' . $mes . '-' . $dia) . "','$uploadMime')");                $uID = $con->ultimoInsertId();                //Definindo Hash                $hashA = \crypt($nomeOriginal, mt_rand()) . crypt($uID, mt_rand()) . $uID;                //Remove barras do hash se existir                $hashC = \str_replace(['/', '\\', '.'], "9", $hashA) . '.' . $extensao;                //Atualiza Código Hash                $con->executar("UPDATE _upload SET uploadNomeFisico = '$hashC' WHERE uploadCod = $uID");                //Setando Destino                $destino = SIS_DIR_BASE . 'Storage/' . $ano . '/' . $mes . '/' . $dia . '/' . $hashC;                if ($tratarComo === "IMAGEM") {                    $this->uploadImagem($nomeOriginal, $origem, $destino, $alturaMaxima, $larguraMaxima);                    //Grava TB                    if ($config->getThumbnail() === true) {                        $alturaMaximaTB = $config->getAlturaMaximaTB();                        $larguraMaximaTB = $config->getLarguraMaximaTB();                        $destinoTB = SIS_DIR_BASE . 'Storage/' . $ano . '/' . $mes . '/' . $dia . '/tb/' . $hashC;                        $this->uploadImagem($nomeOriginal, $destino, $destinoTB, $alturaMaximaTB, $larguraMaximaTB);                    }                } else {                    $this->uploadArquivo($origem, $destino);                }            }        }    }    public function removerArquivos(\Pixel\Form\FormUpload $config, $uploadCodReferencia, $uploadCod = 0)    {        $con = \Zion\Banco\Conexao::conectar();        $sqlWhere = empty($uploadCod) ? "uploadCodReferencia = $uploadCodReferencia" : "uploadCodReferencia = $uploadCodReferencia AND uploadCod = $uploadCod";                $rS = $con->executar("SELECT uploadCod, uploadNomeFisico, uploadDataCadastro FROM _upload WHERE " . $sqlWhere);        $nL = $con->nLinhas($rS);        if ($nL < 1) {            return;        }        while ($dados = $rS->fetch()) {            //Remove do banco            $con->executar("DELETE FROM _upload WHERE uploadCodReferencia = $uploadCodReferencia AND uploadCod = " . $dados['uploadCod']);            //Diretorios            $diretorioBase = SIS_DIR_BASE . 'Storage/' . \str_replace('-', '/', $dados['uploadDataCadastro']) . '/';            $diretorioDestino = $diretorioBase . $dados['uploadNomeFisico'];            try {                //Tenta Remover do servidor                $this->removeArquivo($diretorioDestino);                if ($config->getThumbnail() === true) {                    $diretorioDestinoTB = $diretorioBase . 'tb/' . $dados['uploadNomeFisico'];                    $this->removeArquivo($diretorioDestinoTB);                }            } catch (\Exception $e) {                //não faz nada.            }        }    }    public function visualizarArquivos($nomeCampo, $uploadCodReferencia)    {        $htmlArquivos = '';        if (\is_numeric($uploadCodReferencia)) {            $con = \Zion\Banco\Conexao::conectar();            $rS = $con->executar("SELECT uploadCod, uploadCodReferencia, uploadNomeOriginal, uploadDataCadastro FROM _upload WHERE uploadCodReferencia = $uploadCodReferencia");            $nR = $con->nLinhas($rS);            if ($nR > 0) {                $htmlArquivos.= '<div class="">Arquivos:</div>';                while ($dados = $rS->fetch()) {                    $htmlArquivos.= '<div class="">';                    //sisUploadRemovido                    $htmlArquivos.= '<label><input name="sisUR' . $nomeCampo . '[]" type="checkbox" value="' . $dados['uploadCod'] . '" /> Remover </label>';                    $htmlArquivos.= ' - <a href="' . SIS_URL_BASE . 'Storage/ArquivoDownload.php?uploadCod=' . $dados['uploadCod'] . '&modo=download" alt="' . $dados['uploadNomeOriginal'] . '" border="0" >' . $dados['uploadNomeOriginal'] . '</a>';                    $htmlArquivos.= '</div>';                }            }        }        return $htmlArquivos;    }    public function criaDiretorioStorage($ano, $mes, $dia)    {        $this->criaDiretorio(SIS_DIR_BASE . 'Storage/' . $ano, 0777);        $this->criaDiretorio(SIS_DIR_BASE . 'Storage/' . $ano . '/' . $mes, 0777);        $this->criaDiretorio(SIS_DIR_BASE . 'Storage/' . $ano . '/' . $mes . '/' . $dia, 0777);        $this->criaDiretorio(SIS_DIR_BASE . 'Storage/' . $ano . '/' . $mes . '/' . $dia . '/tb', 0777);    }    private function contaArquivos($nomeCampo)    {        if (empty($_FILES[$nomeCampo]['name'][0])) {            return 0;        } else {            return \count($_FILES[$nomeCampo]['name']);        }    }}