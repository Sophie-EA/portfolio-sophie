<article class="projet-detail">
    
    <!-- HERO -->
    <header class="projet-hero">
        <div class="container">
            <h1><?= htmlspecialchars($project['title']) ?></h1>
            
            <?php if (!empty($project['technologies'])): ?>
            <div class="projet-tech">
                <?php 
                // On utilise la virgule comme séparateur (vérifie tes données en BDD)
                $techs = array_filter(array_map('trim', explode(',', $project['technologies'])));
                foreach ($techs as $tech): 
                ?>
                <span class="tech-badge"><?= htmlspecialchars($tech) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="projet-image-hero">
            <img src="/public/images/projects/<?= htmlspecialchars($project['image']) ?>" 
                 alt="<?= htmlspecialchars($project['title']) ?>" />
        </div>
    </header>

    <!-- CONTENU -->
    <div class="projet-content container">
        <div class="projet-grid">
            
            <!-- Description -->
            <div class="projet-description">
                <h2 id="description">À propos du projet</h2>
                <div class="description-text">
                    <?= nl2br(htmlspecialchars($project['description'])) ?>
                </div>
            </div>

            <!-- Sidebar sticky -->
            <aside class="projet-meta">
                <?php if (!empty($project['github_url'])): ?>
                <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" class="btn btn-primary">
                    <span>🐙</span> Voir sur GitHub
                </a>
                <?php endif;
                if (!empty($project['demo_url'])): ?>
                <a href="<?= htmlspecialchars($project['demo_url']) ?>" target="_blank" class="btn btn-secondary">
                    <span>🚀</span> Voir la démo
                </a>
                <?php endif; ?>
                <div class="meta-card">
                    <h3>🎯 Contexte</h3>
                    <ul>
                        <li><strong>Type :</strong> <?= htmlspecialchars($project['type'] ?? 'Projet de formation') ?></li>
                        <li><strong>Rôle :</strong> <?= htmlspecialchars($project['role'] ?? 'Développeuse web') ?></li>
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
                        <a href="#galerie">Galerie</a>
                        <?php if (!empty($project['demo_url'])): ?>
                        <a href="<?= htmlspecialchars($project['demo_url']) ?>" target="_blank">Tester le projet ↗</a>
                        <?php endif; ?>
                    </nav>
                </div>
                <a href="/index.php#projects" class="btn-retour-aside">← Retour aux projets</a>
            </aside>
        </div>

        <!-- GALERIE MASONRY -->
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

    <!-- Retour -->
    <nav class="projet-nav">
        <a href="/index.php#projects" class="btn-retour">← Retour aux projets</a>
    </nav>
</article>
