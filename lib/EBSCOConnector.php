<?php

/**
 * @file
 * The EBSCO Connector and Exception classes.
 *
 * Used when EBSCO API calls return error messages.
 *
 * Copyright [2017] [EBSCO Information Services]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


/**
 * EBSCOException class.
 */
class EBSCOException extends Exception {
  const CRITICAL_ERROR = 1;

  /**
   * Make message argument mandatory.
   */
  public function __construct($message, $code = self::CRITICAL_ERROR, Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
  }

}
/**
 * EBSCOConnector class.
 */
class EBSCOConnector {
  /**
     * Error codes defined by EDS API.
     */
  const EDS_UNKNOWN_PARAMETER              = 100;
  const EDS_INCORRECT_PARAMETER_FORMAT     = 101;
  const EDS_INVALID_PARAMETER_INDEX        = 102;
  const EDS_MISSING_PARAMETER              = 103;
  const EDS_AUTH_TOKEN_INVALID             = 104;
  const EDS_INCORRECT_ARGUMENTS_NUMBER     = 105;
  const EDS_UNKNOWN_ERROR                  = 106;
  const EDS_AUTH_TOKEN_MISSING             = 107;
  const EDS_SESSION_TOKEN_MISSING          = 108;
  const EDS_SESSION_TOKEN_INVALID          = 109;
  const EDS_INVALID_RECORD_FORMAT          = 110;
  const EDS_UNKNOWN_ACTION                 = 111;
  const EDS_INVALID_ARGUMENT_VALUE         = 112;
  const EDS_CREATE_SESSION_ERROR           = 113;
  const EDS_REQUIRED_DATA_MISSING          = 114;
  const EDS_TRANSACTION_LOGGING_ERROR      = 115;
  const EDS_DUPLICATE_PARAMETER            = 116;
  const EDS_UNABLE_TO_AUTHENTICATE         = 117;
  const EDS_SEARCH_ERROR                   = 118;
  const EDS_INVALID_PAGE_SIZE              = 119;
  const EDS_SESSION_SAVE_ERROR             = 120;
  const EDS_SESSION_ENDING_ERROR           = 121;
  const EDS_CACHING_RESULTSET_ERROR        = 122;
  const EDS_INVALID_EXPANDER_ERROR         = 123;
  const EDS_INVALID_SEARCH_MODE_ERROR      = 124;
  const EDS_INVALID_LIMITER_ERROR          = 125;
  const EDS_INVALID_LIMITER_VALUE_ERROR    = 126;
  const EDS_UNSUPPORTED_PROFILE_ERROR      = 127;
  const EDS_PROFILE_NOT_SUPPORTED_ERROR    = 128;
  const EDS_INVALID_CONTENT_PROVIDER_ERROR = 129;
  const EDS_INVALID_SOURCE_TYPE_ERROR      = 130;
  const EDS_XSLT_ERROR                     = 131;
  const EDS_RECORD_NOT_FOUND_ERROR         = 132;
  const EDS_SIMULTANEOUS_USER_LIMIT_ERROR  = 133;
  const EDS_NO_GUEST_ACCESS_ERROR          = 134;
  const EDS_DBID_NOT_IN_PROFILE_ERROR      = 135;
  const EDS_INVALID_SEARCH_VIEW_ERROR      = 136;
  const EDS_RETRIEVING_FULL_TEXT_ERROR     = 137;


  /**
     * HTTP status codes constants
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html.
     *
     * @global integer HTTP_OK        The request has succeeded
     * @global integer HTTP_NOT_FOUND The server has not found anything matching the Request-URI
     */
  const HTTP_OK                    = 200;
  const HTTP_BAD_REQUEST           = 400;
  const HTTP_NOT_FOUND             = 404;
  const HTTP_INTERNAL_SERVER_ERROR = 500;


  /**
   * The HTTP_Request object used for API transactions.
   *
   * @global object HTTP_Request
   */
  private $client;


  /**
   * The URL of the EBSCO API server.
   *
   * @global string
   */
  private static $end_point = 'http://eds-api.ebscohost.com/EDSAPI/rest';


  /**
   * The URL of the EBSCO API server.
   *
   * @global string
   */
  private static $authentication_end_point =  'https://eds-api.ebscohost.com/AuthService/rest';


  /**
   * The password used for API transactions.
   *
   * @global string
   */
  private $password;


  /**
   * The user id used for API transactions.
   *
   * @global string
   */
  private $userId;


  /**
   * The profile ID used for API transactions.
   *
   * @global string
   */
  private $profileId;


  /**
   * The interface ID used for API transactions.
   *
   * @global string
   */
  private $interfaceId;


  /**
   * The customer ID used for API transactions.
   *
   * @global string
   */
  private $orgId;


  /**
   * The isGuest used for API transactions.
   *
   * @global string 'y' or 'n'
   */
  private $isGuest;

  /**
   * Contains the list of ip addresses.
   *
   * @global string
   */
  private $local_ip_address;


  /**
   * You can log HTTP_Request requests using this option.
   *
   * @global bool logAPIRequests
   */

  private $logAPIRequests;

    /**
   * Autocomplete
   * @global string
   */
  private $autoComplete;


  /**
   * The logger object.
   *
   * @global object Logger
   */
  //private $logger;

  /**
   * Constructor.
   *
   * Sets up the EBSCO API settings.
   *
   * @param none
   *
   * @access public
   */
  public function __construct($config) {
    $this->password = $config['password'];
    $this->userId = $config['user'];
    $this->interfaceId = $config['interface'];
    $this->profileId = $config['profile'];
    $this->autoComplete = $config['autocomplete'];
    $this->orgId = $config['organization'];
    $this->local_ip_address = $config['local_ip_address'];
    $this->isGuest = (\Drupal::currentUser()->isAuthenticated() || $this->isGuestIPAddress($_SERVER["REMOTE_ADDR"])) ? 'n' : 'y';
    $this->logAPIRequests = ($config['log'] == 1);
    
  }

  /**
   * Detects if the user is authorized based on the IP address.
   *
   * @return string
   */
  public function isGuestIPAddress($ipUser) {
    $s = $this->local_ip_address;

    if (trim($s) == "") {
      return FALSE;
    }
    // Break records.
    $m = explode(",", $s);

    foreach ($m as $ip) {
      if (strcmp(substr($ipUser, 0, strlen(trim($ip))), trim($ip)) == 0) {
        // Inside of ip address range of customer.
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Public getter for private isGuest .
   *
   * @param none
   *
   * @return string isGuest
   *
   * @access public
   */
  public function isGuest() {
    return $this->isGuest;
  }

  /**
   * Request the authentication token.
   *
   * @param none
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestAuthenticationToken() {
    $url = self::$authentication_end_point .  '/uidauth';

    // Add the body of the request.
    $params = '<UIDAuthRequestMessage xmlns="http://www.ebscohost.com/services/public/AuthService/Response/2012/06/01">'
					.'<UserId>'.$this->userId.'</UserId>'
					.'<Password>'.$this->password.'</Password>'
          .'<InterfaceId>wsapi</InterfaceId>'
          .'<Options>
              <Option>'.$this->autoComplete.'</Option>
            </Options>'
					.'</UIDAuthRequestMessage>';

    $response = $this->request($url,$params, array(), 'POST');

    return $response;
  }

  /**
   * Request the session token.
   *
   * @param array $headers
   *   Authentication token.
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestSessionToken($headers) {
    $url = self::$end_point . '/CreateSession';

    // Add the HTTP query params.
    $params = array(
      'profile' => $this->profileId,
      'org'     => $this->orgId,
      'guest'   => $this->isGuest
    );

    $response = $this->request($url, $params, $headers);

    return $response;
  }

  /**
   * Request the search records.
   *
   * @param array $params
   *   Search specific parameters.
   * @param array $headers
   *   Authentication and session tokens.
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestSearch($params, $headers) {
    $url = self::$end_point . '/Search';

    $response = $this->request($url, $params, $headers);
    return $response;
  }

  /**
   * Request a specific record.
   *
   * @param array $params
   *   Retrieve specific parameters.
   * @param array $headers
   *   Authentication and session tokens.
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestRetrieve($params, $headers) {
    $url = self::$end_point . '/Retrieve';

    $response = $this->request($url, $params, $headers);

    return $response;
  }

  /**
   * Export a specific record.
   *
   * @param array $params
   *   Export specific parameters.
   * @param array $headers
   *   Authentication and session tokens.
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestExport($params, $headers) {
    $url = self::$end_point . '/ExportFormat';

    $response = $this->request($url, $params, $headers);

    return $response;
  }

  /**
   * CitationStyles a specific record.
   *
   * @param array $params
   *   CitationStyles specific parameters.
   * @param array $headers
   *   Authentication and session tokens.
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestCitationStyles($params, $headers) {
    $url = self::$end_point . '/CitationStyles';

    
    $responseCitation = $this->request($url, $params, $headers);
    $response = $responseCitation->Citations;

    return $response;
    
  }


  /**
   * Request the info data.
   *
   * @param null $params
   *   Not used.
   * @param array $headers
   *   Authentication and session tokens.
   *
   * @return object SimpleXml or PEAR_Error
   *
   * @access public
   */
  public function requestInfo($params, $headers) {
    $url = self::$end_point . '/Info';

	
    $response = $this->request($url, $params, $headers);
    return $response;
  }

  /**
   * Send an HTTP request and inspect the response.
   *
   * @param string $url
   *   The url of the HTTP request.
   * @param array $params
   *   The parameters of the HTTP request.
   * @param array $headers
   *   The headers of the HTTP request.
   * @param array $body
   *   The body of the HTTP request.
   * @param string $method
   *   The HTTP method, default is 'GET'.
   *
   * @return object             SimpleXml or PEAR_Error
   *
   * @access protected
   */
  protected function request($url, $params, $headers = array(), $method = 'GET') {
    $xml = FALSE;
    $return = FALSE;
    $data = NULL;

    // Add compression in case its not there.
	array_push($headers,	'Content-Type: text/xml');
	$data=$params;
    // Send the request.
	$response=null;
 

    try {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_MAXREDIRS, 10 );		
		    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
					
        switch ($method)
        {
            case 'GET':
                if ($data) { $url = sprintf('%s?%s', $url, http_build_query($data)); }
				curl_setopt($curl, CURLOPT_URL, $url);
				break;
				
            case 'POST':
                if ($data) { 
					curl_setopt($curl,CURLOPT_POST, 1);
					curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
				}            
				break;
				
			case 'DELETE':
                if ($data) { 
					if (count($headers)>0) {
						curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
					}
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				}
				break;
        }
		
		
        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
		
      switch ($code) {
        case self::HTTP_OK:

          $xml_str = $response;

          try {
            // Clean EMP namespace.
            $xml_str = str_replace(array("<a:", "</a:"), array("<", "</"), $xml_str);
            $xml = simplexml_load_string($xml_str);
            $return = $xml;
          }
          catch (Exception $e) {
            $return = new EBSCOException($xml);
          }
          break;

        case self::HTTP_BAD_REQUEST:
          $xml_str = $response;
          try {
            $xml = simplexml_load_string($xml_str);

            // If the response is an API error.
            $isError = isset($xml->ErrorNumber) || isset($xml->ErrorCode);
            if ($isError) {
              $error = ''; $code = 0;
              if (isset($xml->DetailedErrorDescription) && !empty($xml->DetailedErrorDescription)) {
                $error = (string) $xml->DetailedErrorDescription;
              }
              elseif (isset($xml->ErrorDescription)) {
                $error = (string) $xml->ErrorDescription;
              }
              elseif (isset($xml->Reason)) {
                $error = (string) $xml->Reason;
              }
              if (isset($xml->ErrorNumber)) {
                $code = (integer) $xml->ErrorNumber;
              }
              elseif (isset($xml->ErrorCode)) {
                $code = (integer) $xml->ErrorCode;
              }
              $return = new EBSCOException($error, $code);
            }
            else {
              $return = new EBSCOException("HTTP {$code} : The request could not be understood by the server due to malformed syntax. Modify your search before retrying.");
            }
          }
          catch (Exception $e) {
            $return = new EBSCOException($xml);
          }
          break;

        case self::HTTP_NOT_FOUND:
          $return = new EBSCOException("HTTP {$code} : The resource you are looking for might have been removed, had its name changed, or is temporarily unavailable.");
          break;

        case self::HTTP_INTERNAL_SERVER_ERROR:
          $return = new EBSCOException("HTTP {$code} : The server encountered an unexpected condition which prevented it from fulfilling the request.");
          break;

        default:
          $return = new EBSCOException("HTTP {$code} : Unexpected HTTP error.");
          break;
      }
    }
    catch (Exception $e) {
      // Or $this->toString($response)
      $message = $this->toString($e);
      \Drupal::logger('ebsco')->error($message);
      $return = new EBSCOException($response);
    }

    // Log any error
    if ($this->logAPIRequests) {
    // $client = both the HTTP request and response
    // $response = only the HTTP response
    $message = $this->toString($client); // or $this->toString($response)
    \Drupal::logger('ebsco')->error($client);
    }

    return $return;
  }

  /**
   * Capture the output of print_r into a string.
   *
   * @param object Any object
   *
   * @access private
   */
  private function toString($object) {
    ob_start();
    print_r($object);
    return ob_get_clean();
  }

}
