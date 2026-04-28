<?php
/**
 * Template Sokoban : structure standard + démo jeu intégrée
 * Variables attendues : $project (array), $galleryImages (array, optionnel)
 */
?>

<article class="projet-detail sokoban-page">

    <!-- ==========================================
         HERO (standard.php)
         ========================================== -->
    <header class="projet-hero">
        <div class="container">
            <h1><?= htmlspecialchars($project['title']) ?></h1>
            
            <?php if (!empty($project['technologies'])): ?>
            <?php $techs = array_filter(array_map('trim', explode(',', $project['technologies']))); ?>
            <div class="projet-tech">
                <?php foreach ($techs as $tech): ?>
                <span class="tech-badge"><?= htmlspecialchars($tech) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($project['image'])): ?>
        <div class="projet-image-hero">
            <img src="/public/images/projects/<?= htmlspecialchars($project['image']) ?>" 
                 alt="<?= htmlspecialchars($project['title']) ?>" />
        </div>
        <?php endif; ?>
    </header>

    <!-- ==========================================
         CONTENU PRINCIPAL (standard grid)
         ========================================== -->
    <div class="projet-content container">
        <div class="projet-grid">

            <!-- Colonne gauche : Description + Jeu + Galerie -->
            <div class="projet-main">

                <!-- DESCRIPTION -->
                <div class="projet-description" id="description">
                    <h2>À propos du projet</h2>
                    <div class="description-text">
                        <?= nl2br(htmlspecialchars($project['description'])) ?>
                    </div>
                </div>

                <!-- SECTION JEU : ta démo interactive -->
                <section class="projet-demo-section" id="demo">
                    <h2>Démo interactive</h2>
                    <p class="demo-intro">
                        Utilisez les <strong>flèches du clavier</strong> ou les 
                        <strong>boutons tactiles</strong> ci-dessous pour jouer.
                        Poussez chaque caisse sur la cible de sa couleur.
                    </p>

                    <!-- Wrapper pour isoler le CSS du jeu du reste du portfolio -->
                    <div class="sokoban-game-outer">
                        
                        <header class="game-header">
                            <h3>Jeu Sokoban</h3>
                            <p class="game-subtitle">Testez mes compétences en résolution de problème !</p>
                        </header>
                        <section class="instructions" aria-labelledby="instructions-heading">
                            <h4 id="instructions-heading">Règles du jeu</h4>
                            <p>Poussez chaque caisse sur la cible de la même couleur.</p>
                            <ul class="ul-instruction">
                                <li class="li-instruction">Le joueur peut se déplacer dans les 4 directions.</li>
                                <li class="li-instruction">Il ne peut pousser qu'une seule caisse à la fois.</li>
                                <li class="li-instruction">Une caisse ne peut pas être tirée.</li>
                                <li class="li-instruction">Utilisez le bouton Retour arrière pour annuler un coup.</li>
                            </ul>
                        </section>

                        <article id="sokoban-game" class="game-wrapper" role="application" aria-label="Sokoban, jeu de puzzle">
                            <div class="container-grid">
                                <div id="board" aria-label="Plateau de jeu Sokoban">
                                    <div id="floors"></div>
                                    <div id="goals"></div>
                                    <div id="walls"></div>
                                    <div id="boxes"></div>
                                    <img id="player" src="/public/projects/sokoban/assets/bas.png" alt="Personnage" />
                                </div>
                            </div>

                            <section class="hud" aria-label="Commandes et informations">
                                <div class="hud-top">
                                    <div id="msg" role="status" aria-live="polite" hidden>
                                      <p></p>
                                      <button type="button" class="msg-close">OK</button>
                                    </div>
                                    <div class="level-info">
                                        <span id="current-level-name">Nv 1</span>
                                        <span id="level-counter">/ 9</span>
                                    </div>
                                </div>

                                <div class="hud-actions">
                                    <button id="back" type="button" aria-label="Annuler le dernier coup" class="btn-retour" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14L4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/></svg>
                                        Retour arrière
                                    </button>
                                    <button id="reinit" type="button" aria-label="Recommencer le niveau" class="btn-retour">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                        Recommencer
                                    </button>
                                    <button id="next" type="button" aria-label="Niveau suivant" class="btn-retour" hidden>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                        Suivant
                                    </button>
                                </div>
                            </section>

                            <nav class="touch-controls" aria-label="Contrôles tactiles directionnels">
                                <div>
                                    <button id="btn-up" aria-label="Haut" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#4a6fa5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
                                    </button>
                                </div>
                                <div>
                                    <button id="btn-left" aria-label="Gauche" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#4a6fa5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button id="btn-down" aria-label="Bas" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#4a6fa5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12l7 7 7-7"/></svg>
                                    </button>
                                    <button id="btn-right" aria-label="Droite" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="#4a6fa5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </nav>

                        </article>

                    </div>
                </section>

                <!-- GALERIE (optionnel, si tu as des screenshots en DB) -->
                <?php if (!empty($galleryImages)): ?>
                <section class="galerie" id="galerie">
                    <h2>Galerie du projet</h2>
                    <div class="galerie-grid">
                        <?php foreach ($galleryImages as $index => $img): ?>
                        <figure class="galerie-item" data-index="<?= $index ?>">
                            <img src="/public/images/projects/<?= htmlspecialchars($img['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($img['alt_text'] ?? 'Capture d\'écran du projet ' . $project['title']) ?>"
                                 loading="lazy">
                            <?php if (!empty($img['alt_text'])): ?>
                            <figcaption><?= htmlspecialchars($img['alt_text']) ?></figcaption>
                            <?php endif; ?>
                        </figure>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

            </div>

            <!-- ==========================================
                 SIDEBAR STICKY (standard.php)
                 ========================================== -->
            <aside class="projet-meta">
                <?php if (!empty($project['github_url'])): ?>
                <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" class="btn btn-primary">
                    <span>🐙</span> Voir sur GitHub
                </a>
                <?php endif; ?>

                <?php if (!empty($project['demo_url'])): ?>
                <a href="<?= htmlspecialchars($project['demo_url']) ?>" target="_blank" class="btn btn-secondary">
                    <span>🚀</span> Voir la démo
                </a>
                <?php endif; ?>

                <div class="meta-card">
                    <h3>🎯 Contexte</h3>
                    <ul>
                        <li><strong>Type :</strong> <?= htmlspecialchars($project['type'] ?? 'Projet de formation') ?></li>
                        <li><strong>Rôle :</strong> <?= htmlspecialchars($project['role'] ?? 'Développeuse web') ?></li>
                        <li><strong>Durée :</strong> <?= htmlspecialchars($project['duration'] ?? '2 semaines') ?></li>
                        <li><strong>Année :</strong> <?= date('Y', strtotime($project['created_at'])) ?></li>
                    </ul>
                </div>

                <?php if (!empty($project['technologies'])): ?>
                <div class="meta-card">
                    <h3>🛠️ Stack technique</h3>
                    <div class="tech-list">
                        <?php foreach ($techs as $tech): ?>
                        <span class="tech-tag-small"><?= htmlspecialchars($tech) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="meta-card">
                    <h3>⚡ Navigation</h3>
                    <nav class="quick">
                        <a href="#description">Description</a>
                        <a href="#demo">Tester le jeu ↗</a>
                        <?php if (!empty($galleryImages)): ?>
                        <a href="#galerie">Galerie</a>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- <a href="/index.php#projects" class="btn-retour-aside">← Retour aux projets</a> -->
            </aside>

        </div>
    </div>

    <!-- RETOUR -->
    <nav class="projet-nav">
        <a href="/index.php#projects" class="btn-retour">← Retour aux projets</a>
    </nav>

</article>

<!-- JS spécifique Sokoban -->
<script src="/public/projects/sokoban/js/game.js"></script>

