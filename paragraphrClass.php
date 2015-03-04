<?php
class Paragraphr {
	/**
	 * @var array $urlIDVals The 36 characters used for a document's unique URL
	 * identifier. The index of each character is its real "value".
	 * @var int $urlLen The length of each document's unique URL identifier.
	 * @var string $json_dir The directory path for saved json files of the documents
	 * @var string $dl_dir The directory path for txt and zip files of documents to be
	 * downloaded.
	 */
	private $urlIDVals = array('y', 'a', 'b', '1', 'x', 'z', '2', 'c', '3', 'd', 'v', '4', '9', 'e', '5', 'w', '8', '0', 'u', 'f', '7', 'g', '6', 't', 'h', 'i', 'k', 's', 'j', 'q', 'r', 'l', 'm', 'n', 'o', 'p');
	private $urlLen;
	private $json_dir = 'paragraphr-app/json/';
	private $dl_dir = 'wp-content/uploads/files_DL/';

	public function Paragraphr($len=4){
		$this->urlLen = $len;
	}
	/**
	 * Sanitize and clean data.
	 * @param string The data.
	 * @return string The sanitized data.
	 */
	public function sanitize_input($data) {
       $data = trim($data);
       $data = stripslashes($data);
       $data = htmlspecialchars($data);
	   return $data;
	}
	/**
	 * Connect to mySQL database.
	 * @param string Database nickname.
	 * @return Object The database connection.
	 */
	private function db_connect($name){
		require_once('db_connect.php');
		$creds = $dbs[$name];
		$servername = $creds['servername'];
		$database = $creds['database'];
		$conn = new PDO("mysql:host=$servername;dbname=$database", $creds['username'], $creds['password']);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	}
	/**
	 * Get the mySQL rows containing documents that are $num days old.
	 * @param string The number of days the returned documents should be, default 60.
	 * @return Object The relevant mySQL rows.
	 */
	public function get_oldest_entries($num=60){
		$conn = $this->db_connect('myApps');
		$stmt = $conn->prepare("SELECT ID FROM Paragraphr as p WHERE DATEDIFF(NOW(), p.Date) = (?)");
		$stmt->bindParam(1, $num);
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		return $stmt;
	}
	/**
	 * Record an entry in mySQL for saved document, and obtain a unique URL identifier.
	 * Save the JSON object of the document to a file, named by the URL identifier.
	 * @param string The JSON object string.
	 * @return string The unique URL identifier for the document.
	 */
	public function save_JSON($jsonPOST) {
		$url='none';
		$conn = $this->db_connect('myApps');
		$stmt = $conn->prepare("INSERT INTO Paragraphr (Date) VALUES (?)");
		$stmt->bindParam(1, $date);
		$date = date('Y-m-d');
		$stmt->execute();

		$id = $conn->lastInsertID();
		$url = $this->get_unique_url($id);
		$fp = fopen(($this->json_dir).$url.'.json', 'w');
		fwrite($fp, $jsonPOST);
		fclose($fp);

		return $url;
	}
	/**
	 * Convert a JSON-decoded object of the document into a string.
	 * @param string $json The JSON-decoded object (ie its corresponding PHP array).
	 * @param string $output Whether to output the document as HTML or just text, default
	 * html.
	 * @return The document as a string, or -1 if something went wrong.
	 */
	private function convert_JSON($json, $output='html'){
		$doc = '';
		foreach($json as $key => $arr){
			if (!array_key_exists('intro', $arr) || !array_key_exists('pbody', $arr)) {
				return -1;
			}
			if ($output == 'html') {
				$par = '<p>'.$this->sanitize_input($arr['intro']).' '.$this->sanitize_input($arr['pbody']).'</p>';
			} else if ($output == 'text') {
				$par = $arr['intro'].' '.$arr['pbody']."\r\n\r\n";
			}
			$doc .= $par;
		}
		return $doc;
	}
	/**
	 * Get the document as a string in a particular format, given its unique URL 
	 * identifier.
	 * @param string $url The unique URL identifier.
	 * @param string $output The output format (text or HTML). 
	 * @return The document as a string, or -1 if something went wrong.
	 */
	public function get_doc($url, $output){
		$url = $this->sanitize_input($url);
		$file = ($this->json_dir).$url.'.json';
		if (strlen($url) == $this->urlLen && file_exists($file) && $json = file_get_contents($file)){
			$json = json_decode($json, FILE_USE_INCLUDE_PATH);
			return ($json) ? $this->convert_JSON($json, $output) : -1;
		} else {
			return -1;
		}
	}
	/**
	 * Create the downloadable zip of the document in the download directory if it
	 * doesn't exist.
	 * @param string $url The unique URL identifier of the document.
	 * @param sting $format The file type of the downloaded document (ie txt)
	 * @return True if the downloadable zip is in the directory, false if not.
	 */
	public function download($url, $format){
		$url = $this->sanitize_input($url);
		$file = 'paragraphr_'.$url;
		if (!file_exists(($this->dl_dir).$file.$format)) {
			$doc = $this->get_doc($url, 'text');
			if ($doc != -1){
				$fp = fopen(($this->dl_dir).$file.$format, 'w');
				fwrite($fp, $doc);
				fclose($fp);
			}
			$zip = new ZipArchive();
			$zip->open(($this->dl_dir).$file.'.zip', ZipArchive::CREATE);
			$rtn = $zip->addFile(($this->dl_dir).$file.$format, $file.$format);
			$zip->close();
		} else {
			$rtn = true;
		}
		return $rtn;
	}
	/**
	 * Obtain a unique URL identifier for a document given its ID from the mySQL table
	 * @param int The mySQL id.
	 * @return string The unique URL identifier.
	 */
	public function get_unique_URL($id){
		$url = '';
		$n = count($this->urlIDVals);
		while ($id != 0){
			$rem = $id % $n;
			$url = $this->urlIDVals[$rem] . $url;
			$id = floor($id / $n);
		}
		$url = str_repeat($this->urlIDVals[0], $this->urlLen) . $url;
		return substr($url, -($this->urlLen));
	}
	/**
	 * Get the mySQL ID for a document given its unique URL identifier.
	 * @param string The unique URL identifier
	 * @return int The mySQL ID.
	 */
	public function get_unique_ID($url){
		$url = sanitize_input($url);
		$mul = 1;
		$sum = 0;
		for ($i=strlen($url)-1; $i > 0; $i--){
			$val = array_search($url[$i], $this->urlIDVals);
			$sum += $val * $mul;
			$mul *= count($this->urlIDVals);
		}
		return $sum;
	}
}