// public/js/app.js - minimal interactions
document.addEventListener('DOMContentLoaded', function(){
  // progressive enhancement: make cards clickable
  document.querySelectorAll('.card[data-href]').forEach(function(card){
    card.addEventListener('click', function(e){
      if (e.target.tagName.toLowerCase() !== 'a' && e.target.closest('a') === null) {
        window.location = card.getAttribute('data-href');
      }
    })
  });
});
