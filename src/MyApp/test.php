<?php
function get_page($url, $headers) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        // return headers 0 no 1 yes
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json, text/javascript, */*','Accept-Encoding: gzip,deflate,sdch' ,'Content-type: application/xml','X-TS-AJAX-Request: true','X-Requested-With: XMLHttpRequest'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
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
        
        // the page encoding
        //curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        $data = curl_exec($ch);
        
        // execute the http request
        curl_close($ch);
        
        return $data;
    }