# Block Editor — Specifiche

## Apertura

1. Il campo mostra una preview cliccabile nel form Filament.
2. Al click si apre un **editor a schermo intero** (overlay fullscreen).

---

## Struttura dell'editor (2 colonne)

3. **Colonna sinistra** — contiene tutto ciò che si usa per costruire:
   - Palette dei **blocchi layout** (contenitori)
   - Palette dei **blocchi di contenuto** (testo, immagine, video…)
   - **Galleria media** del model (immagini trascinabili nei blocchi)
   > **Particolarità dell'uso attuale (traduzioni):** la galleria mostra i media sia del model di traduzione che del model "padre". Da generalizzare in futuro.

4. **Colonna destra** — l'editor vero e proprio:
   - In cima il **wrapper radice** con i controlli di margin/padding.
   - Sotto, il canvas dove si impilano e compongono i blocchi.

---

## Blocchi contenitore (layout)

6. Si trascina un blocco contenitore nel canvas: di default è **1 colonna**.
7. Nell'intestazione del blocco si può cambiare il numero di colonne: **1 / 2 / 3 / 4**.
8. Si possono impilare più contenitori uno sotto l'altro per creare layout complessi.
9. All'interno di ogni colonna si inseriscono i blocchi di contenuto.

### Opzioni di spaziatura (nell'intestazione del blocco contenitore)

10. Ogni blocco contenitore espone nell'intestazione i controlli di spaziatura:
    - **Margin** — 4 campi numerici: top, right, bottom, left (in px)
    - **Padding** — 4 campi numerici: top, right, bottom, left (in px)
11. Per ora i controlli stanno nell'intestazione del blocco; in futuro si valuterà una modale dedicata alle opzioni avanzate.
12. I valori di margin/padding vengono applicati come stile inline sul wrapper HTML del contenitore al momento del salvataggio.

### Responsive

13. I blocchi contenitore sono sempre **responsive**: su mobile le colonne si impilano verticalmente (1 colonna), su desktop si affiancano secondo il layout scelto.
14. L'HTML generato usa classi CSS o media query inline per gestire il breakpoint mobile/desktop (da definire in fase di implementazione: classi Tailwind, Bootstrap grid, o `@media` inline).

---

## Blocchi di contenuto (prima versione)

10. **Testo** — editor HTML ricco (bold, italic, liste, link…).
11. **Immagine** — singola immagine, trascinabile dalla galleria laterale.
12. **Video** — codice embed YouTube (textarea per incollare il codice `<iframe>`).

---

## Salvataggio

13. Il campo Filament è una `textarea` con colonna DB di tipo `longtext`; il block editor è montato sopra quel campo.
14. Al salvataggio l'HTML prodotto dai blocchi **sovrascrive il valore di quel campo** — nessuna struttura JSON separata nel DB.
15. Quell'HTML viene poi usato liberamente dove serve (frontend, PDF, email…).

