<?phpnamespace Zion\Email;use Zion\Exception\EmailException;class Email{    /**     * PhpMailer::enviarEmail()     *      * @param string $email     * @param string $assunto     * @param string $msg     * @param string $from posição em $SIS_CFG['mailAccounts'] contendo as informações de login no servidor smtp.     * @return bool     */    public function enviarEmail($email, $assunto, $msg, $from)    {        $geral = \Zion\Validacao\Geral::instancia();        if ($geral->validaEmail($email) === false){            throw new EmailException("O Email '". $email ."' é Inválido!");        }        require \SIS_NAMESPACE_FRAMEWORK . '/phpMailer/PHPMailerAutoload.php';        $mail = new \PHPMailer();        $namespace = '\\' . \SIS_ID_NAMESPACE_PROJETO . '\\Config';        $configs = $namespace::$SIS_CFG['mailAccounts'][$from];        $mail->IsSMTP();        $mail->Host = $configs['host'];        $mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)        $mail->Username = $configs['email']; // Usuário do servidor SMTP        $mail->Password = $configs['pass']; // Senha do servidor SMTP        $mail->From = $configs['email']; // Seu e-mail        $mail->FromName = $configs['fromName']; // Seu nome        $mail->AddAddress($email, \SIS_NOME_PROJETO);        $mail->IsHTML(true);        $mail->CharSet = 'UTF-8';                //$mail->SMTPDebug = 1;        if (!empty($configs['secureSmtp'])) {            $mail->Port = $configs['port'];            $mail->SMTPSecure = $configs['secureSmtp'];        }        $mail->Subject = $assunto; // Assunto da mensagem        $mail->Body = $msg;        $Enviado = $mail->Send();        $mail->clearAllRecipients();        if (!$Enviado) {            throw new EmailException('Não foi possivel enviar o e-mail. Tente novamente em instantes. <span style="display: none;">'. $mail->ErrorInfo .'</span>');        }        return true;    }}