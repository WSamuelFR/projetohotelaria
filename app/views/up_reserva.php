<?php
require_once('layout/header.php');
require_once('layout/sidebar.php');
$reserva_id = $_GET['id'] ?? null;
?>

<div class="container mt-4">
    <h2 class="text-primary mb-4">Editar Reserva #<?php echo $reserva_id; ?></h2>
    
    <div class="card shadow">
        <div class="card-body">
            <form id="upReservaForm">
                <input type="hidden" name="reserva_id" id="reserva_id" value="<?php echo $reserva_id; ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cliente (Somente Leitura)</label>
                        <input type="text" class="form-control" id="nome_cliente" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quarto (Número)</label>
                        <input type="number" class="form-control" name="quarto" id="quarto" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Data de Check-in</label>
                        <input type="date" class="form-control" name="data_checkin" id="data_checkin" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Data de Check-out</label>
                        <input type="date" class="form-control" name="data_checkout" id="data_checkout" required>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    <a href="lobby.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once('layout/footer.php'); ?>
<script src="../../public/js/up_reserva.js"></script>