<fieldset>
    <form id="myForm">
        <div class="form-group">
            <label for="cartellino">Cartellino</label>
            <input type="text" name="cartellino" value="" placeholder="Inserisci il cartellino interessato"
                class="form-control" id="cartellino">
        </div>
        <div class="form-group">
            <h2 class="page-header page-action-links text-left">Oppure</h2>
            <label for="commessa">Commessa</label>
            <input type="text" name="commessa" value="" placeholder="Inserisci la commessa interessata"
                class="form-control" id="commessa">
        </div>
        <div class="form-group floating-button">
            <button type="submit" class="btn btn-primary btn-lg btn-block">AVANTI</button>
        </div>
    </form>
</fieldset>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script>
    document.getElementById('myForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Impedisce il ricaricamento della pagina
        var cartellinoValue = document.getElementById('cartellino').value;
        var commessaValue = document.getElementById('commessa').value;
        // Verifica che almeno uno dei campi sia compilato
        if (!cartellinoValue && !commessaValue) {
            Swal.fire({
                icon: 'error',
                title: 'Errore',
                text: "Per favore, inserisci un cartellino o una commessa."
            });
            return;
        }
        // Determina il tipo di ricerca da effettuare
        if (cartellinoValue) {
            // Se il campo cartellino è compilato, esegui la ricerca per cartellino
            checkCartellino(cartellinoValue);
        } else {
            // Altrimenti, esegui la ricerca per commessa
            checkCommessa(commessaValue);
        }
    });
    function checkCartellino(cartellino) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_cartellino.php?cartellino=' + cartellino, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.exists) {
                    // Il cartellino esiste, quindi puoi reindirizzare l'utente alla pagina successiva
                    window.location.href = 'add_step2.php?cartellino=' + cartellino;
                } else {
                    // Il cartellino non esiste, mostra un alert SweetAlert2
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: "Il cartellino non esiste. Per favore, inserisci un cartellino valido. Se sei sicuro che dovrebbe essere presente, contatta l'amministratore per aggiornare il database."
                    });
                }
            }
        };
        xhr.send();
    }
    function checkCommessa(commessa) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_commessa.php?commessa=' + commessa, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.exists) {
                    // La commessa esiste, usa il cartellino restituito per reindirizzare l'utente alla pagina successiva
                    window.location.href = 'add_step2.php?cartellino=' + response.cartellino;
                } else {
                    // La commessa non esiste, mostra un alert SweetAlert2
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: "La commessa non esiste. Per favore, inserisci una commessa valida. Se sei sicuro che dovrebbe essere presente, contatta l'amministratore per aggiornare il database."
                    });
                }
            }
        };
        xhr.send();
    }
</script>