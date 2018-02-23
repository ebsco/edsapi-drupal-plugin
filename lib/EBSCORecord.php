<?php

/**
 * @file
 * The EBSCO record object.
 *
 * PHP version 5
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
 * EBSCORecord class.
 */
class EBSCORecord {
  /**
   * The array of data.
   *
   * @global array
   */
  private $data = array();

  /**
   * The result id (the EBSCO counter) of the record.
   *
   * @global integer
   */
  public $result_id = NULL;

  /**
   * The id of the record.
   *
   * @global integer
   */
  public $record_id = NULL;

  /**
   * The summary of the record.
   *
   * @global string
   */
  public $summary = NULL;

  /**
   * The authors of the record.
   *
   * @global string
   */
  public $authors = NULL;

  /**
   * The subjects of the record.
   *
   * @global string
   */
  public $subjects = NULL;

  /**
   * The custom links provided for the record.
   *
   * @global array
   */
  public $custom_links = array();

  /**
   * The database label of the record.
   *
   * @global string
   */
  public $db_label = NULL;

  /**
   * The full-text availability of the record.
   *
   * @global boolean
   */
  public $full_text_availability = NULL;

  /**
   * The full text of the record.
   *
   * @global string
   */
  public $full_text = NULL;

  /**
   * The PDF availability of the record.
   *
   * @global boolean
   */
  public $pdf_availability = NULL;

  /**
   * The items of the record.
   *
   * @global array
   */
  public $items = array();

  /**
   * The external link of the record.
   *
   * @global string
   */
  public $p_link = NULL;

  /**
   * The external link to the PDF version of the record.
   *
   * @global string
   */
  public $pdf_link = NULL;

  /**
   * The publication type link of the record.
   *
   * @global string
   */
  public $publication_type = NULL;

  /**
   * The external thumbnails links of the record.
   *
   * @global string
   */
  public $small_thumb_link = NULL;
  public $medium_thumb_link = NULL;

  /**
   * The title of the record.
   *
   * @global string
   */
  public $title = NULL;

  /**
   * The source of the record.
   *
   * @global string
   */
  public $source = NULL;

  /**
   * The access level of the record.
   *
   * @global string
   */
  public $access_level = NULL;

  /**
   * Constructor.
   *
   * @param array $data
   *   Raw data from the EBSCO search representing the record.
   */
  public function __construct($data = array()) {
    $this->data = $data;
    $this->record_id = $this->record_id();
    $this->result_id = $this->result_id();
    $this->title = $this->title();
    $this->summary = $this->summary();
    $this->authors = $this->authors();
    $this->subjects = $this->subjects();
    $this->custom_links = $this->custom_links();
    $this->db_label = $this->db_label();
    $this->full_text_availability = $this->full_text_availability();
    $this->full_text = $this->full_text();
    $this->items = $this->items();
    $this->p_link = $this->p_link();
    $this->publication_type = $this->publication_type();
    $this->pdf_availability = $this->pdf_availability();
    $this->pdf_link = $this->pdf_link();
    $this->small_thumb_link = $this->thumb_link();
    $this->medium_thumb_link = $this->thumb_link('medium');
    $this->source = $this->source();
    $this->access_level = $this->access_level();
  }

  /********************************************************
   *
   * Getters
   *
   ********************************************************/

  /**
   * Get the summary of the record.
   *
   * @return string
   */
  public function access_level() {
    return isset($this->data['AccessLevel']) ?
            $this->data['AccessLevel'] : '';
  }

  /**
   * Get the summary of the record.
   *
   * @return string
   */
  public function summary() {
    return isset($this->data['Items']['Abstract']) ?
            $this->data['Items']['Abstract']['Data'] : '';
  }

  /**
   * Get the authors of the record.
   *
   * @return string
   */
  public function authors() {
    return isset($this->data['Items']['Author']) ?
            $this->data['Items']['Author']['Data'] : '';
  }

  /**
   * Get the custom links of the record.
   *
   * @return array
   */
  public function custom_links() {
    return isset($this->data['CustomLinks']) ?
            $this->data['CustomLinks'] : array();
  }

  /**
   * Get the database label of the record.
   *
   * @return string
   */
  public function db_label() {
    return isset($this->data['DbLabel']) ?
            $this->data['DbLabel'] : '';
  }

  /**
   * Get the full text availability of the record.
   *
   * @return bool
   */
  public function full_text() {
    return isset($this->data['FullText']) &&
            isset($this->data['FullText']['Value']) ? $this->data['FullText']['Value'] : '';
  }

  /**
   * Get the full text availability of the record.
   *
   * @return bool
   */
  public function full_text_availability() {
    return isset($this->data['FullText']) &&
            $this->data['FullText']['Availability'];
  }

  /**
   * Get the items of the record.
   *
   * @return array
   */
  public function items() {
    return isset($this->data['Items']) ? $this->data['Items'] : array();
  }

  /**
   * Get the external url of the record.
   *
   * @return string
   */
  public function p_link() {
    return isset($this->data['PLink']) ? $this->data['PLink'] : '';
  }

  /**
   * Get the publication type of the record.
   *
   * @return string
   */
  public function publication_type() {
    return isset($this->data['PubType']) ? $this->data['PubType'] : '';
  }

  /**
   * Get the PDF availability of the record.
   *
   * @return bool
   */
  public function pdf_availability() {
    return isset($this->data['FullText']) &&
            isset($this->data['FullText']['Links']) &&
            isset($this->data['FullText']['Links']['pdflink']) &&
            $this->data['FullText']['Links']['pdflink'];
  }

  /**
   * Get the PDF url of the record.
   *
   * @return string
   */
  public function pdf_link() {
    return isset($this->data['FullText']) &&
            isset($this->data['FullText']['Links']) &&
            isset($this->data['FullText']['Links']['pdflink']) ?
            $this->data['FullText']['Links']['pdflink'] :
            '';
  }

  /**
   * Get the result id of the record.
   *
   * @return int
   */
  public function result_id() {
    return isset($this->data['ResultId']) ?
            $this->data['ResultId'] : '';
  }

  /**
   * Get the subject data of the record.
   *
   * @return string
   */
  public function subjects() {
    return isset($this->data['Items']['Subject']) ?
            $this->data['Items']['Subject']['Data'] : '';
  }

  /**
   * Return a URL to a thumbnail preview of the record, if available; false
   * otherwise.
   *
   * @param string $size
   *   Size of thumbnail (small, medium or large -- small is
   *   default).
   *
   * @return string
   */
  public function thumb_link($size = 'small') {
    $imageInfo = isset($this->data['ImageInfo']) ? $this->data['ImageInfo'] : '';
    if ($imageInfo && isset($imageInfo['thumb'])) {
      switch ($size) {
        case 'large':
        case 'medium':
          return $imageInfo['medium'];

        break;

        case 'small':
        default:
          return $imageInfo['thumb'];

        break;
      }
    }
    return FALSE;
  }

  /**
   * Get the title of the record.
   *
   * @return string
   */
  public function title() {
    return isset($this->data['Items']['Title']) ?
            $this->data['Items']['Title']['Data'] : '';
  }

  /**
   * Get the source of the record.
   *
   * @return string
   */
  public function source() {
    return isset($this->data['Items']['TitleSource']) ?
            $this->data['Items']['TitleSource']['Data'] : '';
  }

  /**
   * Return the identifier of this record within the EBSCO databases.
   *
   * @return string Unique identifier.
   */
  public function record_id() {
    return isset($this->data['id']) ?
            $this->data['id'] : '';
  }

}
