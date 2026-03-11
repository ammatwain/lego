const SpeechRecognition =
  window.SpeechRecognition || window.webkitSpeechRecognition;

if (SpeechRecognition) {

  const recognition = new SpeechRecognition();

  recognition.lang = "it-IT";
  recognition.continuous = true;
  recognition.interimResults = false;

  let activeField = null;
  let listening = false;

  function insertAtCursor(field, text) {

    const start = field.selectionStart;
    const end = field.selectionEnd;

    field.value =
      field.value.substring(0, start) +
      text +
      field.value.substring(end);

    field.selectionStart = field.selectionEnd = start + text.length;
  }

  function processVoiceCommands(text) {

    text = text.toLowerCase();

    if (text.includes("cancella ultima parola")) {

      activeField.value =
        activeField.value.replace(/\s*\S+\s*$/, " ");

      return "";
    }

    if (text.includes("cancella tutto")) {

      activeField.value = "";
      return "";
    }

    return text
      .replace(/punto/gi, ".")
      .replace(/virgola/gi, ",")
      .replace(/due punti/gi, ":")
      .replace(/punto e virgola/gi, ";")
      .replace(/punto interrogativo/gi, "?")
      .replace(/punto esclamativo/gi, "!")
      .replace(/nuova riga/gi, "\n");
  }

  recognition.onresult = function(event) {

    let transcript = "";

    for (let i = event.resultIndex; i < event.results.length; i++) {

      transcript += event.results[i][0].transcript;

    }

    transcript = processVoiceCommands(transcript);

    if (activeField && transcript) {

      insertAtCursor(activeField, transcript + " ");

    }

  };

  recognition.onend = function() {

    if (listening) {
      recognition.start();
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

      listening = true;
      recognition.start();

      console.log("🎤 dettatura attiva");

    }

    if (e.key === "Escape") {

      listening = false;
      recognition.stop();

      console.log("⏹ dettatura fermata");

    }

  });

}