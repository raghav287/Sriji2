<?php

class SMTPMailer
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $debug = false;

    public function __construct($host, $port, $username, $password, $fromEmail, $fromName)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send($to, $subject, $body)
    {
        $socket = fsockopen("ssl://{$this->host}", $this->port, $errno, $errstr, 30);
        if (!$socket) {
            $this->log("Error connecting: $errstr ($errno)");
            return false;
        }

        $this->read($socket); // Welcome message

        if (!$this->command($socket, "EHLO " . $_SERVER['SERVER_NAME'], 250))
            return false;
        if (!$this->command($socket, "AUTH LOGIN", 334))
            return false;
        if (!$this->command($socket, base64_encode($this->username), 334))
            return false;
        if (!$this->command($socket, base64_encode($this->password), 235))
            return false;

        if (!$this->command($socket, "MAIL FROM: <{$this->fromEmail}>", 250))
            return false;
        if (!$this->command($socket, "RCPT TO: <$to>", 250))
            return false;

        if (!$this->command($socket, "DATA", 354))
            return false;

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n";

        fwrite($socket, "$headers\r\n$body\r\n.\r\n");
        $response = $this->read($socket);

        if (substr($response, 0, 3) != '250') {
            $this->log("Error sending data: $response");
            return false;
        }

        $this->command($socket, "QUIT", 221);
        fclose($socket);
        return true;
    }

    private function command($socket, $cmd, $expectedCode)
    {
        fwrite($socket, $cmd . "\r\n");
        $response = $this->read($socket);
        if (substr($response, 0, 3) != $expectedCode) {
            $this->log("Command failed: $cmd. Response: $response");
            return false;
        }
        return true;
    }

    private function read($socket)
    {
        $response = "";
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ")
                break;
        }
        if ($this->debug)
            echo "SERVER: $response<br>";
        return $response;
    }

    private function log($msg)
    {
        if ($this->debug)
            echo "DEBUG: $msg<br>";
        error_log("SMTP Error: $msg");
    }
}
?>