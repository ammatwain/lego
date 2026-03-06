# Diagnosi: Widget nelle sidebar non visibili sul front-end

Questo problema si verifica nei **temi FSE (block theme)** che usano sidebar classiche
registrate con `register_sidebar()` e rese nel template tramite il blocco
`core/widget-group`.

## Causa principale

In WordPress FSE, quando qualcuno apre il **Site Editor** e salva un template,
WordPress crea una copia del template nel database (tabella `wp_posts`,
`post_type = wp_template`). Questa copia ha **la precedenza** sul file su disco
(es. `templates/index.html`).

Durante il salvataggio, il Site Editor può rimuovere o svuotare blocchi che non
gestisce bene — come `<!-- wp:widget-group {"id":"..."} /-->` — lasciando le
sidebar come div vuoti.

## Checklist diagnostica

### 1. Le sidebar esistono?

```bash
wp sidebar list
```

Verifica che le sidebar registrate dal tema compaiano nell'elenco.

### 2. Ci sono widget assegnati?

```bash
wp widget list <sidebar-id>
```

Deve restituire almeno un widget. Se la lista è vuota, il problema è
nell'assegnazione dei widget da wp-admin.

### 3. `dynamic_sidebar()` produce output?

```bash
wp eval 'dynamic_sidebar("<sidebar-id>");'
```

Se produce HTML (es. il calendario), la registrazione e i widget sono ok.
Il problema è nel template.

### 4. Esiste un template nel DB che sovrascrive il file?

```bash
wp post list --post_type=wp_template --fields=ID,post_name,post_status
```

Se compare un record con `post_name` uguale al template che ti interessa
(es. `index`), WordPress usa quello al posto del file su disco.

### 5. Il template nel DB contiene i blocchi widget-group?

```bash
wp post get <ID> --field=post_content | grep widget-group
```

Se non restituisce nulla, i blocchi `widget-group` sono stati rimossi.

## Soluzioni

### Opzione A: Eliminare il template dal DB (ripristina il file)

```bash
wp post delete <ID> --force
```

WordPress tornerà a usare il file `templates/index.html` che contiene i
blocchi `widget-group` corretti.

### Opzione B: Aggiornare il template nel DB

```bash
wp post get <ID> --field=post_content
```

Confronta il contenuto con il file su disco e reinserisci i blocchi mancanti:

```bash
wp post update <ID> --post_content='...(contenuto corretto)...'
```

### Opzione C: Filtro di sicurezza in functions.php

Aggiungere questo filtro garantisce che, anche se il blocco `widget-group` è
presente ma senza inner blocks, i widget vengano comunque renderizzati
chiamando `dynamic_sidebar()`:

```php
add_filter('render_block_core/widget-group', function ($block_content, $parsed_block) {
    $sidebar_id = $parsed_block['attrs']['id'] ?? '';

    if ($sidebar_id && is_active_sidebar($sidebar_id)) {
        ob_start();
        dynamic_sidebar($sidebar_id);
        return ob_get_clean();
    }

    return $block_content;
}, 10, 2);
```

Questo funge da rete di sicurezza permanente: anche se il Site Editor modifica
il template, le sidebar continueranno a funzionare.

## Prevenzione

- Evita di salvare template dal Site Editor se contengono blocchi `widget-group`
- Dopo ogni salvataggio nel Site Editor, verifica con il punto 5 che i blocchi
  siano ancora presenti
- Includi il filtro dell'Opzione C come misura preventiva in tutti i temi FSE
  che usano sidebar classiche
