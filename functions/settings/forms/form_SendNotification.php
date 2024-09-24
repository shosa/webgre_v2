<?php
require_once '../../../config/config.php'; ?>
<label class="alert alert-warning w-100">Permette l'invio di una notifica manuale ad un determinato utente</label>
<form id="notificationForm" class="p-4 border rounded shadow-sm bg-light">
    <!-- Select ID Utente -->
    <div class="form-group">
        <label for="user_id">Seleziona Utente</label>
        <select class="form-control" id="user_id" name="user_id" required>
            <?php
            $conn = getDbInstance();
            $query = "SELECT id, user_name, nome FROM utenti";
            $stmt = $conn->query($query);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                echo "<option value='" . htmlspecialchars($user['id']) . "'>" . htmlspecialchars($user['user_name']) . " | " . htmlspecialchars($user['nome']) . "</option>";
            }
            ?>
        </select>
    </div>

    <!-- Tipo di Notifica con Radio Button e Icone -->
    <div class="form-group">
        <label for="type">Tipo di Notifica</label>
        <div class="d-flex justify-content-start">
            <div class="form-check mr-4">
                <input class="form-check-input" type="radio" name="type" id="type_info" value="info" required>
                <label class="form-check-label" for="type_info">
                    <i class="fas fa-info-circle text-info"></i> Avviso
                </label>
            </div>
            <div class="form-check mr-4">
                <input class="form-check-input" type="radio" name="type" id="type_primary" value="primary" required>
                <label class="form-check-label" for="type_primary">
                    <i class="fas fa-exclamation-circle text-primary"></i> Messaggio
                </label>
            </div>
            <div class="form-check mr-4">
                <input class="form-check-input" type="radio" name="type" id="type_warning" value="warning" required>
                <label class="form-check-label" for="type_warning">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Urgente
                </label>
            </div>
            <div class="form-check mr-4">
                <input class="form-check-input" type="radio" name="type" id="type_success" value="success" required>
                <label class="form-check-label" for="type_success">
                    <i class="fas fa-check-circle text-success"></i> Completo
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="type" id="type_danger" value="danger" required>
                <label class="form-check-label" for="type_danger">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Errore
                </label>
            </div>
        </div>
    </div>

    <!-- Messaggio Notifica -->
    <div class="form-group">
        <label for="message">Messaggio</label>
        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
    </div>

    <!-- Link (Opzionale) -->
    <div class="form-group">
        <label for="link">Link (Opzionale)</label>
        <input type="text" class="form-control" id="link" name="link">
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-success btn-block">Invia Notifica</button>
</form>
