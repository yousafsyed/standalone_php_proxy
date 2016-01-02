<?php
namespace YousafSyed;

/**
 * ProxyServer is a standalone HTTP Proxy Server.
 * Which acts as standard HTTP proxy server and can
 * be used in Browser or in curl request as a normal proxy.
 *
 * (The MIT License)
 *
 * ProxyServer is Copyright (c) 2015 Yousaf Syed
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the 'Software'), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject
 * to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT
 * OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
error_reporting(E_STRICT);
class ProxyServer
{
    
    /**
     * The Host Name or IP to bind.
     *
     * @var $host
     */
    private $host;
    
    /**
     * Port on which ProxyServer will Listen
     *
     * @var $port
     */
    private $port;
    
    /**
     * Array That contains connected Socket Clients
     *
     * @var $clients
     */
    private $clients = array();
    
    /**
     * Colors array for console logging
     *
     * @var $consoleColor
     */
    private $consoleColor = array(
        'green' => "\033[32m"
    );
    
    /**
     * Guzzle HTTP client Instance
     *
     * @var $clients \GuzzleHttp\Client
     */
    private $guzzle;
    
    /**
     * Parameters array/string for HTTP request
     *
     * @var $params
     */
    private $params;
    
    /**
     * White color or ending color
     *
     * @var $endColor
     */
    private $endColor = "\033[0m";
    
    /**
     * Variable to store request time
     *
     * @var $time
     */
    private $time;
    
    /**
     * HTTP method of request TYPE
     *
     * @var $reqType
     */
    private $reqtype;
    
    /**
     * Time when request is initiated
     *
     * @var $startTime
     */
    private $startTime;
    
    /**
     * Total time elapsed to complete the request
     *
     * @var $timeElapsed
     */
    private $timeElapsed;
    
    /**
     * Construct a ProxyServer instance.
     *
     * @param String $port
     * @param String $host
     */
    function __construct($port = "8000", $host = "localhost") {
        $this->host = $host;
        $this->port = $port;
        $this->guzzle = new \GuzzleHttp\Client();
        $this->params = array(
            'exceptions' => false
        );
    }
    
    /**
     * Run function donot takes any parameters,only job of this
     * function is to start the whole processe of ProxyServer and
     * bind the host and port and start listening for connection.
     *
     */
    
    public function run() {
        
        //manage multipal connections
        $this->clients;
        
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        //reuseable port
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        //bind socket to specified host
        socket_bind($socket, 0, $this->port);
        
        //listen to port
        socket_listen($socket);
        echo "\033[32mListening on port " . $this->port . PHP_EOL . "\033[0m";
        
        //create & add listning socket to the list
        $clients = array(
            $socket
        );
        $this->listen($clients, $socket);
    }
    
    /**
     * Listen function loops through on connected clients for any
     * Change or Messages and notifies when client disconnects.
     *
     * @param Mixed $clients
     * @param Socket $socket
     */
    public function listen($clients, $socket) {
        while (true) {
            $changed = $clients;
            $null = NULL;
            
            //returns the socket resources in $changed array
            socket_select($changed, $null, $null, 0, 10);
            
            //check for new socket
            if (in_array($socket, $changed)) {
                $socket_new = socket_accept($socket);
                
                //accpet new socket
                $this->clients[] = $socket_new;
                
                //add socket to client array
                
                $this->onRequest($socket_new);
                
                //perform websocket handshake
                
                //make room for new socket
                $found_socket = array_search($socket, $changed);
                unset($changed[$found_socket]);
            }
            $this->CheckClients($changed);
        }
    }
    
    /**
     * This function checks the status of clients or state of client
     * and triggers dissconnected function if client disconnects
     *
     *
     * @param Socket $changed
     */
    public function CheckClients($changed) {
        
        //loop through all connected sockets
        foreach ($this->clients as $changed_socket) {
            
            //check for any incomming data
            $buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
            if ($buf === false) {
                $this->disconnected($changed_socket);
            }
        }
    }
    
    /**
     * Handle's disconnected clients and makes room for new
     *
     * @param Socket $disconnectedClient
     */
    
    public function disconnected($disconnectedClient) {
        
        // check disconnected client
        // remove client for $clients array
        $found_socket = array_search($disconnectedClient, $this->clients);
        
        unset($this->clients[$found_socket]);
        
        //notify all users about disconnected connection
        echo "Disconnected" . PHP_EOL;
    }
    
    /**
     * Handle's HTTP request by parsing RAW headers
     *
     * @param Socket $socket_new
     */
    public function onRequest($socket_new) {
        $this->startTime = microtime(true);
        $header = socket_read($socket_new, 1024);
        $message = $this->handle_request($header);
        $this->send($message, $socket_new);
        $this->log_request($this->reqtype, $this->current_url, 'green');
    }
    
    /**
     * Send responce back to client and close the socket connection.
     *
     * @param String $message
     * @param Socket $client
     */
    
    public function send($message, $client) {
        
        @socket_write($client, $message, strlen($message));
        socket_close($client);
        return true;
    }
    
    /**
     * Log the request to console screen
     *
     * @param String $type
     * @param String $url
     * @param String $color
     */
    private function log_request($type, $url, $color) {
        $this->time = date('m/d/Y H:i:s');
        $this->time_elapsed_secs = microtime(true) - $this->startTime;
        $this->time_elapsed_secs = round($this->time_elapsed_secs * 1000);
        echo $this->time . " " . $this->consoleColor[$color] . " " . $type . $this->endColor . " " . "$url  " . $this->time_elapsed_secs . " ms" . PHP_EOL;
    }
    
    /**
     * Handle HTTP request by parsing raw HTTP REQUEST to Guzzle Client
     *
     *
     * @param String $req // Request
     * @return String $responce // Response String
     */
    public function handle_request($req) {
        
        $headers = $req;
        
        $req = explode("\n", $req);
        
        $HTTP_INFO = explode(" ", $req[0]);
        $reqType = $HTTP_INFO[0];
        $this->reqtype = $reqType;
        if ($reqType == "CONNECT") {
            $reqUrl = "https://" . $HTTP_INFO[1];
            return "https is not supported";
        } 
        else {
            $reqUrl = $HTTP_INFO[1];
        }
        
        $this->current_url = $reqUrl;
        $trimmed_array = array_map('trim', $req);
        unset($req[0]);
        
        $headers = $this->http_parse_headers($headers);
        $headersArray = array();
        
        unset($headers['Host']);
        unset($headers['User-Agent']);
        unset($headers['Accept']);
        unset($headers['Proxy-Connection']);
        unset($headers['Content-Length']);
        foreach ($headers as $key => $value) {
            $headersArray[] = $key . ": " . trim($value);
        }
        
        $url = $reqUrl;
        
        $this->params['headers'] = $headers;
        
        $data = "";
        $postData = "";
        if (strpos($headers['Content-Type'], "x-www-form-urlencoded") !== false) {
            
            $postData = $req[count($req) ];
            $array = array();
            parse_str($postData, $array);
            $postData = $array;
        }
        
        if ($reqType == "POST") {
            
            $this->params['body'] = $postData;
            $data = $this->guzzle->post($url, $this->params);
        } 
        else {
            
            $data = $this->guzzle->get($url, $this->params);
        }
        
        return $data->getBody(true);
    }
    
    /**
     * HTTP Header parser from raw String
     *
     *
     * @param String $req // Headers in String
     * @return Array $retVal // Headers in array
     */
    function http_parse_headers($header) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    $retVal[$match[1]] = array(
                        $retVal[$match[1]],
                        $match[2]
                    );
                } 
                else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }
}
