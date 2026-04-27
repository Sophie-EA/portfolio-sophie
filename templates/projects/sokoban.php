<?php
// templates/projects/sokoban.php
// Fragment inclus dans le layout global par projet.php
?>
<article class="projet-detail sokoban-custom">

  <header class="game-header">
    <a href="/index.php#projects" class="btn-retour">← Retour aux projets</a>
    <div class="level-info">
      Niveau <span id="current-level-num">1</span> : <span id="current-level-name">Nv1</span>
    </div>
  </header>

  <div id="sokoban-wrapper">
    <!-- Le plateau de jeu -->
    <div id="board">
      <div id="floors"></div>
      <div id="goals"></div>     <!-- Cibles (injectées ici) -->
      <div id="walls"></div>     <!-- Murs (injectés ici) -->
      <div id="boxes"></div>     <!-- Caisse (4 img fixes réutilisées) -->

      <!-- Joueur unique -->
      <img id="player" src="/public/projects/sokoban/assets/bas.png" class="piece player" alt="joueur">
    </div>

    <!-- Message de victoire -->
    <div id="msg" hidden></div>

    <!-- Boutons desktop -->
    <div class="controls">
      <button id="back">↩ Annuler</button>
      <button id="reinit">↺ Recommencer</button>
      <button id="next" hidden>Niveau suivant →</button>
    </div>

    <!-- Boutons tactiles (IDs obligatoires pour le JS) -->
    <div class="touch-controls" id="mobile-controls">
      <button id="btn-up">↑</button>
      <div>
        <button id="btn-left">←</button>
        <button id="btn-down">↓</button>
        <button id="btn-right">→</button>
      </div>
    </div>
  </div>

</article>
