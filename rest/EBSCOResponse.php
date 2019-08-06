<?php

/**
 * EBSCO Response class
 *
 * PHP version 5
 *
 */


/**
 * EBSCOResponse class
 */
class EBSCOResponse
{

    /**
     * A SimpleXml object
     * @global object
     */
    private $response;

    /**
     * Constructor
     *
     * Setup the EBSCO Response
     *
     * @param none
     *
     * @access public
     */
    public function __construct($response)
    {
        $this->response = $response;
    }


    /**
     * A proxy method which decides which subsequent method should be     * called, based on the SimpleXml object structure
     *
     * @param none
     *
     * @return array      An associative array of data or the SimpleXml object itself in case of API error messages
     * @access public
     */
    public function result()
    {
        // If there is an ErrorNumber tag then return the object itself.
        // Should not happen, this method is called after parsing the SimpleXml for API errors
        if(!empty($this->response->ErrorNumber)) {
            return $this->response;
        } else {
            if (!empty($this->response->AuthToken)) {
                return $this->buildAuthenticationToken();
            } else if (!empty($this->response->SessionToken)) {
                return (string)$this->buildSessionToken();
            } else if (!empty($this->response->SearchResult)) {
                return $this->buildSearch();
            } else if(!empty($this->response->Record)) {
                return $this->buildRetrieve();
            } else if(!empty($this->response->AvailableSearchCriteria)) {
                return $this->buildInfo();
            }
        }
    }


    /**
     * Parse the SimpleXml object when an AuthenticationToken API call was executed
     *
     * @param none
     *
     * @return array   An associative array of data
     * @access private
     */
     private function buildAuthenticationToken()
     {
        $token = (string) $this->response->AuthToken;
        $timeout = (integer) $this->response->AuthTimeout;

        $result = array(
            'authenticationToken'   => $token,
            'authenticationTimeout' => $timeout,
            'authenticationTimeStamp'=> time()
        );
        return $result;
     }
      /**
     * Parse the SimpleXml object when an SessionToken API call was executed
     *
     * @param none
     *
     * @return sessionToken
     * @access private
     */
     private function buildSessionToken()
     {  
        $sessionToken = (string) $this->response->SessionToken;      
        return $sessionToken;
     }

    /**
     * Parse the SimpleXml object when a Search API call was executed
     *
     * @param none
     *
     * @return array   An associative array of data
     * @access private
     */
    private function buildSearch()
    {
        $hits = (integer) $this->response->SearchResult->Statistics->TotalHits;
        $queryString = (string)$this->response->SearchRequestGet->QueryString;
        $records = array();
        $facets = array();
        $queries = array();
        $appliedFacets = array();
        $appliedLimiters = array();
        $appliedExpanders = array();
        
        if($this->response->SearchRequestGet->SearchCriteriaWithActions->QueriesWithAction){
        $queriesWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->QueriesWithAction->QueryWithAction;
        foreach($queriesWithAction as $queryWithAction){
            $queries[]=array(
                'query' => (string)$queryWithAction->Query->Term,
                'removeAction'=> (string) $queryWithAction->RemoveAction
            );
        }
        }
        
        if($this->response->SearchRequestGet->SearchCriteriaWithActions->FacetFiltersWithAction){
            $facetFiltersWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->FacetFiltersWithAction->FacetFilterWithAction;
            foreach($facetFiltersWithAction as $facetFilterWithAction){
                    $facetValue = array();
                foreach($facetFilterWithAction->FacetValuesWithAction->FacetValueWithAction as $facetValueWithAction){                   
                    $facetValue[] = array(
                    'Id' => (string)$facetValueWithAction->FacetValue->Id,
                    'value'=>(string)$facetValueWithAction->FacetValue->Value,
                    'removeAction'=>(string)$facetValueWithAction->RemoveAction
                );
                }
                
                $appliedFacets[] = array(
                    'filterId' => (string)$facetFilterWithAction->FilterId,
                    'facetValue'=> $facetValue,
                    'removeAction'=> (string)$facetFilterWithAction->RemoveAction
                );
            }
        }
        
        if($this->response->SearchRequestGet->SearchCriteriaWithActions->LimitersWithAction){
        $limitersWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->LimitersWithAction->LimiterWithAction;
        foreach($limitersWithAction as $limiterWithAction){
            $limiterValue = array(
                'value' => (string) $limiterWithAction->LimiterValuesWithAction->LimiterValueWithAction->Value,
                'removeAction'=> (string) $limiterWithAction->LimiterValuesWithAction->LimiterValueWithAction->RemoveAction
            );
            $appliedLimiters[] = array(
                'Id' => (string)$limiterWithAction->Id,
                'limiterValue'=>$limiterValue,
                'removeAction'=> (string) $limiterWithAction->RemoveAction
            ); 
        }        
        }
        
        if($this->response->SearchRequestGet->SearchCriteriaWithActions->ExpandersWithAction){
        $expandersWithAction = $this->response->SearchRequestGet->SearchCriteriaWithActions->ExpandersWithAction->ExpanderWithAction;
        foreach($expandersWithAction as $expanderWithAction){
            $appliedExpanders[] = array(
                'Id' => (string)$expanderWithAction->Id,
                'removeAction'=>(string)$expanderWithAction->RemoveAction
            );
        }
        }
        
        if ($hits > 0) {
            $records = $this->buildRecords();
            $researchStarters = $this->buildResearchStarters();
            $facets = $this->buildFacets();
        }

        $results = array(
            'recordCount' => $hits,
            'queryString' => $queryString,
            'queries'     => $queries,
            'appliedFacets'=>$appliedFacets,
            'appliedLimiters'=>$appliedLimiters,
            'appliedExpanders'=>$appliedExpanders,
            'records'     => $records,
            'facets'      => $facets,
            'researchStarters'=>$researchStarters
        );

        return $results;
    }


    /**
     * Parse the SimpleXml object when a Search API call was executed
     * and find all records
     *
     * @param none
     *
     * @return array    An associative array of data
     * @access private
     */
private function buildRecords()            
    {
        $results = array();

        $records = $this->response->SearchResult->Data->Records->Record;
        foreach ($records as $record) {
            $result = array();
            $result['AccessLevel'] = $record->Header -> AccessLevel?(string)$record->Header -> AccessLevel:'';
            $result['pubType'] = $record -> Header-> PubType? (string)$record -> Header-> PubType:'';  
            $result['PubTypeId']=$record->Header->PubTypeId? (string) $record->Header->PubTypeId:'';
            $result['ResultId'] = $record->ResultId ? (integer) $record->ResultId : '';
            $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
            $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel:'';
            $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
            $result['PLink'] = $record->PLink ? (string) $record->PLink : '';
            $result['PDF'] = $record->FullText->Links ? (string) $record->FullText->Links->Link->Type : '';
            $result['HTML'] = $record->FullText->Text->Availability? (string) $record->FullText->Text->Availability : '';
            if (!empty($record->ImageInfo->CoverArt)) {
                foreach ($record->ImageInfo->CoverArt as $image) {
                    $size = (string) $image->Size;
                    $target = (string) $image->Target;
                    $result['ImageInfo'][$size] = $target;
                }
            } else {
                $result['ImageInfo'] = '';
            }

            $result['FullText'] = $record->FullText ? (string) $record->FullText : '';

            if ($record->CustomLinks) {
                $result['CustomLinks'] = array();
                foreach ($record->CustomLinks->CustomLink as $customLink) {
                    $category = $customLink->Category ? (string) $customLink->Category : '';
                    $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                    $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                    $name = $customLink->Name ? (string) $customLink->Name : '';
                    $text = $customLink->Text ? (string) $customLink->Text : '';
                    $url = $customLink->Url ? (string) $customLink->Url : '';
                    $result['CustomLinks'][] = array(
                        'Category'      => $category,
                        'Icon'          => $icon,
                        'MouseOverText' => $mouseOverText,
                        'Name'          => $name,
                        'Text'          => $text,
                        'Url'           => $url
                    );
                }
             }
             
             if ($record->FullText->CustomLinks) {
                $result['FullTextCustomLinks'] = array();
                foreach ($record->FullText->CustomLinks->CustomLink as $customLink) {
                    $category = $customLink->Category ? (string) $customLink->Category : '';
                    $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                    $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                    $name = $customLink->Name ? (string) $customLink->Name : '';
                    $text = $customLink->Text ? (string) $customLink->Text : '';
                    $url = $customLink->Url ? (string) $customLink->Url : '';
                    $result['CustomLinks'][] = array(
                        'Category'      => $category,
                        'Icon'          => $icon,
                        'MouseOverText' => $mouseOverText,
                        'Name'          => $name,
                        'Text'          => $text,
                        'Url'           => $url
                    );
                }
             }

            if($record->Items) {
                $result['Items'] = array();
                foreach ($record->Items->Item as $item) {                   
                    $label = $item->Label ? (string) $item->Label : '';
                    $group = $item->Group ? (string) $item->Group : '';
                    $data = $item->Data ? (string) $item->Data : '';
                    $result['Items'][$group][] = array(                     
                        'Label' => $label,
                        'Group' => $group,
                        'Data'  => $this->toHTML($data, $group)
                    );
                }
            }
            
            if($record->RecordInfo){
               $result['RecordInfo'] = array();
               $result['RecordInfo']['BibEntity']=array(
                   'Identifiers'=>array(),
                   'Languages'=>array(),
                   'PhysicalDescription'=>array(),
                   'Subjects'=>array(),
                   'Titles'=>array()
               );
                
               if($record->RecordInfo->BibRecord->BibEntity->Identifiers){
               foreach($record->RecordInfo->BibRecord->BibEntity->Identifiers->Identifier as $identifier){
                   $type = $identifier->Type? (string) $identifier->Type:'';
                   $value = $identifier->Value? (string) $identifier->Value:'';
                   $result['RecordInfo']['BibEntity']['Identifiers'][]= array(
                   'Type'=>$type,
                   'Value'=>$value
                   );
               }            
               }
               
               if($record->RecordInfo->BibRecord->BibEntity->Languages){
               foreach($record->RecordInfo->BibRecord->BibEntity->Languages->Language as $language){
                   $code = $language->Code? (string)$language->Code:'';
                   $text = $language->Text? (string)$language->Text:'';
                   $result['RecordInfo']['BibEntity']['Languages'][]= array(
                   'Code'=>$code,
                   'Text'=>$text
                   );
               }             
               }
               
               if($record->RecordInfo->BibRecord->BibEntity->PhysicalDescription){
               $pageCount = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount:'';
               $startPage = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage:'';
               $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'] = $pageCount;
               $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'] = $startPage;
               }
               
               if($record->RecordInfo->BibRecord->BibEntity->Subjects){
               foreach($record->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject){
                   $subjectFull = $subject->SubjectFull? (string)$subject->SubjectFull:'';
                   $type = $subject->Type? (string)$subject->Type:'';
                   $result['RecordInfo']['BibEntity']['Subjects'][]=array(
                       'SubjectFull'=>$subjectFull,
                       'Type'=>$type
                   );
               }
               }
               
               if($record->RecordInfo->BibRecord->BibEntity->Titles){
               foreach($record->RecordInfo->BibRecord->BibEntity->Titles->Title as $title){
                   $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                   $type = $title->Type? (string)$title->Type:'';
                   $result['RecordInfo']['BibEntity']['Titles'][]=array(
                       'TitleFull'=>$titleFull,
                       'Type'=>$type
                   );
               }
               }
               
               $result['RecordInfo']['BibRelationships']=array(
                   'HasContributorRelationships'=>array(),
                   'IsPartOfRelationships'=>array()                
               );
               
               if($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships){
               foreach($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships->HasContributor as $contributor){
                   $nameFull = isset($contributor->PersonEntity->Name->NameFull)? (string)$contributor->PersonEntity->Name->NameFull:'';
                   $result['RecordInfo']['BibRelationships']['HasContributorRelationships'][]=array(
                       'NameFull'=>$nameFull
                   );
               }
               }
               
               if($record->RecordInfo->BibRecord->BibRelationships){
               foreach($record->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships->IsPartOf as $relationship){
                   if($relationship->BibEntity->Dates){
                       foreach($relationship->BibEntity->Dates->Date as $date){
                   $d = $date->D? (string)$date->D:'';
                   $m = $date->M? (string)$date->M:'';
                   $type = $date->Type? (string)$date->Type:'';
                   $y = $date->Y? (string)$date->Y:'';
                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'][] = array(
                     'D'=> $d,
                     'M'=>$m,
                     'Type'=>$type,
                     'Y'=>$y
                   );
                   }
                   }
                   
                   if($relationship->BibEntity->Identifiers){
                   foreach($relationship->BibEntity->Identifiers->Identifier as $identifier){
                       $type = $identifier->Type? (string) $identifier->Type :'';
                       $value = $identifier->Value? (string) $identifier->Value:'';
                       $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'][]=array(
                           'Type'=>$type,
                           'Value'=>$value
                       );
                   }
                   }
                   
                   if($relationship->BibEntity->Titles){
                       foreach($relationship->BibEntity->Titles->Title as $title){
                          $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                          $type = $title->Type? (string)$title->Type:'';
                           $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'][]=array(
                             'TitleFull' => $titleFull,
                             'Type'=>$type
                           );
                       }
                   }
                   
                   if($relationship->BibEntity->Numbering){
                       foreach($relationship->BibEntity->Numbering->Number as $number){
                        $type = (string)$number->Type;
                        $value= (string)$number->Value;
                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'][] = array(
                     'Type'=> $type,
                     'Value'=>$value
                   );
                   }
                   }
               }
            }
            }

            $results[] = $result;
        }

        return $results;
    }


     /**
     * Parse the SimpleXml object when a Search API call was executed
     * and find all facets
     *
     * @param none
     *
     * @return array    An associative array of data
     * @access private
     */
    private function buildFacets()
    {
        $results = array();
        
        if($this->response->SearchResult->AvailableFacets){
        $facets = $this->response->SearchResult->AvailableFacets->AvailableFacet;
        foreach ($facets as $facet) {
            $values = array();
            foreach ($facet->AvailableFacetValues->AvailableFacetValue as $value) {
                                      
               $values[] = array(
                   'Value'  => (string) $value->Value,
                   'Action' => (string) $value->AddAction,
                   'Count'  => (string) $value->Count
               );
            }
            $id = (string) $facet->Id;
            $label = (string) $facet->Label;
            $results[] = array(
                'Id'     => $id,
                'Label'  => $label,
                'Values' => $values
            );
        }
        }

        return $results;
    }

private function buildResearchStarters()            
    {
        $results = array();
        if (!(isset($this->response->SearchResult->RelatedContent->RelatedRecords))) {
            return $results;
        }
        $records = $this->response->SearchResult->RelatedContent->RelatedRecords->RelatedRecord;
        $count = 0;

        foreach ($records as $relatedrecord) {
            if ((string)$relatedrecord->Type == 'rs') {
                $relatedRecords = $relatedrecord->Records->Record;
                foreach($relatedRecords as $record) {
                    $result = array();                
                    $result['AccessLevel'] = $record->Header -> AccessLevel?(string)$record->Header -> AccessLevel:'';
                    $result['pubType'] = $record -> Header-> PubType? (string)$record -> Header-> PubType:'';  
                    $result['PubTypeId']=$record->Header->PubTypeId? (string) $record->Header->PubTypeId:'';
                    $result['ResultId'] = $record->ResultId ? (integer) $record->ResultId : '';
                    $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
                    $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel:'';
                    $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
                    $result['PLink'] = $record->PLink ? (string) $record->PLink : '';
                    $result['PDF'] = $record->FullText->Links ? (string) $record->FullText->Links->Link->Type : '';
                    //$result['HTML'] = $record->FullText->Text->Availability? (string) $record->FullText->Text->Availability : '';
                    if (!empty($record->ImageInfo->CoverArt)) {
                        foreach ($record->ImageInfo->CoverArt as $image) {
                            $size = (string) $image->Size;
                            $target = (string) $image->Target;
                            $result['ImageInfo'][$size] = $target;
                        }
                    } else {
                        $result['ImageInfo'] = '';
                    }
        
                    $result['FullText'] = $record->FullText ? (string) $record->FullText : '';
        
        
                    if($record->Items) {
                        $result['Items'] = array();
                        foreach ($record->Items->Item as $item) {                   
                            $label = $item->Label ? (string) $item->Label : '';
                            $group = $item->Group ? (string) $item->Group : '';
                            $data = $item->Data ? (string) $item->Data : '';
                            $result['Items'][$group][] = array(                     
                                'Label' => $label,
                                'Group' => $group,
                                'Data'  => $this->toHTML($data, $group)
                            );
                        }
                    }
                    
                    if($record->RecordInfo){
                       $result['RecordInfo'] = array();
                       $result['RecordInfo']['BibEntity']=array(
                           'Identifiers'=>array(),
                           'Languages'=>array(),
                           'PhysicalDescription'=>array(),
                           'Subjects'=>array(),
                           'Titles'=>array()
                       );
                        
                       if($record->RecordInfo->BibRecord->BibEntity->Identifiers){
                       foreach($record->RecordInfo->BibRecord->BibEntity->Identifiers->Identifier as $identifier){
                           $type = $identifier->Type? (string) $identifier->Type:'';
                           $value = $identifier->Value? (string) $identifier->Value:'';
                           $result['RecordInfo']['BibEntity']['Identifiers'][]= array(
                           'Type'=>$type,
                           'Value'=>$value
                           );
                       }            
                       }
                       
                       if($record->RecordInfo->BibRecord->BibEntity->Languages){
                       foreach($record->RecordInfo->BibRecord->BibEntity->Languages->Language as $language){
                           $code = $language->Code? (string)$language->Code:'';
                           $text = $language->Text? (string)$language->Text:'';
                           $result['RecordInfo']['BibEntity']['Languages'][]= array(
                           'Code'=>$code,
                           'Text'=>$text
                           );
                       }             
                       }
                       
                       if($record->RecordInfo->BibRecord->BibEntity->PhysicalDescription){
                       $pageCount = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount:'';
                       $startPage = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage:'';
                       $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'] = $pageCount;
                       $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'] = $startPage;
                       }
                       
                       if($record->RecordInfo->BibRecord->BibEntity->Subjects){
                       foreach($record->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject){
                           $subjectFull = $subject->SubjectFull? (string)$subject->SubjectFull:'';
                           $type = $subject->Type? (string)$subject->Type:'';
                           $result['RecordInfo']['BibEntity']['Subjects'][]=array(
                               'SubjectFull'=>$subjectFull,
                               'Type'=>$type
                           );
                       }
                       }
                       
                       if($record->RecordInfo->BibRecord->BibEntity->Titles){
                       foreach($record->RecordInfo->BibRecord->BibEntity->Titles->Title as $title){
                           $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                           $type = $title->Type? (string)$title->Type:'';
                           $result['RecordInfo']['BibEntity']['Titles'][]=array(
                               'TitleFull'=>$titleFull,
                               'Type'=>$type
                           );
                       }
                       }
                       
                       $result['RecordInfo']['BibRelationships']=array(
                           'HasContributorRelationships'=>array(),
                           'IsPartOfRelationships'=>array()                
                       );
                       
                       if($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships){
                       foreach($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships->HasContributor as $contributor){
                           $nameFull = $contributor->PersonEntity->Name->NameFull? (string)$contributor->PersonEntity->Name->NameFull:'';
                           $result['RecordInfo']['BibRelationships']['HasContributorRelationships'][]=array(
                               'NameFull'=>$nameFull
                           );
                       }
                       }
                       
                       if($record->RecordInfo->BibRecord->BibRelationships){
                       foreach($record->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships->IsPartOf as $relationship){
                           if($relationship->BibEntity->Dates){
                               foreach($relationship->BibEntity->Dates->Date as $date){
                           $d = $date->D? (string)$date->D:'';
                           $m = $date->M? (string)$date->M:'';
                           $type = $date->Type? (string)$date->Type:'';
                           $y = $date->Y? (string)$date->Y:'';
                           $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'][] = array(
                             'D'=> $d,
                             'M'=>$m,
                             'Type'=>$type,
                             'Y'=>$y
                           );
                           }
                           }
                           
                           if($relationship->BibEntity->Identifiers){
                           foreach($relationship->BibEntity->Identifiers->Identifier as $identifier){
                               $type = $identifier->Type? (string) $identifier->Type :'';
                               $value = $identifier->Value? (string) $identifier->Value:'';
                               $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'][]=array(
                                   'Type'=>$type,
                                   'Value'=>$value
                               );
                           }
                           }
                           
                           if($relationship->BibEntity->Titles){
                               foreach($relationship->BibEntity->Titles->Title as $title){
                                  $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                                  $type = $title->Type? (string)$title->Type:'';
                                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'][]=array(
                                     'TitleFull' => $titleFull,
                                     'Type'=>$type
                                   );
                               }
                           }
                           
                           if($relationship->BibEntity->Numbering){
                               foreach($relationship->BibEntity->Numbering->Number as $number){
                                $type = (string)$number->Type;
                                $value= (string)$number->Value;
                           $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'][] = array(
                             'Type'=> $type,
                             'Value'=>$value
                           );
                           }
                           }
                       }
                    }
                    }
            $results[] = $result;

                }

            }
        }

        return $results;
    }
    

    /**
     * Parse the SimpleXml object when an Info API call was executed
     *
     * @param none
     *
     * @return array      An associative array of data
     * @access private
     */
    private function buildInfo()
    {
        // Sort options
        $sort = array();
        foreach ($this->response->AvailableSearchCriteria->AvailableSorts->AvailableSort as $element) {
            $sort[] = array(
                'Id'     => (string) $element->Id,
                'Label'  => (string) $element->Label,
                'Action' => (string) $element->AddAction
            );
        }

        // Search fields
        $search = array();
        foreach ($this->response->AvailableSearchCriteria->AvailableSearchFields->AvailableSearchField as $element) {
            $search[] = array(
                'Label' => (string) $element->Label,
                'Code'  => (string) $element->FieldCode
            );
        }

        // Expanders
        $expanders = array();
        foreach ($this->response->AvailableSearchCriteria->AvailableExpanders->AvailableExpander as $element) {
            $expanders[] = array(
                'Id'     => (string) $element->Id,
                'Label'  => (string) $element->Label,
                'Action' => (string) $element->AddAction,
                'DefaultOn' => (string) $element->DefaultOn
            );
        }

        // Limiters
        $limiters = array();
        foreach ($this->response->AvailableSearchCriteria->AvailableLimiters->AvailableLimiter as $element) {
            $values = array();
            if ($element->LimiterValues) {
                $items = $element->LimiterValues->LimiterValue;                
                foreach($items as $item) {
                    $values[] = array(
                        'Value'  => (string) $item->Value,
                        'Action' => (string) $item->AddAction
                    );
                }
            }
            $limiters[] = array(
                'Id'     => (string) $element->Id,
                'Label'  => (string) $element->Label,
                'Action' => (string) $element->AddAction,
                'Type'   => (string) $element->Type,
                'DefaultOn' => (string) $element->DefaultOn,
                'Order' => (string) $element->Order,
                'values' => $values
            );
        }

        $result = array(
            'sort'      => $sort,
            'search'    => $search,
            'expanders' => $expanders,
            'limiters'  => $limiters
        );

        return $result;
    }


    /**
     * Parse a SimpleXml object when a Retrieve API call was executed
     *
     * @param none
     *
     * @return array      An associative array of data
     * @access private
     */
    private function buildRetrieve()
    {
        $record = $this->response->Record;

        if ($record) {
            $record = $record[0]; // there is only one record
        }

        $result = array();
        $result['AccessLevel'] = $record->Header -> AccessLevel?(string)$record->Header -> AccessLevel:'';
        $result['pubType'] = $record -> Header-> PubType? (string)$record -> Header-> PubType:'';
        $result['PubTypeId']=$record->Header->PubTypeId? (string) $record->Header->PubTypeId:'';
        $result['DbId'] = $record->Header->DbId ? (string) $record->Header->DbId : '';
        $result['DbLabel'] = $record->Header->DbLabel ? (string) $record->Header->DbLabel:'';
        $result['An'] = $record->Header->An ? (string) $record->Header->An : '';
        $result['PLink'] = $record->PLink ? (string) $record->PLink : ''; 
        $result['pdflink'] = $record->FullText->Links ? (string) $record->FullText->Links->Link->Url : '';
        $result['PDF'] = $record->FullText->Links ? (string) $record->FullText->Links->Link->Type : '';
        $value = $record->FullText->Text->Value ? (string) $record->FullText->Text->Value : '';
        $result['htmllink'] = $this->toHTML($value,$group = '');
        $result['HTML'] = $record->FullText->Text->Availability? (string) $record->FullText->Text->Availability : '';
        if (!empty($record->ImageInfo->CoverArt)) {
            foreach ($record->ImageInfo->CoverArt as $image) {
                $size = (string) $image->Size;
                $target = (string) $image->Target;
                $result['ImageInfo'][$size] = $target;
            }
        } else {
            $result['ImageInfo'] = '';
        }
        $result['FullText'] = $record->FullText ? (string) $record->FullText : '';

        if ($record->CustomLinks) {
            $result['CustomLinks'] = array();
            foreach ($record->CustomLinks->CustomLink as $customLink) {
                $category = $customLink->Category ? (string) $customLink->Category : '';
                $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                $name = $customLink->Name ? (string) $customLink->Name : '';
                $text = $customLink->Text ? (string) $customLink->Text : '';
                $url = $customLink->Url ? (string) $customLink->Url : '';
                $result['CustomLinks'][] = array(
                    'Category'      => $category,
                    'Icon'          => $icon,
                    'MouseOverText' => $mouseOverText,
                    'Name'          => $name,
                    'Text'          => $text,
                    'Url'           => $url
                );
            }
        }
        
        if ($record->FullText->CustomLinks) {
                $result['FullTextCustomLinks'] = array();
                foreach ($record->FullText->CustomLinks->CustomLink as $customLink) {
                    $category = $customLink->Category ? (string) $customLink->Category : '';
                    $icon = $customLink->Icon ? (string) $customLink->Icon : '';
                    $mouseOverText = $customLink->MouseOverText ? (string) $customLink->MouseOverText : '';
                    $name = $customLink->Name ? (string) $customLink->Name : '';
                    $text = $customLink->Text ? (string) $customLink->Text : '';
                    $url = $customLink->Url ? (string) $customLink->Url : '';
                    $result['CustomLinks'][] = array(
                        'Category'      => $category,
                        'Icon'          => $icon,
                        'MouseOverText' => $mouseOverText,
                        'Name'          => $name,
                        'Text'          => $text,
                        'Url'           => $url
                    );
                }
             }

        if($record->Items) {
            $result['Items'] = array();
            foreach ($record->Items->Item as $item) {              
                $label = $item->Label ? (string) $item->Label : '';
                $group = $item->Group ? (string) $item->Group : '';
                $data = $item->Data ? (string) $item->Data : '';
                $result['Items'][] = array(                
                    'Label' => $label,
                    'Group' => $group,
                    'Data'  => $this->retrieveHTML($data, $group)
                );
            }
        }
        
        if($record->RecordInfo){
               $result['RecordInfo'] = array();
               $result['RecordInfo']['BibEntity']=array(
                   'Identifiers'=>array(),
                   'Languages'=>array(),
                   'PhysicalDescription'=>array(),
                   'Subjects'=>array(),
                   'Titles'=>array()
               );
                           
               if($record->RecordInfo->BibRecord->BibEntity->Identifiers){
               foreach($record->RecordInfo->BibRecord->BibEntity->Identifiers->Identfier as $identifier){
                   $type = $identifier->Type? (string) $identifier->Type:'';
                   $value = $identifier->Value? (string) $identifier->Value:'';
                   $result['RecordInfo']['BibEntity']['Identifiers'][]= array(
                   'Type'=>$type,
                   'Value'=>$value
                   );
               }            
               }
               
               if($record->RecordInfo->BibRecord->BibEntity->Languages){
               foreach($record->RecordInfo->BibRecord->BibEntity->Languages->Language as $language){
                   $code = $language->Code? (string)$language->Code:'';
                   $text = $language->Text? (string)$language->Text:'';
                   $result['RecordInfo']['BibEntity']['Languages'][]= array(
                   'Code'=>$code,
                   'Text'=>$text
                   );
               }             
               }             
               
               if($record->RecordInfo->BibRecord->BibEntity->PhysicalDescription){
               $pageCount = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->PageCount:'';
               $startPage = $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage? (string) $record->RecordInfo->BibRecord->BibEntity->PhysicalDescription->Pagination->StartPage:'';
               $result['RecordInfo']['BibEntity']['PhysicalDescription']['Pagination'] = $pageCount;
               $result['RecordInfo']['BibEntity']['PhysicalDescription']['StartPage'] = $startPage;
               }
                              
               if($record->RecordInfo->BibRecord->BibEntity->Subjects){
               foreach($record->RecordInfo->BibRecord->BibEntity->Subjects->Subject as $subject){
                   $subjectFull = $subject->SubjectFull? (string)$subject->SubjectFull:'';
                   $type = $subject->Type? (string)$subject->Type:'';
                   $result['RecordInfo']['BibEntity']['Subjects'][]=array(
                       'SubjectFull'=>$subjectFull,
                       'Type'=>$type
                   );
               }
               }
               
               if($record->RecordInfo->BibRecord->BibEntity->Titles){
               foreach($record->RecordInfo->BibRecord->BibEntity->Titles->Title as $title){
                   $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                   $type = $title->Type? (string)$title->Type:'';
                   $result['RecordInfo']['BibEntity']['Titles'][]=array(
                       'TitleFull'=>$titleFull,
                       'Type'=>$type
                   );
               }
               }
               
               $result['RecordInfo']['BibRelationships']=array(
                   'HasContributorRelationships'=>array(),
                   'IsPartOfRelationships'=>array()                
               );
               
               if($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships){
               foreach($record->RecordInfo->BibRecord->BibRelationships->HasContributorRelationships->HasContributor as $contributor){
                   $nameFull = $contributor->PersonEntity->Name->NameFull? (string)$contributor->PersonEntity->Name->NameFull:'';
                   $result['RecordInfo']['BibRelationships']['HasContributorRelationships'][]=array(
                       'NameFull'=>$nameFull
                   );
               }
               }
               
               if($record->RecordInfo->BibRecord->BibRelationships){
               foreach($record->RecordInfo->BibRecord->BibRelationships->IsPartOfRelationships->IsPartOf as $relationship){
                   if($relationship->BibEntity->Dates){
                       foreach($relationship->BibEntity->Dates->Date as $date){
                   $d = $date->D? (string)$date->D:'';
                   $m = $date->M? (string)$date->M:'';
                   $type = $date->Type? (string)$date->Type:'';
                   $y = $date->Y? (string)$date->Y:'';
                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['date'][] = array(
                     'D'=> $d,
                     'M'=>$m,
                     'Type'=>$type,
                     'Y'=>$y
                   );
                   }
                   }
                   
                   if($relationship->BibEntity->Identifiers){
                   foreach($relationship->BibEntity->Identifiers->Identfier as $identifier){
                       $type = $identifier->Type? (string) $identifier->Type :'';
                       $value = $identifier->Value? (string) $identifier->Value:'';
                       $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Identifiers'][]=array(
                           'Type'=>$type,
                           'Value'=>$value
                       );
                   }
                   }
                   
                   if($relationship->BibEntity->Numbering){
                       foreach($relationship->BibEntity->Numbering->Number as $number){
                        $type = (string)$number->Type;
                        $value= (string)$number->Value;
                   $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['numbering'][] = array(
                     'Type'=> $type,
                     'Value'=>$value
                   );
                   }
                   }
                   
                   if($relationship->BibEntity->Titles){
                       foreach($relationship->BibEntity->Titles->Title as $title){
                          $titleFull = $title->TitleFull? (string)$title->TitleFull:'';
                          $type = $title->Type? (string)$title->Type:'';
                           $result['RecordInfo']['BibRelationships']['IsPartOfRelationships']['Titles'][]=array(
                             'TitleFull' => $titleFull,
                             'Type'=>$type
                           );
                       }
                   }
               }
            }
            }

        return $result;
    }


    /**
     * Parse the "inner XML" of a SimpleXml element and 
     * return it as an HTML string
     *
     * @param SimpleXml $element  A SimpleXml DOM
     *
     * @return string            The HTML string
     * @access protected
     */
    private function retrieveHTML($data, $group = '')
    {
        //global $path;
        // Any group can be added here, but we only use Au (Author) 
        // Other groups, not present here, won't be transformed to HTML links
        $allowed_searchlink_groups = array('Au','Su');
        $allowed_link_groups = array('URL');
        // Map xml tags to the HTML tags
        // This is just a small list, the total number of xml tags is far more greater
        $xml_to_html_tags = array(
            '<jsection'    => '<section',
            '</jsection'   => '</section',
            '<highlight'   => '<span class="highlight"',
            '<highligh'    => '<span class="highlight"', // Temporary bug fix
            '</highlight>' => '</span>', // Temporary bug fix
            '</highligh'   => '</span>',
            '<text'        => '<div',
            '</text'       => '</div',
            '<title'       => '<h2',
            '</title'      => '</h2',
            '<anid'        => '<p',
            '</anid'       => '</p',
            '<aug'         => '<strong',
            '</aug'        => '</strong',
            '<hd'          => '<h3',
            '</hd'         => '</h3',
            '<linebr'      => '<br',
            '</linebr'     => '',
            '<olist'       => '<ol',
            '</olist'      => '</ol',
            '<reflink'     => '<a',
            '</reflink'    => '</a',
            '<blist'       => '<p class="blist"',
            '</blist'      => '</p',
            '<bibl'        => '<a',
            '</bibl'       => '</a',
            '<bibtext'     => '<span',
            '</bibtext'    => '</span',
            '<ref'         => '<div class="ref"',
            '</ref'        => '</div',
            '<ulink'       => '<a',
            '</ulink'      => '</a',
            '<superscript' => '<sup',
            '</superscript'=> '</sup',
            '<relatesTo'   => '<sup',
            '</relatesTo'  => '</sup',
            // A very basic security implementation, using a  blackist instead of a whitelist as needed.
            // But the total number of xml tags is so large that we won't build a whitelist right now
            '<script'      => '',
            '</script'     => ''
        );

        // Map xml types to Search types used by the UI
        $xml_to_search_types = array(
            'Au' => 'Author',
            'Su' => 'Subject'
        );

        //  The XML data is XML escaped, let's unescape html entities (e.g. &lt; => <)
        $data = html_entity_decode($data);

        // Start parsing the xml data
        if (!empty($data)) {
            // Replace the XML tags with HTML tags
            $search = array_keys($xml_to_html_tags);
            $replace = array_values($xml_to_html_tags);
            $data = str_replace($search, $replace, $data);

            // Temporary : fix unclosed tags
            $data = preg_replace('/<\/highlight/', '</span>', $data);
            $data = preg_replace('/<\/span>>/', '</span>', $data);
            $data = preg_replace('/<\/searchLink/', '</searchLink>', $data);
            $data = preg_replace('/<\/searchLink>>/', '</searchLink>', $data);

            // Parse searchLinks
            if (!empty($group) && in_array($group, $allowed_searchlink_groups)) {
                $type = $xml_to_search_types[$group];
                $link_xml = '/<searchLink fieldCode="([^"]*)" term="([^"]*)">/';
                $link_html = "<a class=\"searchlinks\" href=\"results.php?query=$2&fieldcode=$1\">";  //replaced $path with "result.php"
                $data = preg_replace($link_xml, $link_html, $data);
                $data = str_replace('</searchLink>', '</a>', $data);
                $data = str_replace('*','',$data);
            }
             // Parse link
            if (!empty($group) && in_array($group, $allowed_link_groups)) {          
                $link_xml = '/<link linkTarget="([^"]*)" linkTerm="([^"]*)" linkWindow="([^"]*)">/';
                $link_html = "<a name=\"$1\" href=\"$2\" target=\"$3\">";  //replaced $path with "result.php"
                $data = preg_replace($link_xml, $link_html, $data);
                $data = str_replace('</link>', '</a>', $data);            
            }
            // Replace the rest of searchLinks with simple spans
            $link_xml = '/<searchLink fieldCode="([^\"]*)" term="%22([^\"]*)%22">/';
            $link_html = '<span>';
            $data = preg_replace($link_xml, $link_html, $data);
            $data = str_replace('</searchLink>', '</span>', $data);
             // Parse bibliography (anchors and links)
            $data = preg_replace('/<a idref="([^\"]*)"/', '<a href="#$1"', $data);
            $data = preg_replace('/<a id="([^\"]*)" idref="([^\"]*)" type="([^\"]*)"/', '<a id="$1" href="#$2"', $data);
        }

        return $data;
    }

    
    private function toHTML($data, $group = '')
    {
        global $path;
        // Any group can be added here, but we only use Au (Author) 
        // Other groups, not present here, won't be transformed to HTML links
        $allowed_searchlink_groups = array('Au','Su');
        $allowed_link_groups = array('URL');
        // Map xml tags to the HTML tags
        // This is just a small list, the total number of xml tags is far more greater
        $xml_to_html_tags = array(
            '<jsection'    => '<section',
            '</jsection'   => '</section',
            '<highlight'   => '<span class="highlight"',
            '<highligh'    => '<span class="highlight"', // Temporary bug fix
            '</highlight>' => '</span>', // Temporary bug fix
            '</highligh'   => '</span>',
            '<text'        => '<div',
            '</text'       => '</div',
            '<title'       => '<h2',
            '</title'      => '</h2',
            '<anid'        => '<p class="ftmetadata"',
            '</anid'       => '</p',
            '<aug'         => '<strong',
            '</aug'        => '</strong',
            '<hd'          => '<h3',
            '</hd'         => '</h3',
            '<linebr'      => '<br',
            '</linebr'     => '',
            '<olist'       => '<ol',
            '</olist'      => '</ol',
            '<reflink'     => '<a',
            '</reflink'    => '</a',
            '<blist'       => '<p class="blist"',
            '</blist'      => '</p',
            '<bibl'        => '<a',
            '</bibl'       => '</a',
            '<bibtext'     => '<span',
            '</bibtext'    => '</span',
            '<ref'         => '<div class="ref"',
            '</ref'        => '</div',
            '<ulink'       => '<a',
            '</ulink'      => '</a',
            '<superscript' => '<sup',
            '</superscript'=> '</sup',
            '<relatesTo'   => '<sup',
            '</relatesTo'  => '</sup',
            // A very basic security implementation, using a  blackist instead of a whitelist as needed.
            // But the total number of xml tags is so large that we won't build a whitelist right now
            '<script'      => '',
            '</script'     => '',
            '<i>'          => '',
            '</i>'         => ''
        );

        // Map xml types to Search types used by the UI
        $xml_to_search_types = array(
            'Au' => 'Author',
            'Su' => 'Subject'
        );

        //  The XML data is XML escaped, let's unescape html entities (e.g. &lt; => <)
        $data = html_entity_decode($data);

        // Start parsing the xml data
        if (!empty($data)) {
            // Replace the XML tags with HTML tags
            $search = array_keys($xml_to_html_tags);
            $replace = array_values($xml_to_html_tags);
            $data = str_replace($search, $replace, $data);

            // Temporary : fix unclosed tags
            $data = preg_replace('/<\/highlight/', '</span>', $data);
            $data = preg_replace('/<\/span>>/', '</span>', $data);
            $data = preg_replace('/<\/searchLink/', '</searchLink>', $data);
            $data = preg_replace('/<\/searchLink>>/', '</searchLink>', $data);

            // Parse searchLinks
            if (!empty($group) && in_array($group, $allowed_searchlink_groups)) {
                $type = $xml_to_search_types[$group];
                $link_xml = '/<searchLink fieldCode="([^"]*)" term="([^"]*)">/';
                $link_html = "<a class=\"searchlinks\" href=\"results.php?query=$2&fieldcode=$1\">";  //replaced $path with "result.php"
                $data = preg_replace($link_xml, $link_html, $data);
                $data = str_replace('</searchLink>', '</a>', $data);
                $data = str_replace('<br />', '; ', $data);
                $data = str_replace('*','',$data);
            }
             // Parse link
            if (!empty($group) && in_array($group, $allowed_link_groups)) {          
                $link_xml = '/<link linkTarget="([^"]*)" linkTerm="([^"]*)" linkWindow="([^"]*)">/';
                $link_html = "<a name=\"$1\" href=\"$2\" target=\"$3\">";  //replaced $path with "result.php"
                $data = preg_replace($link_xml, $link_html, $data);
                $data = str_replace('</link>', '</a>', $data);            
            }
            // Replace the rest of searchLinks with simple spans
            $link_xml = '/<searchLink fieldCode="([^\"]*)" term="%22([^\"]*)%22">/';
            $link_html = '<span>';
            $data = preg_replace($link_xml, $link_html, $data);
            $data = str_replace('</searchLink>', '</span>', $data);
             // Parse bibliography (anchors and links)
            $data = preg_replace('/<a idref="([^\"]*)"/', '<a href="#$1"', $data);
            $data = preg_replace('/<a id="([^\"]*)" idref="([^\"]*)" type="([^\"]*)"/', '<a id="$1" href="#$2"', $data);
        }

        return $data;
    }

}


?>