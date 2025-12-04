<?php
class Game {
    private $scenario;
    private $state;

    public function __construct($scenario) {
        $this->scenario = $scenario;
        $this->state = [
            'current_node' => 'pre_start',
            'inventory' => [],
            'message' => ''
        ];
    }

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        if (is_array($state)) $this->state = $state;
    }

    public function getCurrentNodeData() {
        return $this->scenario->getNode($this->state['current_node']);
    }

    public function getInventory() {
        return $this->state['inventory'];
    }

    public function getFeedbackMessage() {
        return $this->state['message'];
    }

    private function addInventory($item) {
        if (!in_array($item, $this->state['inventory'])) {
            $this->state['inventory'][] = $item;
        }
    }

    private function removeInventory($item) {
        $this->state['inventory'] = array_values(array_diff($this->state['inventory'], [$item]));
    }

    public function processAction($action, $input = null) {
        $msg = '';
        $this->state['message'] = '';

        if (isset($_SESSION['item_image'])) unset($_SESSION['item_image']);

        switch ($action) {
            case 'start_game':
                $this->state['current_node'] = 'start';
                $this->state['inventory'] = [];
                break;

            case 'restart':
                $this->state['current_node'] = 'pre_start';
                $this->state['inventory'] = [];
                break;

            case 'back_to_living_room':
                $this->state['current_node'] = 'living_room';
                break;

            case 'back_to_previous':
                if ($this->state['current_node'] === 'gate_puzzle') {
                    $this->state['current_node'] = 'start';
                } elseif ($this->state['current_node'] === 'clock_puzzle') {
                    $this->state['current_node'] = 'living_room';
                } elseif ($this->state['current_node'] === 'lab_inside') {
                    $this->state['current_node'] = 'lab_entrance';
                } elseif ($this->state['current_node'] === 'tunnel_entrance') {
                    $this->state['current_node'] = 'lab_entrance';
                } else {
                    $this->state['current_node'] = 'start';
                }
                break;

            case 'inspect_gate':
                $this->state['current_node'] = 'gate_puzzle';
                break;

            case 'search_garden':
                $this->state['current_node'] = 'garden_search';
                $this->addInventory('Catatan Angka');
                break;

            case 'back_to_start':
                $this->state['current_node'] = 'start';
                break;

            case 'solve_gate':
                if (trim($input) === '10,4,60,1,15,6') {
                    $this->state['current_node'] = 'living_room';
                } else {
                    $msg = 'Perkalian';
                }
                break;

            case 'inspect_clock':
                $this->state['current_node'] = 'clock_puzzle';
                break;

            case 'search_desk':
                $this->state['current_node'] = 'desk_search';
                $this->addInventory('Peta');
                break;

            case 'solve_clock':
                if (trim($input) === '08:50') {
                    $this->state['current_node'] = 'lab_entrance';
                } else {
                    $msg = 'Jarum Jam';
                }
                break;

            case 'enter_lab':
                $this->state['current_node'] = 'lab_inside';
                break;

            case 'solve_lab':
                if (trim($input) === '60') {
                    $this->state['current_node'] = 'tunnel_entrance';
                } else {
                    $msg = 'Pola';
                }
                break;

            case 'solve_tunnel':
                $check = strtoupper(preg_replace('/[^UDLR]/', '', $input));
                if ($check === 'RDLULDR') {
                    $this->state['current_node'] = 'final_scene';
                } else {
                    $msg = 'Peta, 7 Pergerakan';
                }
                break;

            case 'end_good':
                $this->state['current_node'] = 'end_good_final';
                break;
            case 'end_neutral':
                $this->state['current_node'] = 'end_neutral_final';
                break;
            case 'end_bad':
                $this->state['current_node'] = 'end_bad_final';
                break;

            default:
                if (strpos($action, 'use::') === 0) {
                    $item = substr($action, 5);
                    if (in_array($item, $this->state['inventory'])) {
                        switch ($item) {
                            case 'Catatan Angka':
                                $_SESSION['item_image'] = 'semak.png';
                                break;
                            case 'Peta':
                                $_SESSION['item_image'] = 'map.jpeg';
                                break;
                            default:
                                $msg .= 'Item ini tampaknya biasa saja.';
                                break;
                        }
                    } else {
                        $msg = 'Error: Item tidak ditemukan di inventori.';
                    }
                } else {
                    $msg = 'Aksi tidak dikenal.';
                }
                break;
        }

        $this->state['message'] = $msg;
    }
}
?>