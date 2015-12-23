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
        
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        //echo $msg;
        $from->send("req recieved");
        $this->handle_request($msg);
        $from->close();
        foreach ($this->clients as $client) {
            
            // The sender is not the receiver, send to each client connected
            $client->send($msg);
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage() }\n";
        
        $conn->close();
    }
    
    public function handle_request($req) {
        echo $req;
        $req = explode("\n", $req);
        
        $HTTP_INFO = explode(" ", $req[0]);
        $reqType = $HTTP_INFO[0];
        $reqUrl = $HTTP_INFO[1];
        $trimmed_array = array_map('trim', $req);
        unset($req[0]);
        print_r($req);
        $data = array();
        $this->parse_raw_http_request($data);
        print_r($data);
        
        // $req = explode(PHP_EOL,$req);
        
        // return $request;
        
        
    }
    public function parse_raw_http_request(array & $a_data) {
        
        // read incoming data
        $input = file_get_contents('php://input');
        
        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        
        // content type is probably regular form-encoded
        if (!count($matches)) {
            
            // we expect regular puts to containt a query string containing data
            parse_str(urldecode($input) , $a_data);
            return $a_data;
        }
        
        $boundary = $matches[1];
        
        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);
        
        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block)) continue;
            
            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
            
            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE) {
                
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $a_data['files'][$matches[1]] = $matches[2];
            }
            
            // parse all other fields
            else {
                
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                $a_data[$matches[1]] = $matches[2];
            }
        }
    }
}
