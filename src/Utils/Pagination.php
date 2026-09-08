<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\Annotation as JMS;

/**
 * Description of Pagination
 *
 */
class Pagination {

	/**
	 * @JMS\Exclude();
	 */
	private $searchParam;
	private $currentPage;
	private $sortField;
	private $sortDirection;
	private $offset;
	private $limit;

	/**
	 * @JMS\Exclude();
	 */
	private $request;
	private $pageSize = 5;
	private $result;
	private $totalResults;

	function __construct() {
		
	}

	function process(Request $request) {
		$this->request = $request;
		$this->searchParam = $this->request->request->all();
		$this->currentPage = $this->request->query->get('page');
		$this->sortField = $this->request->query->get('sort');
		$this->sortDirection = $this->request->query->get('direction');
		$this->currentPage = null == $this->getCurrentPage() ? 1 : $this->getCurrentPage();
		$this->offset = ($this->getCurrentPage() - 1) * $this->pageSize;
		$this->limit = $this->pageSize;
	}

	function getRequest() {
		return $this->request;
	}

	function setRequest($request) {
		$this->request = $request;
	}

	function getSearchParam(): array 
    {
		return $this->searchParam;
	}

	function getCurrentPage() {
		return $this->currentPage;
	}

	function getSortField() {
		return $this->sortField;
	}

	function getSortDirection() {
		return $this->sortDirection;
	}

	function getOffset() {
		return $this->offset;
	}

	function getLimit() {
		return $this->limit;
	}

	function setSearchParam($searchParam) {
		$this->searchParam = $searchParam;
	}

	function setCurrentPage($currentPage) {
		$this->currentPage = $currentPage;
	}

	function setSortField($sortField) {
		$this->sortField = $sortField;
	}

	function setSortDirection($sortDirection) {
		$this->sortDirection = $sortDirection;
	}

	function setOffset($offset) {
		$this->offset = $offset;
	}

	function setLimit($limit) {
		$this->limit = $limit;
	}

	function getPageSize() {
		return $this->pageSize;
	}

	function setPageSize($pageSize) {
		$this->pageSize = $pageSize;
	}

	function getResult() {
		return $this->result;
	}

	function getTotalResults() {
		return $this->totalResults;
	}

	function setResult($result) {
		$this->result = $result;
	}

	function setTotalResults($totalResults) {
		$this->totalResults = $totalResults;
	}

}