<?php
require_once __DIR__ . '/../db.php';

class ImportModel {
    public static function importCsv($csvFilePath, $delimiter = ';')
    {
        if (!file_exists($csvFilePath) || !is_readable($csvFilePath)) {
            return ["message" => "Le fichier CSV est introuvable ou illisible.", "status" => "error"];
        }
        $header = null;
        $data = [];
        if (($handle = fopen($csvFilePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    if (count($row) === count($header)) {
                        $data[] = array_combine($header, $row);
                    }
                }
            }
            fclose($handle);
        }
        if (empty($data)) {
            return ["message" => "Le fichier CSV est vide ou mal formaté.", "status" => "error"];
        }
        return ["data" => $data, "status" => "success"];
    }
}
