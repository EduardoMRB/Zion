<?php/** * @author Pablo Vanni - pablovanni@gmail.com */namespace Zion\Email;class PhpMailer{    public function enviarEmail($Email, $Assunto, $msg)    {        $fPHP = new FuncoesPHP();        if (!$fPHP->verificaEmail($Email))            throw new Exception("O Email '$Email' é Inválido!");        $mail = new PHPMailer();        $mail->IsSMTP();        $mail->Host = ConfigSIS::$CFG['HostEmail'];        $mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)        $mail->Username = ConfigSIS::$CFG['EmailPadrao']; // Usuário do servidor SMTP        $mail->Password = ConfigSIS::$CFG['SenhaEmail']; // Senha do servidor SMTP        $mail->From = ConfigSIS::$CFG['EmailPadrao']; // Seu e-mail        $mail->FromName = ConfigSIS::$CFG['EmailTag']; // Seu nome        $mail->AddAddress($Email, 'Usuário do Site');        $mail->IsHTML(true);        $mail->CharSet = 'UTF-8';        //$mail->SMTPDebug = 2;         if (ConfigSIS::$CFG['PortaEmail']) {            $mail->Port = ConfigSIS::$CFG['PortaEmail'];            $mail->SMTPSecure = "ssl";        }        $mail->Subject = $Assunto; // Assunto da mensagem        $mail->Body = $msg;        $Enviado = $mail->Send();        $mail->clearAllRecipients();        if (!$Enviado) {            throw new Exception('Não foi possivel enviar o e-mail, motivo: ' . $mail->ErrorInfo);        }    }    public function enviarEmailGrupo($GrupoEmails, $Assunto, $msg)    {        $fPHP = new FuncoesPHP();        $mail = new PHPMailer();        $mail->IsSMTP();        $mail->Host = ConfigSIS::$CFG['HostEmail'];        $mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)        $mail->Username = ConfigSIS::$CFG['EmailPadrao']; // Usuário do servidor SMTP        $mail->Password = ConfigSIS::$CFG['SenhaEmail']; // Senha do servidor SMTP        $mail->From = ConfigSIS::$CFG['EmailPadrao']; // Seu e-mail        $mail->FromName = ConfigSIS::$CFG['EmailTag']; // Seu nome                $mail->IsHTML(true);        $mail->CharSet = 'iso-8859-1';        if (ConfigSIS::$CFG['PortaEmail']) {            $mail->Port = ConfigSIS::$CFG['PortaEmail'];            $mail->SMTPSecure = "ssl";        }        $mail->Subject = $Assunto; // Assunto da mensagem        $mail->Body = $msg;        $TotalEnviados = 0;        foreach ($GrupoEmails as $Email) {            if ($fPHP->verificaEmail($Email)) {                $mail->AddAddress($Email, 'Usuário do Site');                $Enviado = $mail->Send();                $mail->clearAllRecipients();                if ($Enviado) {                    $TotalEnviados++;                }            }        }        return $TotalEnviados;    }    public function teste()    {        return 'funcionei';    }}