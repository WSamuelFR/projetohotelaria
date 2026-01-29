<?php
require_once('../models/reservaModel.php');
$id = $_GET['id'] ?? null;
$model = new ReservaModel();
$dados = $model->getReservaById($id);
echo json_encode($dados);