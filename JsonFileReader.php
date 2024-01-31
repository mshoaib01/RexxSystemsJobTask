<?php
class JsonFileReader
{
    private $data = [];
    public function __construct($filePath) {
        $this->readJsonFile($filePath);
    }

    private function readJsonFile($filePath) {
        try {
            // Check if the file exists
            if (!file_exists($filePath)) {
                throw new Exception("The JSON file does not exist.");
            }

            // Read the file contents
            $jsonContents = file_get_contents($filePath);

            if ($jsonContents === false) {
                throw new Exception("Failed to read the JSON file.");
            }

            // Parse the JSON data
            $jsonData = json_decode($jsonContents, true);

            if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error parsing the JSON file: " . json_last_error_msg());
            }

            $this->data = $jsonData;
        } catch (Exception $e) {
            // Handle exceptions here
            echo "Error: " . $e->getMessage();
        }
    }

    public function getData() {
        return $this->data;
    }
}