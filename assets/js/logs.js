document.addEventListener("DOMContentLoaded", () => { 
    botwriter_logs_countup(); 
});


function botwriter_logs_countup() {
  var i = 1;
  var interval = setInterval(function() {    
    var countupElement = document.getElementById('countup');
    if (countupElement) {
      countupElement.innerHTML = "Working on tasks, every 120s a new one is done (can be modified in settings)... " + i + " seconds";            
    }
    i++;
    // cada 2' refrescar la página
    if (i % 60 == 0) {
      location.reload();
    }
  }, 1000);
}
