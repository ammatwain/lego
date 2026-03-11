const SpeechRecognition =
  window.SpeechRecognition || window.webkitSpeechRecognition;

if (SpeechRecognition) {

    const recognition = new SpeechRecognition();

    recognition.lang = "it-IT";
    recognition.continuous = true;
    recognition.interimResults = false;

    let activeField = null;

    function insertAtCursor(field, text) {

        const start = field.selectionStart;
        const end = field.selectionEnd;

        field.value =
            field.value.substring(0, start) +
            text +
            field.value.substring(end);

        field.selectionStart = field.selectionEnd = start + text.length;
    }

    function processCommands(text) {

        return text
            .replace(/punto/gi, ".")
            .replace(/virgola/gi, ",")
            .replace(/due punti/gi, ":")
            .replace(/punto e virgola/gi, ";")
            .replace(/nuova riga/gi, "\n")
            .replace(/punto interrogativo/gi, "?")
            .replace(/punto esclamativo/gi, "!");
    }

    recognition.onresult = function(event) {

        let transcript = "";

        for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }

        transcript = processCommands(transcript);

        if (activeField) {
            insertAtCursor(activeField, transcript + " ");
        }
    };

    document.addEventListener("focusin", e => {

        if (
            e.target.tagName === "TEXTAREA" ||
            (e.target.tagName === "INPUT" && e.target.type === "text")
        ) {
            activeField = e.target;
        }

    });

    document.addEventListener("keydown", e => {

        if (e.ctrlKey && e.code === "Space") {
            e.preventDefault();
            recognition.start();
        }

        if (e.key === "Escape") {
            recognition.stop();
        }

    });

}