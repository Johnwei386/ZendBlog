<?php
namespace ZF2AuthAcl\Utility;

class Smtp
{ 
    public $smtp_port;    
    public $time_out;   
    public $host_name;    
    public $log_file;    
    public $relay_host;    
    public $debug;    
    public $auth;    
    public $user;    
    public $pass;
    private $sock;   
    
    public function __construct($relay_host = "", $smtp_port = 25, $auth = false, $user, $pass) 
    {
        $this->debug = false;    
        $this->smtp_port = $smtp_port;    
        $this->relay_host = $relay_host;    
        $this->time_out = 30; //is used in fsockopen()    
        $this->auth = $auth; //auth    
        $this->user = $user;    
        $this->pass = $pass;    
        $this->host_name = "localhost"; //is used in HELO command
        $this->log_file = '';    
        $this->sock = false;
    }
    
    /* Main Function */    
    public function sendmail($to, $from, $subject = "", $body = "", $mailtype, $cc = "", $bcc = "", $additional_headers = "")
    {
        $mail_from = $this->get_address($this->strip_comment($from));    
        $body = preg_replace("/(^|(".PHP_EOL."))(\\.)/", "\\1.\\3", $body);
        $header = '';
        $header .= "MIME-Version:1.0".PHP_EOL;    
        if ($mailtype == "HTML") {
            $header .= "Content-Type:text/html".PHP_EOL;
        }    
        $header .= "To: " . $to . PHP_EOL;
    
        if ($cc != "") {
            $header .= "Cc: " . $cc . PHP_EOL;
        }    
        $header .= "From: $from<" . $from . ">".PHP_EOL;    
        $header .= "Subject: " . $subject .PHP_EOL;    
        $header .= $additional_headers;    
        $header .= "Date: " . date("r") . PHP_EOL;    
        $header .= "X-Mailer:By Redhat (PHP/" . phpversion() . ")".PHP_EOL;    
        list ($msec, $sec) = explode(" ", microtime());    
        $header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $mail_from . ">".PHP_EOL;    
        $TO = explode(",", $this->strip_comment($to));
        
        if ($cc != "") {
            $TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
        }
    
        if ($bcc != "") {
            $TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
        }
    
        $sent = true;    
        foreach ($TO as $rcpt_to) {
            $rcpt_to = $this->get_address($rcpt_to);    
            if (!$this->smtp_sockopen($rcpt_to)) {
                $this->log_write("Error: Cannot send email to " . $rcpt_to . PHP_EOL);    
                $sent = false;    
                continue;
            }
    
            if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body)) {
                $this->log_write("E-mail has been sent to <" . $rcpt_to . ">".PHP_EOL);
            } else {
                $this->log_write("Error: Cannot send email to <" . $rcpt_to . ">".PHP_EOL);    
                $sent = false;
            }    
            fclose($this->sock);
    
            $this->log_write("Disconnected from remote host".PHP_EOL);
        }    
        return $sent;
    }
    
    /* Private Functions */    
    private function smtp_send($helo, $from, $to, $header, $body = "") {
        if (!$this->smtp_putcmd("HELO", $helo)) {
            return $this->smtp_error("sending HELO command");
        }
        // auth
        if ($this->auth) {
            if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user))) {
                return $this->smtp_error("sending HELO command");
            }
    
            if (!$this->smtp_putcmd("", base64_encode($this->pass))) {
                return $this->smtp_error("sending HELO command");
            }
        }
    
        if (!$this->smtp_putcmd("MAIL", "FROM:<" . $from . ">")) {
            return $this->smtp_error("sending MAIL FROM command");
        }
    
        if (!$this->smtp_putcmd("RCPT", "TO:<" . $to . ">")) {
            return $this->smtp_error("sending RCPT TO command");
        }
    
        if (!$this->smtp_putcmd("DATA")) {
            return $this->smtp_error("sending DATA command");
        }
    
        if (!$this->smtp_message($header, $body)) {
            return $this->smtp_error("sending message");
        }
    
        if (!$this->smtp_eom()) {
            return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
        }
    
        if (!$this->smtp_putcmd("QUIT")) {
            return $this->smtp_error("sending QUIT command");
        }    
        return true;
    }
    
    private function smtp_sockopen($address) 
    {
        if ($this->relay_host == "") {
            return $this->smtp_sockopen_mx($address);
        } else {
            return $this->smtp_sockopen_relay();
        }
    }
    
    private function smtp_sockopen_relay() 
    {
        $this->log_write("Trying to " . $this->relay_host . ":" . $this->smtp_port . PHP_EOL);    
        $this->sock = @ fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);
    
        if (!($this->sock && $this->smtp_ok())) {
            $this->log_write("Error: Cannot connenct to relay host " . $this->relay_host . PHP_EOL);    
            $this->log_write("Error: " . $errstr . " (" . $errno . ")".PHP_EOL);    
            return false;
        }    
        $this->log_write("Connected to relay host " . $this->relay_host . PHP_EOL);
    
        return true;
        ;
    }
    
    private function smtp_sockopen_mx($address) 
    {
        $domain = preg_replace("/^.+@([^@]+)$", "\1/", $address);    
        if (!@ getmxrr($domain, $MXHOSTS)) {
            $this->log_write("Error: Cannot resolve MX '" . $domain . "'".PHP_EOL);    
            return false;
        }
    
        foreach ($MXHOSTS as $host) {
            $this->log_write("Trying to " . $host . ":" . $this->smtp_port . PHP_EOL);    
            $this->sock = @ fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);    
            if (!($this->sock && $this->smtp_ok())) {
                $this->log_write("Warning: Cannot connect to mx host " . $host . PHP_EOL);    
                $this->log_write("Error: " . $errstr . " (" . $errno . ")".PHP_EOL);    
                continue;
            }    
            $this->log_write("Connected to mx host " . $host . PHP_EOL);    
            return true;
        }    
        $this->log_write("Error: Cannot connect to any mx hosts (" . implode(", ", $MXHOSTS) . ")".PHP_EOL);    
        return false;
    }
    
    private function smtp_message($header, $body) 
    {
        fputs($this->sock, $header . PHP_EOL . $body);    
        $this->smtp_debug("> " . str_replace("\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> "));    
        return true;
    }
    
    private function smtp_eom() 
    {
        fputs($this->sock, PHP_EOL.'.'.PHP_EOL);    
        $this->smtp_debug(". [EOM]".PHP_EOL);    
        return $this->smtp_ok();
    }
    
    private function smtp_ok() 
    {
        $response = str_replace(PHP_EOL, "", fgets($this->sock, 512));    
        $this->smtp_debug($response . PHP_EOL);
    
        if (!preg_match("/^[23]/", $response)) {
            fputs($this->sock, "QUIT".PHP_EOL);    
            fgets($this->sock, 512);    
            $this->log_write("Error: Remote host returned '" . $response . "'".PHP_EOL);    
            return false;
        }    
        return true;
    }
    
    private function smtp_putcmd($cmd, $arg = "") 
    {
        if ($arg != "") {
            if ($cmd == ""){
                $cmd = $arg;    
            }else {
                $cmd = $cmd . " " . $arg;
            }
        }
    
        fputs($this->sock, $cmd . PHP_EOL);    
        $this->smtp_debug("> " . $cmd . PHP_EOL);    
        return $this->smtp_ok();
    }
    
    private function smtp_error($string) 
    {
        $this->log_write("Error: Error occurred while " . $string . ".\n");    
        return false;
    }
    
    private function log_write($message) 
    {
        $this->smtp_debug($message);    
        if ($this->log_file == "") {
            return true;
        }    
        $message = date("M d H:i:s ") . get_current_user() . "[" . getmypid() . "]: " . $message;    
        if (!@ file_exists($this->log_file) || !($fp = @ fopen($this->log_file, "a"))) {
            $this->smtp_debug("Warning: Cannot open log file '" . $this->log_file . "'".PHP_EOL);    
            return false;
        }    
        flock($fp, LOCK_EX);   
        fputs($fp, $message);    
        fclose($fp);    
        return true;
    }
    
    private function strip_comment($address) 
    {
        $comment = "/\\([^()]*\\)/"; //提取括号里面的内容    
        while (preg_match($comment, $address)) {
            $address = preg_replace($comment, "", $address);
        }    
        return $address;
    }
    
    private function get_address($address) 
    {
        $address = preg_replace("/([ \t\r\n])+/", "", $address); //将所有的制表符、换行符、回车符都变为空    
        $address = preg_replace("/^.*<(.+)>.*$/", "\\1", $address); //提取尖括号里面的内容    
        return $address;
    }
    
    private function smtp_debug($message) 
    {
        if ($this->debug) {
            echo $message . ";";
        }
    }
}