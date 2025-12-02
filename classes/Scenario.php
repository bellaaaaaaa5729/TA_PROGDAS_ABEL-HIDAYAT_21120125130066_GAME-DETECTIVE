<?php
class Scenario {
    private $data;

    public function __construct($filepath) {
        $json = @file_get_contents($filepath);
        $this->data = json_decode($json, true);
        if (!$this->data) {
            die("Error decoding JSON scenario.");
        }
    }

    public function getNode($key) {
        return $this->data[$key] ?? [
            'title' => 'Error',
            'description' => 'Node tidak ditemukan: ' . htmlspecialchars($key),
            'options' => ['restart' => 'Restart Game']
        ];
    }
}
?>