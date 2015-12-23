<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Proxy implements MessageComponentInterface
{
    protected $clients;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    
    public function onOpen(ConnectionInterface $conn) {
        
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        
        echo "New request! ({$conn->resourceId})\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        //echo $msg;
        $from->send($this->handle_request($msg));
        
        $from->close();
        
        // foreach ($this->clients as $client) {
        
        //     // The sender is not the receiver, send to each client connected
        //     $client->send($msg);
        // }
        
        
    }
    
    public function onClose(ConnectionInterface $conn) {
        
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        
        echo "Request {$conn->resourceId} has completed\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage() }\n";
        
        $conn->close();
    }
    
    public function handle_request($req) {
        
        $headers = $req;
        $req = explode("\n", $req);
        
        $HTTP_INFO = explode(" ", $req[0]);
        $reqType = $HTTP_INFO[0];
        $reqUrl = $HTTP_INFO[1];
        $trimmed_array = array_map('trim', $req);
        unset($req[0]);
        
        $headers = $this->http_parse_headers($headers);
        $headersArray =array();
        unset($headers['Host']);
        unset($headers['User-Agent']);
        unset($headers['Accept']);
        unset($headers['Proxy-Connection']);
        foreach($headers as $key => $header){

            $headersArray[] = $key.": ".$header;
        }

        return $this->get_page($reqUrl, $headersArray);
        
        // $req = explode(PHP_EOL,$req);
        
        // return $request;
        
        
    }
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
    
    public function get_proxy() {
        $proxy = file("proxy-list.txt");
        
        return $proxy[array_rand($proxy) ];
    }
    
    public function get_page($url, $headers) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        // return headers 0 no 1 yes
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json, text/javascript, */*','Accept-Encoding: gzip,deflate,sdch' ,'Content-type: application/xml','X-TS-AJAX-Request: true','X-Requested-With: XMLHttpRequest'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        print_r( $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        curl_setopt($ch, CURLOPT_PROXY, $this->get_proxy());
        // return page 1:yes
        //curl_setopt($ch, CURLOPT_REFERER, 'https://myaccount.stubhub.com/myaccount/listings');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        // http request timeout 20 seconds
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Follow redirects, need this if the url changes
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        
        //if http server gives redirection responce
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7");
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // false for https
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // the page encoding
        //curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $data = curl_exec($ch);
        
        // execute the http request
        curl_close($ch);
        
        return $data;
    }
}
