<?php

namespace POData\Writers\Json;

use POData\ObjectModel\ODataFeed;
use POData\ObjectModel\ODataEntry;
use POData\ObjectModel\ODataURLCollection;
use POData\ObjectModel\ODataURL;
use POData\ObjectModel\ODataLink;
use POData\ObjectModel\ODataPropertyContent;
use POData\ObjectModel\ODataBagContent;
use POData\ObjectModel\ODataProperty;
use POData\ObjectModel\ODataMediaLink;
use POData\Writers\Json\JsonWriter;
use POData\Writers\BaseODataWriter;
use POData\Common\Version;
use POData\Common\ODataConstants;
use POData\Common\Messages;
use POData\Common\ODataException;
use POData\Common\InvalidOperationException;

/**
 * Class JsonODataV2Writer is a writer for the json format in OData V2 AKA JSON Verbose
 * @package POData\Writers\Json
 */
class JsonODataV2Writer extends JsonODataV1Writer
{
	//The key difference between 1 and 2 is that in 2 collection results
	//are wrapped in a "result" array.  this is to allow a place for collection metadata to be placed
	//
	//IE {d : [ item1, item2, item3] }
	//is now { d : { results :[item1, item2, item3], meta1 : x, meta2 : y }
	//So we override the collection methods to shove this stuff in there

	protected $dataArrayName = ODataConstants::JSON_RESULT_NAME;

	protected $rowCountName = ODataConstants::JSON_ROWCOUNT_STRING;

	protected $nextLinkName = ODataConstants::JSON_NEXT_STRING;

	/**
	 * Write the given OData model in a specific response format
	 *
	 * @param  ODataURL|ODataURLCollection|ODataPropertyContent|ODataFeed|ODataEntry $model Object of requested content.
	 *
	 * @return JsonODataV1Writer
	 */
	public function write($model){
		// { "d" :
		$this->_writer
			->startObjectScope()
			->writeName("d")
			->startObjectScope();


		if ($model instanceof ODataURL) {

			$this->writeURL($model);
		} elseif ($model instanceof ODataURLCollection) {
			$this->writeURLCollection($model);
		} elseif ($model instanceof ODataPropertyContent) {
			$this->writeProperties($model);
		} elseif ($model instanceof ODataFeed) {
			$this->writeFeed($model);
		}elseif ($model instanceof ODataEntry) {
			$this->writeEntry($model);
		}


		$this->_writer->endScope();
		$this->_writer->endScope();

		return $this;
	}


    /** 
     * begin write OData links
     * 
     * @param ODataURLCollection $urls url collection to write
     * 
     * @return JsonODataV2Writer
     */
    public function writeUrlCollection(ODataURLCollection $urls)
    {

        $this->writeRowCount($urls->count);
	    $this->writeNextPageLink($urls->nextPageLink);

        // Json Format V2:
        // "results":
	    $this->_writer
	        ->writeName($this->dataArrayName)
		    ->startArrayScope();

	   	parent::writeUrlCollection($urls);

	    $this->_writer->endScope();

	    return $this;
    }
  
    /**
     * Start writing a feed
     *
     * @param ODataFeed $feed Feed to write
     * 
     * @return JsonODataV2Writer
     */
    protected function writeFeed(ODataFeed $feed)
    {
	    $this->writeRowCount($feed->rowCount);
        $this->writeNextPageLink($feed->nextPageLink);


        // Json Format V2:
        // "results":
	    $this->_writer
		    ->writeName($this->dataArrayName)
	        ->startArrayScope();

	    parent::writeFeed($feed);

	    $this->_writer->endScope();

	    return $this;
    }
  

	/**
	 * Writes the row count.
	 *
	 * @param int $count Row count value.
	 *
	 * @return JsonODataV2Writer
	 */
	protected function writeRowCount($count)
	{
		if ($count != null) {
			$this->_writer->writeName($this->rowCountName);
			$this->_writer->writeValue($count);
		}

		return $this;
	}


	/**
	 * Writes the next page link.
	 *
	 * @param ODataLink $nextPageLinkUri Uri for next page link.
	 *
	 * @return JsonODataV2Writer
	 */
	protected function writeNextPageLink(ODataLink $nextPageLinkUri = null)
	{
		// "__next" : uri
		if ($nextPageLinkUri != null) {
			$this->_writer
				->writeName($this->nextLinkName)
				->writeValue($nextPageLinkUri->url);
		}

		return $this;
	}

}