<?php declare(strict_types=1);

// 1. Connexion BDD et Template
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/Template.php';

// 2. Récupération des projets depuis la base
try {
    $stmt = $db->prepare("SELECT * FROM projects ORDER BY project_date DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur récupération projets : " . $e->getMessage());
}

// 3. Initialisation du Template
$template = new Template(__DIR__ . '/templates/layout.php');

// 4. Titre de la page
$template->section('title');
echo 'Portfolio Sophie El Asry - Développeuse Web';
$template->endSection();

// 5. Contenu principal (tout ton HTML d'ancien.html intégré ici)
$template->section('content');
?>
<?php if (isset($_GET['contact'])): 
        $toastType = $_GET['contact'] === 'success' ? 'success' : 'error';
        $toastMsg  = $_GET['contact'] === 'success' 
            ? '✅ Message envoyé ! Merci.' 
            : '❌ Erreur lors de l\'envoi.';
    ?>
    <div id="toast" class="toast toast-<?= $toastType ?> show">
        <?= htmlspecialchars($toastMsg) ?>
    </div>
<?php endif; ?>

<!-- SECTION HERO (Accueil) -->
<section class="accueil">
    <p class="pres">
        À travers chaque projet, je relève des défis techniques avec exigence
        et passion. <br />
        Mon objectif : concrétiser des idées utiles dans un web plus sobre et
        bien fait.
    </p>
    <img
        src="/public/images/arbre-1000x1000-2.svg"
        alt="Arbre de vie dans une main"
        class="arbre"
        id="arbre"
    />
    <div class="col3">
        <h1>
            Sophie <br />
            El Asry
        </h1>
    </div>
</section>

<!-- SECTION PROJETS (Dynamique - Grille régulière) -->
<section class="card-project" id="projects">
    <h2 class="section-title">Mes Projets</h2>
    <div class="projects-grid">
        <?php if (empty($projects)): ?>
            <p class="no-projects">Aucun projet à afficher pour le moment.</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
            <article class="card">
                <div class="card-image">
                    <img src="/public/images/projects/<?= htmlspecialchars($project['image']) ?>"
                    alt="<?= htmlspecialchars($project['title']) ?>" />
                </div>
            <h3><?= htmlspecialchars($project['title']) ?></h3>
            <p class="card-content"><?= htmlspecialchars($project['short_description']) ?></p>
            <?php if (!empty($project['slug'])): ?>
                <a href="/projet.php?slug=<?= htmlspecialchars($project['slug']) ?> "class="card-link">
                <?php else: ?>
                <a href="/projet.php?id=<?= $project['id'] ?>" class="card-link">
            <?php endif; ?>
            Voir le projet</a>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- SECTION À PROPOS  -->
<section class="timeline-section" id="about">
    <h2 class="timeline-title">Mon parcours</h2>
    
    <div class="timeline-container">
        
        <!-- Étape 1 : Passé -->
        <div class="timeline-item">
            <div class="timeline-dot" aria-hidden="true"></div>
            <div class="timeline-content left">
                <span class="timeline-date">Avant</span>
                <h3>Manager en agroalimentaire</h3>
                <p>Responsable de secteur, j'ai dirigé des équipes, géré des urgences et accompagné mes collaborateurs avec <strong>rigueur et bienveillance</strong>.</p>
                <p>Ce que j'aimais : être sur le terrain, résoudre des problèmes concrets, transmettre.</p>
            </div>
        </div>

        <!-- Étape 2 : Le tournant -->
        <div class="timeline-item">
            <div class="timeline-dot highlight" aria-hidden="true"></div>
            <div class="timeline-content right">
                <span class="timeline-date">Le tournant</span>
                <h3>Remise en question</h3>
                <p>Après un burn-out, j'ai pris le temps de me demander ce que je voulais <em>vraiment</em> faire.</p>
                <p>Envie de comprendre, créer, évoluer... Le développement web est devenu une évidence.</p>
            </div>
        </div>

        <!-- Étape 3 : Maintenant -->
        <div class="timeline-item">
            <div class="timeline-dot active" aria-hidden="true"></div>
            <div class="timeline-content left">
                <span class="timeline-date now">Aujourd'hui</span>
                <h3>Développeuse Web & Mobile</h3>
                <p>En formation jusqu'en juin 2026. Je pose des bases solides : HTML, CSS, JavaScript, et monte en compétence PHP/SQL.</p>
                <div class="skills-tags">
                    <span>Frontend</span>
                    <span>Backend</span>
                    <span>Accessibilité</span>
                </div>
            </div>
        </div>

        <!-- Étape 4 : Futur -->
        <div class="timeline-item">
            <div class="timeline-dot future" aria-hidden="true"></div>
            <div class="timeline-content right">
                <span class="timeline-date">Bientôt</span>
                <h3>À la recherche d'un emploi</h3>
                <p>Je veux concevoir des applications <strong>utiles, éthiques et responsables</strong>.</p>
                <p>Convaincue qu'un web plus sobre et plus humain est possible.</p>
                <a href="#contact" class="timeline-cta">Me contacter</a>
            </div>
        </div>

    </div>
</section>


<!-- SECTION AVIS  -->
<h2 id="titreAvis">Qu'en pense t-on?</h2>
<section class="avis-section" id="avis">
    
    <!-- La courbe de Bézier  -->
    <svg class="bezier-line" viewBox="0 0 3000 600" preserveAspectRatio="none" aria-hidden="true">
        <path 
            d="M -70 300 Q 90 330 180 530 T 300 285 T 500 290 T 780 280 T 990 280 T 1240 270 T 1450 290 T 1690 300 T 1980 290 T 2170 280 T 2330 260 T 2540 280 T 2570 300 T 2800 260"
            fill="none"
            stroke="var(--color1)"
            stroke-width="1.5"
            stroke-opacity="0.25"
            stroke-linecap="round"
        />
    </svg>

    <h2 class="avis-title">Témoignages</h2>

    <div class="carousel-multi">
        <button class="carousel-arrow prev" aria-label="Précédent">❮</button>
        
        <div class="carousel-viewport">
            <div class="carousel-track-multi">
                
                <!-- Card 1 -->
                <article class="avis-card-small">
                    <div class="avis-header">
                        <img src="/public/images/prof3.jpg" alt="" class="avis-avatar">
                        <div class="avis-meta">
                            <h4>Marie Dupont</h4>
                            <span>Formatrice DWWM</span>
                        </div>
                    </div>
                    <p>"Sophie apporte une rigueur managériale rare. Sa capacité à structurer un projet est un atout majeur."</p>
                </article>

                <!-- Card 2 -->
                <article class="avis-card-small">
                    <div class="avis-header">
                        <img src="/public/images/prof2.jpg" alt="" class="avis-avatar">
                        <div class="avis-meta">
                            <h4>Jean Martin</h4>
                            <span>Lead Dev</span>
                        </div>
                    </div>
                    <p>"Un sens du détail exemplaire. Ses maquettes sont toujours soignées et pertinentes."</p>
                </article>

                <!-- Card 3 -->
                <article class="avis-card-small">
                    <div class="avis-header">
                        <img src="/public/images/prof1.jpg" alt="" class="avis-avatar">
                        <div class="avis-meta">
                            <h4>Lucas Bernard</h4>
                            <span>Collègue</span>
                        </div>
                    </div>
                    <p>"Communication claire et deadlines respectées. Travail d'équipe impeccable."</p>
                </article>

                <!-- Card 4 -->
                <article class="avis-card-small">
                    <div class="avis-header">
                        <img src="/public/images/prof4.png" alt="" class="avis-avatar">
                        <div class="avis-meta">
                            <h4>Sarah Cohen</h4>
                            <span>Responsable RH</span>
                        </div>
                    </div>
                    <p>"Son parcours atypique lui donne une perspective unique sur les besoins métier."</p>
                </article>

            </div>
        </div>

        <button class="carousel-arrow next" aria-label="Suivant">❯</button>
    </div>
</section>



<!-- SECTION CONTACT  -->
<h4 class="title-reseau">Contact via le formulaire ou les réseaux</h4>
<section class="formContact" id="contact">
    <div class="formulaire">
        <form action="public/send-contact.php" method="POST">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name">
            <label for="email">Adresse e-mail </label>
            <input type="email" id="mail" name="email" required />
            
            <label for="objet">Objet </label>
            <input type="text" id="objet" name="subject" />
            
            <label for="message">Votre message </label>
            <textarea name="message" id="message" rows="5"></textarea>
            
            <input type="submit" name="envoi" id="envoi" value="Envoyer" />
        </form>
    </div>
    <div class="reseaux">
        <a href="https://linkedin.com" target="_blank">
            <img src="/public/images/linkedin.png" alt="logo linkedin" />Sophie El Asry
        </a>
        <br />
        <a href="https://github.com" target="_blank">
            <img src="/public/images/github.png" alt="logo github" />Sophie-EA
        </a>
    </div>
</section>

<?php
$template->endSection();

// 6. Rendu final
$template->render([
    'projects' => $projects
]);
?>
