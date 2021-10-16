<?php

/**
 * @file
 * The EBSCO Response object.
 *
 * PHP version 5
 *
 * Copyright [2017] [EBSCO Information Services]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
// use Drupal\Core\Url;
// use Symfony\Component\HttpFoundation\RedirectResponse;

require_once 'sanitizer.class.php';

/**
 * EBSCOResponse class.
 */
class EBSCOResponse
{
    /**
     * A SimpleXml object.
     *
     * @global object
     */
    private $response;

    /**
     * Constructor.
     *
     * Sets up the EBSCO Response.
     *
     * @param none
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Returns the XML as an associative array of data.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    public function result()
    {
        if (!empty($this->response->AuthToken)) {
            return $this->buildAuthenticationToken();
        } elseif (!empty($this->response->SessionToken)) {
            return (string) $this->response->SessionToken;
        } elseif (!empty($this->response->AutocompleteToken)) {
            return (string) $this->response->AutocompleteToken;
        } elseif (!empty($this->response->SearchResult)) {
            return $this->buildSearch();
        } elseif (!empty($this->response->Record)) {
            return $this->buildRetrieve();
        } elseif (!empty($this->response->AvailableSearchCriteria)) {
            return $this->buildInfo();
        } elseif (!empty($this->response->Format) && $this->response->Format == 'RIS') {
            return $this->response->Data;
        } elseif (!empty($this->response)) {
            return $this->buildCitationStyles();
        }
        // Should not happen, it may be an exception.
        else {
            return $this->response;
        }
    }

    /**
     * Parse the SimpleXml object when an AuthenticationToken API call was executed.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    private function buildAuthenticationToken()
    {
        $token = (string) $this->response->AuthToken;
        $timeout = (int) $this->response->AuthTimeout;
        $autocompleteUrl = (string) $this->response->Autocomplete->Url;
        $autoToken = (string) $this->response->Autocomplete->Token;
        $autocompleteTokenTimeOut = (int) $this->response->Autocomplete->TokenTimeOut;
        $autocompleteCustId = (string) $this->response->Autocomplete->CustId;

        $result = [
      'authenticationToken' => $token,
      'authenticationTimeout' => $timeout,
      'autocompleteUrl' => $autocompleteUrl,
      'autocompleteToken' => $autoToken,
      'autocompleteTokenTimeOut' => $autocompleteTokenTimeOut,
      'autocompleteCustId' => $autocompleteCustId,
    ];

        return $result;
    }

    /**
     * Parse a SimpleXml object and
     * return it as an associative array.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    private function buildSearch()
    {
        $hits = (int) $this->response->SearchResult->Statistics->TotalHits;
        $searchTime = (int) $this->response->SearchResult->Statistics->TotalSearchTime / 1000;
        $records = [];
        $facets = [];
        if ($hits > 0) {
            $records = $this->buildRecords();
            $facets = $this->buildFacets();
        }

        // Research Starters & emp.
        $relatedC = null;
        if ($this->response->SearchResult->RelatedContent) {
            $result = json_decode(json_encode($this->response->SearchResult->RelatedContent), true);
            $relatedC = $result;
        }

        // Did you mean / auto suggest.
        $autoSuggestTerms = null;
        if ($this->response->SearchResult->AutoSuggestedTerms) {
            $result = json_decode(json_encode($this->response->SearchResult->AutoSuggestedTerms), true);
            $autoSuggestTerms = $result;
        }

        //quickimageview
        $imageQuickViewTerms = null;
        if ($this->response->SearchResult->imageQuickViewedTerms) {
            $result = json_decode(json_encode($this->response->SearchResult->imageQuickViewedTerms), true);
            $imageQuickViewTerms = $result;
        }

        $citationStylesTerms = null;
        if ($this->response->SearchResult->citationStylesTerms) {
            $result = json_decode(json_encode($this->response->SearchResult->citationStylesTerms), true);
            $citationStylesTerms = $result;
        }

        $results = [
      'recordCount' => $hits,
      'searchTime' => $searchTime,
      'numFound' => $hits,
      'start' => 0,
      'documents' => $records,
      'relatedContent' => $relatedC,
      'autoSuggestTerms' => $autoSuggestTerms,
      'facets' => $facets,
      'imageQuickViewTerms' => $imageQuickViewTerms,
      'citationStylesTerms' => $citationStylesTerms,
    ];

        return $results;
    }

    /**
     * Parse a SimpleXml object and
     * return it as an associative array.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    private function buildRecords()
    {
        $results = [];

        $records = $this->response->SearchResult->Data->Records->Record;
        foreach ($records as $record) {
            $result = [];
            $result['ResultId'] = $record->ResultId ? (int) $record->ResultId : '';
            $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
            $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel : '';
            $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
            $result['PubType'] = $record->Header->PubType ? (string) $record->Header->PubType : '';
            $result['AccessLevel'] = $record->Header->AccessLevel ? (string) $record->Header->AccessLevel : '';
            $result['id'] = $result['An'].'|'.$result['DbId'];
            $result['PLink'] = $record->PLink ? (string) $record->PLink : '';

            if (!empty($record->ImageInfo->CoverArt)) {
                foreach ($record->ImageInfo->CoverArt as $image) {
                    $size = (string) $image->Size;
                    $target = (string) $image->Target;
                    $result['ImageInfo'][$size] = $target;
                }
            } else {
                $result['ImageInfo'] = '';
            }

            if ($record->FullText) {
                $availability = (int) $record->FullText->Text->Availability == 1;
                $links = [];
                // RF 2012-12-18.
                if (isset($record->FullText->Links)) {
                    foreach ($record->FullText->Links->Link as $link) {
                        $type = (string) $link->Type;
                        $url = (string) $link->Url;
                        // If we have an empty url when type is pdflink then just return something so
                        // that the UI check for empty string will pass.
                        $url = empty($url) && $type == 'pdflink' ? 'https://content.ebscohost.com' : $url;
                        $links[$type] = $url;
                    }
                }
                $result['FullText'] = [
          'Availability' => $availability,
          'Links' => $links,
        ];
            }

            if ($record->CustomLinks) {
                $result['CustomLinks'] = [];
                foreach ($record->CustomLinks->CustomLink as $customLink) {
                    $category = $customLink->Category ? (string) $customLink->Category : '';
                    $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                    $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                    $name = $customLink->Name ? (string) $customLink->Name : '';
                    $text = $customLink->Text ? (string) $customLink->Text : '';
                    $url = $customLink->Url ? (string) $customLink->Url : '';
                    $result['CustomLinks'][] = [
            'Category' => $category,
            'Icon' => $icon,
            'MouseOverText' => $mouseOverText,
            'Name' => $name,
            'Text' => $text,
            'Url' => $url,
          ];
                }
            }

            if ($record->Items) {
                $result['Items'] = [];
                foreach ($record->Items->Item as $item) {
                    $name = $item->Name ? (string) $item->Name : '';
                    $label = $item->Label ? (string) $item->Label : '';
                    $group = $item->Group ? (string) $item->Group : '';
                    $data = $item->Data ? (string) $item->Data : '';
                    $result['Items'][$name] = [
            'Name' => $name,
            'Label' => $label,
            'Group' => $group,
            'Data' => $this->toHTML($data, $group),
          ];
                }
            }

            if ($record->RecordInfo) {
                $result['RecordInfo'] = [];
                $result['RecordInfo']['BibEntity'] = [
            'Identifiers' => [],
            'Languages' => [],
            'PhysicalDescription' => [],
            'Subjects' => [],
            'Titles' => [],
        ];

                if ($record->RecordInfo->BibRecord->BibEntity->Identifiers) {
                    foreach ($record->RecordInfo->BibRecord->BibEntity->Identifiers->Identifier as $identifier) {
                        $type = $identifier->Type ? (string) $identifier->Type : '';
                        $value = $identifier->Value ? (string) $identifier->Value : '';
                        $result['RecordInfo']['BibEntity']['Identifiers'][] = [
            'Type' => $type,
            'Value' => $value,
            ];
                    }
                }

                if ($record->RecordInfo->BibRecord->BibEntity->Languages) {
                    foreach ($record->RecordInfo->BibRecord->BibEntity->Languages->Language as $language) {
                        $code = $language->Code ? (string) $language->Code : '';
                        $text = $language->Text ? (string) $language->Text : '';
                        $result['RecordInfo']['BibEntity']['Languages'][] = [
            'Code' => $code,
            'Text' => $text,
            ];
                    }
                }

                if ($record->RecordInfo->BibRecord->BibEntity->PhysicalDescription) {
                    $pageCount = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount ? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount : '';
                    $startPage = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage ? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage : '';
                    $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'] = $pageCount;
                    $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'] = $startPage;
                }

                if ($record->RecordInfo->BibRecord->BibEntity->Subjects) {
                    foreach ($record->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject) {
                        $subjectFull = $subject->SubjectFull ? (string) $subject->SubjectFull : '';
                        $type = $subject->Type ? (string) $subject->Type : '';
                        $result['RecordInfo']['BibEntity']['Subjects'][] = [
                'SubjectFull' => $subjectFull,
                'Type' => $type,
            ];
                    }
                }

                if ($record->RecordInfo->BibRecord->BibEntity->Titles) {
                    foreach ($record->RecordInfo->BibRecord->BibEntity->Titles->Title as $title) {
                        $titleFull = $title->TitleFull ? (string) $title->TitleFull : '';
                        $type = $title->Type ? (string) $title->Type : '';
                        $result['RecordInfo']['BibEntity']['Titles'][] = [
                'TitleFull' => $titleFull,
                'Type' => $type,
            ];
                    }
                }

                $result['RecordInfo']['BibRelationships'] = [
            'HasContributorRelationships' => [],
            'IsPartOfRelationships' => [],
        ];

                if ($record->RecordInfo->BibRecord->BibRelationships) {
                    foreach ($record->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships->IsPartOf as $relationship) {
                        if ($relationship->BibEntity->Dates) {
                            foreach ($relationship->BibEntity->Dates->Date as $date) {
                                $d = $date->D ? (string) $date->D : '';
                                $m = $date->M ? (string) $date->M : '';
                                $type = $date->Type ? (string) $date->Type : '';
                                $y = $date->Y ? (string) $date->Y : '';
                                $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'][] = [
              'D' => $d,
              'M' => $m,
              'Type' => $type,
              'Y' => $y,
            ];
                            }
                        }

                        if ($relationship->BibEntity->Identifiers) {
                            foreach ($relationship->BibEntity->Identifiers->Identifier as $identifier) {
                                $type = $identifier->Type ? (string) $identifier->Type : '';
                                $value = $identifier->Value ? (string) $identifier->Value : '';
                                $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'][] = [
                    'Type' => $type,
                    'Value' => $value,
                ];
                            }
                        }

                        if ($relationship->BibEntity->Titles) {
                            foreach ($relationship->BibEntity->Titles->Title as $title) {
                                $titleFull = $title->TitleFull ? (string) $title->TitleFull : '';
                                $type = $title->Type ? (string) $title->Type : '';
                                $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'][] = [
                      'TitleFull' => $titleFull,
                      'Type' => $type,
                    ];
                            }
                        }

                        if ($relationship->BibEntity->Numbering) {
                            foreach ($relationship->BibEntity->Numbering->Number as $number) {
                                $type = (string) $number->Type;
                                $value = (string) $number->Value;
                                $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'][] = [
              'Type' => $type,
              'Value' => $value,
            ];
                            }
                        }
                    }
                }
            }

            if ($record->ImageQuickViewItems->ImageQuickViewItem) {
                $result['iqv'] = [];
                foreach ($record->ImageQuickViewItems->ImageQuickViewItem as $iqv) {
                    $dbid = $iqv->DbId ? (string) $iqv->DbId : '';
                    $an = $iqv->An ? (string) $iqv->An : '';
                    $type = $iqv->Type ? (string) $iqv->Type : '';
                    $url = $iqv->Url ? (string) $iqv->Url : '';
                    $result['iqv'][] = [
            'DbId' => $dbid,
            'An' => $an,
            'Type' => $type,
            'Url' => $url,
        ];
                }
            }

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Parse a SimpleXml object and
     * return it as an associative array.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    private function buildFacets()
    {
        $results = [];

        $facets = $this->response->SearchResult->AvailableFacets->AvailableFacet;
        if ($facets) {
            foreach ($facets as $facet) {
                $values = [];
                foreach ($facet->AvailableFacetValues->AvailableFacetValue as $value) {
                    $this_value = (string) $value->Value;
                    $this_value = str_replace(['\(', '\)'], ['(', ')'], $this_value);
                    $this_action = (string) $value->AddAction;
                    $this_action = str_replace(['\(', '\)'], ['(', ')'], $this_action);
                    $values[] = [
            'Value' => $this_value,
            'Action' => $this_action,
            'Count' => (string) $value->Count,
          ];
                }
                $id = (string) $facet->Id;
                $label = (string) $facet->Label;
                if (!empty($label)) {
                    $results[] = [
            'Id' => $id,
            'Label' => $label,
            'Values' => $values,
            'isApplied' => false,
          ];
                }
            }
        }

        return $results;
    }

    /**
     * Parse a SimpleXml object and
     * return it as an associative array.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    private function buildInfo()
    {
        // Sort options.
        $elements = $this->response->AvailableSearchCriteria->AvailableSorts->AvailableSort;

        $sort = [];
        foreach ($elements as $element) {
            $sort[] = [
                'Id' => (string) $element->Id,
                'Label' => (string) $element->Label,
                'Action' => (string) $element->AddAction,
            ];
        }

        // Search fields.
        $elements = $this->response->AvailableSearchCriteria->AvailableSearchFields->AvailableSearchField;
        $tags = [];
        foreach ($elements as $element) {
            $tags[] = [
        'Label' => (string) $element->Label,
        'Code' => (string) $element->FieldCode,
      ];
        }

        // Expanders.
        $elements = $this->response->AvailableSearchCriteria->AvailableExpanders->AvailableExpander;
        $expanders = [];
        foreach ($elements as $element) {
            $expanders[] = [
        'Id' => (string) $element->Id,
        'Label' => (string) $element->Label,
        'Action' => (string) $element->AddAction,
      // Added because of the checkboxes.
        'selected' => false,
      ];
        }

        $imagemQuickViewElements = $this->response->ViewResultSettings->IncludeImageQuickView;
        $includeImageQuickView = [];

        foreach ($imagemQuickViewElements as $imagemQuickViewElement) {
            $includeImageQuickView[] = [
                'Label' => (string) $imagemQuickViewElement->Label,
                'Id' => (string) $imagemQuickViewElement->Id,
                'DefaultOn' => (string) $imagemQuickViewElement->DefaultOn,
            ];
        }

        // Limiters.
        $elements = $this->response->AvailableSearchCriteria->AvailableLimiters->AvailableLimiter;
        $limiters = [];
        $values = [];
        foreach ($elements as $element) {
            if ($element->LimiterValues) {
                $items = $element->LimiterValues->LimiterValue;
                foreach ($items as $item) {
                    $values[] = [
            'Value' => (string) $item->Value,
            'Action' => (string) $item->AddAction,
          // Added because of the checkboxes.
            'selected' => false,
          ];
                }
            }
            $limiters[] = [
            'Id' => (string) $element->Id,
            'Label' => (string) $element->Label,
            'Action' => (string) $element->AddAction,
            'Type' => (string) $element->Type,
            'Values' => $values,
            'selected' => false,
        ];
        }

        $result = [
            'sort' => $sort,
            'tags' => $tags,
            'expanders' => $expanders,
            'limiters' => $limiters,
            'includeImageQuickView' => $includeImageQuickView,
        ];

        return $result;
    }

    /**
     * Parse a SimpleXml object and
     * return it as an associative array.
     *
     * @param none
     *
     * @return array An associative array of data
     */
    private function buildRetrieve()
    {
        $record = $this->response->Record;

        if ($record) {
            // There is only one record.
            $record = $record[0];
        }

        $result = [];
        $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
        $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel : '';
        $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
        $result['id'] = $result['An'].'|'.$result['DbId'];
        $result['PubType'] = $record->Header->PubType ? (string) $record->Header->PubType : '';
        $result['AccessLevel'] = $record->Header->AccessLevel ? (string) $record->Header->AccessLevel : '';
        $result['PLink'] = $record->PLink ? (string) $record->PLink : '';
        $result['IllustrationInfo'] = $record->IllustrationInfo ? (string) $record->IllustrationInfo : '';
        $result['Type'] = $record->ImageQuickViewItems->Type ? (string) $record->ImageQuickViewItems->Type : '';

        if (!empty($record->ImageInfo->CoverArt)) {
            foreach ($record->ImageInfo->CoverArt as $image) {
                $size = (string) $image->Size;
                $target = (string) $image->Target;
                $result['ImageInfo'][$size] = $target;
            }
        } else {
            $result['ImageInfo'] = '';
        }

        if ($record->FullText) {
            $availability = (int) ($record->FullText->Text->Availability) == 1;
            $links = [];
            if (isset($record->FullText->Links->Link)) {
                foreach ($record->FullText->Links->Link as $link) {
                    $type = (string) $link->Type;
                    $url = (string) $link->Url;
                    // If we have an empty url when type is pdflink then just return something so
                    // that the UI check for empty string will pass.
                    $url = empty($url) && $type == 'pdflink' ? 'https://content.ebscohost.com' : $url;
                    $links[$type] = $url;
                }
            }
            $value = $this->toHTML($record->FullText->Text->Value);
            $result['FullText'] = [
          'Availability' => $availability,
          'Links' => $links,
          'Value' => $value,
        ];
        }

        if ($record->CustomLinks) {
            $result['CustomLinks'] = [];
            foreach ($record->CustomLinks->CustomLink as $customLink) {
                $category = $customLink->Category ? (string) $customLink->Category : '';
                $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                $name = $customLink->Name ? (string) $customLink->Name : '';
                $text = $customLink->Text ? (string) $customLink->Text : '';
                $url = $customLink->Url ? (string) $customLink->Url : '';
                $result['CustomLinks'][] = [
            'Category' => $category,
            'Icon' => $icon,
            'MouseOverText' => $mouseOverText,
            'Name' => $name,
            'Text' => $text,
            'Url' => $url,
          ];
            }
        }

        if ($record->Items) {
            $result['Items'] = [];
            foreach ($record->Items->Item as $item) {
                $name = $item->Name ? (string) $item->Name : '';
                $label = $item->Label ? (string) $item->Label : '';
                $group = $item->Group ? (string) $item->Group : '';
                $data = $item->Data ? (string) $item->Data : '';
                $result['Items'][$name] = [
            'Name' => $name,
            'Label' => $label,
            'Group' => $group,
            'Data' => $this->toHTML($data, $group),
          ];
            }
        }

        if ($record->RecordInfo) {
            $result['RecordInfo'] = [];
            $result['RecordInfo']['BibEntity'] = [
            'Identifiers' => [],
            'Languages' => [],
            'PhysicalDescription' => [],
            'Subjects' => [],
            'Titles' => [],
        ];

            if ($record->RecordInfo->BibRecord->BibEntity->Identifiers) {
                foreach ($record->RecordInfo->BibRecord->BibEntity->Identifiers->Identfier as $identifier) {
                    $type = $identifier->Type ? (string) $identifier->Type : '';
                    $value = $identifier->Value ? (string) $identifier->Value : '';
                    $result['RecordInfo']['BibEntity']['Identifiers'][] = [
            'Type' => $type,
            'Value' => $value,
            ];
                }
            }

            if ($record->RecordInfo->BibRecord->BibEntity->Languages) {
                foreach ($record->RecordInfo->BibRecord->BibEntity->Languages->Language as $language) {
                    $code = $language->Code ? (string) $language->Code : '';
                    $text = $language->Text ? (string) $language->Text : '';
                    $result['RecordInfo']['BibEntity']['Languages'][] = [
            'Code' => $code,
            'Text' => $text,
            ];
                }
            }

            if ($record->RecordInfo->BibRecord->BibEntity->PhysicalDescription) {
                $pageCount = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount ? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount : '';
                $startPage = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage ? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage : '';
                $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'] = $pageCount;
                $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'] = $startPage;
            }

            if ($record->RecordInfo->BibRecord->BibEntity->Subjects) {
                foreach ($record->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject) {
                    $subjectFull = $subject->SubjectFull ? (string) $subject->SubjectFull : '';
                    $type = $subject->Type ? (string) $subject->Type : '';
                    $result['RecordInfo']['BibEntity']['Subjects'][] = [
                'SubjectFull' => $subjectFull,
                'Type' => $type,
            ];
                }
            }

            if ($record->RecordInfo->BibRecord->BibEntity->Titles) {
                foreach ($record->RecordInfo->BibRecord->BibEntity->Titles->Title as $title) {
                    $titleFull = $title->TitleFull ? (string) $title->TitleFull : '';
                    $type = $title->Type ? (string) $title->Type : '';
                    $result['RecordInfo']['BibEntity']['Titles'][] = [
                'TitleFull' => $titleFull,
                'Type' => $type,
            ];
                }
            }

            $result['RecordInfo']['BibRelationships'] = [
            'HasContributorRelationships' => [],
            'IsPartOfRelationships' => [],
        ];

            if ($record->RecordInfo->BibRecord->BibRelationships) {
                foreach ($record->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships->IsPartOf as $relationship) {
                    if ($relationship->BibEntity->Dates) {
                        foreach ($relationship->BibEntity->Dates->Date as $date) {
                            $d = $date->D ? (string) $date->D : '';
                            $m = $date->M ? (string) $date->M : '';
                            $type = $date->Type ? (string) $date->Type : '';
                            $y = $date->Y ? (string) $date->Y : '';
                            $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'][] = [
                                'D' => $d,
                                'M' => $m,
                                'Type' => $type,
                                'Y' => $y,
                                ];
                        }
                    }

                    if ($relationship->BibEntity->Identifiers) {
                        foreach ($relationship->BibEntity->Identifiers->Identfier as $identifier) {
                            $type = $identifier->Type ? (string) $identifier->Type : '';
                            $value = $identifier->Value ? (string) $identifier->Value : '';
                            $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'][] = [
                        'Type' => $type,
                        'Value' => $value,
                    ];
                        }
                    }

                    if ($relationship->BibEntity->Numbering) {
                        foreach ($relationship->BibEntity->Numbering->Number as $number) {
                            $type = (string) $number->Type;
                            $value = (string) $number->Value;
                            $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'][] = [
                  'Type' => $type,
                  'Value' => $value,
                ];
                        }
                    }

                    if ($relationship->BibEntity->Titles) {
                        foreach ($relationship->BibEntity->Titles->Title as $title) {
                            $titleFull = $title->TitleFull ? (string) $title->TitleFull : '';
                            $type = $title->Type ? (string) $title->Type : '';
                            $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'][] = [
                          'TitleFull' => $titleFull,
                          'Type' => $type,
                        ];
                        }
                    }
                }
            }
        }

        if ($record->ImageQuickViewItems->ImageQuickViewItem) {
            $result['iqv'] = [];
            foreach ($record->ImageQuickViewItems->ImageQuickViewItem as $iqv) {
                $dbcode = $iqv->DbId ? (string) $iqv->DbId : '';
                $an = $iqv->An ? (string) $iqv->An : '';
                $type = $iqv->Type ? (string) $iqv->Type : '';
                $url = $iqv->Url ? (string) $iqv->Url : '';
                $result['iqv'][] = [
            'DbId' => $dbcode,
            'An' => $an,
            'Type' => $type,
            'url' => $url,
        ];
            }
        }

        if ($record->IllustrationInfo) {
            $result['IllustrationInfo'] = [];
            foreach ($record->IllustrationInfo->Images->Image as $img) {
                $size = $img->Size ? (string) $img->Size : '';
                $target = $img->Target ? (string) $img->Target : '';
                $result['IllustrationInfo'][] = [
              'Size' => $size,
              'Target' => $target,
          ];
            }
        }

        return $result;
    }

    private function buildCitationStyles()
    {
        $recordCitation = $this->response;

        $result = [];

        if ($recordCitation) {
            $result['Citation'] = [];

            foreach ($recordCitation->Citation as $key => $citationItem) {
                $id = $citationItem->Id ? (string) $citationItem->Id : '';
                $label = $citationItem->Label ? (string) $citationItem->Label : '';
                $sectionLabel = $citationItem->SectionLabel ? (string) $citationItem->SectionLabel : '';
                $data = $citationItem->Data ? (string) $citationItem->Data : '';
                $caption = $citationItem->Caption ? (string) $citationItem->Caption : '';
                $result['Citation'][] = [
                'Id' => $id,
                'Label' => $label,
                'SectionLabel' => $sectionLabel,
                'Data' => $data,
                'Caption' => $caption,
            ];
            }
        }

        return $result;
    }

    /**
     * Parse a SimpleXml element and
     * return it's inner XML as an HTML string.
     *
     * @return string The HTML string
     */
    private function toHTML($data, $group = null)
    {
        // Any group can be added here, but we only use Au (Author)
        // Other groups, not present here, won't be transformed to HTML links.
        $allowed_searchlink_groups = ['au'];

        // Map xml tags to the HTML tags
        // This is just a small list, the total number of xml tags is far more greater.
        $xml_to_html_tags = [
      '<jsection' => '<section',
      '</jsection' => '</section',
      '<highlight' => '<span class="highlight"',
    // Temporary bug fix.
      '<highligh' => '<span class="highlight"',
    // Temporary bug fix.
      '</highlight>' => '</span>',
      '</highligh' => '</span>',
      '<text' => '<div',
      '</text' => '</div',
      '<title' => '<h2',
      '</title' => '</h2',
      '<anid' => '<p',
      '</anid' => '</p',
      '<aug' => '<p class="aug"',
      '</aug' => '</p',
      '<hd' => '<h3',
      '</hd' => '</h3',
      '<linebr' => '<br',
      '</linebr' => '',
      '<olist' => '<ol',
      '</olist' => '</ol',
      '<reflink' => '<a',
      '</reflink' => '</a',
      '<blist' => '<p class="blist"',
      '</blist' => '</p',
      '<bibl' => '<a',
      '</bibl' => '</a',
      '<bibtext' => '<span',
      '</bibtext' => '</span',
      '<ref' => '<div class="ref"',
      '</ref' => '</div',
      '<ulink' => '<a',
      '</ulink' => '</a',
      '<superscript' => '<sup',
      '</superscript' => '</sup',
      '<relatesTo' => '<sup',
      '</relatesTo' => '</sup',
    ];

        // Map xml types to Search types used by the UI.
        $xml_to_search_types = [
      'au' => 'Author',
      'su' => 'Subject',
    ];

        // The XML data is XML escaped, let's unescape html entities (e.g. &lt; => <)
        $data = html_entity_decode($data);

        // Start parsing the xml data.
        if (!empty($data)) {
            // Replace the XML tags with HTML tags.
            $search = array_keys($xml_to_html_tags);
            $replace = array_values($xml_to_html_tags);
            $data = str_replace($search, $replace, $data);

            // Temporary : fix unclosed tags.
            $data = preg_replace('/<\/highlight/', '</span>', $data);
            $data = preg_replace('/<\/span>>/', '</span>', $data);
            $data = preg_replace('/<\/searchLink/', '</searchLink>', $data);
            $data = preg_replace('/<\/searchLink>>/', '</searchLink>', $data);

            // Parse searchLinks.
            if (!empty($group)) {
                $group = strtolower($group);
                if (in_array($group, $allowed_searchlink_groups)) {
                    $type = $xml_to_search_types[$group];
                    $path = \Drupal\Core\Url::fromRoute('ebsco.results', ['type' => $type])->toString();
                    $link_xml = '/<searchLink fieldCode="([^\"]*)" term="%22([^\"]*)%22">/';
                    $link_html = "<a href=\"{$path}&lookfor=$2\">";
                    $data = preg_replace($link_xml, $link_html, $data);
                    $data = str_replace('</searchLink>', '</a>', $data);
                }
            }

            // Replace the rest of searchLinks with simple spans.
            $link_xml = '/<searchLink fieldCode="([^\"]*)" term="%22([^\"]*)%22">/';
            $link_html = '<span>';
            $data = preg_replace($link_xml, $link_html, $data);
            $data = str_replace('</searchLink>', '</span>', $data);

            // Parse bibliography (anchors and links)
            $data = preg_replace('/<a idref="([^\"]*)"/', '<a href="#$1"', $data);
            $data = preg_replace('/<a id="([^\"]*)" idref="([^\"]*)" type="([^\"]*)"/', '<a id="$1" href="#$2"', $data);
        }

        $sanitizer = new HTML_Sanitizer();
        $data = $sanitizer->sanitize($data);

        return $data;
    }
}
