<?php
namespace Charts_Plots;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";

// starts with the defined constants
if(!defined("BAR_CHART"))  define("BAR_CHART", "bar");
if(!defined("LINE_CHART")) define("LINE_CHART", "line");
if(!defined("PIE_CHART"))  define("PIE_CHART", "pie");
if(!defined("TYPES"))      define("TYPES", [BAR_CHART, LINE_CHART, PIE_CHART]);
if(!defined("CHART_STRT")) define("CHART_STRT", [
	"type" => 'bar',
	"data" => [
		"labels" => [],  // clients useds.
		"datasets" => []  // clients data. Separated by year.
	],
	"options" => [
		"title" => [
			"display" => true,
			"text" => ''
		],
		"maintainAspectRatio" => false,
		"scales" => [
			"yAxes" => [
				"ticks" =>["beginAtZero" => true]
			]
		]
	]
]);



// exceptions generation
// they're actually empty, because i'm too lazy to generate all the child proccesses.

use Exception;
use Core\ClientsAccessData;
use Core\ClientsData;

/**
 * Exception thrown when the number of labels is differtent then the datasets number, it's important to
 */
class IncompatibilityError extends Exception{}

/**
 * Exception thrown when the class try to access the chart array data, but the $gotData param isn't true, that means the class don't
 * have the chart data fully loaded.
 */
class UndefinedChartData extends Exception{}


/**
 * Exception thrown when the class try to load the chart data, but the received chart type isn't valid.
 */
class InvalidChartData extends Exception{}

/**
 * Exception raised when the class try to override the class chart type.
 */
class UnwritebleData extends Exception{}

/**
 * Excpetion thrown when the class try to add a repeated label, but it can't accept repeated labels.
 */
class UniqueLabelError extends Exception{}


// now the fun begins

/**
 * That class represents the access charts, it's technically a globalization of the charts used.
 * That tool will be present in all the processes.
 *
 * @var array $base The chart array base, it'll be converted to JS language (JSON technically)
 * @var string _BGS The default background color used to the data labels.
 * @var string _BDS The default border color used to the data labels.
 * @var integer _BDW The default border width used to the data labels.
 * @var ClientsAccessData $dbHnd The client access database object to handle the database actions.
 * @var boolean UNIQUE_LBS If the chart data manager will only accept distinct labels.
 */
class AccessPlot{

	private $base = [
		"type" => 'bar',
		"data" => [
			"labels" => [],  // clients useds.
			"datasets" => []  // clients data. Separated by year.
		],
		"options" => [
			"title" => [
				"display" => true,
				"text" => null
			],
			"maintainAspectRatio" => false,
			"scales" => [
				"yAxes" => [
					"beginAtZero" => true
				]
			]
		]
	];

	private $dbHnd;
	private $gotDt;

	const _BDS = "rgb(0, 0, 0)";
	const _BGS = "rgb(0, 0, 0)";
	const _BDW = 1;
	const UNIQUE_LBS = false;

	/**
	 * That method starts the class and set the chart type.
	 *
	 * @param string $type The chart type to set.
	 * @throws InvalidChartData If the chart type received (param) isn't at the TYPES array constant.
	 * @throws UnwritebleData If the chart type was already setted
	 */
	public function __construct(string $title, string $type = BAR_CHART){
		if(!in_array($type, TYPES)) throw new InvalidChartData("The type '$type' isn't valid!", 1);
		$this->base['title']['text'] = $title;
		$this->base['type'] = $type;
		$this->dbHnd = new ClientsAccessData("giulliano_php", "");
		$this->gotDt = true;
	}

	/**
	 * That method cleans the chart type and data in it. Making the class able to load another chart data.
	 * @throws UndefinedChartData If the chart type and/or data wasn't defined.
	 * @return void
	 */
	public function flush(){
		$this->base = CHART_STRT;
		$this->gotDt = false;
	}

	/**
	 * Default class method used for the instance garbage collection when it's no more necessary
	 *
	 */
	public function __destruct(){
		if(!$this->gotDt) $this->flush();  // flush the data before collecting the garbage.
	}

	/**
	 * That method checks if a label already exist in the chart labels
	 *
	 * @param string $needle The needle to search as a label
	 * @throws UndefinedChartData If the chart type isn't defined yet
	 * @return boolean
	 */
	private function isLabel(string $needle): bool{
		foreach($this->base['data']['labels'] as $label){
			if($needle == $label) return true;
		}
		return false;
	}

	/**
	 * Default class to add labels at the base. It add a simple label reference
	 *
	 * @param string $label The label to add
	 * @throws UndefinedChartData If the chart type wasn't defined yet.
	 * @throws UniqueLabelError If the chart can't accept repeated label (and the label is repeated)
	 * @return void
	 */
	public function addLabel(string $label){
		if($this->isLabel($label) && self::UNIQUE_LBS)
			throw new UniqueLabelError("The chart don't accept repeated labels, as '$label'", 1);
		array_push($this->base['data']['labels'], $label);
	}

	/**
	 * That method removes a label from the labels array at the chart data.
	 * It will override the original labels array.
	 *
	 * @param string $label The label to remove
	 * @throws UndefinedChartData If the chart type isn't defined yet.
	 * @return void
	 */
	public function rmLabel(string $label){
		$newAr = [];
		foreach($this->base['data']['labels'] as $labelN){
			if($labelN != $label) $newAr[] = $labelN;
		}
		$this->base['data']['labels'] = $newAr;
	}

	/**
	 * That method checks the countage of the items at every parameter, to check if the length of them is the same as
	 * the length of the labels (base[data][labels])
	 *
	 * @param array $data The array with data to check
	 * @param array $bgs The array with the background colors to check
	 * @param array $bds The array with the border colors to check
	 * @param array $bdw The array with the border width to check
	 * @throws UndefinedChartData If the chart type wasn't defined yet.
	 * @return boolean If are the same length of every array, and if the length is the same as the labels array
	 */
	private function ckIncompatibilities(array $data, array $bgs, array $bds, array $bdw): bool{
		$ac = count($this->base['data']['labels']);
		if(count($data) != $ac) return false;
		else if(count($bgs) != $ac) return false;
		else if(count($bds) != $ac) return false;
		else if(count($bdw) != $ac) return false;
		else return true;
	}

	/**
	 * That function generates a array with a specific length and only one value repeated along
	 *
	 * @param integer $times The array length
	 * @param mixed $value The item to repeat
	 * @return array The array generated
	 */
	private static function fillArray(int $times, $value): array{
		$rr = [];
		for($i = 0; $i < $times; $i++) $rr[] = $value;
		return $rr;
	}

	/**
	 * That method adds a new dataset using the parameter received data for each field.
	 *
	 * @param string $label The dataset label
	 * @param array $data The data of each labels (data-labels)
	 * @param string[]|null $bgs The background color of each label (data-labels). If null will get the
	 */
	public function addData(string $label, array $data, array $bgs = null, array $bds = null, array $bdw = null){
		$_bgs = is_null($bgs) ? $this->fillArray(count($data), self::_BGS) : $bgs;
		$_bds = is_null($bds) ? $this->fillArray(count($data), self::_BDS) : $bds;
		$_bdw = is_null($bdw) ? $this->fillArray(count($data), self::_BDW) : $bdw;
		$dataset = [
			"label" => $label,
			"data" => $data,
			"backgroundColor" => $_bgs,
			"borderColor" => $_bds,
			"borderWidth" => $_bdw
		];
		$this->base['data']['datasets'][] = $dataset;
	}

	/**
	 * That method sends the client data to the built-in JS function ::createChart at chart.js file.
	 * @param string $canvas The canvas HTML element id, to generate the chart.
	 * @throws UndefinedChartData If the chart data wasn't defined yet
	 * @return string The JS content with the chart variables.
	 */
	public function generateChart(string $canvas = "client-plot"): string{
		$arrDp = json_encode($this->base);
		return '<script> generateChart("' . $canvas . '", ' . $arrDp . ');</script>';
	}

	/**
	 * That function sets the chart base data as all the clients data, filtering by a proprietary.
	 *
	 * @param string $proprietary The proprietary to search in the database.
	 * @param boolean $override If the method will flush the base before changing the data.
	 * @throws UnwritebleData If the chart already have the data loaded
	 * @return void
	 */
	public function allClientsChart(string $proprietary, bool $override = false){
		if($this->gotDt){
			if($override) $this->flush();
			else throw new UnwritebleData("Can't override the actual chart data", 1);
		}
		$this->base['options']['title']['text'] = "Clients of $proprietary";
		$this->base['options']['type'] = "bar";
		$this->gotDt = true;
		$all_dt = $this->dbHnd->getAllClientsChart($proprietary);
		for($i = 0; $i < count($all_dt['Clients']); $i++) $this->addLabel($all_dt['Clients'][$i]['nm_client']);
		foreach($all_dt as $year => $accesses){
			if($year != "Clients"){
				$this->addData($year, $accesses);
			}
		}
	}

	/**
	 * Sets the chart with the client unsuccessful access.
	 *
	 * @param string $proprietary The proprietary to get
	 * @param boolean $override If the method will override the actual chart.
	 * @throws UnwritebleData If the class chart already got the a chart data.
	 * @return void
	 */
	public function allClientsUnsuccessulChart(string $proprietary, bool $override = false){
		if($this->gotDt){
			if($override) $this->flush();
			else throw new UnwritebleData("Can't override the actual chart data", 1);
		}
		$this->base['options']['title']['text'] = "Clients of $proprietary";
		$this->base['options']['type'] = "bar";
		$this->gotDt = true;
		$all_dt = $this->dbHnd->getAllUnsuccesfulChart($proprietary);
		for($i = 0; $i < count($all_dt['Clients']); $i++) $this->addLabel($all_dt['Clients'][$i]['nm_client']);
		foreach($all_dt as $year => $accesses){
			if($year != "Clients"){
				$this->addData($year, $accesses);
			}
		}
	}

	/**
	 * Sets the chart with the client successful access records
	 *
	 * @param string $proprietary The proprietary to get the clients
	 * @param boolean $override If the method will override the actual chart
	 * @throws UnwritebleData If the class chart is already setted.
	 * @return void
	 */
	public function allClientsSuccessfulChart(string $proprietary, bool $override = false){
		if($this->gotDt){
			if($override) $this->flush();
			else throw new UnwritebleData("Can't override the actual chart data", 1);
		}
		$this->base['options']['title']['text'] = "Succesful Clients of $proprietary";
		$this->base['options']['type'] = "bar";
		$this->gotDt = true;
		$all_dt = $this->dbHnd->getAllSuccessfulChart($proprietary);
		$this->addLabel($all_dt['Clients'][0]['nm_client']);
		foreach($all_dt as $year => $accesses){
			if($year != "Clients"){
				$this->addData($year, $accesses);
			}
		}
	}

	/**
	 * That method set the chart base with all the client accesses, of a specific client.
	 *
	 * @param integer $client_cd The client primary key reference
	 * @param boolean $override If the method will override the
	 * @return void
	 */
	public function getClientAccesses(int $client_cd, bool $override = false){
		if($this->gotDt){
			if($override) $this->flush();
			else throw new UnwritebleData("Can't override the actual chart data", 1);
		}
		$this->base['options']['type'] = "bar";
		$this->gotDt = true;
		$all_dt = $this->dbHnd->getClientAllAccess($client_cd);
		$this->base['options']['title']['text'] = "Access of Client " . $all_dt['Clients'][0]["nm_client"];
		for($i = 0; $i < count($all_dt['Clients']); $i++) $this->addLabel($all_dt['Clients'][$i]['nm_client']);
		foreach($all_dt as $year => $accesses){
			if($year != "Clients"){
				$this->addData($year, $accesses);
			}
		}
	}

	/**
	 * That method set the chart base with the sucessful accesses of a client
	 *
	 * @param integer $client_cd The client primary key reference
	 * @param boolean $override If the method will override the local base
	 * @return void
	 */
	public function getClientSuccessful(int $client_cd, bool $override = false){
		if($this->gotDt){
			if($override) $this->flush();
			else throw new UnwritebleData("Can't override the actual chart data", 1);
		}
		$this->base['options']['type'] = "bar";
		$this->gotDt = true;
		$all_dt = $this->dbHnd->getClientSuccessfulAc($client_cd);
		$this->base['options']['title']['text'] = "Successful access of " . $all_dt['Clients'][0]['nm_client'];
		for($i = 0; $i < count($all_dt['Clients']); $i++) $this->addLabel($all_dt['Clients'][$i]['nm_client']);
		foreach($all_dt as $year => $accesses){
			if($year != "Clients"){
				$this->addData($year, $accesses);
			}
		}
	}

	/**
	 * That method set the chart base with the unsucessful accesses of a client
	 *
	 * @param integer $client_cd The client primary key reference
	 * @param boolean $override If the method will override the local base
	 * @return void
	 */
	public function getClientUnsuccessful(int $client_cd, bool $override = false){
		if($this->gotDt){
			if($override) $this->flush();
			else throw new UnwritebleData("Can't override the actual chart data", 1);
		}
		$this->base['options']['type'] = "bar";
		$this->gotDt = true;
		$all_dt = $this->dbHnd->getClientUnsuccessfulAc($client_cd);
		$this->base['options']['title']['text'] = "Unsuccessful accesses of " . $all_dt['Clients'][0]['nm_client'];
		for($i = 0; $i < count($all_dt['Clients']); $i++) $this->addLabel($all_dt['Clients'][$i]['nm_client']);
		foreach($all_dt as $year => $accesses){
			if($year != "Clients"){
				$this->addData($year, $accesses);
			}
		}
	}

}
?>
