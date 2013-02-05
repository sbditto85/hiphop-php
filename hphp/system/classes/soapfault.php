<?php

class SoapFault extends Exception {
  public $faultcode;
  public $faultcodens;
  public $faultstring;
  public $faultactor;
  public $detail;
  public $_name;
  public $headerfault;

  public function __construct($code, $message, $actor = null, $detail = null,
                              $name = null, $header = null) {
    $fault_ns = null;
    $fault_code = null;
    if (is_string($code)) {
      $fault_code = $code;
    } else if (is_array($code) && count($code) == 2) {
      $code = array_values($code);
      $fault_ns = $code[0];
      $fault_code = $code[1];
      if (!is_string($fault_ns) || !is_string($fault_code)) {
        hphp_throw_fatal_error("Invalid fault code"); 
        return;
      }
    } else  {
      hphp_throw_fatal_error("Invalid fault code"); 
      return;
    }
    $this->faultcodens = $fault_ns;
    $this->faultcode = $fault_code;
    if (empty($this->faultcode)) {
      hphp_throw_fatal_error("Invalid fault code"); 
      return;
    }

    $this->faultstring = $this->message = $message;
    $this->faultactor = $actor;
    $this->detail = $detail;
    $this->_name = $name;
    $this->headerfault = $header;

    $SOAP_1_1 = 1;
    $SOAP_1_2 = 2;
    $SOAP_1_1_ENV_NAMESPACE = 'http://schemas.xmlsoap.org/soap/envelope/';
    $SOAP_1_2_ENV_NAMESPACE = 'http://www.w3.org/2003/05/soap-envelope';

    $soap_version = _soap_active_version();
    if (empty($this->faultcodens)) {
      if ($soap_version == $SOAP_1_1) {
        if ($this->faultcode == "Client" ||
            $this->faultcode == "Server" ||
            $this->faultcode == "VersionMismatch" ||
            $this->faultcode == "MustUnderstand") {
          $this->faultcodens = $SOAP_1_1_ENV_NAMESPACE;
        }
      } else if ($soap_version == $SOAP_1_2) {
        if ($this->faultcode == "Client") {
          $this->faultcode = "Sender";
          $this->faultcodens = $SOAP_1_2_ENV_NAMESPACE;
        } else if ($this->faultcode == "Server") {
          $this->faultcode = "Receiver";
          $this->faultcodens = $SOAP_1_2_ENV_NAMESPACE;
        } else if ($this->faultcode == "VersionMismatch" ||
                   $this->faultcode == "MustUnderstand" ||
                   $this->faultcode == "DataEncodingUnknown") {
          $this->faultcodens = $SOAP_1_2_ENV_NAMESPACE;
        }
      }
    }
  }

  public function __toString() {
    return "SoapFault exception: [" . $this->faultcode . "] " .
           $this->faultstring;
  }
}