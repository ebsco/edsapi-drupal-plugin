<?php

/**
 * @file
 * The EBSCO EDS API class.
 *
 * PHP version 5
 *
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

require_once 'EBSCOConnector.php';
require_once 'EBSCOResponse.php';

/**
 * EBSCO API class.
 */
class EBSCOAPI {
  /**
   * The authentication token used for API transactions.
   *
   * @global string
   */
  private $authenticationToken;


  /**
   * The session token for API transactions.
   *
   * @global string
   */
  private $sessionToken;


  /**
   * The EBSCOConnector object used for API transactions.
   *
   * @global object EBSCOConnector
   */
  private $connector;


  /**
   * Configuration options.
   */
  private $config;

  /**
   * VuFind search types mapped to EBSCO search types
   * used for urls in search results / detailed result.
   *
   * @global array
   */
  private static $search_tags = array(
    ''          => '',
    'AllFields' => '',
    'Abstract'  => 'AB',
    'Author'    => 'AU',
    'Source'    => 'SO',
    'Subject'   => 'SU',
    'Title'     => 'TI',
    'ISBN'     => 'IB',
    'ISSN'     => 'IS',
  );


  /**
   * EBSCO sort options .
   *
   * @global array
   */
  private static $sort_options = array(
    'relevance',
    'date',
    'date2',
    'source',
  );


  /**
   * VuFind sort types mapped to EBSCO sort types
   * used for urls in Search results / Detailed view.
   *
   * @global array
   */
  private static $mapped_sort_options = array(
    ''           => 'relevance',
    'relevance'  => 'relevance',
    'subject'    => 'date',
    'date'       => 'date2',
    'date_asc'   => 'date2',
    'date_desc'  => 'date',
    'callnumber' => 'date',
    'author'     => 'author',
    'title'      => 'date',
  );

  /**
   * Constructor.
   *
   * @param array config
   *
   * @access public
   */
  public function __construct($config) {
    $this->config = $config;

  }

  /**
   * Setter / Getter for authentication token.
   *
   * @param string     The authentication token
   *
   * @return string or none
   *
   * @access public
   */
  public function authenticationToken($token = NULL) {
    if (empty($token)) {
      $token = $this->readSession('authenticationToken');
      return !empty($token) ? $token : $this->authenticationToken;
    }
    else {
      $this->authenticationToken = $token;
      $this->writeSession('authenticationToken', $token);
    }
  }

  /**
   * Setter / Getter for session token.
   *
   * @param string     The session token
   *
   * @return string or none
   *
   * @access public
   */
  public function sessionToken($token = NULL) {
    if (empty($token)) {
      $token = $this->readSession('sessionToken');
      return !empty($token) ? $token : $this->sessionToken;
    }
    else {
      $this->sessionToken = $token;
      $this->writeSession('sessionToken', $token);
    }
  }

  /**
   * Getter for isGuest.
   *
   * @param string 'y' or 'n'
   *
   * @return string or none
   *
   * @access public
   */
  public function isGuest($boolean = NULL) {
    if (empty($boolean)) {
      return $this->readSession('isGuest');
    }
    else {
      $this->writeSession('isGuest', $boolean);
    }
  }

  /**
   * Create a new EBSCOConnector object or reuse an existing one.
   *
   * @param none
   *
   * @return EBSCOConnector object
   *
   * @access public
   */
  public function connector() {
    if (empty($this->connector)) {
      $this->connector = new EBSCOConnector($this->config);
    }
    return $this->connector;
  }

  /**
   * Create a new EBSCOResponse object.
   *
   * @param object $response
   *
   * @return EBSCOResponse object
   *
   * @access public
   */
  public function response($response) {
    $responseObj = new EBSCOResponse($response);
    return $responseObj;
  }

  /**
   * Request authentication and session tokens, then send the API request.
   * Retry the request if authentication errors occur.
   *
   * @param string $action
   *   The EBSCOConnector method name.
   * @param array $params
   *   The parameters of the HTTP request.
   * @param int $attempts
   *   The number of retries.
   *
   * @return object             SimpleXml DOM or PEAR Error
   *
   * @access protected
   */
  protected function request($action, $params = NULL, $attempts = 5) {
    $authenticationToken = $this->authenticationToken();
    $sessionToken = $this->sessionToken();

    // If authentication token is missing then the session token is missing too, so get both tokens
    // If session token is missing then the authentication token may be invalid, so get both tokens.
    if (empty($authenticationToken) || empty($sessionToken)) {
      $result = $this->apiAuthenticationAndSessionToken();
      if ($this->isError($result)) {
        // Any error should terminate the request immediately
        // in order to prevent infinite recursion.
        return $result;
      }
    }

    // Any change of the isGuest should request a new session
    // (and don't terminate the current request if there was an error during the session request
    // since it's not that important)
    if ($this->isGuest() != $this->connector()->isGuest()) {
      $this->apiSessionToken();
    }

    $headers = array(
      'x-authenticationToken: '.$this->authenticationToken(),
      'x-sessionToken: '.$this->sessionToken(),
    );

    $response = call_user_func_array(array($this->connector(), "request{$action}"), array($params, $headers));
    if ($this->isError($response)) {
      // Retry the request if there were authentication errors.
      $code = $response->getCode();
      switch ($code) {
        // If authentication token is invalid then the session token is invalid too, so get both tokens
        // If session token is invalid then the authentication token may be invalid too, so get both tokens.
        case EBSCOConnector::EDS_AUTH_TOKEN_INVALID:
          $result = $this->apiAuthenticationToken();
          if ($this->isError($result)) {
            // Any error should terminate the request immediately
            // in order to prevent infinite recursion.
            return $result;
          }
          if ($attempts > 0) {
            $result = $this->request($action, $params, --$attempts);
          }
          break;

        case EBSCOConnector::EDS_SESSION_TOKEN_INVALID:
          $result = $this->apiAuthenticationAndSessionToken();
          if ($this->isError($result)) {
            // Any error should terminate the request immediately
            // in order to prevent infinite recursion.
            return $result;
          }
          if ($attempts > 0) {
            $result = $this->request($action, $params, --$attempts);
          }
          break;

        default:
          $result = $this->handleError($response);
          break;
      }
    }
    else {
      $result = $this->response($response)->result();
    }

    return $result;
  }

  /**
   * Wrapper for authentication API call.
   *
   * @param none
   *
   * @access public
   */
  public function apiAuthenticationToken() {
    $response = $this->connector()->requestAuthenticationToken();

    if ($this->isError($response)) {
      return $response;
    }
    else {
      $result = $this->response($response)->result();
      if (isset($result['authenticationToken'])) {
        $this->authenticationToken($result['authenticationToken']);
        return $result['authenticationToken'];
      }
      else {
        return new EBSCOException("No authentication token was found in the response.");
      }
    }
  }

  /**
   * Wrapper for session API call.
   *
   * @param none
   *
   * @access public
   */
  public function apiSessionToken() {
    // Add authentication tokens to headers.
    $headers = array(
      'x-authenticationToken: '.$this->authenticationToken(),
    );

    $response = $this->connector()->requestSessionToken($headers);
    // Raise the exception so that any code running this method should exit immediately.
    if ($this->isError($response)) {
      return $response;
    }
    else {
      $result = $this->response($response)->result();
      if (is_string($result)) {
        $this->sessionToken($result);
        return $result;
      }
      else {
        return new EBSCOException("No session token was found in the response.");
      }
    }
  }

  /**
   * Initialize the authentication and session tokens.
   *
   * @param none
   *
   * @access public
   */
  public function apiAuthenticationAndSessionToken() {
    $authenticationToken = $this->apiAuthenticationToken();
    if ($this->isError($authenticationToken)) {
      // An authentication error should terminate the request immediately.
      return $authenticationToken;
    }

    $sessionToken = $this->apiSessionToken();
    if ($this->isError($sessionToken)) {
      // A session error should terminate the request immediately.
      return $sessionToken;
    }

    // We don't have to return anything, both tokens can be accessed using the getters.
    return TRUE;
  }

  /**
   * Wrapper for search API call.
   *
   * @param array $search
   *   The search terms.
   * @param array $filters
   *   The facet filters.
   * @param string $start
   *   The page to start with.
   * @param string $limit
   *   The number of records to return.
   * @param string $sortBy
   *   The value to be used by for sorting.
   * @param string $amount
   *   The amount of data to be returned.
   * @param string $mode
   *   The search mode.
   *
   * @throws object             PEAR Error
   *
   * @return array              An array of query results
   *
   * @access public
   */
  public function apiSearch($search,
  $filters,
  $start = 1,
  $limit = 10,
  $sortBy = 'relevance',
  $amount = 'detailed',
  $mode = 'all',
  $rs = FALSE,
  $emp = FALSE,
  $autosuggest = FALSE,
  $includeimagequickview = FALSE,
  $styles = '',
  $IllustrationInfo = FALSE
  ) {
    $query = array();

    // Basic search.
    if (!empty($search['lookfor'])) {
      $lookfor = $search['lookfor'];
      $type = isset($search['index']) && !empty($search['index']) ? $search['index'] : 'AllFields';

      // Escape some characters from lookfor term.
      $term = str_replace(array(',', ':', '(', ')'), array('\,', '\:', '\(', '\)'), $lookfor);
      // Replace multiple consecutive empty spaces with one empty space.
      $term = preg_replace("/\s+/", ' ', $term);

      // Search terms
      // Complex search term.
      if (preg_match('/(.*) (AND|OR) (.*)/i', $term)) {
        $query['query'] = $term;
      }
      else {
        $tag = self::$search_tags[$type];
        $op = 'AND';
        $query_str = implode(',', array($op, $tag));
        $query_str = implode(($tag ? ':' : ''), array($query_str, $term));
        $query['query-1'] = $query_str;
      }

      // Advanced search.
    }
    elseif (!empty($search['group'])) {

      $counter = 1;
      foreach ($search['group'] as $group) {
        $type = $group['type'];
        if (isset($group['lookfor'])) {
          $term = $group['lookfor'];
          $op = isset($group['bool'])?$group['bool']:"AND";
          $tag = $type && isset(self::$search_tags[$type]) ? self::$search_tags[$type] : '';

          // Escape some characters from lookfor term.
          $term = str_replace(array(',', ':', '(', ')'), array('\,', '\:', '\(', '\)'), $term);
          // Replace multiple consecutive empty spaces with one empty space.
          $term = preg_replace("/\s+/", ' ', $term);
          if (!empty($term)) {
            $query_str = implode(',', array($op, $tag));
            $query_str = implode(($tag ? ':' : ''), array($query_str, $term));
            $query["query-$counter"] = $query_str;
            $counter++;
          }
        }
      }

      // No search term, return an empty array.
    }
    else {
      $results = array(
        'recordCount' => 0,
        'numFound'    => 0,
        'start'       => 0,
        'documents'   => array(),
        'facets'      => array(),
      );
      return $results;
    }

    // Add filters.
    $limiters = array(); $expanders = array(); $facets = array();
    foreach ($filters as $filter) {
      if (preg_match('/addlimiter/', $filter)) {
        list($action, $str) = explode('(', $filter, 2);
        // e.g. FT:y or GZ:Student Research, Projects and Publications.
        $field_and_value = substr($str, 0, -1);
        list($field, $value) = explode(':', $field_and_value, 2);
        $limiters[$field][] = $value;
      }
      elseif (preg_match('/addexpander/', $filter)) {
        list($action, $str) = explode('(', $filter, 2);
        // Expanders don't have value.
        $field = substr($str, 0, -1);
        $expanders[] = $field;
      }
      elseif (preg_match('/addfacetfilter/', $filter)) {
        list($action, $str) = explode('(', $filter, 2);
        // e.g. ZG:FRANCE.
        $field_and_value = substr($str, 0, -1);
        list($field, $value) = explode(':', $field_and_value, 2);
        $facets[$field][] = $field_and_value;
      }
    }
    if (!empty($limiters)) {
      $query['limiter']='';
        foreach ($limiters as $field => $limiter) {
          // e.g. LA99:English,French,German.
          $query['limiter'].= $field . ':' . implode(',', $limiter);
        }
      }
    if (!empty($expanders)) {
      // e.g. fulltext, thesaurus.
      $query['expander'] = implode(',', $expanders);
    }
    if (!empty($facets)) {
      $groupId = 1;
      foreach ($facets as $field => $facet) {
        // e.g. 1,DE:Math,DE:History.
        $query['facetfilter'] = $groupId . ',' . implode(',', $facet);
        $groupId += 1;
      }
    }

    // 2014-03-26 - new action to jump to page.
    if ($start > 1) {
      $query['action'] = "GoToPage(" . $start . ")";
    }

    // Add the sort option.
    $sortBy = in_array($sortBy, self::$sort_options) ? $sortBy : self::$mapped_sort_options[$sortBy];

    // Add the HTTP query params.
    $params = array(
        // Specifies the sort. Valid options are:
        // relevance, date, date2
        // date = Date descending
        // date2 = Date ascending.
      'sort'           => $sortBy,
        // Specifies the search mode. Valid options are:
        // bool, any, all, smart.
      'searchmode'     => $mode,
        // Specifies the amount of data to return with the response
        // Valid options are:
        // title: Title only
        // brief: Title + Source, Subjects
        // detailed: Brief + full abstract.
      'view'           => $amount,
        // Specifies whether or not to include facets.
      'includefacets'  => 'y',
      'resultsperpage' => $limit,

    // 2014-03-26 RF.
      'pagenumber'     => $start,
    // 'pagenumber'     => 1,
        // Specifies whether or not to include highlighting in the search results.
      'highlight'      => 'y',

      
      'includeimagequickview' => $includeimagequickview,


      'format' => 'ris',

      'styles'    => $styles,

    
    );

    if ($autosuggest == TRUE) {
      $params["autosuggest"] = "y";
    }

    if ($rs == TRUE) {
      $params["relatedcontent"] = "rs";
    }

    if ($emp == TRUE) {
      if (isset($params["relatedcontent"])) {
        $params["relatedcontent"] .= ",emp";
      }
      else {
        $params["relatedcontent"] = "emp";
      }
    }

    if ($includeimagequickview == TRUE) {
      $params["includeimagequickview"] = "y";
    }

    if ($styles == 'all') {
      $params["styles"] = "all";
    }

    $params = array_merge($params, $query);


    $result = $this->request('Search', $params);

    return $result;
  }

  /**
   * Wrapper for retrieve API call.
   *
   * @param array $an
   *   The accession number.
   * @param string $start
   *   The short database name.
   *
   * @throws object             PEAR Error
   *
   * @return array              An associative array of data
   *
   * @access public
   */
  public function apiRetrieve($an, $db) {
    // Add the HTTP query params.
    //$includeimagequickviewDetail = FALSE;
    $params = array(
      'an'        => $an,
      'dbid'      => $db,
      'highlight' => 'y',
      //'includeimagequickview' => $includeimagequickviewDetail,
      //'IllustrationInfo' => 'y',
      'IllustrationInfo' => $IllustrationInfo,
      'format' => 'ris',
      'styles'    => $styles,
    );
    
    $result = $this->request('Retrieve', $params);    
        
    return $result;
    
  }

  

  public function apiExport($an, $db) {

    $params = array(
      'an'        => $an,
      'dbid'      => $db,
      'format' => 'ris'
    );
    
    $result = $this->request('Export', $params);

    return $result;
    
  }

  public function apiCitationStyles($an, $db, $styles) {

    $params = array(
      'an'        => $an,
      'dbid'      => $db,
      'styles'    => $styles
    );
    
    $result = $this->request('CitationStyles', $params);
    
    return $result;
    
  }

  
  /**
   * Wrapper for info API call.
   *
   * @throws object             PEAR Error
   *
   * @return array              An associative array of data
   *
   * @access public
   */
  public function apiInfo() {
    if ($result = $this->readSession('info')) {
      return $result;
    }
    $result = $this->request('Info');

    if (!$this->isError($result)) {
      $this->writeSession('info', $result);
    }

    return $result;
  }

  /**
   * Handle a PEAR_Error. Return :
   * - if the error is critical : an associative array with the current error message
   * - if the error is not critical : the error message .
   *
   * @param Pear_Error $exception
   *
   * @return array or the Pear_Error exception
   *
   * @access protected
   */
  private function handleError($error) {
    $errorCode = $error->getCode();
    switch ($errorCode) {
      // This kind of error was generated by user , so display it to user.
      case EBSCOConnector::EDS_INVALID_ARGUMENT_VALUE:
        // Any other errors are system errors, don't display them to user.
      default:
        $errorMessage = 'An error occurred when getting the data.';
        break;
    }
    $result = array(
      'errors' => $errorMessage,
      'recordCount' => 0,
      'numFound'    => 0,
      'start'       => 0,
      'documents'   => array(),
      'facets'      => array(),
    );
    return $result;
  }

  /**
   * Store the given object into session.
   *
   * @param string $key
   *   The key used for reading the value.
   * @param object $value
   *   The object stored in session.
   *
   * @return none
   *
   * @access protected
   */
  protected function writeSession($key, $value) {
    if (!empty($key) && !empty($value)) {
      $_SESSION['EBSCO'][$key] = $value;
    }
  }

  /**
   * Read from session the object having the given key.
   *
   * @param string $key
   *   The key used for reading the object.
   *
   * @return object
   *
   * @access protected
   */
  protected function readSession($key) {
    $value = isset($_SESSION['EBSCO'][$key]) ? $_SESSION['EBSCO'][$key] : '';
    return $value;
  }

  /**
   * Check if given object is an EBSCOException object.
   *
   * @param object $object
   *
   * @return bool
   *
   * @access protected
   */
  protected function isError($object) {
    return is_a($object, 'EBSCOException');
  }

}
