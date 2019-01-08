let deferredPrompt;
const btnAdd = document.getElementById("btnAdd");
window.addEventListener('beforeinstallprompt', (e) => {
  // Prevent Chrome 67 and earlier from automatically showing the prompt
  e.preventDefault();
  // Stash the event so it can be triggered later.
  deferredPrompt = e;
  // show the button
  btnAdd.style.display = "block";
  return false;
});
document.getElementById("btnAdd").addEventListener("click", (e) => {
  e.preventDefault();
  deferredPrompt.prompt();
  // Wait for the user to respond to the prompt
  deferredPrompt.userChoice
    .then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        btnAdd.style.display = "none";
        console.log('User accepted the A2HS prompt');
      } else {
        console.log('User dismissed the A2HS prompt');
        btnAdd.style.display = "block";
      }
      deferredPrompt = null;
    });
});
