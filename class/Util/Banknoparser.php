<?php
namespace Asper\Util;

class BankNoParser{
	
	// Source: https://www.fisc.com.tw/tc/service/branch.aspx
	protected $bankingTxtUrl = "http://www.fisc.com.tw/tc/download/twd.txt";
	protected $cacheFilename = "dat/bankno.json";

	private $fileResource;
	protected $bankInfo;
	protected $bankInfoTitleMapping = [
		'no', 'name', 'abbr'
	];

	public function __construct(){
		if( !$this->is_cacheExist() ){
			$this->parseTxt();
			return;
		}

		$this->loadCache();
	}

	protected function is_cacheExist(){
		return file_exists($this->cacheFilename);
	}

	// load cache data
	protected function loadCache(){
		//load data from json file
		if( !file_exists($this->cacheFilename) ){
			throw new Exception(__CLASS__.": Cache File Not Exists");
		}
		
		$data = file_get_contents($this->cacheFilename);
		$this->bankInfo = json_decode($data, true);
		return true;
	}

	// save data to json file
	protected function saveCache(){		
		$data = json_encode($this->bankInfo, JSON_PRETTY_PRINT );
		return file_put_contents($this->cacheFilename, $data);
	}

	/**
	 * Load Bank Info Text	 
	 */
	protected function loadFile(){
		//load txt file
		try{
			$this->fileResource = fopen($this->bankingTxtUrl, 'r');
		}catch( Exception $e ){
			throw new Exception(__CLASS__.': Banking File open failed.');
		}		
	}

	/**
	 * Close file resource
	 */
	protected function closeFile(){
		fclose($this->fileResource);
	}

	/**
	 * Read file resource one line
	 * @return [string]  read line
	 */
	protected function readLine(){
		//read line by line and decide some situation
		if( feof($this->fileResource) ){ return false; }

		$line = fgets($this->fileResource);

		return $line;
	}


	/**
	 * Parse Txt to Array
	 * @param  [string] $txt 
	 * @return [array]   
	 */
	protected function paserToArray($txt){
		$txt = iconv('Big5', 'UTF-8', $txt);
		$bankNoLength = 8;
		$bankNameLength = 20;

		$data['no'] = mb_substr($txt, 0, $bankNoLength);
		$data['name'] = mb_substr($txt, $bankNoLength, $bankNameLength, 'UTF-8');
		$data['abbr'] = mb_substr($txt, $bankNoLength+$bankNameLength, null, 'UTF-8');
		
		$data = array_map(function($value){
			return preg_replace('/\s+|　/', '', $value);	
		}, $data);
		
		$data = $this->removeInvalidData($data);

		return $data;
	}

	/**
	 * Remove value is invalid like:space
	 * @param  Array  $row [description]
	 * @return [type]      [description]
	 */
	protected function removeInvalidData(Array $row){
		$data = [];
		foreach($row as $key => $value){
			if( !strlen($value) ){ continue; }
			$data[$key] = $value;
		}
		return $data;
	}



	/**
	 * decide data is valid
	 * @param  Array   $data [description]
	 * @return boolean       [description]
	 */
	protected function is_validData(Array $data){
		return true;
	}

	/**
	 * Add main bank name and no to every branch
	 */
	protected function addMain2Branch(){
		foreach($this->bankInfo as $no => $bank){
			if( !isset($bank['branch']) ){ continue; }
			foreach($bank['branch'] as $branchNo => $branch){				
				$this->bankInfo[$no]['branch'][$branchNo]['mainNo'] = $no;
				$this->bankInfo[$no]['branch'][$branchNo]['mainName'] = $bank['main']['name'];
			}
		}
	}

	/**
	 * Main function to start read bank info text
	 * @return [type] [description]
	 */
	protected function parseTxt(){
		//main function to start parse txt
		$this->loadFile();

		// Data Structure
		// [
		// 	'004' => [
		// 		'main' => [
		// 			name' => '臺灣銀行',
		// 			'abbr' => '臺灣銀行',
		// 		],
		// 		'branch' => [
		// 			'0040037' => [
		// 				'name' => '臺灣銀行營業部',
		// 				'abbr' => '臺灣銀行',
		// 			]
		// 			....
		// 		]
		// 	]
		// ]
		while( $line = $this->readLine() ){
			$data = $this->paserToArray($line);

			//decide is need to move on
			if( !$this->is_validData($data) ){ continue; }

			//branch
			if( strlen($data['no']) > 3 ){
				$bankNo = substr($data['no'], 0, 3);
				$this->bankInfo[$bankNo]['branch'][ $data['no'] ] = $data;
				continue;
			}

			//main bank
			$bankNo = $data['no'];
			$this->bankInfo[$bankNo]['main'] = $data;
		}

		$this->closeFile();

		$this->addMain2Branch();

		$this->saveCache();
	}

	/**
	 * Search every column
	 * @param  [string] $search keyword
	 * @param  [array] $bank   Bank info
	 * @return [boolean]       match result
	 */
	protected function searchInfo($search, Array $bank){		
		foreach($bank as $col=>$value){
			if( strpos($value, $search) !== FALSE ){
				return true;
			}
		}
		return false;
	}

	/**
	 * Retrive all main bank data
	 * @param  [interger] $bankNo Bank No, if not will return all bank list
	 * @return [Array]         Bank Info, if bank not exist will return empty aray
	 */
	public function bankList($bankNo=null){
		if( !is_null($bankNo) ){
			return isset($this->bankInfo[$bankNo]) ? $this->bankInfo[$bankNo]['main'] : [];
		}

		$data = [];
		foreach($this->bankInfo as $no => $bank){
			$data[$no] = $bank['main'];
		}
		ksort($data);
		return $data;
	}

	/**
	 * Search all main bank info
	 * @param  [type] $search [description]
	 * @return [type]         [description]
	 */
	public function findBank($search){
		if( !strlen($search) ){ return []; }

		$data = [];
		foreach($this->bankInfo as $no => $bank){
			if( $this->searchInfo($search, $bank['main']) ){
				$data[$no] = $bank['main'];
			}
		}
		ksort($data);
		return $data;
	}

	/**
	 * Retrive all branch bank data
	 * @param  [interger] $bankNo branch No, if not will return all branch list
	 * @return [Array]         branch Info, if branch not exist will return empty aray
	 */
	public function branchList($bankNo=null){
		$data = [];
		foreach($this->bankInfo as $no => $bank){
			if( !isset($bank['branch']) ){ continue; }
			foreach($bank['branch'] as $branchNo => $branch){
				$data[$branchNo] = $branch;
			}
		}
		ksort($data);

		if( !is_null($bankNo) ){
			return isset($data[$bankNo]) ? $data[$bankNo] : [];
		}

		return $data;
	}

	/**
	 * Search all branch bank info
	 * @param  [type] $search [description]
	 * @return [type]         [description]
	 */
	public function findBranch($search=null){
		if( !strlen($search) ){ return []; }

		$data = [];
		foreach($this->bankInfo as $no => $bank){
			if( !isset($bank['branch']) ){ continue; }
			foreach($bank['branch'] as $branchNo => $branch){
				if( $this->searchInfo($search, $branch) ){
					$data[$branchNo] = $branch;
				}
			}
		}
		ksort($data);
		return $data;
	}

	public function branchListByMainNo($bankNo){
		if( isset($this->bankInfo[$bankNo]) ){
			$data = $this->bankInfo[$bankNo]['branch'];
			ksort($data);
			return $data;
		}

		return [];
	}

	// test
	// echo '<pre>';
	// print_r( $bank->bankList() );
	// print_r( $bank->bankList('700') );
	// print_r( $bank->findBank('台灣') );
	// 
	// print_r( $bank->branchList() );
	// print_r( $bank->branchList('7000010') );
	// print_r( $bank->findBranch('劃撥') );
	// print_r( $bank->branchListByMainNo('700') );
	// echo '</pre>';
}