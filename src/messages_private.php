<?php

define('BOT_TOKEN', '241165842:AAEInOobvO6PXnxXbIj-5ywqGUBSHsZc4cg');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

function request($method, $params = array(), $options = array()) {
    $options += array(
      'http_method' => 'GET',
      'timeout' => $this->netTimeout,
    );
    $params_arr = array();
    foreach ($params as $key => &$val) {
      if (!is_numeric($val) && !is_string($val)) {
        $val = json_encode($val);
      }
      $params_arr[] = urlencode($key).'='.urlencode($val);
    }
    $query_string = implode('&', $params_arr);
    $url = $this->apiUrl.'/'.$method;
    if ($options['http_method'] === 'POST') {
      curl_setopt($this->handle, CURLOPT_SAFE_UPLOAD, false);
      curl_setopt($this->handle, CURLOPT_POST, true);
      curl_setopt($this->handle, CURLOPT_POSTFIELDS, $query_string);
    } else {
      $url .= ($query_string ? '?'.$query_string : '');
      curl_setopt($this->handle, CURLOPT_HTTPGET, true);
    }
    $connect_timeout = $this->netConnectTimeout;
    $timeout = $options['timeout'] ?: $this->netTimeout;
    curl_setopt($this->handle, CURLOPT_URL, $url);
    curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
    curl_setopt($this->handle, CURLOPT_TIMEOUT, $timeout);
    $response_str = curl_exec($this->handle);
    $errno = curl_errno($this->handle);
    $http_code = intval(curl_getinfo($this->handle, CURLINFO_HTTP_CODE));
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    } else if ($http_code >= 500 || $errno) {
      sleep($this->netDelay);
      if ($this->netDelay < 30) {
        $this->netDelay *= 2;
      }
    }
    $response = json_decode($response_str, true);
    return $response;
  }